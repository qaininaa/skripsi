@extends('layouts.app')

@section('title', 'Edit Pengguna')
@section('page-title', 'Edit Pengguna')
@section('content')

    <div class="max-w-2xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('users.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke daftar pengguna
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-1">Edit Pengguna</h2>
            <p class="text-sm text-gray-500 mb-4">
                <span class="font-medium text-gray-700">{{ $user->name }}</span>
                <span class="text-gray-400">·</span>
                <span class="text-gray-500">{{ $user->username }}</span>
            </p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                    <p class="font-semibold mb-1">Terjadi kesalahan:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $managerExistsForOther = \Domain\User\Models\User::where('role', 'manajer')
                    ->where('id', '!=', $user->id)
                    ->exists();
            @endphp

            <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}"
                           class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        @php $currentRole = old('role', $user->role); @endphp
                        <option value="super"      {{ $currentRole === 'super'      ? 'selected' : '' }}>Super Admin</option>
                        <option value="admin"      {{ $currentRole === 'admin'      ? 'selected' : '' }}>Admin QC</option>
                        <option value="analis"     {{ $currentRole === 'analis'     ? 'selected' : '' }}>Analis</option>
                        <option value="supervisor" {{ $currentRole === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                        <option value="manajer"    {{ $currentRole === 'manajer'    ? 'selected' : '' }} {{ $managerExistsForOther ? 'disabled' : '' }}>
                            Manajer{{ $managerExistsForOther ? ' (sudah ada)' : '' }}
                        </option>
                    </select>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <h3 class="text-sm font-semibold text-gray-700">Reset Password (Opsional)</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Kosongkan kedua kolom jika tidak ingin mengganti password. Mengisi password berarti mereset akun.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <input type="password" name="password" autocomplete="new-password"
                           class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Kosongkan jika tidak ingin mengubah">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" autocomplete="new-password"
                           class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Ulangi password baru">
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-green-700 text-white text-sm font-medium hover:bg-indigo-700 shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
