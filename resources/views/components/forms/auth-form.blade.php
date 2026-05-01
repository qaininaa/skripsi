{{--
    Komponen form autentikasi — dipakai di login page dan modal konfirmasi.
    Props:
      - action      : URL form submit (hanya dipakai saat withForm=true)
      - usernameId  : id attribute input username (default: 'username')
      - passwordId  : id attribute input password (default: 'password')
      - withForm    : true = render <form> lengkap dengan CSRF + tombol submit (untuk login page)
                      false = hanya render field username+password tanpa wrapper (untuk modal)
--}}
@props([
    'action'     => '',
    'usernameId' => 'username',
    'passwordId' => 'password',
    'withForm'   => true,
    'usernameName' => 'username',
    'passwordName' => 'password',
    'usernameLabel' => 'Username',
    'passwordLabel' => 'Password',
    'usernameValue' => null,
    'usernameAutocomplete' => 'username',
    'passwordAutocomplete' => 'current-password',
    'autofocusUsername' => true,
    'submitLabel' => 'Masuk',
    'loadingLabel' => 'Memeriksa...',
])

@if($withForm)
<form action="{{ $action }}" method="POST" class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
    @csrf
@endif

<div>
    <label for="{{ $usernameId }}" class="block text-sm/6 font-medium text-gray-900">{{ $usernameLabel }}</label>
    <div class="mt-2">
        <input id="{{ $usernameId }}" type="text"
               @if($withForm || $usernameName) name="{{ $usernameName }}" @endif
               value="{{ old($usernameName, $usernameValue) }}"
               required @if($autofocusUsername) autofocus @endif autocomplete="{{ $usernameAutocomplete }}"
               class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-emerald-600 sm:text-sm/6" />
    </div>
    @if($withForm)
        @error($usernameName)
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    @endif
</div>

<div>
    <label for="{{ $passwordId }}" class="block text-sm/6 font-medium text-gray-900">{{ $passwordLabel }}</label>
    <div class="mt-2 relative" x-data="{ show: false }">
        <input id="{{ $passwordId }}" :type="show ? 'text' : 'password'"
               @if($withForm || $passwordName) name="{{ $passwordName }}" @endif
               required autocomplete="{{ $passwordAutocomplete }}"
               class="block w-full rounded-md bg-white px-3 py-1.5 pr-10 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-emerald-600 sm:text-sm/6" />
        <button type="button" @click="show = !show"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none">
            <img x-show="!show" src="{{ asset('icons/eye.svg') }}" alt="Tampilkan password" class="w-4 h-4">
            <img x-show="show"  src="{{ asset('icons/eye-slash.svg') }}" alt="Sembunyikan password" class="w-4 h-4">
        </button>
    </div>
    @if($withForm)
        @error($passwordName)
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    @endif
</div>

@if($withForm)
<div>
    <button type="submit" :disabled="loading"
            class="flex w-full justify-center rounded-md bg-emerald-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-sm hover:bg-emerald-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 disabled:opacity-70 disabled:cursor-not-allowed transition-all">
        <span x-show="!loading">{{ $submitLabel }}</span>
        <span x-show="loading" class="flex items-center gap-2">
            <img src="{{ asset('icons/spinner.svg') }}" alt="" class="animate-spin w-4 h-4">
            {{ $loadingLabel }}
        </span>
    </button>
</div>

</form>
@endif
