@extends('layouts.admin')

@section('title', 'Dashboard Supervisor')
@section('page-title', 'Dashboard')
@section('avatar-color', 'bg-green-600')
@section('content')

{{-- Welcome Banner --}}
<div class="mb-6 bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl p-6 text-white shadow-lg">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">Selamat Datang, {{ Auth::user()->name }}!</h2>
            <p class="mt-1 text-emerald-200 text-sm">Panel Supervisor Lab. Mikrobiologi — tinjau dan setujui laporan dari analis.</p>
            <p class="mt-2 text-emerald-300 text-xs">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
        </div>
        <div class="hidden md:flex h-20 w-20 rounded-2xl bg-white bg-opacity-10 items-center justify-center flex-shrink-0">
            <svg class="w-11 h-11 text-white opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
        <div class="h-12 w-12 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-medium">Menunggu Review</p>
            <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ $pending }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
        <div class="h-12 w-12 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-medium">Disetujui</p>
            <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ $approved }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
        <div class="h-12 w-12 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-medium">Ditolak</p>
            <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ $rejected }}</p>
        </div>
    </div>
</div>

{{-- Quick link --}}
@if ($pending > 0)
<div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <p class="text-sm font-medium text-amber-800">Ada <span class="font-bold">{{ $pending }}</span> laporan menunggu tinjauan Anda.</p>
    </div>
    <a href="{{ route('supervisor.laporan-masuk') }}"
       class="flex-shrink-0 px-4 py-2 rounded-lg bg-amber-500 text-white text-sm font-medium hover:bg-amber-600 transition-colors">
        Tinjau Sekarang
    </a>
</div>
@endif

@endsection
