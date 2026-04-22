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
                   placeholder="Cari nama atau nomor ruangan..."
                   class="block w-full rounded-lg border-gray-300 pl-9 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
        </div>
        <select name="class" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500 pr-8">
            <option value="">Semua Kelas</option>
            <option value="A" {{ request('class') === 'A' ? 'selected' : '' }}>Kelas A</option>
            <option value="B" {{ request('class') === 'B' ? 'selected' : '' }}>Kelas B</option>
            <option value="C" {{ request('class') === 'C' ? 'selected' : '' }}>Kelas C</option>
            <option value="D" {{ request('class') === 'D' ? 'selected' : '' }}>Kelas D</option>
        </select>
        <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700 shadow-sm transition-colors">
            Cari
        </button>
        <a href="{{ route('master.ruangan.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 shadow-sm bg-white text-gray-700 text-sm font-medium hover:bg-gray-100 transition-colors">
            Reset
        </a>
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
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($rooms as $room)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3.5 text-gray-400 text-xs">{{ $rooms->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-3.5 font-medium text-gray-800">{{ $room->room_name }}</td>
                            <td class="px-6 py-3.5 text-gray-600">{{ $room->room_number }}</td>
                            <td class="px-6 py-3.5">
                                @php
                                    $classBadge = match($room->class) {
                                        'A' => 'bg-purple-100 text-purple-700',
                                        'B' => 'bg-blue-100 text-blue-700',
                                        'C' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $classBadge }}">
                                    {{ $room->class }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-gray-600">{{ $room->locations_count }} lokasi</td>
                            <td class="px-6 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <x-edit-button :href="route('master.ruangan.edit', $room)" />
                                    <x-delete-button
                                        :action="route('master.ruangan.destroy', $room)"
                                        :name="$room->room_name"
                                    />
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
