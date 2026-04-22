@extends('layouts.app')

@section('title', 'Jenis Laporan')
@section('page-title', 'Jenis Laporan')
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
            <h2 class="text-xl font-bold text-gray-800">Daftar Jenis Laporan</h2>
            <p class="text-sm text-gray-500 mt-0.5">Kelola jenis laporan Annex beserta konfigurasinya.</p>
        </div>
        <a href="{{ route('report-types.create') }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-green-700 text-white text-sm font-medium hover:bg-green-800 transition-colors shadow-sm sm:whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
            </svg>
            Tambah Jenis Laporan
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-10">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Annex</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kode SOP</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Versi SOP</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Seksi</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($reportTypes as $reportType)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3.5 text-gray-400 text-xs">{{ $loop->iteration }}</td>
                            <td class="px-3 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                                    Annex {{ $reportType->annex_number }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 font-medium text-gray-800">{{ $reportType->sop_code }}</td>
                            <td class="px-6 py-3.5 font-medium text-gray-800">{{ $reportType->sop_version }}</td>
                            <td class="px-6 py-3.5 font-medium text-gray-800">{{ $reportType->name }}</td>
                            <td class="px-6 py-3.5 text-center text-gray-600">{{ $reportType->sections_count }}</td>
                            <td class="px-6 py-3.5 text-center">
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
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400 text-sm">
                                Belum ada jenis laporan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reportTypes->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $reportTypes->links() }}
            </div>
        @endif
    </div>

    <x-delete-modal
        title="Hapus Jenis Laporan"
        warning="Data jenis laporan akan dihapus permanen dan tidak dapat dikembalikan."
    />
</div>
@endsection
