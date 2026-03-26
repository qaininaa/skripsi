@extends('layouts.admin')

@section('title', 'Tambah Tugas Pelaporan')
@section('page-title', 'Tambah Tugas Pelaporan')
@section('avatar-color', 'bg-emerald-600')

@section('sidebar')
    @include('dashboard.tugas-pelaporan.partials.sidebar')
@endsection

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
         x-data="tugasForm({{ $analis->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values()->toJson() }})">

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
                <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}"
                       class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
            </div>

            {{-- SHIFT 1 --}}
            <div class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-4">
                <h3 class="text-sm font-bold text-emerald-800 uppercase tracking-wide mb-3">Shift 1</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Analis <span class="text-red-500">*</span>
                    </label>
                    <select name="shift1_analis_id" x-model="s1" @change="onS1Change()"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                        <option value="">— Pilih Analis —</option>
                        @foreach ($analis as $a)
                            <option value="{{ $a->id }}" {{ old('shift1_analis_id') == $a->id ? 'selected' : '' }}>
                                {{ $a->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('shift1_analis_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- SHIFT 2 --}}
            <div class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-4">
                <h3 class="text-sm font-bold text-indigo-800 uppercase tracking-wide mb-3">Shift 2</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Analis <span class="text-red-500">*</span>
                    </label>
                    <select name="shift2_analis_id" x-model="s2"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">— Pilih Analis —</option>
                        <template x-for="a in availS2" :key="a.id">
                            <option :value="a.id" x-text="a.name"
                                    :selected="a.id == {{ old('shift2_analis_id', 0) }}"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Tidak bisa memilih analis yang sudah ditugaskan di Shift 1.</p>
                    @error('shift2_analis_id')
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
function tugasForm(analysts) {
    return {
        analysts: analysts,
        s1: '{{ old('shift1_analis_id', '') }}',
        s2: '{{ old('shift2_analis_id', '') }}',

        get availS2() {
            return this.analysts.filter(a => String(a.id) !== String(this.s1));
        },

        onS1Change() {
            if (String(this.s2) === String(this.s1)) {
                this.s2 = '';
            }
        },
    };
}
</script>
@endsection
