@extends('layouts.app')

@section('title', 'Edit Tugas Pelaporan')
@section('page-title', 'Edit Tugas Pelaporan')
@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-4">
        <a href="{{ route('tugas-pelaporan.index') }}"
           class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke daftar
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">

        <h2 class="text-lg font-semibold text-gray-800 mb-5">Edit Tugas Pelaporan</h2>

        @if ($errors->any())
            <div class="mb-5 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                <p class="font-semibold mb-1">Terjadi kesalahan:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('tugas-pelaporan.update', $tugasPelaporan) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                <input type="text" name="product_name" value="{{ old('product_name', $tugasPelaporan->product_name) }}"
                       placeholder="Masukkan nama produk"
                       class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                @error('product_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Batch Produk <span class="text-red-500">*</span></label>
                <input type="text" name="batch_number" value="{{ old('batch_number', $tugasPelaporan->batch_number) }}"
                       placeholder="Masukkan nomor batch produk"
                       class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                @error('batch_number')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Laporan <span class="text-red-500">*</span></label>
                <select name="report_type_id"
                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                    <option value="">— Pilih Jenis Laporan —</option>
                    @foreach ($reportTypes as $rt)
                        <option value="{{ $rt->id }}" {{ $tugasPelaporan->report_type_id == $rt->id ? 'selected' : '' }}>
                            {{ $rt->annex_number }} — {{ $rt->name }}
                        </option>
                    @endforeach
                </select>
                @error('report_type_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2 flex justify-end gap-3">
                <a href="{{ route('tugas-pelaporan.index') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
