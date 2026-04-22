@extends('layouts.app')

@section('title', 'Jenis Laporan')
@section('page-title', 'Jenis Laporan')
@section('content')

<div class="max-w-5xl mx-auto" x-data="{ showDeleteModal: false, deleteAction: '', itemName: '' }">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Daftar Jenis Laporan</h2>
            <p class="text-sm text-gray-500 mt-0.5">Kelola jenis laporan Annex beserta konfigurasinya.</p>
        </div>
        <a href="{{ route('report-types.create') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-green-700 text-white text-sm font-medium hover:opacity-70 shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Tambah Jenis Laporan
        </a>
    </div>

    @if (session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-100 rounded-xl text-sm text-green-700 flex items-center gap-2">
        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-xl text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Annex</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Nama</th>
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Seksi</th>
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($reportTypes as $reportType)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                            {{ $reportType->annex_number }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-800 max-w-xs truncate">{{ $reportType->name }}</td>
                    <td class="px-5 py-3.5 text-center text-sm text-gray-600">{{ $reportType->sections_count }}</td>
                    <td class="px-5 py-3.5 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <x-buttons.detail-button :href="route('report-types.show', $reportType)" />
                            <x-buttons.edit-button :href="route('report-types.edit', $reportType)" />
                            <x-buttons.delete-button
                                :action="route('report-types.destroy', $reportType)"
                                :name="$reportType->name"
                            />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-400">Belum ada jenis laporan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $reportTypes->links() }}
    </div>
    <x-delete-modal
        title="Hapus Jenis Laporan"
        warning="Data jenis laporan akan dihapus permanen dan tidak dapat dikembalikan."
    />
</div>
@endsection
