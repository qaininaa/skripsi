<div class="flex flex-col h-full bg-white text-gray-800 border-r border-gray-100">

    {{-- Brand --}}
    <div class="flex items-center h-16 px-5 border-b border-gray-100 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg bg-green-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <span class="font-bold text-base tracking-tight text-gray-900">Quality Control Panel</span>
        </div>
    </div>

    {{-- Role Badge --}}
    <div class="px-5 py-3 border-b border-gray-100 flex-shrink-0">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700">
            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
            @if(Auth::user()->role === 'super')
                Super Admin
            @elseif(Auth::user()->role === 'admin')
                Admin Quality Control
            @elseif(Auth::user()->role === 'analis')
                Analis Lab. Mikrobiologi
            @elseif(Auth::user()->role === 'supervisor')
                Supervisor Mikrobiologi
            @elseif(Auth::user()->role === 'manajer')
                Manajer
            @endif
        </span>
    </div>

    {{-- Navigation --}}
    @php
        $sidebarIncomingCount = 0;
        $sidebarOngoingCount = 0;
        $currentRole = Auth::user()->role;

        if (in_array($currentRole, ['supervisor', 'manajer'], true)) {
            $userId = Auth::id();

            $sidebarIncomingCount = \App\Models\ReportApproval::query()
                ->where('step', $currentRole === 'supervisor' ? 2 : 3)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->count();

            $sidebarOngoingCount = \App\Models\Report::query()
                ->whereDoesntHave('approvals', function ($query) {
                    $query->where('step', 3)->where('status', 'approved');
                })
                ->where(function ($query) {
                    $query->whereIn('status', ['pending', 'monitoring', 'reading'])
                        ->orWhereHas('approvals', function ($approvalQuery) {
                            $approvalQuery->where('step', 2)->where('status', 'pending');
                        })
                        ->orWhereHas('approvals', function ($approvalQuery) {
                            $approvalQuery->where('step', 3)->where('status', 'pending');
                        });
                })
                ->count();
        }
    @endphp

    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

    <div class="pt-2 pb-1 px-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Dashboard</p>
        </div>

        {{-- Dashboard --}}
        @if(Auth::user()->role === 'super')
        <a href="{{ route('dashboard.super-admin') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('dashboard.super-admin') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} font-medium text-sm transition-colors">
        @elseif(Auth::user()->role === 'admin')
        <a href="{{ route('dashboard.admin-qc') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('dashboard.admin-qc') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} font-medium text-sm transition-colors">
        @elseif(Auth::user()->role === 'analis')
        <a href="{{ route('dashboard.analis') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('dashboard.analis') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} font-medium text-sm transition-colors">
        @elseif(Auth::user()->role === 'supervisor')
        <a href="{{ route('dashboard.supervisor') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('dashboard.supervisor') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} font-medium text-sm transition-colors">
        @elseif(Auth::user()->role === 'manajer')
        <a href="{{ route('dashboard.manajer') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('dashboard.manajer') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} font-medium text-sm transition-colors">
        @else
        <a href="#"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 font-medium text-sm transition-colors">
        @endif
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </a>

        {{-- Super Admin only --}}
        @if(Auth::user()->role === 'super')

        <a href="{{ route('users.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('users.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} font-medium text-sm transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Manajemen Pengguna
        </a>

        <a href="{{ route('audit-logs.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('audit-logs.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} font-medium text-sm transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Audit Trail
        </a>

        <a href="{{ route('settings.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('settings.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} font-medium text-sm transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Pengaturan Password
        </a>

        {{-- Admin QC only --}}
        @elseif(Auth::user()->role === 'admin')
            
         <div class="pt-2 pb-1 px-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Master Data</p>
        </div>
            
                <a href="{{ route('master.room.index') }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('master.ruangan.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} text-sm transition-colors">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Ruangan
                </a>
                <a href="{{ route('master.location.index') }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('master.location.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} text-sm transition-colors">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Lokasi
                </a>
                <a href="{{ route('report-types.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('report-types.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} font-medium text-sm transition-colors">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Manajemen Laporan
                </a>


        <div class="pt-2 pb-1 px-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Laporan</p>
        </div>
        <a href="{{ route('report-assignment.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('report-assignment.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} font-medium text-sm transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            Tugas Pelaporan
        </a>

        {{-- Analis only --}}
        @elseif(Auth::user()->role === 'analis')

        <a href="{{ route('laporan.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('laporan.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} font-medium text-sm transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Laporan
        </a>

        {{-- Supervisor only --}}
        @elseif(Auth::user()->role === 'supervisor')

         <div class="pt-2 pb-1 px-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Laporan</p>
        </div>

        <a href="{{ route('supervisor.laporan-masuk') }}"
           class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('supervisor.laporan-masuk') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} font-medium text-sm transition-colors">
            <span class="flex items-center gap-3 min-w-0">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <span>Laporan Masuk</span>
            </span>
            @if ($sidebarIncomingCount > 0)
                <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-[11px] font-semibold bg-red-100 text-red-700">
                    {{ $sidebarIncomingCount }}
                </span>
            @endif
        </a>

        <a href="{{ route('supervisor.laporan-sedang-dikerjakan') }}"
           class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('supervisor.laporan-sedang-dikerjakan') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} font-medium text-sm transition-colors">
            <span class="flex items-center gap-3 min-w-0">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Sedang Dikerjakan</span>
            </span>
            @if ($sidebarOngoingCount > 0)
                <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-[11px] font-semibold bg-emerald-100 text-emerald-700">
                    {{ $sidebarOngoingCount }}
                </span>
            @endif
        </a>

        {{-- Manajer only --}}
        @elseif(Auth::user()->role === 'manajer')

        <div class="pt-2 pb-1 px-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Laporan</p>
        </div>

        <a href="{{ route('manajer.laporan-masuk') }}"
           class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('manajer.laporan-masuk') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} font-medium text-sm transition-colors">
            <span class="flex items-center gap-3 min-w-0">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <span>Laporan Masuk</span>
            </span>
            @if ($sidebarIncomingCount > 0)
                <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-[11px] font-semibold bg-red-100 text-red-700">
                    {{ $sidebarIncomingCount }}
                </span>
            @endif
        </a>

        <a href="{{ route('manajer.laporan-sedang-dikerjakan') }}"
           class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('manajer.laporan-sedang-dikerjakan') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} font-medium text-sm transition-colors">
            <span class="flex items-center gap-3 min-w-0">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Sedang Dikerjakan</span>
            </span>
            @if ($sidebarOngoingCount > 0)
                <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-[11px] font-semibold bg-emerald-100 text-emerald-700">
                    {{ $sidebarOngoingCount }}
                </span>
            @endif
        </a>

        @endif
        
        @if(Auth::user()->role !== 'super')
        <div class="pt-2 pb-1 px-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Arsip</p>
        </div>

        <a href="{{ route('arsip-laporan.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('arsip-laporan.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-500 hover:bg-green-50 hover:text-green-800' }} font-medium text-sm transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8" />
            </svg>
            Arsip Laporan
        </a>
        @endif
    </nav>

    {{-- User footer --}}
    <div class="p-4 border-t border-gray-100 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-bold text-sm flex-shrink-0">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>

</div>
