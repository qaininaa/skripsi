@php
    $roleText = match(auth()->user()->role) {
        'super' => 'Panel Super Admin - kelola pengguna, jenis laporan, dan pengaturan sistem.',
        'admin' => 'Panel Admin QC - buat dan kelola tugas pelaporan untuk analis.',
        'supervisor' => 'Panel Supervisor - review dan setujui laporan dari analis lab.',
        'manager' => 'Panel Manajer - pantau laporan dan hasil quality control.',
        default => 'Panel Analis Lab. Mikrobiologi - lihat dan kerjakan laporan pemantauan ruangan.',
    };
@endphp

<div class="mb-6 bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl p-6 text-white shadow-lg">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">Selamat Datang, {{ auth()->user()->name }}!</h2>
            <p class="mt-1 text-emerald-200 text-sm">{{ $roleText }}</p>
            <p class="mt-2 text-emerald-300 text-xs">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
        </div>
        <!-- <div class="hidden md:flex h-20 w-20 rounded-2xl bg-white bg-opacity-10 items-center justify-center flex-shrink-0">
            <svg class="w-11 h-11 text-white opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
            </svg>
        </div> -->
    </div>
</div>