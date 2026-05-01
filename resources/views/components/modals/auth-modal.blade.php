{{--
    Komponen auth-modal.
    Mode:
      - variant='single'     : 1 modal konfirmasi auth (reusable)
      - variant='collection' : paket modal laporan (save/handover/alert/confirm)
    Input auth menggunakan x-forms.auth-form dengan withForm=false.

    Props:
      - variant       : string ('single' | 'collection')
      - isMonitoringPhase : bool (dipakai di variant='collection')
      - modalId       : string — id elemen modal (mis: 'save-modal')
      - title         : string — judul modal (default: 'Konfirmasi')
      - titleId       : string|null — id <h3>, supaya JS bisa mengubah teks dinamis
      - description   : string — teks deskripsi (opsional)
      - descId        : string|null — id <p>, supaya JS bisa mengubah teks dinamis
      - onClose       : string — JS expression untuk menutup modal (mis: closeSaveModal())
      - confirmId     : string — id tombol konfirmasi
      - confirmLabel  : string — label tombol (default: 'Konfirmasi')
      - confirmColor  : 'sky' | 'amber' | 'emerald' | 'red'
      - onConfirm     : string — JS expression saat tombol konfirmasi diklik
      - usernameId    : string — id input username, diteruskan ke x-forms.auth-form
      - passwordId    : string — id input password, diteruskan ke x-forms.auth-form
      - errorId       : string — id elemen pesan error JS

    Slot default: konten ekstra di antara deskripsi dan form auth
      (mis: supervisor select, radio buttons, dll.)
--}}
@props([
    'variant'      => 'single', // single | collection
    'isMonitoringPhase' => false,
    'modalId',
    'title'        => 'Konfirmasi',
    'titleId'      => null,
    'description'  => '',
    'descId'       => null,
    'onClose',
    'confirmId',
    'confirmLabel' => 'Konfirmasi',
    'confirmColor' => 'sky',
    'onConfirm',
    'usernameId',
    'passwordId',
    'errorId',
    'showAuthForm' => true,
    'usernameName' => 'username',
    'passwordName' => 'password',
    'usernameLabel' => 'Username',
    'passwordLabel' => 'Password',
    'autofocusUsername' => true,
])
@php
    $btnColorClass = match($confirmColor) {
        'amber'   => 'bg-amber-500 hover:bg-amber-600',
        'emerald' => 'bg-emerald-500 hover:bg-emerald-600',
        'red'     => 'bg-red-500 hover:bg-red-600',
        default   => 'bg-sky-500 hover:bg-sky-600',
    };
@endphp

@if($variant === 'collection')
    {{-- Modal: Simpan Draft / Kirim / Estafet --}}
    <x-auth-modal
        modal-id="save-modal"
        title="Konfirmasi Simpan Draft"
        title-id="save-modal-title"
        description="Masukkan username dan password Anda untuk menyimpan."
        desc-id="save-modal-desc"
        on-close="closeSaveModal()"
        confirm-id="save-modal-confirm"
        confirm-label="Simpan"
        on-confirm="confirmSave()"
        username-id="save-modal-username"
        password-id="save-modal-password"
        error-id="save-modal-error">

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
    </x-auth-modal>

    {{-- Modal: Handover / Selesaikan Monitoring --}}
    <x-auth-modal
        modal-id="handover-modal"
        title="Simpan &amp; Serahkan Laporan"
        on-close="closeHandoverModal()"
        confirm-id="hm-confirm"
        confirm-label="Lanjutkan"
        confirm-color="amber"
        on-confirm="confirmHandover()"
        username-id="hm-username"
        password-id="hm-password"
        error-id="hm-error">

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
    </x-auth-modal>

    {{-- Modal: Alert --}}
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

    {{-- Modal: Konfirmasi generik --}}
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
@else
    <div id="{{ $modalId }}" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" onclick="{{ $onClose }}"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4">

            <h3 @if($titleId) id="{{ $titleId }}" @endif
                class="text-base font-semibold text-gray-800">{{ $title }}</h3>

            @if($description || $descId)
            <p @if($descId) id="{{ $descId }}" @endif
               class="text-sm text-gray-500">{{ $description }}</p>
            @endif

            {{-- Slot: konten ekstra (supervisor select, radio buttons, dll.) --}}
            {{ $slot }}

            @if($showAuthForm)
                <div class="space-y-3">
                    <x-forms.auth-form
                        :username-id="$usernameId"
                        :password-id="$passwordId"
                        :username-name="$usernameName"
                        :password-name="$passwordName"
                        :username-label="$usernameLabel"
                        :password-label="$passwordLabel"
                        :autofocus-username="$autofocusUsername"
                        :with-form="false" />
                    <p id="{{ $errorId }}" class="hidden text-xs text-red-600"></p>
                </div>
            @endif

            <div class="flex justify-end gap-2 pt-1">
                <button type="button" onclick="{{ $onClose }}"
                        class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">
                    Batal
                </button>
                <button type="button" id="{{ $confirmId }}" onclick="{{ $onConfirm }}"
                        class="px-4 py-2 rounded-lg {{ $btnColorClass }} text-white text-sm font-medium disabled:opacity-50">
                    {{ $confirmLabel }}
                </button>
            </div>

        </div>
    </div>
@endif
