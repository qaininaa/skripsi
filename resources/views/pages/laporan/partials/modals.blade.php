{{-- ── Modals: Save / Alert / Confirm ─────────────────── --}}
<div id="save-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40" onclick="closeSaveModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4">
        <h3 id="save-modal-title" class="text-base font-semibold text-gray-800">Konfirmasi Simpan Draft</h3>
        <p id="save-modal-desc" class="text-sm text-gray-500">Masukkan username dan password Anda untuk menyimpan.</p>
        <div class="space-y-3">
            <div id="supervisor-select-row" class="hidden">
                <label class="block text-xs font-medium text-gray-600 mb-1">Kirim ke Supervisor</label>
                <select id="save-modal-supervisor"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none bg-white">
                    <option value="">-- Pilih Supervisor --</option>
                    @foreach (\App\Models\User::where('role', 'supervisor')->orderBy('name')->get() as $sup)
                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                    @endforeach
                </select>
                <p id="save-modal-supervisor-error" class="hidden mt-1 text-xs text-red-600">Pilih supervisor terlebih dahulu.</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Username</label>
                <input id="save-modal-username" type="text" autocomplete="username"
                       class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Password</label>
                <div class="relative" x-data="{ show: false }">
                    <input id="save-modal-password" :type="show ? 'text' : 'password'" autocomplete="current-password"
                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 text-sm focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                      <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.578-3.007-9.964-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                      <svg x-show="show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                    </button>
                </div>
            </div>
            <p id="save-modal-error" class="hidden text-xs text-red-600"></p>
        </div>
        <div class="flex justify-end gap-2 pt-1">
            <button type="button" onclick="closeSaveModal()"
                    class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">
                Batal
            </button>
            <button type="button" id="save-modal-confirm" onclick="confirmSave()"
                    class="px-4 py-2 rounded-lg bg-sky-500 text-white text-sm font-medium hover:bg-sky-600 disabled:opacity-50">
                Simpan
            </button>
        </div>
    </div>
</div>

{{-- ── Handover / Finish Monitoring Modal ────────────── --}}
<div id="handover-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40" onclick="closeHandoverModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4">
        <h3 class="text-base font-semibold text-gray-800">Simpan &amp; Serahkan Laporan</h3>

        {{-- Action choice --}}
        <div class="space-y-2">
            <label class="flex gap-3 items-start cursor-pointer rounded-xl border border-gray-200 p-3 hover:border-sky-300 hover:bg-sky-50/40 transition-colors">
                <input type="radio" name="hm-action" value="handover" class="mt-0.5 accent-sky-500" onchange="onHmActionChange()">
                <div>
                    @if($isMonitoringPhase)
                    <div class="text-sm font-medium text-gray-800">Simpan Monitoring</div>
                    <div class="text-xs text-gray-500 mt-0.5">Draft tersimpan, analis lain bisa melanjutkan monitoring</div>
                    @else
                    <div class="text-sm font-medium text-gray-800">Simpan Pembacaan</div>
                    <div class="text-xs text-gray-500 mt-0.5">Draft tersimpan, analis lain bisa melanjutkan pembacaan</div>
                    @endif
                </div>
            </label>
            @if($isMonitoringPhase)
            <label class="flex gap-3 items-start cursor-pointer rounded-xl border border-gray-200 p-3 hover:border-emerald-300 hover:bg-emerald-50/40 transition-colors">
                <input type="radio" name="hm-action" value="finish_monitoring" class="mt-0.5 accent-emerald-500" onchange="onHmActionChange()">
                <div>
                    <div class="text-sm font-medium text-gray-800">Selesaikan Monitoring &amp; Mulai Pembacaan</div>
                    <div class="text-xs text-gray-500 mt-0.5">Data monitoring sudah lengkap, lanjut ke tahap baca</div>
                </div>
            </label>
            @endif
        </div>

        {{-- Password confirmation --}}
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Username</label>
                <input id="hm-username" type="text" autocomplete="username"
                       class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Password</label>
                <div class="relative" x-data="{ show: false }">
                    <input id="hm-password" :type="show ? 'text' : 'password'" autocomplete="current-password"
                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none">
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.578-3.007-9.964-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                    </button>
                </div>
            </div>
            <p id="hm-error" class="hidden text-xs text-red-600"></p>
        </div>

        <div class="flex justify-end gap-2 pt-1">
            <button type="button" onclick="closeHandoverModal()"
                    class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">
                Batal
            </button>
            <button type="button" id="hm-confirm" onclick="confirmHandover()"
                    class="px-4 py-2 rounded-lg bg-amber-500 text-white text-sm font-medium hover:bg-amber-600 disabled:opacity-50">
                Lanjutkan
            </button>
        </div>
    </div>
</div>

<div id="alert-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-3">
        <h3 id="alert-modal-title" class="text-base font-semibold text-gray-800">Perhatian</h3>
        <p id="alert-modal-msg" class="text-sm text-gray-600 whitespace-pre-line"></p>
        <div class="flex justify-end pt-1">
            <button type="button" onclick="closeAlertModal()"
                    class="px-4 py-2 rounded-lg bg-gray-800 text-white text-sm font-medium hover:bg-gray-700">
                OK
            </button>
        </div>
    </div>
</div>

<div id="confirm-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-3">
        <h3 id="confirm-modal-title" class="text-base font-semibold text-gray-800">Konfirmasi</h3>
        <p id="confirm-modal-msg" class="text-sm text-gray-600"></p>
        <div class="flex justify-end gap-2 pt-1">
            <button type="button" onclick="closeConfirmModal()"
                    class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">
                Batal
            </button>
            <button type="button" id="confirm-modal-ok" onclick="doConfirm()"
                    class="px-4 py-2 rounded-lg bg-sky-500 text-white text-sm font-medium hover:bg-sky-600">
                OK
            </button>
        </div>
    </div>
</div>
