@extends('layouts.admin')

@section('title', 'Pengaturan Sistem')
@section('page-title', 'Pengaturan Sistem')
@section('avatar-color', 'bg-green-600')

@section('content')

<div class="max-w-2xl">

    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-lg font-bold text-gray-900">Pengaturan Sistem</h2>
        <p class="text-sm text-gray-500 mt-0.5">Konfigurasi kebijakan keamanan akun pengguna.</p>
    </div>

    {{-- Success flash --}}
    @if (session('success'))
    <div class="mb-5 px-4 py-3 bg-green-50 border border-green-100 rounded-xl text-sm text-green-700 flex items-center gap-2">
        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">

            {{-- Password Expiration --}}
            <div class="px-6 py-5">
                <div class="flex items-start justify-between gap-6">
                    <div class="flex-1">
                        <label for="password_expiration_days" class="block text-sm font-semibold text-gray-800">
                            Masa Berlaku Password
                        </label>
                        <p class="text-xs text-gray-500 mt-1">
                            Pengguna diwajibkan mengganti password setelah jumlah hari ini. Berlaku untuk semua role.
                        </p>
                        @error('password_expiration_days')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <input type="number"
                               id="password_expiration_days"
                               name="password_expiration_days"
                               value="{{ old('password_expiration_days', $settings['password_expiration_days']) }}"
                               min="1" max="365"
                               class="w-20 rounded-lg border border-gray-200 px-3 py-2 text-sm text-center text-gray-800 font-semibold
                                      focus:border-green-400 focus:ring-1 focus:ring-green-400 focus:outline-none
                                      @error('password_expiration_days') border-red-400 @enderror">
                        <span class="text-sm text-gray-500 whitespace-nowrap">hari</span>
                    </div>
                </div>
            </div>

            {{-- Password History --}}
            <div class="px-6 py-5">
                <div class="flex items-start justify-between gap-6">
                    <div class="flex-1">
                        <label for="password_history_count" class="block text-sm font-semibold text-gray-800">
                            Riwayat Password yang Diingat
                        </label>
                        <p class="text-xs text-gray-500 mt-1">
                            Pengguna tidak bisa memakai kembali sejumlah password terakhir ini saat mengganti password.
                        </p>
                        @error('password_history_count')
                            <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <input type="number"
                               id="password_history_count"
                               name="password_history_count"
                               value="{{ old('password_history_count', $settings['password_history_count']) }}"
                               min="1" max="24"
                               class="w-20 rounded-lg border border-gray-200 px-3 py-2 text-sm text-center text-gray-800 font-semibold
                                      focus:border-green-400 focus:ring-1 focus:ring-green-400 focus:outline-none
                                      @error('password_history_count') border-red-400 @enderror">
                        <span class="text-sm text-gray-500 whitespace-nowrap">password</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Save button --}}
        <div class="mt-4 flex justify-end">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold
                           hover:bg-green-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Pengaturan
            </button>
        </div>

    </form>

</div>

@endsection
