@extends('layouts.app')

@section('title', 'Audit Trail')
@section('page-title', 'Audit Trail')
@section('content')

    <div class="mb-4">
        <h2 class="text-xl font-bold text-gray-800">Audit Trail</h2>
        <p class="text-sm text-gray-500 mt-0.5">Riwayat aktivitas penting seperti login dan manajemen akun.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Deskripsi</th>
                        <!-- <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">IP</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User Agent</th> -->
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @php
                                        $name = $log->user->name ?? 'Tidak diketahui';
                                        $role = $log->user->role ?? null;
                                        $avatarColor = match($role) {
                                            'super' => 'bg-indigo-500',
                                            'admin' => 'bg-emerald-500',
                                            default => 'bg-gray-400',
                                        };
                                    @endphp
                                    <div class="h-7 w-7 rounded-full {{ $avatarColor }} flex items-center justify-center text-white text-xs font-semibold">
                                        {{ strtoupper(substr($name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $name }}</p>
                                        @if($role)
                                            <p class="text-xs text-gray-400">{{ $role }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase">{{ str_replace('_', ' ', $log->action) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $log->description }}</td>
                            <!-- <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $log->ip_address ?? '—' }}</td>
                            <td class="px-4 py-3 text-[11px] text-gray-400 max-w-xs truncate">{{ $log->user_agent ?? '—' }}</td> -->
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada data audit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs instanceof \Illuminate\Contracts\Pagination\Paginator || $logs instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
