@extends('layouts.admin')

@section('title', 'Tambah Tugas Pelaporan')
@section('page-title', 'Tambah Tugas Pelaporan')
@section('avatar-color', 'bg-green-600')
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

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
         x-data="tugasForm(
            {{ $analis->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values()->toJson() }},
            {{ $reportTypes->map(fn($rt) => ['id' => $rt->id, 'annex_number' => $rt->annex_number, 'name' => $rt->name, 'instrument' => $rt->instrument])->values()->toJson() }},
            {{ $instrumentMap->toJson() }}
         )"
    >

        <h2 class="text-lg font-semibold text-gray-800 mb-5">Tambah Tugas Pelaporan</h2>

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

        <form action="{{ route('tugas-pelaporan.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Tanggal --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="report_date" value="{{ old('report_date', date('Y-m-d')) }}"
                       class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
            </div>

            {{-- Nama Produk --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                <input type="text" name="product_name" value="{{ old('product_name') }}"
                       placeholder="Masukkan nama produk"
                       class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                @error('product_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nomor Batch Produk --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Batch Produk <span class="text-red-500">*</span></label>
                <input type="text" name="batch_number" value="{{ old('batch_number') }}"
                       placeholder="Masukkan nomor batch produk"
                       class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                @error('batch_number')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- SHIFT 1 --}}
            <div class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-4 space-y-4">
                <h3 class="text-sm font-bold text-emerald-800 uppercase tracking-wide">Shift 1</h3>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Analis <span class="text-red-500">*</span>
                    </label>
                    <select name="shift1_analyst_id" x-model="s1" @change="onS1Change()"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                        <option value="">— Pilih Analis —</option>
                        @foreach ($analis as $a)
                            <option value="{{ $a->id }}" {{ old('shift1_analyst_id') == $a->id ? 'selected' : '' }}>
                                {{ $a->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('shift1_analyst_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- SHIFT 2 --}}
            <div class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-4 space-y-4">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-indigo-800 uppercase tracking-wide">Shift 2</h3>
                    <span class="text-xs text-gray-400 font-normal normal-case">(opsional)</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Analis</label>
                    <select name="shift2_analyst_id" x-model="s2"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— Tidak ada Shift 2 —</option>
                        <template x-for="a in availS2" :key="a.id">
                            <option :value="a.id" x-text="a.name"
                                    :selected="a.id == {{ old('shift2_analyst_id', 0) }}"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Kosongkan jika laporan ini tidak memiliki Shift 2. Tidak bisa memilih analis yang sama dengan Shift 1.</p>
                    @error('shift2_analyst_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- LAPORAN (shared shift 1 & 2, estafet) --}}
            <div class="rounded-xl border border-gray-200 bg-gray-50/40 p-4 space-y-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-1">Laporan yang Dikerjakan</h3>
                    <p class="text-xs text-gray-400 mb-3">Shift 1 mengerjakan terlebih dahulu, kemudian dilanjutkan Shift 2.</p>
                </div>

                {{-- Nama Alat --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Alat <span class="text-red-500">*</span></label>
                    <select x-model="selectedInstrument" @change="onInstrumentChange()"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                        <option value="">— Pilih Nama Alat —</option>
                        @foreach ($instruments as $inst)
                            <option value="{{ $inst }}" {{ old('instrument') == $inst ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $inst)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Jenis Laporan (filtered by instrument) --}}
                <div x-show="selectedInstrument" x-transition>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Laporan <span class="text-red-500">*</span></label>
                    <select name="report_type_id" x-model="selectedReportType"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                        <option value="">— Pilih Jenis Laporan —</option>
                        <template x-for="rt in filteredReportTypes" :key="rt.id">
                            <option :value="rt.id" x-text="rt.annex_number + ' — ' + rt.name"
                                    :selected="rt.id == {{ old('report_type_id', 0) }}"></option>
                        </template>
                    </select>
                    @error('report_type_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-2 flex justify-end gap-3">
                <a href="{{ route('tugas-pelaporan.index') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 shadow-sm">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function tugasForm(analysts, reportTypes, instrumentMap) {
    return {
        analysts: analysts,
        reportTypes: reportTypes,
        instrumentMap: instrumentMap,
        s1: '{{ old('shift1_analyst_id', '') }}',
        s2: '{{ old('shift2_analyst_id', '') }}',
        selectedInstrument: '{{ old('instrument', '') }}',
        selectedReportType: '{{ old('report_type_id', '') }}',

        get availS2() {
            return this.analysts.filter(a => String(a.id) !== String(this.s1));
        },

        get filteredReportTypes() {
            if (!this.selectedInstrument || !this.instrumentMap[this.selectedInstrument]) return [];
            const allowedIds = this.instrumentMap[this.selectedInstrument];
            return this.reportTypes.filter(rt => allowedIds.includes(rt.id));
        },

        onS1Change() {
            if (String(this.s2) === String(this.s1)) {
                this.s2 = '';
            }
        },

        onInstrumentChange() {
            this.selectedReportType = '';
        },
    };
}
</script>
@endsection
