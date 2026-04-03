@extends('layouts.admin')

@section('title', 'Tugas Pelaporan')
@section('page-title', 'Tugas Pelaporan')
@section('avatar-color', 'bg-green-600')
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
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($tugas as $item)
                        <tr class="hover:bg-gray-50 align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 whitespace-nowrap">
                                {{ $item->tanggal->format('d M Y') }}
                            </td>

                            {{-- Nama Produk --}}
                            <td class="px-4 py-3 text-sm text-gray-800">
                                {{ $item->nama_produk ?? '—' }}
                            </td>

                            {{-- Batch Produk --}}
                            <td class="px-4 py-3 text-sm text-gray-800">
                                {{ $item->nomor_batch_produk ?? '—' }}
                            </td>

                            {{-- Alat Instrumen --}}
                            <td class="px-4 py-3 text-sm text-gray-800">
                                {{ ucwords(str_replace('_', ' ', $item->reportType->instrument ?? '—')) }}
                            </td>

                            {{-- Shift 1 --}}
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 whitespace-nowrap">
                                {{ $item->shift1Analis->name ?? '—' }}
                            </td>

                            {{-- Shift 2 --}}
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 whitespace-nowrap">
                                {{ $item->shift2Analis->name ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ $item->createdBy->name ?? '—' }}</td>

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
                                      onsubmit="return confirm('Hapus tugas pelaporan tanggal {{ $item->tanggal->format('d M Y') }}?')">
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
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-500">
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
