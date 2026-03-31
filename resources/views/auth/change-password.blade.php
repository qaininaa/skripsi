<!DOCTYPE html>
<html class="h-full bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">

<div class="flex min-h-full items-center justify-center px-6 py-12">
    <div class="w-full max-w-md">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="text-center text-2xl font-bold tracking-tight text-gray-900">Ubah Password</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                @if(!auth()->user()->last_password_changed_at)
                    Password Anda telah di-reset oleh admin. Silakan buat password baru.
                @else
                    Password Anda sudah kedaluwarsa. Silakan buat password baru untuk melanjutkan.
                @endif
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-sm">
            @if (session('warning'))
                <div class="mb-4 rounded-md bg-yellow-50 p-4">
                    <p class="text-sm text-yellow-700">{{ session('warning') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 p-4">
                    <ul class="list-disc list-inside text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('password.change.update') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-900">Password Saat Ini</label>
                    <div class="mt-2">
                        <input id="current_password" type="password" name="current_password" required autocomplete="current-password"
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm" />
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-900">Password Baru</label>
                    <div class="mt-2">
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm" />
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Min. 8 karakter, huruf besar, huruf kecil, angka, dan simbol.</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-900">Konfirmasi Password Baru</label>
                    <div class="mt-2">
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm" />
                    </div>
                </div>

                <div>
                    <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Ubah Password
                    </button>
                </div>
            </form>

            <div class="mt-4 text-center">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
