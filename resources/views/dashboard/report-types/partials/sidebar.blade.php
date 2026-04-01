<div class="flex flex-col h-full bg-indigo-950 text-white">
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

    <div class="px-5 py-3 border-b border-indigo-800 flex-shrink-0">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-700 text-indigo-100">
            <span class="h-1.5 w-1.5 rounded-full bg-indigo-300"></span>
            Super Admin
        </span>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
        <a href="{{ route('dashboard.super-admin') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-indigo-300 hover:bg-indigo-800 hover:text-white font-medium text-sm transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </a>

        <a href="{{ route('users.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('users.*') ? 'bg-indigo-800 text-white' : 'text-indigo-300 hover:bg-indigo-800 hover:text-white' }} font-medium text-sm transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Manajemen Pengguna
        </a>

        <a href="{{ route('report-types.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('report-types.*') ? 'bg-indigo-800 text-white' : 'text-indigo-300 hover:bg-indigo-800 hover:text-white' }} font-medium text-sm transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            Jenis Laporan
        </a>

        <a href="{{ route('dashboard.admin-qc') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-indigo-300 hover:bg-indigo-800 hover:text-white font-medium text-sm transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            Dashboard QC
        </a>

        <a href="#"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-indigo-300 hover:bg-indigo-800 hover:text-white font-medium text-sm transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Laporan
        </a>

        <a href="{{ route('audit-logs.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('audit-logs.*') ? 'bg-indigo-800 text-white' : 'text-indigo-300 hover:bg-indigo-800 hover:text-white' }} font-medium text-sm transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Audit Trail
        </a>

        <div class="pt-2 pb-1 px-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-500">Akun</p>
        </div>

        <a href="{{ route('profile.edit') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-indigo-300 hover:bg-indigo-800 hover:text-white font-medium text-sm transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Profil Saya
        </a>
    </nav>

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
