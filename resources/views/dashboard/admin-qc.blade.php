@extends('layouts.admin')

@section('title', 'Admin QC')
@section('page-title', 'Dashboard Quality Control')
@section('avatar-color', 'bg-green-600')

{{-- ===================== SIDEBAR ===================== --}}
{{-- ===================== CONTENT ===================== --}}
@section('content')

{{-- Welcome Banner --}}
<div class="mb-6 bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl p-6 text-white shadow-lg">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">Selamat Datang, {{ Auth::user()->name }}! 👷</h2>
            <p class="mt-1 text-emerald-200 text-sm">Panel Quality Control — kelola inspeksi dan laporan produk.</p>
            <p class="mt-2 text-emerald-300 text-xs">{{ now()->format('l, d F Y') }}</p>
        </div>
        <div class="hidden md:flex h-20 w-20 rounded-2xl bg-white bg-opacity-10 items-center justify-center flex-shrink-0">
            <svg class="w-11 h-11 text-white opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
        </div>
    </div>
</div>

{{-- Stats Grid --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-5 mb-6">

    {{-- Total Inspeksi --}}
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="h-11 w-11 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-800">0</p>
        <p class="text-sm font-medium text-gray-600 mt-0.5">Total Inspeksi</p>
        <p class="text-xs text-gray-400 mt-1">Sepanjang waktu</p>
    </div>

    {{-- Menunggu Review --}}
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="h-11 w-11 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-amber-500">0</p>
        <p class="text-sm font-medium text-gray-600 mt-0.5">Menunggu</p>
        <p class="text-xs text-gray-400 mt-1">Perlu ditinjau</p>
    </div>

    {{-- Lulus QC --}}
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="h-11 w-11 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-emerald-600">0</p>
        <p class="text-sm font-medium text-gray-600 mt-0.5">Lulus QC</p>
        <p class="text-xs text-gray-400 mt-1">Produk lolos</p>
    </div>

    {{-- Tidak Lulus --}}
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="h-11 w-11 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-red-500">0</p>
        <p class="text-sm font-medium text-gray-600 mt-0.5">Tidak Lulus</p>
        <p class="text-xs text-gray-400 mt-1">Produk ditolak</p>
    </div>

</div>

{{-- Content Grid --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Inspeksi Terbaru --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Inspeksi Terbaru</h3>
            <a href="#" class="text-xs font-medium text-emerald-600 hover:text-emerald-800 transition-colors">Lihat semua →</a>
        </div>

        {{-- Empty state --}}
        <div class="flex flex-col items-center justify-center py-14 px-6 text-center">
            <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <h4 class="text-sm font-semibold text-gray-700 mb-1">Belum Ada Data Inspeksi</h4>
            <p class="text-xs text-gray-400 max-w-xs leading-relaxed">Data inspeksi akan tampil di sini setelah Anda mulai menginput hasil pemeriksaan produk.</p>
            <a href="#" class="mt-5 inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Mulai Inspeksi Baru
            </a>
        </div>
    </div>

    {{-- Side Panel --}}
    <div class="flex flex-col gap-5">

        {{-- Aktivitas Hari Ini --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Aktivitas Hari Ini</h3>
            <div class="space-y-2.5">
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-amber-400 flex-shrink-0"></div>
                        <span class="text-sm text-gray-600">Menunggu Review</span>
                    </div>
                    <span class="text-sm font-bold text-gray-800">0</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-emerald-500 flex-shrink-0"></div>
                        <span class="text-sm text-gray-600">Disetujui</span>
                    </div>
                    <span class="text-sm font-bold text-gray-800">0</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-red-500 flex-shrink-0"></div>
                        <span class="text-sm text-gray-600">Ditolak</span>
                    </div>
                    <span class="text-sm font-bold text-gray-800">0</span>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Aksi Cepat</h3>
            <div class="space-y-2">
                <a href="#" class="flex items-center gap-3 p-3 rounded-lg border border-dashed border-emerald-200 hover:border-emerald-400 hover:bg-emerald-50 transition-colors">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="text-sm font-medium text-emerald-700">Buat Inspeksi Baru</span>
                </a>
                <a href="#" class="flex items-center gap-3 p-3 rounded-lg border border-dashed border-blue-200 hover:border-blue-400 hover:bg-blue-50 transition-colors">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span class="text-sm font-medium text-blue-700">Export Laporan</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 p-3 rounded-lg border border-dashed border-gray-200 hover:border-gray-400 hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-600">Edit Profil</span>
                </a>
            </div>
        </div>

    </div>

</div>

@endsection
