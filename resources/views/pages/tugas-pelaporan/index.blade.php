@extends('layouts.app')

@section('title', 'Tugas Pelaporan')
@section('page-title', 'Tugas Pelaporan')
@section('content')

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
            {{ session('error') }}
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

    {{-- Filter & Pencarian --}}
    <form method="GET" action="{{ route('tugas-pelaporan.index') }}" class="mb-4 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama produk atau nomor batch..."
                   class="block w-full rounded-lg border-gray-300 pl-9 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
        </div>
        <select name="status"
                class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="">Semua Status</option>
            <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
            <option value="ongoing"   {{ request('status') === 'ongoing'   ? 'selected' : '' }}>Ongoing</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
        </select>
        <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 shadow-sm transition-colors">
            Filter
        </button>
        <button type="button" onclick="window.location.href='{{ route('tugas-pelaporan.index') }}'" 
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 shadow-sm bg-white text-gray-700 text-sm font-medium hover:bg-gray-200 transition-colors">
             Reset
        </button>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Produk</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Batch Produk</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Alat Instrumen</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Shift 1</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Shift 2</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dibuat Oleh</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($tugas as $item)
                        <tr class="hover:bg-gray-50 align-top">
                            {{-- Tanggal --}}
                            <td class="px-4 py-3 text-sm text-gray-800">
                                {{ $item->created_at ?? '-' }}
                            </td>
                            {{-- Nama Produk --}}
                            <td class="px-4 py-3 text-sm text-gray-800">
                                {{ $item->product_name ?? '-' }}
                            </td>

                            {{-- Batch Produk --}}
                            <td class="px-4 py-3 text-sm text-gray-800">
                                {{ $item->batch_number ?? '-' }}
                            </td>

                            {{-- Alat Instrumen --}}
                            <td class="px-4 py-3 text-sm text-gray-800">
                                {{ ucwords(str_replace('_', ' ', $item->reportType->instrument ?? '-')) }}
                            </td>

                            {{-- Shift 1 --}}
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 whitespace-nowrap">
                                {{ $item->shift1Analis->name ?? '-' }}
                            </td>

                            {{-- Shift 2 --}}
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 whitespace-nowrap">
                                {{ $item->shift2Analis->name ?? '-' }}
                            </td>

                            <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ $item->createdBy->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs whitespace-nowrap {{ $item->status == 'completed' ? 'text-green-600' : 'text-orange-600' }}">{{ $item->status ?? '-' }}</td>

                            {{-- Tombol edit & hapus --}}
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('tugas-pelaporan.edit', $item) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </a>

                                <form action="{{ route('tugas-pelaporan.destroy', $item) }}" method="POST"
                                      class="inline-block ml-1"
                                      onsubmit="return confirm('Hapus tugas pelaporan {{ $item->product_name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-red-100 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">
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
