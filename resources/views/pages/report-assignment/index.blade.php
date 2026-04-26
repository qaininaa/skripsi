@extends('layouts.app')

@section('title', 'Tugas Pelaporan')
@section('page-title', 'Tugas Pelaporan')
@section('content')

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

    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Tugas Pelaporan</h2>
            <p class="text-sm text-gray-500 mt-0.5">Daftar tugas pelaporan yang dibuat.</p>
        </div>
        <a href="{{ route('report-assignment.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Tugas
        </a>
    </div>

    {{-- Filter & Pencarian --}}
    <x-form.search-filter
        :action="route('report-assignment.index')"
        placeholder="Cari nama atau nomor batch.."
        :resetRoute="route('report-assignment.index')"
    >
         <select name="status"
                class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="">Semua Status</option>
            <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
            <option value="ongoing"   {{ request('status') === 'ongoing'   ? 'selected' : '' }}>Sedang Berlangsung</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
        </select>
    </x-form.search-filter>


    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Produk</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Batch Produk</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Laporan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dibuat Oleh</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($reportAssignments as $item)
                        <tr class="hover:bg-gray-50 align-center">
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
                                {{ $item->reportType->annex_number ?? '-' }} — {{ $item->reportType->name ?? '-' }}
                            </td>

                            <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ $item->createdBy->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs whitespace-nowrap {{ $item->status == 'completed' ? 'text-green-600' : 'text-orange-600' }}">{{ $item->status ?? '-' }}</td>

                            {{-- Tombol lihat, edit & hapus --}}
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <x-buttons.view-button href="{{ route('admin.laporan.preview', $item) }}" />
                                <x-buttons.edit-button onclick="window.location.href='{{ route('report-assignment.edit', $item) }}'" />
                                    <x-buttons.delete-button
                                        :action="route('report-assignment.destroy', $item)"
                                        :name="$item->product_name"
                                    />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">
                                Belum ada tugas pelaporan. <a href="{{ route('report-assignment.create') }}" class="text-emerald-600 hover:underline">Tambah sekarang</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reportAssignments->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                {{ $reportAssignments->links() }}
            </div>
        @endif
    </div>

@endsection
