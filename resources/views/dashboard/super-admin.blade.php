@extends('layouts.admin')

@section('title', 'Super Admin')
@section('page-title', 'Dashboard Super Admin')
@section('avatar-color', 'bg-indigo-600')

{{-- ===================== SIDEBAR ===================== --}}
@section('sidebar')
<div class="flex flex-col h-full bg-indigo-950 text-white">

    {{-- Brand --}}
    <div class="flex items-center h-16 px-5 border-b border-indigo-800 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg bg-indigo-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <span class="font-bold text-base tracking-tight">AdminPanel</span>
        </div>
    </div>

    {{-- Role Badge --}}
    <div class="px-5 py-3 border-b border-indigo-800 flex-shrink-0">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-700 text-indigo-100">
            <span class="h-1.5 w-1.5 rounded-full bg-indigo-300"></span>
            Super Admin
        </span>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

        {{-- Dashboard --}}
        <a href="{{ route('dashboard.super-admin') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-800 text-white font-medium text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </a>

        {{-- Manajemen Pengguna --}}
        <a href="#"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-indigo-300 hover:bg-indigo-800 hover:text-white font-medium text-sm transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Manajemen Pengguna
        </a>

        {{-- Lihat Dashboard QC --}}
        <a href="{{ route('dashboard.admin-qc') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-indigo-300 hover:bg-indigo-800 hover:text-white font-medium text-sm transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            Dashboard QC
        </a>

        {{-- Laporan --}}
        <a href="#"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-indigo-300 hover:bg-indigo-800 hover:text-white font-medium text-sm transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Laporan
        </a>

        <div class="pt-2 pb-1 px-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-500">Akun</p>
        </div>

        {{-- Profil --}}
        <a href="{{ route('profile.edit') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-indigo-300 hover:bg-indigo-800 hover:text-white font-medium text-sm transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Profil Saya
        </a>

    </nav>

    {{-- User info at bottom --}}
    <div class="p-4 border-t border-indigo-800 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-indigo-400 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>

</div>
@endsection

{{-- ===================== CONTENT ===================== --}}
@section('content')

{{-- Welcome Banner --}}
<div class="mb-6 bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-2xl p-6 text-white shadow-lg">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">Selamat Datang, {{ Auth::user()->name }}! 👋</h2>
            <p class="mt-1 text-indigo-200 text-sm">Berikut ringkasan aktivitas sistem hari ini.</p>
            <p class="mt-2 text-indigo-300 text-xs">{{ now()->format('l, d F Y') }}</p>
        </div>
        <div class="hidden md:flex h-20 w-20 rounded-2xl bg-white bg-opacity-10 items-center justify-center flex-shrink-0">
            <svg class="w-11 h-11 text-white opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
        </div>
    </div>
</div>

{{-- Stats Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">

    {{-- Total Pengguna --}}
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Aktif</span>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ \App\Models\User::count() }}</p>
        <p class="text-sm font-medium text-gray-600 mt-0.5">Total Pengguna</p>
        <p class="text-xs text-gray-400 mt-1">Semua akun terdaftar</p>
    </div>

    {{-- Total Admin QC --}}
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Bertugas</span>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ \App\Models\User::where('role', 'admin-qc')->count() }}</p>
        <p class="text-sm font-medium text-gray-600 mt-0.5">Admin QC</p>
        <p class="text-xs text-gray-400 mt-1">Petugas Quality Control</p>
    </div>

    {{-- Total Super Admin --}}
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">Admin</span>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ \App\Models\User::where('role', 'super admin')->count() }}</p>
        <p class="text-sm font-medium text-gray-600 mt-0.5">Super Admin</p>
        <p class="text-xs text-gray-400 mt-1">Administrator sistem</p>
    </div>

    {{-- Status Sistem --}}
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                <div class="h-4 w-4 rounded-full bg-green-500 animate-pulse"></div>
            </div>
            <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">100% Uptime</span>
        </div>
        <p class="text-3xl font-bold text-green-600">Online</p>
        <p class="text-sm font-medium text-gray-600 mt-0.5">Status Sistem</p>
        <p class="text-xs text-gray-400 mt-1">Semua layanan normal</p>
    </div>

</div>

{{-- Bottom Grid --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Users Table --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Daftar Pengguna</h3>
            <a href="#" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors">Lihat semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Nama</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Username</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Role</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Bergabung</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach(\App\Models\User::latest()->take(5)->get() as $user)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-3">
                                @php
                                    $avatarColor = match($user->role) {
                                        'super admin' => 'bg-indigo-500',
                                        'admin-qc'    => 'bg-emerald-500',
                                        default       => 'bg-gray-400',
                                    };
                                @endphp
                                <div class="h-8 w-8 rounded-full {{ $avatarColor }} flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-gray-800">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3.5 text-sm text-gray-500">{{ $user->username ?? '—' }}</td>
                        <td class="px-6 py-3.5">
                            @php
                                $badgeClass = match($user->role) {
                                    'super admin' => 'bg-indigo-100 text-indigo-700',
                                    'admin-qc'    => 'bg-emerald-100 text-emerald-700',
                                    default       => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-sm text-gray-400">{{ $user->created_at->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Side Panel --}}
    <div class="flex flex-col gap-5">

        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Aksi Cepat</h3>
            <div class="space-y-2">
                <a href="#" class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:border-indigo-300 hover:bg-indigo-50 transition-colors group">
                    <div class="h-9 w-9 rounded-lg bg-indigo-100 group-hover:bg-indigo-200 flex items-center justify-center flex-shrink-0 transition-colors">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">Tambah Pengguna</p>
                        <p class="text-xs text-gray-400">Buat akun baru</p>
                    </div>
                </a>
                <a href="#" class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:border-emerald-300 hover:bg-emerald-50 transition-colors group">
                    <div class="h-9 w-9 rounded-lg bg-emerald-100 group-hover:bg-emerald-200 flex items-center justify-center flex-shrink-0 transition-colors">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">Lihat Laporan</p>
                        <p class="text-xs text-gray-400">Laporan QC terkini</p>
                    </div>
                </a>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:border-blue-300 hover:bg-blue-50 transition-colors group">
                    <div class="h-9 w-9 rounded-lg bg-blue-100 group-hover:bg-blue-200 flex items-center justify-center flex-shrink-0 transition-colors">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">Edit Profil</p>
                        <p class="text-xs text-gray-400">Ubah data akun</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- System Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Info Sistem</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">Versi Aplikasi</span>
                    <span class="text-xs font-semibold text-gray-800">v1.0.0</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">Framework</span>
                    <span class="text-xs font-semibold text-gray-800">Laravel {{ app()->version() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">Database</span>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                        Terhubung
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">Environment</span>
                    <span class="text-xs font-semibold text-gray-800 capitalize">{{ app()->environment() }}</span>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection
