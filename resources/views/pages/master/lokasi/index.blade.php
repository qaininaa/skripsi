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
    <form method="GET" action="{{ route('master.lokasi.index') }}" class="mb-4 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama ruangan atau nomor lokasi..."
                   class="block w-full rounded-lg border-gray-300 pl-9 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
        </div>
        <select name="room_id"
                class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
            <option value="">Semua Ruangan</option>
            @foreach ($rooms as $room)
                <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                    {{ $room->room_name }}
                </option>
            @endforeach
        </select>
        <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700 shadow-sm transition-colors">
            Filter
        </button>
        @if(request('search') || request('room_id'))
        <a href="{{ route('master.lokasi.index') }}"
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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-10">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ruangan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Lokasi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipe Pengukuran</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Frekuensi</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Alert Bakt.</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Alert Jmr.</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($locations as $loc)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3.5 text-gray-400 text-xs">{{ $locations->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3.5">
                                <p class="font-medium text-gray-800">{{ $loc->room->room_name ?? '—' }}</p>
                                <p class="text-xs text-gray-400">{{ $loc->room->room_number ?? '' }} &middot; Kelas {{ $loc->room->class ?? '' }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-gray-600">{{ $loc->location_number ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-gray-600">{{ $loc->measurement_type ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-gray-600">{{ $loc->frequency->name ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-center">
                                @if($loc->alert_limit_bacteria !== null || $loc->alert_action_bacteria !== null)
                                    <span class="text-xs text-gray-600">
                                        L: {{ $loc->alert_limit_bacteria ?? '—' }} / A: {{ $loc->alert_action_bacteria ?? '—' }}
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($loc->alert_limit_fungi !== null || $loc->alert_action_fungi !== null)
                                    <span class="text-xs text-gray-600">
                                        L: {{ $loc->alert_limit_fungi ?? '—' }} / A: {{ $loc->alert_action_fungi ?? '—' }}
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('master.lokasi.edit', $loc) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>
                                    <button type="button"
                                            data-action="{{ route('master.lokasi.destroy', $loc) }}"
                                            data-name="{{ $loc->room->room_name ?? 'Lokasi' }} - No. {{ $loc->location_number ?? $loc->id }}"
                                            @click="deleteAction = $el.dataset.action; itemName = $el.dataset.name; showDeleteModal = true"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 transition-colors">
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
