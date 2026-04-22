@extends('layouts.app')

@section('title', 'Edit Ruangan')
@section('page-title', 'Edit Ruangan')
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
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Edit Ruangan</h2>

            @if (session('info'))
                <div class="mb-4 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 text-sm">
                    {{ session('info') }}
                </div>
            @endif

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

            <form action="{{ route('master.ruangan.update', $ruangan) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ruangan <span class="text-red-500">*</span></label>
                    <input type="text" name="room_name" value="{{ old('room_name', $ruangan->room_name) }}"
                           class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Ruangan <span class="text-red-500">*</span></label>
                    <input type="text" name="room_number" value="{{ old('room_number', $ruangan->room_number) }}"
                           class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kelas <span class="text-red-500">*</span></label>
                    <div class="relative" x-data="{ open: false, selected: '{{ old('class', $ruangan->class) }}' }">
                        <button type="button" 
                                @click="open = !open"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 text-left bg-white">
                            <span x-text="selected ? 'Kelas ' + selected : 'Pilih kelas ruangan'"></span>
                        </button>
                        <select name="class"
                                x-model="selected"
                                class="hidden" required>
                            <option value="" disabled>Pilih kelas ruangan</option>
                            <option value="A">Kelas A</option>
                            <option value="B">Kelas B</option>
                            <option value="C">Kelas C</option>
                            <option value="D">Kelas D</option>
                            <option value="E">Kelas E</option>
                        </select>
                        <div class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50 max-h-60 overflow-y-auto"
                             x-show="open"
                             @click.outside="open = false">
                            <button type="button" @click="selected = ''; open = false" class="block w-full text-left px-3 py-2 hover:bg-gray-100 text-sm text-gray-500">— Pilih kelas ruangan —</button>
                            <button type="button" @click="selected = 'A'; open = false" class="block w-full text-left px-3 py-2 hover:bg-green-100 text-sm" :class="selected === 'A' ? 'bg-green-50 font-medium text-green-700' : ''">Kelas A</button>
                            <button type="button" @click="selected = 'B'; open = false" class="block w-full text-left px-3 py-2 hover:bg-green-100 text-sm" :class="selected === 'B' ? 'bg-green-50 font-medium text-green-700' : ''">Kelas B</button>
                            <button type="button" @click="selected = 'C'; open = false" class="block w-full text-left px-3 py-2 hover:bg-green-100 text-sm" :class="selected === 'C' ? 'bg-green-50 font-medium text-green-700' : ''">Kelas C</button>
                            <button type="button" @click="selected = 'D'; open = false" class="block w-full text-left px-3 py-2 hover:bg-green-100 text-sm" :class="selected === 'D' ? 'bg-green-50 font-medium text-green-700' : ''">Kelas D</button>
                            <button type="button" @click="selected = 'E'; open = false" class="block w-full text-left px-3 py-2 hover:bg-green-100 text-sm" :class="selected === 'E' ? 'bg-green-50 font-medium text-green-700' : ''">Kelas E</button>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-green-700 text-white text-sm font-medium hover:bg-green-800 shadow-sm transition-colors">
                        Simpan Perubahan
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
