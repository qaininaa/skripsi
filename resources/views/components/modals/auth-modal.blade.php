{{--
    Komponen auth-modal: satu modal konfirmasi dengan form autentikasi.
    Untuk kumpulan modal laporan (save/handover/alert/confirm), gunakan x-modals.laporan-modals.

    Props:
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
