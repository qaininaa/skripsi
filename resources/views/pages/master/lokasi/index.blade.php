@extends('layouts.app')

@section('title', 'Data Master - Lokasi')
@section('page-title', 'Data Master Lokasi')
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
            <h2 class="text-xl font-bold text-gray-800">Daftar Lokasi</h2>
            <p class="text-sm text-gray-500 mt-0.5">Kelola data lokasi pengambilan sampel.</p>
        </div>
        <a href="{{ route('master.lokasi.create') }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-green-700 text-white text-sm font-medium hover:bg-green-800 transition-colors shadow-sm sm:whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
            </svg>
            Tambah Lokasi
        </a>
    </div>

    {{-- Filter & Pencarian --}}
    <x-form.search-filter
        :action="route('master.lokasi.index')"
        placeholder="Cari nama ruangan atau nomor lokasi..."
        :resetRoute="route('master.lokasi.index')"
    >
        <select name="room_id"
                class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
            <option value="">Semua Ruangan</option>
            @foreach ($rooms as $room)
                <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                    {{ $room->room_name }}
                </option>
            @endforeach
        </select>
    </x-form.search-filter>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-10">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ruangan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Lokasi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipe Pengukuran</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Frekuensi</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Alert Total (T)</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Alert Fungi (F)</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($locations as $loc)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3.5 text-gray-400 text-xs">{{ $locations->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3.5">
                                <p class="font-medium text-gray-800">{{ $loc->room->room_name ?? '-' }}</p>
                                <p class="text-xs text-gray-400">{{ $loc->room->room_number ?? '' }} &middot; Kelas {{ $loc->room->class ?? '' }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-gray-600">{{ $loc->location_number ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-gray-600">{{ $loc->getFormattedMeasurementType() }}</td>
                            <td class="px-4 py-3.5 text-gray-600">{{ $loc->frequency?->getIndonesianLabel() ?? '-' }}</td>
                            <td class="px-4 py-3.5">
                                <p class="text-xs text-gray-600">Batas Alert: {{ $loc->alert_limit_total ?? '-' }}</p>
                                <p class="text-xs text-gray-500">Batas Aksi: {{ $loc->alert_action_total ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3.5">
                                <p class="text-xs text-gray-600">Batas Alert: {{ $loc->alert_limit_fungi ?? '-' }}</p>
                                <p class="text-xs text-gray-500">Batas Aksi: {{ $loc->alert_action_fungi ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-buttons.edit-button :href="route('master.lokasi.edit', $loc)" />
                                    <x-buttons.delete-button
                                        :action="route('master.lokasi.destroy', $loc)"
                                        :name="$loc->room->room_name ?? 'Lokasi' . ' - No. ' . ($loc->location_number ?? $loc->id)"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-400 text-sm">
                                Tidak ada data lokasi ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($locations->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $locations->links() }}
            </div>
        @endif
    </div>

    <x-delete-modal
        title="Hapus Lokasi"
        warning="Data lokasi akan dihapus permanen dan tidak dapat dikembalikan."
    />
</div>

@endsection
