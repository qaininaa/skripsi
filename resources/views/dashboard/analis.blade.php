@extends('layouts.admin')

@section('title', 'Dashboard Analis')
@section('page-title', 'Dashboard')
@section('avatar-color', 'bg-green-600')
@section('content')

{{-- Welcome Banner --}}
<div class="mb-6 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">Selamat Datang, {{ Auth::user()->name }}!</h2>
            <p class="mt-1 text-blue-200 text-sm">Panel Analis Lab. Mikrobiologi — lihat dan kerjakan laporan pemantauan ruangan.</p>
            <p class="mt-2 text-blue-300 text-xs">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
        </div>
        <div class="hidden md:flex h-20 w-20 rounded-2xl bg-white bg-opacity-10 items-center justify-center flex-shrink-0">
            <svg class="w-11 h-11 text-white opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
            </svg>
        </div>
    </div>
</div>

{{-- Quick stats --}}
@php
    use App\Models\Report;
    $myItems = Report::where(fn($q) => $q->where('shift1_analyst_id', Auth::id())
                                         ->orWhere('shift2_analyst_id', Auth::id()))
        ->selectRaw('status, count(*) as total')
        ->groupBy('status')
        ->pluck('total', 'status');
@endphp

@php
    $statCards = []; // tidak dipakai lagi
@endphp

<div class="grid grid-cols-2 xl:grid-cols-4 gap-5 mb-6">

    {{-- Menunggu --}}
    <a href="{{ route('laporan.index', ['status' => 'pending']) }}"
       class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="h-11 w-11 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ $myItems['pending'] ?? 0 }}</p>
        <p class="text-sm font-medium text-gray-600 mt-0.5">Menunggu</p>
        <p class="text-xs text-gray-400 mt-1">Belum dikerjakan</p>
    </a>

    {{-- Dikerjakan --}}
    <a href="{{ route('laporan.index', ['status' => 'in_progress']) }}"
       class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="h-11 w-11 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-amber-500">{{ $myItems['in_progress'] ?? 0 }}</p>
        <p class="text-sm font-medium text-gray-600 mt-0.5">Dikerjakan</p>
        <p class="text-xs text-gray-400 mt-1">Sedang diisi</p>
    </a>

    {{-- Dikirim --}}
    <a href="{{ route('laporan.index', ['status' => 'submitted']) }}"
       class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="h-11 w-11 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-blue-600">{{ $myItems['submitted'] ?? 0 }}</p>
        <p class="text-sm font-medium text-gray-600 mt-0.5">Dikirim</p>
        <p class="text-xs text-gray-400 mt-1">Menunggu review</p>
    </a>

    {{-- Dikembalikan --}}
    <a href="{{ route('laporan.index', ['status' => 'returned']) }}"
       class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="h-11 w-11 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-orange-500">{{ $myItems['returned'] ?? 0 }}</p>
        <p class="text-sm font-medium text-gray-600 mt-0.5">Dikembalikan</p>
        <p class="text-xs text-gray-400 mt-1">Perlu direvisi</p>
    </a>

    {{-- Disetujui --}}
    <a href="{{ route('laporan.index', ['status' => 'approved']) }}"
       class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="h-11 w-11 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-emerald-600">{{ $myItems['approved'] ?? 0 }}</p>
        <p class="text-sm font-medium text-gray-600 mt-0.5">Disetujui</p>
        <p class="text-xs text-gray-400 mt-1">Laporan final</p>
    </a>

</div>

{{-- Shortcut ke laporan --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="font-semibold text-gray-800">Laporan Saya</h3>
            <p class="text-sm text-gray-500 mt-0.5">Lihat semua penugasan dan status laporan Anda.</p>
        </div>
        <a href="{{ route('laporan.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition-colors shadow-sm">
            Buka Laporan
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>
</div>

@endsection
