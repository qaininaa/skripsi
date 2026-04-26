@extends('layouts.app')

@section('title', 'Data Master - Ruangan')
@section('page-title', 'Data Master Ruangan')
@section('content')
<style>[x-cloak]{display:none!important}</style>
<div>

    @if (session('success'))
        <x-messages.success-message>
            {{ session('success') }}
        </x-messages.success-message>
    @endif

    @if (session('error'))
        <x-messages.error-message>
            {{ session('error') }}
        </x-messages.error-message>
    @endif

    {{-- Header + Tombol Tambah --}}
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Daftar Ruangan</h2>
            <p class="text-sm text-gray-500 mt-0.5">Kelola data ruangan yang digunakan dalam laporan.</p>
        </div>
        <a href="{{ route('master.room.create') }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-green-700 text-white text-sm font-medium hover:bg-green-800 transition-colors shadow-sm sm:whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
            </svg>
            Tambah Ruangan
        </a>
    </div>

    {{-- Pencarian --}}
    <x-form.search-filter
        :action="route('master.room.index')"
        placeholder="Cari nama atau nomor ruangan..."
        :resetRoute="route('master.room.index')"
    >
        <select name="class" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500 pr-8">
            <option value="">Semua Kelas</option>
            <option value="A" {{ request('class') === 'A' ? 'selected' : '' }}>Kelas A</option>
            <option value="B" {{ request('class') === 'B' ? 'selected' : '' }}>Kelas B</option>
            <option value="C" {{ request('class') === 'C' ? 'selected' : '' }}>Kelas C</option>
            <option value="D" {{ request('class') === 'D' ? 'selected' : '' }}>Kelas D</option>
        </select>
    </x-form.search-filter>

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
                                    <x-buttons.edit-button onclick="window.location.href='{{ route('master.room.edit', $room) }}'" />
                                    <x-buttons.delete-button
                                        :action="route('master.room.destroy', $room)"
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

</div>

@endsection
