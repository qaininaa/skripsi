@extends('layouts.app')

@section('title', 'Tambah Lokasi')
@section('page-title', 'Tambah Lokasi')
@section('content')

    <div class="max-w-2xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('master.location.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke daftar lokasi
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Tambah Lokasi Baru</h2>

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

            <form action="{{ route('master.location.store') }}" method="POST" class="space-y-4">
                @csrf

                {{-- Ruangan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ruangan <span class="text-red-500">*</span></label>
                    <select name="room_id"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" required>
                        <option value="" disabled {{ old('room_id') ? '' : 'selected' }}>Pilih ruangan</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                {{ $room->room_name }} ({{ $room->room_number }}) — Kelas {{ $room->class }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Nomor Lokasi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Lokasi</label>
                    <input type="text" name="location_number" value="{{ old('location_number') }}"
                           placeholder="Contoh: L-01"
                           class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                </div>

                {{-- Tipe Pengukuran --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Pengukuran</label>
                    <select name="measurement_type"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                        <option value="">— Tidak Ditentukan —</option>
                        <option value="settle_plate" {{ old('measurement_type') === 'settle_plate' ? 'selected' : '' }}>Settle Plate</option>
                        <option value="swab" {{ old('measurement_type') === 'swab' ? 'selected' : '' }}>Swab</option>
                        <option value="air_sampler" {{ old('measurement_type') === 'air_sampler' ? 'selected' : '' }}>Air Sampler</option>
                    </select>
                </div>

                {{-- Frekuensi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Frekuensi Pemantauan</label>
                    <select name="frequency_id"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                        <option value="">— Tidak Ditentukan —</option>
                        @foreach ($frequencies as $freq)
                            <option value="{{ $freq->id }}" {{ old('frequency_id') == $freq->id ? 'selected' : '' }}>
                                {{ $freq->getIndonesianLabel() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Batas Alert Total (B+F) --}}
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-4 space-y-3">
                    <p class="text-sm font-medium text-gray-700">Batas Alert Total/B+F (CFU)</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Alert Limit</label>
                            <input type="number" name="alert_limit_total" value="{{ old('alert_limit_total') }}"
                                   min="0" max="65535" placeholder="0"
                                   class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Action Limit</label>
                            <input type="number" name="alert_action_total" value="{{ old('alert_action_total') }}"
                                   min="0" max="65535" placeholder="0"
                                   class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                    </div>
                </div>

                {{-- Batas Alert Jamur --}}
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-4 space-y-3">
                    <p class="text-sm font-medium text-gray-700">Batas Alert Jamur/Fungi (CFU)</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Alert Limit</label>
                            <input type="number" name="alert_limit_fungi" value="{{ old('alert_limit_fungi') }}"
                                   min="0" max="65535" placeholder="0"
                                   class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Action Limit</label>
                            <input type="number" name="alert_action_fungi" value="{{ old('alert_action_fungi') }}"
                                   min="0" max="65535" placeholder="0"
                                   class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-green-700 text-white text-sm font-medium hover:bg-green-800 shadow-sm transition-colors">
                        Simpan Lokasi
                    </button>
                    <a href="{{ route('master.location.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 shadow-sm transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection
