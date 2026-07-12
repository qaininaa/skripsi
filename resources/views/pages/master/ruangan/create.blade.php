@extends('layouts.app')

@section('title', 'Tambah Ruangan')
@section('page-title', 'Tambah Ruangan')
@section('content')

    <div class="max-w-2xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('master.ruangan.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke daftar ruangan
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Tambah Ruangan Baru</h2>

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

            <form action="{{ route('master.ruangan.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ruangan <span class="text-red-500">*</span></label>
                    <input type="text" name="room_name" value="{{ old('room_name') }}"
                           placeholder="Contoh: Ruang Produksi Tablet"
                           class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Ruangan <span class="text-red-500">*</span></label>
                    <input type="text" name="room_number" value="{{ old('room_number') }}"
                           placeholder="Contoh: R-101"
                           class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kelas <span class="text-red-500">*</span></label>
                    <select name="class"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" required>
                        <option value="" disabled {{ old('class') ? '' : 'selected' }}>Pilih kelas ruangan</option>
                        <option value="A" {{ old('class') === 'A' ? 'selected' : '' }}>Kelas A</option>
                        <option value="B" {{ old('class') === 'B' ? 'selected' : '' }}>Kelas B</option>
                        <option value="C" {{ old('class') === 'C' ? 'selected' : '' }}>Kelas C</option>
                        <option value="D" {{ old('class') === 'D' ? 'selected' : '' }}>Kelas D</option>
                        <option value="E" {{ old('class') === 'E' ? 'selected' : '' }}>Kelas E</option>
                    </select>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-green-700 text-white text-sm font-medium hover:bg-green-800 shadow-sm transition-colors">
                        Simpan Ruangan
                    </button>
                    <a href="{{ route('master.ruangan.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 shadow-sm transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection
