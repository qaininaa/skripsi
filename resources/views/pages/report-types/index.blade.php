@extends('layouts.app')

@section('title', 'Jenis Laporan')
@section('page-title', 'Jenis Laporan')
@section('avatar-color', 'bg-green-600')
@section('content')
<div class="max-w-5xl mx-auto" x-data="{ showDeleteModal: false, deleteAction: '', itemName: '' }">

    @include('components.delete-modal')

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
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Kode</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Nama</th>
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Seksi</th>
                    <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($reportTypes as $rt)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                            {{ $rt->annex_number }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-sm font-mono text-gray-600">{{ $rt->code }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-800 max-w-xs truncate">{{ $rt->name }}</td>
                    <td class="px-5 py-3.5 text-center text-sm text-gray-600">{{ $rt->sections_count }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('report-types.show', $rt) }}"
                               class="btn-action-detail">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Detail
                            </a>
                            <a href="{{ route('report-types.edit', $rt) }}"
                               class="btn-action-edit">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>
                            <button type="button"
                                    data-action="{{ route('report-types.destroy', $rt) }}"
                                    data-name="{{ $rt->name }}"
                                    @click="deleteAction = $el.dataset.action; itemName = $el.dataset.name; showDeleteModal = true"
                                    class="btn-action-delete">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Hapus
                            </button>
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
</div>
@endsection
