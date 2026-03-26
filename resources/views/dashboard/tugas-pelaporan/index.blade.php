@extends('layouts.admin')

@section('title', 'Tugas Pelaporan')
@section('page-title', 'Tugas Pelaporan')
@section('avatar-color', 'bg-emerald-600')

@section('sidebar')
    @include('dashboard.tugas-pelaporan.partials.sidebar')
@endsection

@section('content')

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Tugas Pelaporan</h2>
            <p class="text-sm text-gray-500 mt-0.5">Daftar penugasan analis per shift.</p>
        </div>
        <a href="{{ route('tugas-pelaporan.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Tugas
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Analis Shift 1</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Analis Shift 2</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dibuat Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($tugas as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 whitespace-nowrap">
                                {{ $item->tanggal->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $item->shift1Analis->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $item->shift2Analis->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-400">{{ $item->createdBy->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">
                                Belum ada tugas pelaporan. <a href="{{ route('tugas-pelaporan.create') }}" class="text-emerald-600 hover:underline">Tambah sekarang</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tugas->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                {{ $tugas->links() }}
            </div>
        @endif
    </div>

@endsection
