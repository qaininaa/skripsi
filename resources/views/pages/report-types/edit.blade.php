@extends('layouts.app')

@section('title', 'Edit Jenis Laporan')
@section('page-title', 'Edit Jenis Laporan')
@section('avatar-color', 'bg-green-600')
@section('content')
<div class="max-w-3xl mx-auto" x-data="reportTypeForm()">

    <div class="mb-4">
        <a href="{{ route('report-types.show', $reportType) }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke detail
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Edit Jenis Laporan — {{ $reportType->annex_number }}</h2>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                <p class="font-semibold mb-1">Terjadi kesalahan:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('report-types.update', $reportType) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Info Dasar --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode SOP <span class="text-red-500">*</span></label>
                    <input type="text" name="sop_code" value="{{ old('sop_code', $reportType->sop_code) }}" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Versi SOP <span class="text-red-500">*</span></label>
                    <input type="text" name="sop_version" value="{{ old('sop_version', $reportType->sop_version) }}" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Annex <span class="text-red-500">*</span></label>
                    <input type="number" name="annex_number" value="{{ old('annex_number', $reportType->annex_number) }}" min="1" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Laporan <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $reportType->name) }}" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>


            {{-- Medium Groups --}}
            <div class="border-t border-gray-100 pt-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">Medium Groups</h3>
                        <p class="text-xs text-gray-500">Daftar medium yang digunakan (Medium TSP, Swab Kit, dll).</p>
                    </div>
                    <button type="button" @click="addMedium()" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-medium hover:bg-indigo-100">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
                        Tambah Medium
                    </button>
                </div>
                <datalist id="medium-suggestions">
                    <option value="Medium TSP 60mm">
                    <option value="Medium TSP 65mm">
                    <option value="Medium TSP 90mm">
                    <option value="Swab Kit">
                </datalist>
                <div class="space-y-2">
                    <template x-for="(m, idx) in mediums" :key="idx">
                        <div class="flex items-center gap-2">
                            <input type="text" :name="'medium_labels[' + idx + ']'" x-model="m.label"
                                   list="medium-suggestions"
                                   placeholder="cth: Medium TSP 60mm"
                                   class="flex-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="button" @click="mediums.splice(idx, 1)" class="p-2 text-red-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </template>
                    <p x-show="mediums.length === 0" class="text-xs text-gray-400 italic">Belum ada medium group.</p>
                </div>
            </div>

            {{-- Incubators --}}
            <div class="border-t border-gray-100 pt-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">Inkubator</h3>
                        <p class="text-xs text-gray-500">Pilih suhu inkubasi — durasi min. terisi otomatis.</p>
                    </div>
                    <button type="button" @click="addIncubator()" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-medium hover:bg-indigo-100">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
                        Tambah Inkubator
                    </button>
                </div>
                <div class="space-y-2">
                    <template x-for="(inc, idx) in incubators" :key="idx">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 flex items-center rounded-lg border border-gray-300 shadow-sm bg-white focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
                                <input type="text" :name="'incubator_labels[' + idx + ']'" x-model="inc.label"
                                       @focus="inc.label = inc.label.replace(/°C$/, '')"
                                       @blur="if (inc.label.trim()) { inc.label = inc.label.replace(/°C$/, '').trim() + '°C' }; inc.min_days = incPresets[inc.label] ?? inc.min_days"
                                       placeholder="cth: 20-25"
                                       class="flex-1 min-w-0 px-3 py-[7px] text-sm border border-gray-300 rounded-lg focus:ring-0">
                                <span class="px-3 text-sm text-gray-400 select-none pointer-events-none">°C</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <input type="number" :name="'incubator_min_days[' + idx + ']'" x-model="inc.min_days"
                                       min="1" placeholder="Hari"
                                       class="w-16 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="text-xs text-gray-500 whitespace-nowrap">hari min.</span>
                            </div>
                            <button type="button" @click="incubators.splice(idx, 1)" class="p-2 text-red-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </template>
                    <p x-show="incubators.length === 0" class="text-xs text-gray-400 italic">Belum ada inkubator.</p>
                </div>
            </div>

            <div class="pt-3 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('report-types.show', $reportType) }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-green-700 text-white text-sm font-medium hover:bg-indigo-700 shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@php
    $mediumsJson    = json_encode($reportType->media->map(fn($m) => ['label' => $m->name])->values());
    $incubatorsJson = json_encode($reportType->incubatorConfigs->map(fn($inc) => ['label' => $inc->temperature_label, 'min_days' => $inc->min_days])->values());
@endphp
<script>
function reportTypeForm() {
    return {
        mediums: {!! $mediumsJson !!},
        incubators: {!! $incubatorsJson !!},
        incPresets: { '20-25°C': 5, '30-35°C': 3 },
        addMedium() {
            this.mediums.push({ label: '' });
        },
        addIncubator() {
            this.incubators.push({ label: '', min_days: 3 });
        },
    }
}
</script>
@endsection
