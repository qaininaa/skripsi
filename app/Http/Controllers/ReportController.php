<?php

namespace App\Http\Controllers;

use App\Domains\Report\Services\IncubatorEntryService;
use App\Domains\Report\Services\EnvironmentalEntryService;
use App\Domains\Report\Services\ReportEntryService;
use App\Models\Report;
use App\Models\ReportApproval;
use App\Domains\User\Models\User;
use App\Services\PersonnelInstanceService;
use App\Services\Reports\ReportViewService;
use App\Services\Reports\ReportWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ReportController extends Controller
{
    public function __construct(
        private ReportViewService $viewService,
        private ReportWorkflowService $workflowService,
        private ReportEntryService $entryService,
        private EnvironmentalEntryService $environmentalEntryService,
        private IncubatorEntryService $incubatorEntryService,
        private PersonnelInstanceService $personnelInstanceService,
    ) {}

    /**
     * index() — Halaman daftar laporan milik semua analis.
     *
     * Route   : GET /dashboard/laporan
     * Data in : URL query string ?status=monitoring (opsional, default 'all')
     * Data out: $items (paginated Collection), $status (string filter aktif), $counts (badge counter)
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        $analystVisibleStatuses = [
            'pending',
            'monitoring',
            'reading',
            'submitted',
            'pending_manager',
            'returned',
        ];

        // Baca filter dari URL. Contoh: /laporan?status=reading → $status = 'reading'
        // Kalau tidak ada query string → default 'all' (tampilkan semua status)
        $status = $request->query('status', 'all');
        if ($status !== 'all' && ! in_array($status, $analystVisibleStatuses, true)) {
            $status = 'all';
        }

        $baseQuery = Report::query()
            ->whereIn('status', $analystVisibleStatuses)
            ->where(function ($query) use ($userId) {
                $query->where('status', '!=', 'returned')
                    ->orWhereHas('approvals', function ($approvalQuery) use ($userId) {
                        $approvalQuery->where('status', 'returned')
                            ->where('returned_to_user_id', $userId);
                    });
            });

        // Hitung jumlah laporan per status untuk badge counter di tab navigasi.
        // Cara kerja chaining-nya:
        //   Report::get(['status'])  → SELECT status FROM reports  (hanya kolom status, hemat memori)
        //   ->groupBy('status')      → kelompokkan jadi Collection of Collections:
        //                              ['monitoring' => [Report, Report], 'reading' => [Report], ...]
        //   ->map->count()           → shorthand untuk ->map(fn($group) => $group->count())
        //                              hasil: ['monitoring' => 2, 'reading' => 1, ...]
        $rawCounts = (clone $baseQuery)->get(['status'])->groupBy('status')->map->count();

        // Bungkus ke collect() dengan default 0 untuk SETIAP status yang mungkin ada.
        // Alasan: kalau belum ada laporan berstatus 'approved', $rawCounts['approved'] tidak ada
        // (undefined), sehingga ?? 0 mencegah error.
        $counts = collect([
            'pending' => $rawCounts['pending'] ?? 0,
            'monitoring' => $rawCounts['monitoring'] ?? 0,
            'reading' => $rawCounts['reading'] ?? 0,
            'submitted' => ($rawCounts['submitted'] ?? 0) + ($rawCounts['pending_manager'] ?? 0),
            'returned' => $rawCounts['returned'] ?? 0,
        ]);

        // Query laporan dengan eager load relasi yang dipakai di kartu/tabel view:
        //   reportType   → nama/jenis laporan (mis: "Udara Ruang Produksi")
        //   approvals.user → riwayat approval beserta nama user approver
        //   lockedByUser   → user yang sedang mengerjakan / memegang kunci laporan
        $query = (clone $baseQuery)
            ->with(['reportType', 'approvals.user', 'lockedByUser'])
            ->orderByDesc('created_at');

        // Tambah klausa WHERE hanya jika ada filter aktif.
        // 'all' → tidak difilter, semua status ikut tampil.
        if ($status !== 'all') {
            if ($status === 'submitted') {
                $query->whereIn('status', ['submitted', 'pending_manager']);
            } else {
                $query->where('status', $status);
            }
        }

        // Paginate 15 item per halaman.
        // withQueryString() → link halaman berikutnya tetap menyertakan ?status=...
        $items = $query->paginate(15)->withQueryString();

        return view('pages.laporan.index', compact('items', 'status', 'counts'));
    }

    /**
     * isi() — Halaman pengisian laporan oleh analis.
     *
     * Route        : GET /dashboard/laporan/{report}
     * Route binding: Laravel otomatis resolve {report} UUID → Model Report via route-model binding.
     */
    public function isi(Report $report)
    {
        $userId = auth()->id();

        // Cek akses laporan yang dikembalikan ke analis tertentu.
        $returnedApproval = null;
        if ($report->status === 'returned') {
            $returnedApproval = $this->workflowService->getReturnedApproval($report);
            if ($returnedApproval
                && $returnedApproval->returned_to_user_id !== null
                && $returnedApproval->returned_to_user_id !== $userId) {
                return redirect()->route('laporan.index')
                    ->with('error', 'Laporan ini dikembalikan ke analis lain dan tidak dapat Anda akses.');
            }
        }

        // Klaim laporan ke analis ini (set status/locked_by sesuai kondisi).
        $this->workflowService->claimReport($report, $userId);
        $report->refresh();

        $this->viewService->loadRelations($report);

        $isEditable = in_array($report->status, ['monitoring', 'reading'])
                      && $report->locked_by === auth()->id();

        $isRevision = ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->exists();

        $viewData = $this->viewService->buildViewData($report, $isEditable);

        return view('pages.laporan.isi', array_merge(
            compact('report', 'returnedApproval', 'isRevision'),
            $viewData
        ));
    }

    /**
     * save() — Simpan semua data laporan yang dikirim dari form.
     *
     * Route   : POST /dashboard/laporan/{report}/save
     * Aksi yang tersedia (dari tombol form):
     *   save              → simpan draft, kunci tetap di analis ini
     *   finish_monitoring → selesaikan monitoring, laporan masuk tahap 'reading'
     *   submit            → kirim ke supervisor (dari tahap 'reading')
     *   submit_revision   → kirim ulang setelah revisi
     *   handover          → lepas kunci agar analis lain bisa melanjutkan
     */
    public function save(Request $request, Report $report)
{
    abort_if(in_array($report->status, ['submitted', 'approved']), 403);
    abort_if($report->locked_by !== auth()->id(), 403);

    // ── Handle personnel page actions (add/remove) ────
    $personnelAction = $request->input('_personnel_action');
    if ($personnelAction === 'add_page') {
        $result = $this->personnelInstanceService->addPage($report);
        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }
    if ($personnelAction && str_starts_with($personnelAction, 'remove_page_')) {
        $pageNum = (int) str_replace('remove_page_', '', $personnelAction);
        $result = $this->personnelInstanceService->removePage($report, $pageNum);
        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    // ── Validasi CFU ──────────────────────────────────
    $invalidFields = $this->environmentalEntryService->validateCfu($request->input('entries', []));
    if (! empty($invalidFields)) {
        return back()
            ->withInput()
            ->withErrors(['cfu' => 'Terdapat ' . count($invalidFields) . ' nilai CFU tidak valid. Nilai yang diperbolehkan: bilangan bulat positif (misal: 1, 250), <1, atau TNTC. Nilai nol, desimal, dan negatif tidak diperbolehkan.']);
    }

    $action = $request->input('action', 'save');

    // ── Simpan semua data form ────────────────────────
    [$savedSectionIds, $hasPersonnelData] = $this->entryService->process($request, $report);

    // ── Validasi inkubator khusus saat finish monitoring ─────────────
    if ($action === 'finish_monitoring') {
        [$incubatorErrors, $firstMissingKey] = $this->incubatorEntryService->validateMonitoringCompletion($request, $report);

        if (! empty($incubatorErrors)) {
            $focusInput = $firstMissingKey ? $this->errorKeyToInputName($firstMissingKey) : null;

            $response = back()
                ->withInput()
                ->withErrors($incubatorErrors);

            if ($focusInput !== null) {
                $response = $response->with('focus_input', $focusInput);
            }

            return $response;
        }
    }

    // ── Catat partisipasi analis ──────────────────────
    $this->workflowService->recordParticipation($report, Auth::id());

    // ── Stamp TTD env section (selain save) ───────────
    $this->workflowService->stampSignatures($report, $savedSectionIds, $action);

    // ── Stamp TTD personnel (terpisah dari env section) ───
    if ($hasPersonnelData) {
        $this->workflowService->stampPersonnelSignature($report, $action);
    }

    // ── Cabang aksi ───────────────────────────────────
    if ($action === 'submit') {
        abort_unless($report->status === 'reading', 403);
        $supervisorId = $request->input('supervisor_id');
        abort_if(empty($supervisorId), 422, 'Pilih supervisor terlebih dahulu.');
        abort_unless(
            User::where('id', $supervisorId)->where('role', 'supervisor')->exists(),
            422,
            'Supervisor tidak valid.'
        );
        $this->workflowService->submit($report, $supervisorId);
        return redirect()->route('laporan.index')
            ->with('success', 'Laporan berhasil dikirim ke supervisor.');
    }

    if ($action === 'finish_monitoring') {
        abort_unless($report->status === 'monitoring', 403);
        $this->workflowService->finishMonitoring($report);
        return redirect()->route('laporan.index')
            ->with('success', 'Monitoring selesai. Laporan masuk ke tahap pembacaan.');
    }

    if ($action === 'submit_revision') {
        abort_unless($report->status === 'monitoring', 403);
        $this->workflowService->submitRevision($report);
        return redirect()->route('laporan.index')
            ->with('success', 'Revisi berhasil dikirim ke supervisor.');
    }

    if ($action === 'handover') {
        $this->workflowService->handover($report);
        return redirect()->route('laporan.index')
            ->with('success', 'Draft tersimpan. Laporan bisa dilanjutkan oleh analis lain.');
    }

    return back()->with('success', 'Data berhasil disimpan sebagai draft.');
}

    private function errorKeyToInputName(string $errorKey): string
    {
        $segments = explode('.', $errorKey);
        if (($segments[0] ?? null) !== 'incubator' || count($segments) < 4) {
            return $errorKey;
        }

        return 'incubator[' . $segments[1] . '][' . $segments[2] . '][' . $segments[3] . ']';
    }

    /**
     * verifyPassword() — Verifikasi password analis via AJAX sebelum aksi penting.
     *
     * Dipakai untuk konfirmasi "tanda tangan digital" sebelum submit laporan.
     * Mengembalikan JSON {ok: true} atau {ok: false, message: '...'}.
     */
    public function verifyPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if ($user->username !== $request->username || ! Hash::check($request->password, $user->password)) {
            return response()->json(['ok' => false, 'message' => 'Username atau password salah.'], 422);
        }

        return response()->json(['ok' => true]);
    }
}
