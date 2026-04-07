@extends('layouts.app')

@section('title', 'Data Master - Ruangan')
@section('page-title', 'Data Master Ruangan')
@section('content')
<style>[x-cloak]{display:none!important}</style>
<div x-data="{ showDeleteModal: false, deleteAction: '', itemName: '' }">

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

    {{-- Header + Tombol Tambah --}}
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Daftar Ruangan</h2>
            <p class="text-sm text-gray-500 mt-0.5">Kelola data ruangan yang digunakan dalam laporan.</p>
        </div>
        <a href="{{ route('master.ruangan.create') }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-green-700 text-white text-sm font-medium hover:bg-green-800 transition-colors shadow-sm sm:whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
            </svg>
            Tambah Ruangan
        </a>
    </div>

    {{-- Pencarian --}}
    <form method="GET" action="{{ route('master.ruangan.index') }}" class="mb-4 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama, nomor, atau kelas ruangan..."
                   class="block w-full rounded-lg border-gray-300 pl-9 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
        </div>
        <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700 shadow-sm transition-colors">
            Cari
        </button>
        @if(request('search'))
        <a href="{{ route('master.ruangan.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 shadow-sm bg-white text-gray-700 text-sm font-medium hover:bg-gray-100 transition-colors">
            Reset
        </a>
        @endif
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-10">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Ruangan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nomor Ruangan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jml. Lokasi</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($rooms as $room)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3.5 text-gray-400 text-xs">{{ $rooms->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-3.5 font-medium text-gray-800">{{ $room->room_name }}</td>
                            <td class="px-6 py-3.5 text-gray-600">{{ $room->room_number }}</td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    {{ $room->class }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-gray-600">{{ $room->locations_count }} lokasi</td>
                            <td class="px-6 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('master.ruangan.edit', $room) }}"
                                       class="btn-action-edit">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>
                                    <button type="button"
                                            data-action="{{ route('master.ruangan.destroy', $room) }}"
                                            data-name="{{ $room->room_name }}"
                                            @click="deleteAction = $el.dataset.action; itemName = $el.dataset.name; showDeleteModal = true"
                                            class="btn-action-delete">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400 text-sm">
                                Tidak ada data ruangan ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($rooms->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $rooms->links() }}
            </div>
        @endif
    </div>

    <x-delete-modal
        title="Hapus Ruangan"
        warning="Pastikan tidak ada lokasi yang terhubung ke ruangan ini. Tindakan ini tidak dapat dibatalkan."
    />
</div>

@endsection
