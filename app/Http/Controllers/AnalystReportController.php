<?php

namespace App\Http\Controllers;

use App\Models\Analyst;
use App\Models\EnvSectionInstance;
use App\Models\Report;
use App\Models\ReportEnvironmentalEntry;
use App\Models\User;
use App\Services\ReportSectionService;
use App\Services\Reports\Sections\SectionInstanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AnalystReportController extends Controller
{
    public function __construct(
        private ReportSectionService $sectionService,
        private SectionInstanceService $instanceService
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
        // Baca filter dari URL. Contoh: /laporan?status=reading → $status = 'reading'
        // Kalau tidak ada query string → default 'all' (tampilkan semua status)
        $status = $request->query('status', 'all');

        // Hitung jumlah laporan per status untuk badge counter di tab navigasi.
        // Cara kerja chaining-nya:
        //   Report::get(['status'])  → SELECT status FROM reports  (hanya kolom status, hemat memori)
        //   ->groupBy('status')      → kelompokkan jadi Collection of Collections:
        //                              ['monitoring' => [Report, Report], 'reading' => [Report], ...]
        //   ->map->count()           → shorthand untuk ->map(fn($group) => $group->count())
        //                              hasil: ['monitoring' => 2, 'reading' => 1, ...]
        $rawCounts = Report::get(['status'])->groupBy('status')->map->count();

        // Bungkus ke collect() dengan default 0 untuk SETIAP status yang mungkin ada.
        // Alasan: kalau belum ada laporan berstatus 'approved', $rawCounts['approved'] tidak ada
        // (undefined), sehingga ?? 0 mencegah error.
        $counts = collect([
            'pending' => $rawCounts['pending'] ?? 0,
            'monitoring' => $rawCounts['monitoring'] ?? 0,
            'reading' => $rawCounts['reading'] ?? 0,
            'submitted' => $rawCounts['submitted'] ?? 0,
            'returned' => $rawCounts['returned'] ?? 0,
            'approved' => $rawCounts['approved'] ?? 0,
        ]);

        // Query laporan dengan eager load relasi yang dipakai di kartu/tabel view:
        //   reportType   → nama/jenis laporan (mis: "Udara Ruang Produksi")
        //   approvals.user → riwayat approval beserta nama user approver
        //   lockedByUser   → user yang sedang mengerjakan / memegang kunci laporan
        $query = Report::with(['reportType', 'approvals.user', 'lockedByUser'])
            ->orderByDesc('created_at');

        // Tambah klausa WHERE hanya jika ada filter aktif.
        // 'all' → tidak difilter, semua status ikut tampil.
        if ($status !== 'all') {
            $query->where('status', $status);
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
     * Alur utama:
     *   1. Cek apakah laporan yang dikembalikan boleh diakses user ini
     *   2. "Claim" laporan (set locked_by = user ini) agar tidak dikerjakan dua orang bersamaan
     *   3. Load semua relasi, hitung kebutuhan seksi, siapkan variabel untuk view
     */
    public function isi(Report $report)
    {
        // Simpan ID user yang login — dipakai berkali-kali di bawah agar tidak memanggil auth()->id() berulang.
        $userId = auth()->id();

        // ── CEGAH AKSES LAPORAN YANG DIKEMBALIKAN KE ANALIS LAIN ──────────────────
        // Jika laporan berstatus 'returned', supervisor sudah mengembalikan ke analis tertentu.
        // Kita ambil data approval-nya SEBELUM status berubah (karena di bawah kita mungkin mengubah status).
        // Ini juga dipakai untuk menampilkan banner "laporan dikembalikan" di view.
        $returnedApproval = null;
        if ($report->status === 'returned') {
            // Cari record approval yang berstatus 'returned', beserta data user yang mengembalikan.
            $returnedApproval = \App\Models\ReportApproval::where('report_id', $report->id)
                ->where('status', 'returned')
                ->with('user')
                ->first();

            // Kolom returned_to_user_id menyimpan UUID analis penerima revisi.
            // Null berarti siapa saja boleh mengambil. Kalau ada dan bukan user ini → tolak.
            if ($returnedApproval
                && $returnedApproval->returned_to_user_id !== null
                && $returnedApproval->returned_to_user_id !== $userId) {
                return redirect()->route('laporan.index')
                    ->with('error', 'Laporan ini dikembalikan ke analis lain dan tidak dapat Anda akses.');
            }
        }

        // ── CLAIM LAPORAN (kunci ke analis ini) ───────────────────────────────────
        // Tiga kondisi yang membolehkan analis "mengambil" laporan:
        //   1. Status 'pending' (baru dibuat admin, belum ada yang kerjakan)
        //   2. Status 'returned' (dikembalikan supervisor, sudah lolos cek di atas)
        //   3. Status 'monitoring' tapi locked_by = null (analis sebelumnya sudah handover)
        // → Semua kasus ini: set status = 'monitoring', locked_by = user ini.
        if (in_array($report->status, ['pending', 'returned'])
            || ($report->status === 'monitoring' && $report->locked_by === null)) {
            $report->update(['status' => 'monitoring', 'locked_by' => $userId]);

        // Kondisi ke-4: Laporan sudah di tahap 'reading' (monitoring selesai)
        // dan belum ada analis pembaca yang mengunci. Analis ini menjadi pembaca pertama.
        } elseif ($report->status === 'reading' && $report->locked_by === null) {
            // Kunci laporan ke analis ini.
            $report->update(['locked_by' => auth()->id()]);

            // Catat ke tabel 'analysts' bahwa user ini adalah analis pembaca (type='reading').
            // updateOrCreate → INSERT jika belum ada, tidak duplikat jika sudah ada.
            Analyst::updateOrCreate([
                'report_id' => $report->id,
                'user_id' => auth()->id(),
                'type' => 'reading',
            ]);

            // Simpan timestamp kapan analis ini mulai membaca.
            // header_data adalah kolom JSON di tabel 'reports' — metadata fleksibel.
            // Struktur: header_data['ttd_reading_timestamps']['{user_id}'] = '2026-04-24 10:00:00'
            // ->fresh() → ambil ulang dari DB (memastikan header_data paling baru).
            $hd = $report->fresh()->header_data ?? [];
            $hd['ttd_reading_timestamps'][(string) auth()->id()] = now()->toDateTimeString();
            $report->update(['header_data' => $hd]);
        }

        // Refresh model dari DB agar perubahan status/locked_by di atas langsung tercermin.
        $report->refresh();

        // Jalankan migrasi format ownership lama ke baru (untuk backward compatibility).
        // Lihat migrateFieldOwners() untuk penjelasan.
        $this->migrateFieldOwners($report);

        // ── EAGER LOAD SEMUA RELASI SEKALIGUS ────────────────────────────────────
        // Memuat relasi dalam satu pemanggilan mencegah N+1 query problem.
        // (N+1 = setiap akses relasi di loop view memicu 1 query baru → sangat lambat)
        $report->load([
            // reportType → seksi → lokasi → ruangan: struktur hierarki tipe laporan
            'reportType.sections.locations.room',
            // frekuensi sampling tiap lokasi (harian/mingguan/dll)
            'reportType.sections.locations.frequency',
            // konfigurasi inkubator yang diperlukan tipe laporan ini (suhu target, dll)
            'reportType.incubatorConfigs',
            // media agar yang dipakai (mis: TSA untuk bakteri, SDA untuk jamur)
            'reportType.media',
            // semua baris data CFU/waktu dari tabel report_environmental_entries
            'environmentalEntries',
            // riwayat approval laporan + nama user yang menyetujui/mengembalikan
            'approvals.user',
            // user yang saat ini memegang kunci laporan
            'lockedByUser',
            // data identitas alat Air Sampler (no. ID, tanggal kalibrasi, dll)
            'instrumentIdentities',
            // data identitas medium (nomor batch, GPT, tanggal kedaluwarsa)
            'mediumIdentities',
            // catatan proses inkubasi + user yang memasukkan cawan ke inkubator
            'incubators.incubatedBy',
            // user yang mengeluarkan cawan dari inkubator
            'incubators.removedBy',
            // daftar analis yang terlibat (monitoring/reading) + profil mereka
            'analysts.user',
            // tanda tangan per seksi per instance + profil user penanda tangan
            'signatures.user',
        ]);

        // ── PANGGIL SERVICE LAYER ─────────────────────────────────────────────────
        // ReportSectionService memisahkan logika kompleks dari controller.

        // buildEntryMap() → bangun array 4-dimensi dari seluruh environmentalEntries:
        //   $entryMap[pivot_id][instance][period][shift] = ReportEnvironmentalEntry
        // pivot_id = ID baris di tabel report_section_location (relasi many-to-many seksi↔lokasi)
        // Tujuan: view bisa langsung akses entry untuk sel tabel tertentu tanpa looping ulang.
        $entryMap        = $this->sectionService->buildEntryMap($report);

        // computeSectionNeeds() → cek apakah tipe laporan ini membutuhkan:
        //   needsAirSampler → ada seksi pengambilan udara
        //   needsInkubator  → ada seksi yang perlu inkubasi
        //   needsMedium     → ada seksi yang memakai medium agar
        $sectionNeeds    = $this->sectionService->computeSectionNeeds($report);

        // Pastikan instance original section tersedia sebelum render.
        $this->instanceService->ensureInstancesInitialized($report);

        // buildSectionInstances() sekarang baca dari env_section_instances.
        $sectionInstances = $this->sectionService->buildSectionInstances($report);

        // Unpack dari array hasil computeSectionNeeds() ke variabel terpisah.
        $needsAirSampler   = $sectionNeeds['needsAirSampler'];
        $needsInkubator    = $sectionNeeds['needsInkubator'];
        $needsMedium       = $sectionNeeds['needsMedium'];

        // ── FLAG EDITABILITY ──────────────────────────────────────────────────────
        // $isEditable = true HANYA jika laporan masih bisa diedit DAN dikunci oleh user ini.
        //   Kondisi AND: status harus monitoring/reading, DAN locked_by = user ini.
        //   Kalau laporan dikunci orang lain → $isEditable = false → view tampil read-only.
        $isEditable        = in_array($report->status, ['monitoring', 'reading'])
                             && $report->locked_by === auth()->id();

        // $isMonitoringPhase mengontrol apakah form seksi 2,3,4 (alat/medium/inkubator)
        // bisa diedit. Seksi itu hanya boleh diisi di fase monitoring, bukan reading.
        $isMonitoringPhase = $report->status === 'monitoring';

        // Shift analis saat ini. TODO: multi-shift belum diimplementasi, hardcode = 1.
        $myShift           = 1;

        // ── SIAPKAN DATA UNTUK VIEW ───────────────────────────────────────────────

        // Ambil satu record identitas instrumen (Air Sampler) — biasanya hanya 1 per laporan.
        $instrument = $report->instrumentIdentities->first();

        // Key incubators by report_type_incubator_id agar view bisa akses by UUID inkubator:
        //   $incubators['uuid-inkubator'] → record Incubator model
        $incubators = $report->incubators->keyBy('report_type_incubator_id');

        // Konfigurasi inkubator dari tipe laporan (misal: inkubator 20-25°C, inkubator 30-35°C).
        $incubatorConfigs = $report->reportType->incubatorConfigs;

        // Key mediums by name agar view bisa akses data medium by nama:
        //   $mediums['TSA'] → MediumIdentity model dengan batch_number, dll.
        $mediums = $report->mediumIdentities->keyBy('name');

        // Filter koleksi analysts berdasarkan type.
        $monitoringAnalysts = $report->analysts->where('type', 'monitoring');
        $readingAnalysts = $report->analysts->where('type', 'reading');

        // Kelompokkan tanda tangan by 'section_id|instance_number'.
        // Tujuan: view bisa filter TTD per instance seksi yang tepat.
        $sectionSignatures = $report->signatures->groupBy(
            fn ($sig) => $sig->section_id . '|' . $sig->instance_number
        );

        // Ambil semua user dengan role 'analis' untuk dropdown pemilih analis.
        $analis = User::where('role', 'analis')->orderBy('name')->get();
        // Analis yang bisa menerima handover = semua analis KECUALI user yang sedang login.
        $otherAnalis = $analis->where('id', '!=', auth()->id())->values();

        // Cek apakah laporan ini pernah masuk ke step 2 approval (sudah pernah disubmit).
        // Dipakai untuk membedakan "submit pertama" vs "submit revisi".
        $isRevision = \App\Models\ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->exists();

        // Kirim semua variabel ke view isi.blade.php.
        return view('pages.laporan.isi', compact(
            'report', 'myShift', 'entryMap',
            'needsAirSampler', 'needsInkubator', 'needsMedium',
            'isEditable', 'isMonitoringPhase', 'analis', 'otherAnalis',
            'sectionInstances', 'returnedApproval', 'isRevision',
            'instrument', 'incubators', 'incubatorConfigs', 'mediums',
            'monitoringAnalysts', 'readingAnalysts', 'sectionSignatures'
        ));
    }

    /**
     * lihat() — Tampilkan laporan dalam mode READ-ONLY (tanpa kunci/claim).
     *
     * Dipakai oleh: admin untuk preview, analis yang bukan pemilik kunci, dst.
     * Menggunakan view yang sama dengan isi(), tapi $isEditable selalu false.
     */
    public function lihat(Report $report)
    {
        // Tidak ada claim, tidak ada perubahan status. Langsung load relasi.
        $report->load([
            'reportType.sections.locations.room',
            'reportType.sections.locations.frequency',
            'reportType.incubatorConfigs',
            'reportType.media',
            'environmentalEntries',
            'approvals.user',
            'lockedByUser',
            'instrumentIdentities',
            'mediumIdentities',
            'incubators.incubatedBy',
            'incubators.removedBy',
            'analysts.user',
            'signatures.user',
        ]);

        $entryMap         = $this->sectionService->buildEntryMap($report);
        $sectionNeeds     = $this->sectionService->computeSectionNeeds($report);
        $this->instanceService->ensureInstancesInitialized($report);
        $sectionInstances = $this->sectionService->buildSectionInstances($report);

        $needsAirSampler   = $sectionNeeds['needsAirSampler'];
        $needsInkubator    = $sectionNeeds['needsInkubator'];
        $needsMedium       = $sectionNeeds['needsMedium'];

        // Selalu false karena ini halaman preview/read-only.
        $isEditable        = false;
        $isMonitoringPhase = $report->status === 'monitoring';
        $myShift           = 1;

        $instrument = $report->instrumentIdentities->first();
        $incubators = $report->incubators->keyBy('report_type_incubator_id');
        $incubatorConfigs = $report->reportType->incubatorConfigs;
        $mediums = $report->mediumIdentities->keyBy('name');
        $monitoringAnalysts = $report->analysts->where('type', 'monitoring');
        $readingAnalysts = $report->analysts->where('type', 'reading');

        $sectionSignatures = $report->signatures->groupBy(
            fn ($sig) => $sig->section_id . '|' . $sig->instance_number
        );

        $analis = User::where('role', 'analis')->orderBy('name')->get();
        $otherAnalis = $analis->where('id', '!=', auth()->id())->values();
        // Flag khusus untuk admin agar view bisa tampilkan badge "Admin Preview".
        $isAdminPreview = auth()->user()->role === 'admin';

        return view('pages.laporan.isi', compact(
            'report', 'myShift', 'entryMap',
            'needsAirSampler', 'needsInkubator', 'needsMedium',
            'isEditable', 'isMonitoringPhase', 'analis', 'otherAnalis',
            'sectionInstances', 'isAdminPreview',
            'instrument', 'incubators', 'incubatorConfigs', 'mediums',
            'monitoringAnalysts', 'readingAnalysts', 'sectionSignatures'
        ));
    }

    /**
     * save() — Simpan semua data laporan yang dikirim dari form.
     *
     * Route   : POST /dashboard/laporan/{report}/save
     * Data in : $request berisi field-field form:
     *   - entries[pivot_id][instance][period][shift][cfu_bacteria|cfu_fungi|start_time] → data tabel CFU
     *   - header_data[...] → metadata (analis, shift, jam)
     *   - air_sampler[...] → data identitas Air Sampler
     *   - medium[nama_medium][...] → data identitas medium
     *   - incubator[uuid][...] → data proses inkubasi
     *   - action → aksi yang dilakukan (save/submit/finish_monitoring/dll)
     */
    public function save(Request $request, Report $report)
    {
        // Tolak jika laporan sudah final (submitted/approved → tidak boleh diubah lagi).
        abort_if(in_array($report->status, ['submitted', 'approved']), 403);
        // Tolak jika user ini bukan pemegang kunci (mencegah race condition dua analis).
        abort_if($report->locked_by !== auth()->id(), 403);

        // ── VALIDASI NILAI CFU SEBELUM DISIMPAN ────────────────────────────────
        // Nilai CFU yang valid: '<1', 'TNTC', atau bilangan bulat positif (1, 5, 250, ...).
        // Nilai 0, desimal, negatif → TIDAK valid.
        // Kita loop manual karena struktur entries adalah array bersarang 4 level:
        //   entries[pivot_id][instance_number][period_number][shift][cfu_bacteria/cfu_fungi]
        $entries = $request->input('entries', []);
        $cfuPattern = '/^(<1|TNTC|[1-9][0-9]*)$/i';
        $invalidFields = [];

        // Loop level 1: pivot_id (ID relasi seksi↔lokasi)
        foreach ($entries as $sectionKey => $instanceMap) {
            if (! is_array($instanceMap)) {
                continue; // skip jika bukan array (keamanan input)
            }
            // Loop level 2: instance_number (laporan yang diduplikasi bisa punya > 1 instance)
            foreach ($instanceMap as $instanceKey => $periodMap) {
                if (! is_array($periodMap)) {
                    continue;
                }
                // Loop level 3: period_number (kolom 0=Machine Setup, 1..n=kolom paparan)
                foreach ($periodMap as $periodKey => $shiftMap) {
                    if (! is_array($shiftMap)) {
                        continue;
                    }
                    // Loop level 4: shift (saat ini selalu 1)
                    foreach ($shiftMap as $shiftKey => $data) {
                        if (! is_array($data)) {
                            continue;
                        }
                        // Cek kedua field CFU di setiap sel tabel.
                        foreach (['cfu_bacteria', 'cfu_fungi'] as $field) {
                            $v = trim((string) ($data[$field] ?? ''));
                            // Kosong → oke (tidak wajib diisi). Ada isi tapi tidak match pola → invalid.
                            if ($v !== '' && ! preg_match($cfuPattern, $v)) {
                                $invalidFields[] = "entries.{$sectionKey}.{$instanceKey}.{$periodKey}.{$shiftKey}.{$field}";
                            }
                        }
                    }
                }
            }
        }

        // Jika ada field CFU yang invalid, kembalikan ke form dengan pesan error.
        // withInput() → isi form tidak hilang (user tidak perlu ketik ulang).
        if (! empty($invalidFields)) {
            return back()
                ->withInput()
                ->withErrors(['cfu' => 'Terdapat '.count($invalidFields).' nilai CFU tidak valid. Nilai yang diperbolehkan: bilangan bulat positif (misal: 1, 250), <1, atau TNTC. Nilai nol, desimal, dan negatif tidak diperbolehkan.']);
        }

        // ── SIMPAN SEMUA DATA FORM KE DATABASE ───────────────────────────────────
        // processEntries() menangani simpan medium, alat, inkubator, jam, dan CFU.
        // Mengembalikan $savedSectionIds: array ['section_uuid|instance' => true]
        // berisi seksi yang punya data baru (dipakai untuk stamp tanda tangan).
        $savedSectionIds = $this->processEntries($request, $report);

        // Baca aksi yang dikirim dari tombol form (value attribute button).
        // Default 'save' jika tidak ada (simpan draft).
        $action = $request->input('action', 'save');

        // Catat partisipasi analis di tabel 'analysts' (idempoten = aman dipanggil berkali-kali).
        // Tipe: 'reading' jika laporan di fase reading, 'monitoring' untuk fase lainnya.
        $analystType = $report->status === 'reading' ? 'reading' : 'monitoring';
        Analyst::updateOrCreate([
            'report_id' => $report->id,
            'user_id'   => Auth::id(),
            'type'      => $analystType,
        ]);

        // ── STAMP TANDA TANGAN PER SEKSI ─────────────────────────────────────────
        // Tanda tangan hanya di-stamp jika aksi BUKAN 'save' (draft).
        // Aksi yang memicu tanda tangan: finish_monitoring, submit, submit_revision.
        if ($action !== 'save' && ! empty($savedSectionIds)) {
            $role = $report->status === 'reading' ? 'reading' : 'monitoring';
            // Loop tiap seksi yang baru diisi datanya.
            // Key format: 'section_uuid|instance_number' → ambil keduanya.
            foreach (array_keys($savedSectionIds) as $_sidInst) {
                [$_secId, $_instNum] = explode('|', $_sidInst, 2) + [1 => '1'];
                \App\Models\ReportSignature::updateOrCreate(
                    [
                        'report_id'       => $report->id,
                        'section_id'      => $_secId,
                        'instance_number' => (int) $_instNum,
                        'user_id'         => Auth::id(),
                        'role'            => $role,
                    ],
                    ['signed_at' => now()]
                );
            }
        }

        // ── CABANG AKSI ───────────────────────────────────────────────────────────────

        // AKSI: submit — analis selesai membaca, kirim ke supervisor.
        // Syarat: laporan harus di fase 'reading' (bukan monitoring).
        if ($action === 'submit') {
            abort_unless($report->status === 'reading', 403);
            $supervisorId = $request->input('supervisor_id');
            abort_if(empty($supervisorId), 422, 'Pilih supervisor terlebih dahulu.');
            // Validasi bahwa ID yang dikirim benar-benar user dengan role supervisor.
            abort_unless(
                User::where('id', $supervisorId)->where('role', 'supervisor')->exists(),
                422,
                'Supervisor tidak valid.'
            );

            // Tandai tanda tangan monitoring+reading sebagai sudah ditandatangani.
            $freshHd = $this->markAnalystSignaturesAsSigned($report->fresh()->header_data ?? [], $report);
            // Ubah status laporan ke 'submitted', lepas kunci (locked_by = null).
            $report->update(['header_data' => $freshHd, 'status' => 'submitted', 'locked_by' => null]);

            // Buat/update record approval step 2 (Supervisor) dengan status 'pending'.
            // Step 2 = review supervisor. Step 3 = review manager (belum diimplementasi di sini).
            \App\Models\ReportApproval::updateOrCreate(
                ['report_id' => $report->id, 'step' => 2],
                ['role' => 'Supervisor', 'user_id' => $supervisorId, 'status' => 'pending',
                    'signed_at' => null, 'notes' => null, 'returned_to_user_id' => null]
            );

            return redirect()->route('laporan.index')
                ->with('success', 'Laporan berhasil dikirim ke supervisor.');
        }

        // AKSI: finish_monitoring — analis selesai monitoring, laporan masuk ke fase 'reading'.
        // Di fase reading, analis (bisa berbeda) akan mengisi nilai CFU.
        if ($action === 'finish_monitoring') {
            abort_unless($report->status === 'monitoring', 403);
            // Lepas kunci agar analis pembaca bisa mengambil laporan ini.
            Report::where('id', $report->id)->update(['status' => 'reading', 'locked_by' => null]);

            return redirect()->route('laporan.index')
                ->with('success', 'Monitoring selesai. Laporan masuk ke tahap pembacaan.');
        }

        // AKSI: submit_revision — kirim ulang setelah revisi (laporan sudah pernah dikembalikan).
        // Syarat: step-2 approval harus sudah ada (artinya laporan memang pernah disubmit).
        if ($action === 'submit_revision') {
            abort_unless($report->status === 'monitoring', 403);
            // Must be a revision — step-2 approval must already exist
            $existingStep2 = \App\Models\ReportApproval::where('report_id', $report->id)
                ->where('step', 2)
                ->firstOrFail();

            $freshHd = $this->markAnalystSignaturesAsSigned($report->fresh()->header_data ?? [], $report);
            $report->update(['header_data' => $freshHd, 'status' => 'submitted', 'locked_by' => null]);

            // Reset approval step 2 ke 'pending' agar supervisor bisa review ulang.
            $existingStep2->update([
                'status' => 'pending',
                'signed_at' => null,
                'notes' => null,
                'returned_to_user_id' => null,
            ]);

            return redirect()->route('laporan.index')
                ->with('success', 'Revisi berhasil dikirim ke supervisor.');
        }

        // AKSI: handover — analis melepas kunci agar analis lain bisa melanjutkan.
        // Data sudah tersimpan oleh processEntries() di atas. Hanya lepas kunci.
        if ($action === 'handover') {
            // Release the lock — any analyst can pick it up next
            Report::where('id', $report->id)->update(['locked_by' => null]);

            return redirect()->route('laporan.index')
                ->with('success', 'Draft tersimpan. Laporan bisa dilanjutkan oleh analis lain.');
        }

        // DEFAULT: simpan draft — kunci tetap di analis ini, kembali ke halaman yang sama.
        return back()->with('success', 'Data berhasil disimpan sebagai draft.');
    }

    /**
     * duplicateSection() — Tambah satu instance baru untuk seksi yang sama.
     *
     * Dipakai ketika satu seksi (mis: Ruang Produksi A) harus diukur lebih dari 1 kali
     * (mis: pagi dan siang). Jumlah instance per seksi maks 5.
     *
     * Cara kerja: jumlah instance disimpan di env_section_instances.
     * Original: parent_instance_id = NULL, duplikat: parent_instance_id = id original.
     */
    public function duplicateSection(Report $report, $sectionId)
    {
        abort_unless(
            in_array($report->status, ['monitoring', 'reading']) && $report->locked_by === Auth::id(),
            403
        );

        $result = $this->instanceService->duplicate($report, (string) $sectionId);

        return request()->wantsJson()
            ? response()->json($result, ($result['ok'] ?? false) ? 200 : 422)
            : (($result['ok'] ?? false)
                ? back()->with('success', $result['message'] ?? 'Seksi berhasil diduplikat.')
                : back()->with('error', $result['message'] ?? 'Gagal menduplikasi seksi.'));
    }
    /**
     * removeSection() — Hapus satu instance duplikat dari seksi.
     *
     * Kebalikan dari duplicateSection(). Minimum 1 instance per seksi.
     * Jika sudah di 2 instance → kembali ke 1 (hapus entri di _section_counts).
     * Jika di >2 instance → kurangi 1.
     */
    public function removeSection(Report $report, $sectionId)
    {
        abort_unless(
            in_array($report->status, ['monitoring', 'reading']) && $report->locked_by === Auth::id(),
            403
        );

        $result = $this->instanceService->remove($report, (string) $sectionId);

        return request()->wantsJson()
            ? response()->json($result, ($result['ok'] ?? false) ? 200 : 422)
            : (($result['ok'] ?? false)
                ? back()->with('success', $result['message'] ?? 'Duplikasi seksi berhasil dihapus.')
                : back()->with('error', $result['message'] ?? 'Gagal menghapus duplikasi seksi.'));
    }
    /**
     * migrateFieldOwners() — Migrasi format lama ownership field ke format baru.
     *
     * KONTEKS SISTEM OWNERSHIP:
     * Ketika dua analis bisa mengerjakan laporan bergantian, kita perlu tahu
     * "field ini diisi oleh siapa" agar analis B tidak bisa menimpa data analis A.
     *
     * Format LAMA: header_data['_field_owners']['section_key'] = user_id
     *   (ownership per seksi, bukan per field)
     * Format BARU: header_data['_field_owners']['section_key.field_key'] = user_id
     *   (ownership per field individual, lebih granular)
     *
     * Fungsi ini mengonversi dari lama ke baru saat laporan pertama kali dibuka
     * setelah upgrade sistem.
     */
    private function migrateFieldOwners(Report $report): void
    {
        $hd = $report->header_data ?? [];
        // Jika tidak ada data ownership sama sekali, tidak perlu migrasi.
        $owners = $hd['_field_owners'] ?? [];
        if (empty($owners)) {
            return;
        }
        $changed = false;
        // Loop semua key ownership yang ada.
        foreach (array_keys($owners) as $k) {
            // Format BARU sudah punya titik (mis: 'section_key.field_key') → skip.
            // Format LAMA tidak punya titik (mis: 'section_key') → perlu migrasi.
            if (! str_contains((string) $k, '.')) {
                // Ambil data aktual dari header_data untuk key ini.
                // Catatan: key inkubator (incubator_{uuid}_in) dan jam (settle_times_...) →
                //          TIDAK ada sebagai key di $hd, hanya ada di _field_owners,
                //          jadi $sectionData akan null → lewati tanpa migrasi.
                $sectionData = $hd[$k] ?? null;
                if (is_array($sectionData) && ! empty($sectionData)) {
                    // Buat entry per-field dari entry per-seksi.
                    // Contoh: owners['section_alat'] = 'uuid-A'
                    // → owners['section_alat.no_id'] = 'uuid-A'
                    // → owners['section_alat.calibration_date'] = 'uuid-A'
                    foreach (array_keys($sectionData) as $fk) {
                        if (! isset($owners["{$k}.{$fk}"])) {
                            $owners["{$k}.{$fk}"] = $owners[$k];
                            $changed = true;
                        }
                    }
                    // Hapus entry lama.
                    unset($owners[$k]);
                    $changed = true;
                }
            }
        }
        // Simpan kembali ke DB hanya jika ada perubahan (hindari UPDATE tidak perlu).
        if ($changed) {
            $hd['_field_owners'] = $owners;
            $report->header_data = $hd;
            // saveQuietly() → simpan tanpa memicu event/observer Eloquent.
            $report->saveQuietly();
        }
    }

    /**
     * processEntries() — Inti penyimpanan semua data form laporan.
     *
     * Dipanggil dari save(). Menangani 7 kelompok data secara berurutan:
     *   1. Identitas instrument (Air Sampler)
     *   2. Identitas medium agar
     *   3. Data inkubasi (inkubator)
     *   4. header_data: metadata laporan (assignment, timing umum)
     *   5. Analis yang terlibat + shift assignment
     *   6. Waktu paparan per lokasi (settle/swab/exposure) — "fan-out" ke entries
     *   7. Data CFU + waktu per_location langsung di baris tabel
     *
     * Mengembalikan $savedSectionIds: ['section_uuid|instance' => true]
     * untuk menentukan seksi mana yang perlu di-stamp tanda tangan.
     */
    private function processEntries(Request $request, Report $report): array
    {
        // Shift analis (hardcode 1, belum ada multi-shift).
        $myShift = 1;

        // ── 1. IDENTITAS INSTRUMEN (Air Sampler) ─────────────────────────────────
        // Data dari: $request->input('air_sampler') → form input name="air_sampler[no_id]", dll.
        // Disimpan ke: tabel instrument_identities.
        // updateOrCreate: match by tool_name, update kolom lainnya.
        if ($request->has('air_sampler')) {
            $asData = $request->input('air_sampler', []);
            $report->instrumentIdentities()->updateOrCreate(
                ['tool_name' => $asData['tool_name'] ?? 'Air Sampler'],
                [
                    'no_id' => $asData['no_id'] ?? null ?: null,
                    'calibration_date' => $asData['calibration_date'] ?? null ?: null,
                    'due_date' => $asData['due_date'] ?? null ?: null,
                ]
            );
        }

        // ── 2. IDENTITAS MEDIUM AGAR ───────────────────────────────────────────
        // Data dari: $request->input('medium') → array berindeks nama medium:
        //   medium['TSA']['batch_number'] = 'B001'
        //   medium['TSA']['gpt_number']   = 'G123'
        //   medium['SDA']['expiration_date'] = '2027-01-01'
        // Disimpan ke: tabel medium_identities.
        // Kita perlu medium_id (FK ke tabel media) → cari di reportType.media by name.
        if ($request->has('medium')) {
            // Load relasi media agar bisa dicari by name (lazy-load aman di sini).
            $report->load('reportType.media');
            // Loop tiap medium yang dikirim form (key = nama medium, mis: 'TSA').
            foreach ($request->input('medium', []) as $medKey => $data) {
                // Cari medium di tabel report_type_media berdasarkan nama.
                // Kalau nama tidak cocok dengan tipe laporan ini → skip (tidak simpan).
                $medium = $report->reportType->media->firstWhere('name', $medKey);
                if ($medium) {
                    $report->mediumIdentities()->updateOrCreate(
                        ['name' => $medKey],       // kondisi pencarian (cegah duplikat)
                        [
                            'medium_id' => $medium->id,
                            'batch_number' => $data['batch_number'] ?? null ?: null,
                            'gpt_number' => $data['gpt_number'] ?? null ?: null,
                            'expiration_date' => $data['expiration_date'] ?? null ?: null,
                        ]
                    );
                }
            }
        }

        // ── 3. DATA INKUBASI ─────────────────────────────────────────────────────
        // Data dari: $request->input('incubator') → diindex oleh UUID inkubator:
        //   incubator['uuid-inkubator-1']['no_id'] = 'INK-001'
        //   incubator['uuid-inkubator-1']['incubated_by'] = 'uuid-user'
        //   incubator['uuid-inkubator-1']['date_in'] = '2026-04-24'
        // Disimpan ke: tabel incubators.
        //
        // SISTEM OWNERSHIP inkubator:
        // Data inkubator dibagi 3 grup:
        //   - info  (no_id, kalibrasi)        → key ownership: incubator_{uuid}_info
        //   - in    (memasukkan cawan)        → key ownership: incubator_{uuid}_in
        //   - out   (mengeluarkan cawan)      → key ownership: incubator_{uuid}_out
        // Masing-masing grup bisa dimiliki analis berbeda. Yang sudah ada ownernya tidak bisa ditimpa.
        if ($request->has('incubator')) {
            $freshHdInk = $report->header_data ?? [];
            // Baca tabel ownership dari header_data.
            $inkOwners  = $freshHdInk['_field_owners'] ?? [];

            // Loop tiap inkubator yang dikirim form (key = UUID dari report_type_incubators).
            foreach ($request->input('incubator', []) as $tempKey => $data) {
                // Kelompokkan field berdasarkan grup.
                // array_filter() → hapus key yang nilainya null/kosong agar tidak overwrite dengan null.
                $infoFields = array_filter([
                    'no_id'                => $data['no_id']                ?? null ?: null,
                    'calibration_date'     => $data['calibration_date']     ?? null ?: null,
                    'due_date_calibration' => $data['due_date_calibration'] ?? null ?: null,
                ]);
                $inFields  = array_filter([
                    'incubated_by' => $data['incubated_by'] ?? null ?: null,
                    'date_in'      => $data['date_in']      ?? null ?: null,
                    'time_in'      => $data['time_in']      ?? null ?: null,
                ]);
                $outFields = array_filter([
                    'removed_by' => $data['removed_by'] ?? null ?: null,
                    'date_out'   => $data['date_out']   ?? null ?: null,
                    'time_out'   => $data['time_out']   ?? null ?: null,
                ]);

                // Buat ownership key untuk tiap grup.
                $ownerKeyInfo = "incubator_{$tempKey}_info";
                $ownerKeyIn   = "incubator_{$tempKey}_in";
                $ownerKeyOut  = "incubator_{$tempKey}_out";

                // Cek apakah grup 'info' sudah dimiliki analis lain.
                // Logika: isset($inkOwners[$key]) berarti sudah ada owner, cek apakah itu user ini.
                $infoLocked = isset($inkOwners[$ownerKeyInfo]) && (string) $inkOwners[$ownerKeyInfo] !== (string) Auth::id();
                if (! $infoLocked) {
                    // Belum ada owner atau ownernya adalah user ini → boleh simpan.
                    if (! empty($infoFields)) {
                        $inkOwners[$ownerKeyInfo] = (string) Auth::id(); // klaim ownership
                    }
                } else {
                    $infoFields = []; // dikunci analis lain → kosongkan agar tidak dikirim ke DB
                }

                // Logika yang sama untuk grup 'in'.
                $inLocked = isset($inkOwners[$ownerKeyIn]) && (string) $inkOwners[$ownerKeyIn] !== (string) Auth::id();
                if (! $inLocked) {
                    if (! empty($inFields)) {
                        $inkOwners[$ownerKeyIn] = (string) Auth::id();
                    }
                } else {
                    // Mask in-group fields so they don't overwrite owner's data
                    $inFields = [];
                }

                // Logika yang sama untuk grup 'out'.
                $outLocked = isset($inkOwners[$ownerKeyOut]) && (string) $inkOwners[$ownerKeyOut] !== (string) Auth::id();
                if (! $outLocked) {
                    if (! empty($outFields)) {
                        $inkOwners[$ownerKeyOut] = (string) Auth::id();
                    }
                } else {
                    $outFields = [];
                }

                // Gabungkan ketiga grup yang lolos cek ownership.
                $mergedData = array_merge($infoFields, $inFields, $outFields);
                // Hanya simpan ke DB jika ada field yang boleh diupdate.
                if (! empty($mergedData)) {
                    $report->incubators()->updateOrCreate(
                        ['report_type_incubator_id' => $tempKey],
                        $mergedData
                    );
                }
            }

            // Simpan ownership yang sudah diupdate kembali ke header_data.
            $freshHdInk['_field_owners'] = $inkOwners;
            $report->update(['header_data' => $freshHdInk]);
        }

        // ── 4. HEADER_DATA: METADATA LAPORAN ───────────────────────────────────────
        // header_data adalah kolom JSON di tabel 'reports' untuk semua metadata fleksibel.
        // Dibaca dari DB, dimodifikasi di sini, lalu disimpan kembali.
        $hd = $report->header_data ?? [];
        // Ambil tabel ownership (siapa yang mengisi field apa).
        $owners = $hd['_field_owners'] ?? [];

        // Migrasi inline format ownership lama (per-seksi) → baru (per-field).
        // Sama seperti migrateFieldOwners() tapi dilakukan di sini untuk memastikan
        // $owners yang dipakai di bawah sudah dalam format baru.
        foreach (array_keys($owners) as $k) {
            if (! str_contains((string) $k, '.')) {
                $sectionData = $hd[$k] ?? null;
                if (is_array($sectionData) && ! empty($sectionData)) {
                    foreach (array_keys($sectionData) as $fk) {
                        if (! isset($owners["{$k}.{$fk}"])) {
                            $owners["{$k}.{$fk}"] = $owners[$k];
                        }
                    }
                    unset($owners[$k]);
                }
            }
        }

        // Proses field-field dari header_data yang dikirim form.
        // Struktur form: header_data[section_key][field_key] = value
        // Atau flat: header_data[simple_key] = value
        if ($request->has('header_data')) {
            $incoming = $request->input('header_data');
            // Hapus _field_owners dari incoming agar tidak menimpa ownership yang ada.
            unset($incoming['_field_owners']);

            foreach ($incoming as $sectionKey => $sectionData) {
                // Kasus 1: nilai flat (bukan array) — ownership per key langsung.
                if (! is_array($sectionData)) {
                    $ownerKey = $sectionKey;
                    // Jika field ini sudah dimiliki analis lain → skip.
                    if (isset($owners[$ownerKey]) && (string) $owners[$ownerKey] !== (string) Auth::id()) {
                        continue;
                    }
                    // Klaim ownership jika ada nilai.
                    if ($sectionData !== null && $sectionData !== '') {
                        $owners[$ownerKey] = (string) Auth::id();
                    }
                    $hd[$sectionKey] = $sectionData;
                    continue;
                }

                // Kasus 2: nilai array (kelompok field) — ownership per field individual.
                foreach ($sectionData as $fieldKey => $fieldValue) {
                    $ownerKey = "{$sectionKey}.{$fieldKey}";
                    // Cek ownership field ini.
                    if (isset($owners[$ownerKey]) && (string) $owners[$ownerKey] !== (string) Auth::id()) {
                        continue; // dimiliki analis lain → skip
                    }
                    if ($fieldValue !== null && $fieldValue !== '') {
                        $owners[$ownerKey] = (string) Auth::id();
                    }
                    $hd[$sectionKey][$fieldKey] = $fieldValue;
                }
            }
            $hd['_field_owners'] = $owners;
        }

        // ── 5. ANALIS DAN SHIFT ASSIGNMENT ───────────────────────────────────────
        // Data analis monitoring/reading: disimpan ke tabel 'analysts' (bukan JSON).
        // Form mengirim array user ID yang dipilih di dropdown:
        //   analyst_monitoring[] = 'uuid-user-A'
        //   analyst_monitoring[] = 'uuid-user-B'
        if ($request->has('analyst_monitoring')) {
            // array_filter() → hapus nilai kosong.
            // array_values() → re-index numerik.
            $ids = array_values(array_filter((array) $request->input('analyst_monitoring')));
            foreach ($ids as $uid) {
                Analyst::updateOrCreate(
                    ['report_id' => $report->id, 'user_id' => $uid, 'type' => 'monitoring']
                );
            }
        }
        if ($request->has('analyst_reading')) {
            $ids = array_values(array_filter((array) $request->input('analyst_reading')));
            foreach ($ids as $uid) {
                Analyst::updateOrCreate(
                    ['report_id' => $report->id, 'user_id' => $uid, 'type' => 'reading']
                );
            }
        }

        // Shift assignment: kolom mana yang dikerjakan shift mana.
        // Struktur: shift_assignment[section_id][col_index] = shift_number
        // Disimpan di header_data['shift_assignments'].
        $shiftAssignment = $request->input('shift_assignment', []);
        if (! empty($shiftAssignment)) {
            $existing = $hd['shift_assignments'] ?? [];
            foreach ($shiftAssignment as $secId => $cols) {
                // array_map('intval', ...) → pastikan semua nilai adalah integer.
                $existing[$secId] = array_map('intval', $cols);
            }
            $hd['shift_assignments'] = $existing;
        }
        // Array ini dikembalikan ke save() untuk stamp tanda tangan.
        $savedSectionIds = [];

        // ── BANGUN PETA SEKSI → LOKASI ───────────────────────────────────────────
        // Ini perlu SEBELUM loop waktu di bawah, karena loop waktu perlu tahu
        // lokasi-lokasi mana saja yang ada di tiap seksi.
        //
        // Struktur data yang dibangun:
        //
        //   $sectionLocations[section_uuid] = [
        //       ['pivot_id' => 'abc', 'class' => 'a', 'location_number' => 'R1'],
        //       ['pivot_id' => 'def', 'class' => 'b', 'location_number' => 'R2'],
        //   ]
        //   Pivot_id = ID baris di tabel report_type_section_location (many-to-many antara seksi & lokasi).
        //   class = kelas ruangan ('a' atau 'b') — dipakai oleh settle time untuk bedakan A/B.
        //   location_number = label lokasi ('S1', 'S1-2', dll) — dipakai oleh swab time.
        //
        //   $pivotSectionType[pivot_id]     = measurement_type seksi ('settle'/'swab'/'exposure')
        //   $pivotSectionId[pivot_id]       = UUID seksi (untuk $savedSectionIds)
        //   $pivotSectionTimeSlot[pivot_id] = time_slot_type ('single'/'per_location'/'dual_ab'/'swab')
        //   $pivotLocationClass[pivot_id]   = class ruangan
        //   $pivotLocationNumber[pivot_id]  = nomor lokasi
        $sectionLocations     = []; // sectionId (UUID) → [['pivot_id', 'class', 'location_number'], ...]
        $pivotSectionType     = [];
        $pivotSectionId       = [];
        $pivotSectionTimeSlot = [];
        $pivotLocationClass   = [];
        $pivotLocationNumber  = [];
        // Query semua seksi + lokasi + ruangan untuk tipe laporan ini.
        foreach ($report->reportType->sections()->with('locations.room')->get() as $_sec) {
            $sectionLocations[$_sec->id] = [];
            // Loop tiap lokasi dalam seksi ini.
            foreach ($_sec->locations as $_loc) {
                // pivot = baris di tabel pivot report_type_section_location.
                $pid = $_loc->pivot->id;
                $cls = strtolower($_loc->room->class ?? '');  // 'a' atau 'b'
                $num = $_loc->location_number ?? '';           // 'S1', 'S1-2', dll
                $sectionLocations[$_sec->id][] = ['pivot_id' => $pid, 'class' => $cls, 'location_number' => $num];
                // Lookup tables per pivot_id (dipakai di loop entries bawah).
                $pivotSectionType[$pid]     = $_sec->measurement_type;
                $pivotSectionId[$pid]       = $_sec->id;
                $pivotSectionTimeSlot[$pid] = $_sec->time_slot_type;
                $pivotLocationClass[$pid]   = $cls;
                $pivotLocationNumber[$pid]  = $num;
            }
        }

        // ── BANGUN INSTANCE LOOKUP ────────────────────────────────────────────
        // Map: $instanceLookup[pivot_id][instance_number] = env_section_instance_id
        // Urutan: original (parent_instance_id NULL) = instance 1, duplikat by created_at = 2, 3, ...
        $instanceLookup = [];
        EnvSectionInstance::where('report_id', $report->id)
            ->orderByRaw('CASE WHEN parent_instance_id IS NULL THEN 0 ELSE 1 END, created_at, id')
            ->get()
            ->groupBy('report_section_id')
            ->each(function ($group, $pivotId) use (&$instanceLookup) {
                foreach ($group->values() as $idx => $inst) {
                    $instanceLookup[(string) $pivotId][$idx + 1] = (string) $inst->id;
                }
            });

        // ── 6A. SETTLE TIMES (waktu paparan untuk seksi dual_ab) ───────────────────────
        // KONSEP FAN-OUT:
        // Untuk seksi yang punya banyak lokasi (mis: Ruang A1, A2, A3 semua kelas 'a'),
        // analis hanya mengisi JAM SATU KALI di form (per kolom per kelas A/B).
        // Controller kemudian "menyebarkan" (fan-out) jam tersebut ke SEMUA lokasi
        // di kelas yang sama — sehingga setiap lokasi punya record entry sendiri di DB.
        //
        // Data dari: $request->input('settle_times')
        //   settle_times[section_id][instance][col][class_a_or_b][start_time|end_time]
        //   Contoh: settle_times['sec-uuid']['1']['1']['a']['start_time'] = '08:00'
        // Disimpan ke: header_data['settle_times'] (JSON) DAN tabel report_environmental_entries.
        $settleTimes = $request->input('settle_times', []);
        if (! empty($settleTimes)) {
            // Loop level 1: section_id
            foreach ($settleTimes as $secId => $instanceData) {
                if (! is_array($instanceData)) {
                    continue;
                }
                // Loop level 2: instance_number (mis: 1 atau 2 kalau ada duplikat)
                foreach ($instanceData as $instNum => $data) {
                    if (! is_array($data)) {
                        continue;
                    }
                    // Loop level 3: col (nomor kolom = period_number, mis: 1, 2, 3)
                    foreach ($data as $col => $abData) {
                        if (! is_array($abData)) {
                            continue;
                        }
                        // Key ownership untuk grup ini.
                        $ownerKey = "settle_times_{$secId}_{$instNum}_{$col}";
                        // Jika sudah ada owner yang bukan user ini → skip.
                        if (isset($owners[$ownerKey]) && (string) $owners[$ownerKey] !== (string) Auth::id()) {
                            continue;
                        }
                        // Cek apakah ada nilai yang tidak kosong di data A atau B.
                        $hasVal = collect($abData)->flatten()->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                        if ($hasVal) {
                            $owners[$ownerKey] = (string) Auth::id(); // klaim ownership
                            $savedSectionIds["{$secId}|{$instNum}"] = true;

                            // FAN-OUT: loop setiap lokasi dalam seksi ini.
                            // Tiap lokasi punya class ('a' atau 'b').
                            // Ambil data jam untuk class yang sesuai dengan class lokasi tsb.
                            foreach ($sectionLocations[$secId] ?? [] as $locInfo) {
                                $ab = $locInfo['class']; // 'a' atau 'b'
                                $st = $abData[$ab] ?? [];  // ambil slot A atau B
                                $startTime = ($st['start_time'] ?? '') ?: null;
                                $endTime   = ($st['end_time']   ?? '') ?: null;
                                if ($startTime === null && $endTime === null) {
                                    continue; // tidak ada jam untuk lokasi ini → skip
                                }
                                // Upsert entry untuk lokasi ini dengan jam yang sudah di-fan-out.
                                $instanceId = $instanceLookup[(string) $locInfo['pivot_id']][(int) $instNum] ?? null;
                                if (! $instanceId) {
                                    continue;
                                }
                                ReportEnvironmentalEntry::updateOrCreate(
                                    ['report_id' => $report->id, 'env_section_instance_id' => $instanceId, 'period_number' => (int) $col, 'shift' => $myShift],
                                    ['analyst_id' => Auth::id(), 'start_time' => $startTime, 'end_time' => $endTime]
                                );
                            }
                        }
                        // Simpan juga ke JSON header_data (untuk referensi/display tanpa query entries).
                        // array_replace_recursive → merge dengan data lama, tidak hapus key yang tidak dikirim.
                        $hd['settle_times'][$secId][$instNum][$col] = array_replace_recursive($hd['settle_times'][$secId][$instNum][$col] ?? [], $abData);
                    }
                }
            }
            $hd['_field_owners'] = $owners;
        }
        // ── 6B. SWAB TIMES (waktu untuk seksi swab) ──────────────────────────────
        // Mirip dengan settle times, tapi lokasi dibedakan berdasarkan location_number
        // (S1, S1-2, S1-3) bukan class ruangan A/B.
        // Data dari: $request->input('swab_times')
        //   swab_times[section_id][instance][col][s1|s1_2|s1_3][mulai|selesai]
        $swabTimes = $request->input('swab_times', []);
        if (! empty($swabTimes)) {
            foreach ($swabTimes as $secId => $instanceData) {
                if (! is_array($instanceData)) {
                    continue;
                }
                foreach ($instanceData as $instNum => $data) {
                    if (! is_array($data)) {
                        continue;
                    }
                    foreach ($data as $col => $slotData) {
                        if (! is_array($slotData)) {
                            continue;
                        }
                        $ownerKey = "swab_times_{$secId}_{$instNum}_{$col}";
                        if (isset($owners[$ownerKey]) && (string) $owners[$ownerKey] !== (string) Auth::id()) {
                            continue;
                        }
                        $hasVal = collect($slotData)->flatten()->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                        if ($hasVal) {
                            $owners[$ownerKey] = (string) Auth::id();
                            $savedSectionIds["{$secId}|{$instNum}"] = true;
                            // FAN-OUT: tentukan slot (s1/s1_2/s1_3) berdasarkan location_number lokasi.
                            // S1-3 di-check dulu karena mengandung 'S1-2' juga (mencegah salah match).
                            foreach ($sectionLocations[$secId] ?? [] as $locInfo) {
                                $locNum = $locInfo['location_number'];
                                if (stripos($locNum, 'S1-3') !== false) {
                                    $swabKey = 's1_3';
                                } elseif (stripos($locNum, 'S1-2') !== false) {
                                    $swabKey = 's1_2';
                                } else {
                                    $swabKey = 's1';
                                }
                                $st = $slotData[$swabKey] ?? [];
                                // Field 'mulai' dan 'selesai' (bukan start_time/end_time seperti settle).
                                $startTime = ($st['mulai']   ?? '') ?: null;
                                $endTime   = ($st['selesai'] ?? '') ?: null;
                                if ($startTime === null && $endTime === null) {
                                    continue;
                                }
                                $instanceId = $instanceLookup[(string) $locInfo['pivot_id']][(int) $instNum] ?? null;
                                if (! $instanceId) {
                                    continue;
                                }
                                ReportEnvironmentalEntry::updateOrCreate(
                                    ['report_id' => $report->id, 'env_section_instance_id' => $instanceId, 'period_number' => (int) $col, 'shift' => $myShift],
                                    ['analyst_id' => Auth::id(), 'start_time' => $startTime, 'end_time' => $endTime]
                                );
                            }
                        }
                        $hd['swab_times'][$secId][$instNum][$col] = array_replace_recursive($hd['swab_times'][$secId][$instNum][$col] ?? [], $slotData);
                    }
                }
            }
            $hd['_field_owners'] = $owners;
        }
        // ── 6C. EXPOSURE TIMES (waktu paparan untuk seksi single/exposure) ──────────────
        // Sama dengan settle, tapi SATU jam berlaku untuk SEMUA lokasi di seksi
        // (tidak dibedakan per class A/B).
        // Data dari: $request->input('exposure_times')
        //   exposure_times[section_id][instance][col][start_time|end_time]
        $exposureTimes = $request->input('exposure_times', []);
        if (! empty($exposureTimes)) {
            foreach ($exposureTimes as $secId => $instanceData) {
                if (! is_array($instanceData)) {
                    continue;
                }
                foreach ($instanceData as $instNum => $data) {
                    if (! is_array($data)) {
                        continue;
                    }
                    foreach ($data as $col => $times) {
                        if (! is_array($times)) {
                            continue;
                        }
                        $ownerKey = "exposure_times_{$secId}_{$instNum}_{$col}";
                        if (isset($owners[$ownerKey]) && (string) $owners[$ownerKey] !== (string) Auth::id()) {
                            continue;
                        }
                        $hasVal = collect($times)->flatten()->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                        if ($hasVal) {
                            $owners[$ownerKey] = (string) Auth::id();
                            $savedSectionIds["{$secId}|{$instNum}"] = true;
                            // FAN-OUT: JAM YANG SAMA untuk SEMUA lokasi di seksi ini.
                            // (Beda dengan settle yang bedakan class A/B.)
                            foreach ($sectionLocations[$secId] ?? [] as $locInfo) {
                                $startTime = ($times['start_time'] ?? '') ?: null;
                                $endTime   = ($times['end_time']   ?? '') ?: null;
                                if ($startTime === null && $endTime === null) {
                                    continue;
                                }
                                $instanceId = $instanceLookup[(string) $locInfo['pivot_id']][(int) $instNum] ?? null;
                                if (! $instanceId) {
                                    continue;
                                }
                                ReportEnvironmentalEntry::updateOrCreate(
                                    ['report_id' => $report->id, 'env_section_instance_id' => $instanceId, 'period_number' => (int) $col, 'shift' => $myShift],
                                    ['analyst_id' => Auth::id(), 'start_time' => $startTime, 'end_time' => $endTime]
                                );
                            }
                        }
                        $hd['exposure_times'][$secId][$instNum][$col] = array_replace_recursive($hd['exposure_times'][$secId][$instNum][$col] ?? [], $times);
                    }
                }
            }
            $hd['_field_owners'] = $owners;
        }

        // Simpan header_data ke DB jika ada perubahan di blok 4, 5, atau 6 di atas.
        if ($request->has('header_data') || ! empty($shiftAssignment) || ! empty($settleTimes) || ! empty($swabTimes) || ! empty($exposureTimes)) {
            $report->update(['header_data' => $hd]);
        }

        // Stamp timestamp terakhir kali analis ini menyimpan (selalu diupdate tiap save).
        // Dipakai untuk menampilkan "terakhir diisi oleh X pada Y" di header laporan.
        $freshHd = $report->fresh()->header_data ?? [];
        $tsKey = $report->status === 'reading' ? 'ttd_reading_timestamps' : 'ttd_monitoring_timestamps';
        $freshHd[$tsKey][(string) Auth::id()] = now()->toDateTimeString();
        $report->update(['header_data' => $freshHd]);

        // ── 7. CFU UPSERT + PER_LOCATION TIME ──────────────────────────────────────
        // Blok ini memproses data dari input 'entries' yang berisi CFU dan/atau
        // start_time per lokasi individual (time_slot_type = 'per_location').
        //
        // Beda antara per_location dan fan-out (settle/swab/exposure):
        //   fan-out: analis isi jam SATU KALI, disebarkan ke semua lokasi
        //   per_location: analis isi jam BERBEDA untuk TIAP LOKASI langsung di baris tabel
        //
        // Pertama, ambil semua entry yang sudah punya data CFU dari analis LAIN.
        // Ini mencegah analis A menimpa data CFU yang sudah diisi analis B.
        // Catatan: proteksi hanya berlaku untuk CFU, bukan untuk waktu.
        $lockedEntryKeys = ReportEnvironmentalEntry::where('report_id', $report->id)
            ->where('analyst_id', '!=', Auth::id())
            ->whereNotNull('analyst_id')
            ->where(function ($q) {
                // Entry dianggap 'locked' jika punya minimal satu nilai CFU (bakteri atau jamur).
                $q->whereNotNull('cfu_bacteria')->orWhereNotNull('cfu_fungi');
            })
            ->get()
            // Buat string key unik per entry: "instance_id-period-shift"
            ->map(fn ($e) => "{$e->env_section_instance_id}-{$e->period_number}-{$e->shift}")
            ->toArray();

        // Loop entries dari form: entries[pivot_id][instance][period_col][field]
        foreach ($request->input('entries', []) as $pivotId => $instances) {
            // Cek apakah pivot_id ini valid (ada di peta yang dibangun di atas).
            $sectionType = $pivotSectionType[(string) $pivotId] ?? null;
            if (! $sectionType) {
                continue; // pivot tidak dikenal → skip (keamanan input)
            }

            // Ambil time_slot_type dan section_id dari lookup table yang sudah dibangun.
            $timeSlotType = $pivotSectionTimeSlot[(string) $pivotId] ?? 'none';
            $sectionId    = $pivotSectionId[(string) $pivotId] ?? null;

            // Loop tiap instance (mis: 1 atau 2 kalau ada duplikat seksi).
            foreach ($instances as $instanceNum => $cols) {
                $instanceNumber = max(1, (int) $instanceNum); // minimal 1

                // Loop tiap kolom (period). Kolom 0 = Machine Setup, 1..n = kolom paparan.
                foreach ($cols as $colIdx => $data) {
                    $periodNumber = (int) $colIdx;
                    $shift        = $myShift; // selalu 1 saat ini

                    // Cek apakah ada data CFU yang tidak kosong.
                    $hasCfuData = (($data['cfu_bacteria'] ?? '') !== '' && ($data['cfu_bacteria'] ?? null) !== null)
                               || (($data['cfu_fungi']    ?? '') !== '' && ($data['cfu_fungi']    ?? null) !== null);

                    // Untuk seksi per_location: ada jam start_time langsung di baris → perlu disimpan.
                    // (Beda dengan fan-out yang sudah diproses di blok 6 di atas.)
                    $hasPerLocTime = ($timeSlotType === 'per_location') && (($data['start_time'] ?? '') !== '');

                    // Tidak ada data apapun → lewati (tidak buat entry kosong di DB).
                    if (! $hasCfuData && ! $hasPerLocTime) {
                        continue;
                    }

                    // Proteksi CFU: cek apakah entry ini sudah dikunci analis lain.
                    $instanceId = $instanceLookup[(string) $pivotId][$instanceNumber] ?? null;
                    if (! $instanceId) {
                        continue; // instance belum ada di DB → skip
                    }
                    // Key format: "instance_id-period-shift" (sama dengan $lockedEntryKeys).
                    $entryKey = "{$instanceId}-{$periodNumber}-{$shift}";
                    if ($hasCfuData && in_array($entryKey, $lockedEntryKeys)) {
                        continue; // entry dikunci analis lain → tidak bisa timpa CFU
                    }

                    // Bangun array nilai yang akan di-update/insert.
                    $updateValues = [];
                    if ($hasCfuData) {
                        // Klaim entry ini sebagai milik analis yang sedang login.
                        $updateValues['analyst_id']   = Auth::id();
                        $updateValues['cfu_bacteria'] = self::normalizeCfu($data['cfu_bacteria'] ?? null);
                        $updateValues['cfu_fungi']    = self::normalizeCfu($data['cfu_fungi']    ?? null);
                    }

                    // per_location: jam diisi langsung per baris lokasi (bukan fan-out).
                    // Pastikan analyst_id juga diisi agar INSERT tidak gagal (kolom NOT NULL).
                    if ($hasPerLocTime) {
                        $updateValues['start_time'] = $data['start_time'];
                        $updateValues['end_time']   = null;
                        // Jika blok CFU di atas sudah set analyst_id → tidak perlu set ulang.
                        if (!isset($updateValues['analyst_id'])) {
                            $updateValues['analyst_id'] = Auth::id();
                        }
                    }

                    if (empty($updateValues)) {
                        continue;
                    }

                    // Upsert ke tabel report_environmental_entries.
                    // Argumen 1 (array pertama): kondisi pencarian — kalau sudah ada → UPDATE, kalau belum → INSERT.
                    // Argumen 2 (array kedua): nilai yang di-set.
                    ReportEnvironmentalEntry::updateOrCreate(
                        [
                            'report_id'               => $report->id,
                            'env_section_instance_id' => $instanceId,
                            'period_number'           => $periodNumber,
                            'shift'                   => $shift,
                        ],
                        $updateValues
                    );
                    // Catat seksi ini sebagai "punya data baru" untuk stamp tanda tangan.
                    if ($sectionId) {
                        $savedSectionIds["{$sectionId}|{$instanceNumber}"] = true;
                    }
                }
            }
        }

        return $savedSectionIds;
    }

    /**
     * normalizeCfu() — Normalisasi dan validasi nilai CFU dari input form.
     *
     * Nilai yang VALID:
     *   '<1'   → koloni terlalu sedikit untuk dihitung
     *   'TNTC' → Too Numerous To Count (terlalu banyak)
     *   '1', '250', '1000' → bilangan bulat positif (>0)
     *
     * Nilai yang TIDAK VALID (dikembalikan sebagai null):
     *   '0', '-5', '3.5', 'abc', '' → diabaikan
     */
    private static function normalizeCfu(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null; // kosong → null (field tidak wajib diisi)
        }
        $v = trim((string) $raw);
        if ($v === '') {
            return null;
        }
        // Izinkan '<1' persis (case-sensitive untuk konsistensi).
        if ($v === '<1') {
            return '<1';
        }
        // Izinkan 'TNTC' (case-insensitive, normalisasi ke uppercase).
        if (strtoupper($v) === 'TNTC') {
            return 'TNTC';
        }
        // Izinkan bilangan bulat positif (mulai dari 1). Angka 0 dan negatif tidak valid.
        if (preg_match('/^[1-9][0-9]*$/', $v)) {
            return $v;
        }

        return null; // format tidak dikenal → buang
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

        // Pastikan username yang dikirim cocok dengan user yang sedang login.
        // Ini mencegah seseorang verifikasi dengan kredensial akun orang lain.
        if ($user->username !== $request->username || ! Hash::check($request->password, $user->password)) {
            return response()->json(['ok' => false, 'message' => 'Username atau password salah.'], 422);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * markAnalystSignaturesAsSigned() — Tandai tanda tangan monitoring dan reading sebagai selesai.
     *
     * Dipanggil saat laporan disubmit. Menyimpan:
     *   - ID user yang monitoring dan yang reading
     *   - Timestamp penandatanganan (sama untuk keduanya = waktu submit)
     * ke dalam header_data (JSON), yang nantinya ditampilkan di dokumen laporan.
     */
    private function markAnalystSignaturesAsSigned(array $headerData, Report $report): array
    {
        // Ambil analis pertama dengan type 'monitoring' dan 'reading' dari tabel analysts.
        $monitoringUser = Analyst::where('report_id', $report->id)->where('type', 'monitoring')->first();
        $readingUser = Analyst::where('report_id', $report->id)->where('type', 'reading')->first();

        // Simpan ID user ke header_data untuk referensi tampilan laporan.
        $headerData['ttd_monitoring_id'] = $monitoringUser?->user_id;
        $headerData['ttd_dibaca_id'] = $readingUser?->user_id;

        // Timestamp penandatanganan — dicatat saat submit.
        $signedAt = now()->toDateTimeString();
        $headerData['ttd_monitoring_signed_at'] = $signedAt;
        $headerData['ttd_dibaca_signed_at'] = $signedAt;

        return $headerData;
    }
}

