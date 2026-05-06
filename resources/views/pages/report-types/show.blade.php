@extends('layouts.app')

@section('title', 'Detail Jenis Laporan')
@section('page-title','Annex '.$reportType->annex_number)
@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <x-buttons.back-to-list :href="route('report-types.index')" />
        <div class="flex items-center gap-2">
            <x-buttons.edit-button onclick="window.location.href='{{ route('report-types.edit', $reportType) }}'" size="md"/>
            <x-buttons.delete-button :action="route('report-types.destroy', $reportType)" :name="$reportType->name" size="md" />
        </div>
    </div>

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

    {{-- Info Dasar --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4">Informasi Dasar</h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
            <div>
                <dt class="text-gray-500">Kode SOP</dt>
                <dd class="font-medium text-gray-800">{{ $reportType->sop_code }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Versi SOP</dt>
                <dd class="font-medium text-gray-800">{{ $reportType->sop_version }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Annex</dt>
                <dd class="font-medium text-gray-800">{{ $reportType->annex_number }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-gray-500">Nama</dt>
                <dd class="font-medium text-gray-800">{{ $reportType->name }}</dd>
            </div>
        </dl>

        {{-- Medium Groups --}}
        @if ($reportType->mediumTypes->isNotEmpty())
        <div class="mt-5 pt-4 border-t border-gray-100">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Medium Groups</h4>
            <div class="flex flex-wrap gap-2">
                @foreach ($reportType->mediumTypes as $medium)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                    {{ $medium->name }}
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Incubators --}}
        @if ($reportType->incubatorTypes->isNotEmpty())
        <div class="mt-4 pt-4 border-t border-gray-100">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Inkubator</h4>
            <div class="flex flex-wrap gap-2">
                @foreach ($reportType->incubatorTypes as $inc)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-100">
                    {{ $inc->temperature_label }} — min {{ $inc->min_day }} hari
                </span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Sections --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" x-data="{ showAddSection: false }">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-800">Seksi ({{ $reportType->sections->count() }})</h3>
            <button type="button" @click="showAddSection = !showAddSection" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-medium hover:bg-indigo-100">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
                Tambah Seksi
            </button>
        </div>

        {{-- Add Section Form --}}
        <div x-show="showAddSection" x-cloak class="mb-5 p-4 rounded-lg bg-gray-50 border border-gray-200">
            <form action="{{ route('report-types.sections.store', $reportType) }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Satuan Ukur</label>
                        <input type="text" name="measurement_unit" placeholder="cth: CFU/4hours/plate" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Pengukuran</label>
                        <select name="measurement_type" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" required>
                            <option value="">Pilih</option>
                            <option value="Settle Plate">Settle Plate</option>
                            <option value="Air Sampler">Air Sampler</option>
                            <option value="Contact Plate">Contact Plate</option>
                            <option value="Swab">Swab</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Kolom</label>
                        <input type="number" name="max_column" value="1" min="1" max="20" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Label Kolom</label>
                        <input type="text" name="column_label" value="Exposure" placeholder="Exposure / Shift / custom" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Input Waktu per Kolom</label>
                        <select name="time_slot_type" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" required>
                            <option value="none">Tidak ada</option>
                            <option value="single">1 slot (Mulai–Selesai)</option>
                            <option value="per_location">Per Lokasi (1 slot/lokasi)</option>
                            <option value="dual_ab">2 slot (A & B)</option>
                            <option value="swab">3 slot (S1, S1-2, S1-3)</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-4 md:col-span-2 lg:col-span-2">
                        <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                            <input type="checkbox" name="has_machine_setup" value="1" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span>Machine Set-up (waktu bersama)</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showAddSection = false" class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-100">Batal</button>
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-green-700 text-white text-xs font-medium hover:bg-green-800">Simpan Seksi</button>
                </div>
            </form>
        </div>

        {{-- Existing Sections --}}
        @forelse ($reportType->sections->sortBy('order') as $section)
        <div class="mb-5 last:mb-0" x-data="{ showLocForm: false, editSection: false }">
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                {{-- Section Header --}}
                <div class="bg-gray-50 px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-700 text-xs font-bold">{{ $section->order }}</span>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800">{{ $section->measurement_type }}</h4>
                            <p class="text-xs text-gray-500">
                                {{ $section->measurement_unit }} ·
                                {{ $section->max_column }}x {{ $section->column_label ?: 'Exposure' }}
                                @if ($section->time_slot_type !== 'none')
                                    · Waktu: {{ ['single' => '1 slot', 'per_location' => 'Per Lokasi', 'dual_ab' => 'A/B', 'swab' => 'S1/S1-2/S1-3'][$section->time_slot_type] ?? $section->time_slot_type }}
                                @endif
                                @if ($section->has_machine_setup) · Machine Set-up @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-buttons.edit-button @click="editSection = !editSection" size="sm" />
                        <x-buttons.delete-button :action="route('report-types.sections.destroy', [$reportType, $section])" name="seksi" size="sm" />
                    </div>
                </div>

                {{-- Edit Section Form --}}
                <div x-show="editSection" x-cloak class="px-4 py-3 bg-yellow-50 border-b border-gray-200">
                    <form action="{{ route('report-types.sections.update', [$reportType, $section]) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Satuan Ukur</label>
                                <input type="text" name="measurement_unit" value="{{ $section->measurement_unit }}" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Pengukuran</label>
                                <select name="measurement_type" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" required>
                                    <option value="Settle Plate" {{ $section->measurement_key === 'settle_plate' ? 'selected' : '' }}>Settle Plate</option>
                                    <option value="Air Sampler" {{ $section->measurement_key === 'air_sampler' ? 'selected' : '' }}>Air Sampler</option>
                                    <option value="Contact Plate" {{ $section->measurement_key === 'contact_plate' ? 'selected' : '' }}>Contact Plate</option>
                                    <option value="Swab" {{ $section->measurement_key === 'swab' ? 'selected' : '' }}>Swab</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Kolom</label>
                                <input type="number" name="max_column" value="{{ $section->max_column }}" min="1" max="20" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Label Kolom</label>
                                <input type="text" name="column_label" value="{{ $section->column_label }}" placeholder="Exposure / Shift / custom" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Input Waktu per Kolom</label>
                                <select name="time_slot_type" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" required>
                                    <option value="none" {{ $section->time_slot_type === 'none' ? 'selected' : '' }}>Tidak ada</option>
                                    <option value="single" {{ $section->time_slot_type === 'single' ? 'selected' : '' }}>1 slot (Mulai–Selesai)</option>
                                    <option value="per_location" {{ $section->time_slot_type === 'per_location' ? 'selected' : '' }}>Per Lokasi (1 slot/lokasi)</option>
                                    <option value="dual_ab" {{ $section->time_slot_type === 'dual_ab' ? 'selected' : '' }}>2 slot (A & B)</option>
                                    <option value="swab" {{ $section->time_slot_type === 'swab' ? 'selected' : '' }}>3 slot (S1, S1-2, S1-3)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Urutan</label>
                                <input type="number" name="order" value="{{ $section->order }}" min="0" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" required>
                            </div>
                            <div class="flex items-end gap-4">
                                <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                                    <input type="checkbox" name="has_machine_setup" value="1" {{ $section->has_machine_setup ? 'checked' : '' }} class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                    <span>Machine Set-up</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="editSection = false" class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-100">Batal</button>
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-green-700 text-white text-xs font-medium hover:bg-green-800">Simpan</button>
                        </div>
                    </form>
                </div>

                {{-- Location Table --}}
                <div class="p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Lokasi ({{ $section->locations->count() }})</span>
                        <button type="button" @click="showLocForm = !showLocForm" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Tambah Lokasi</button>
                    </div>

                    {{-- Add Location Form --}}
                    <div x-show="showLocForm" x-cloak class="mb-3 p-3 rounded-lg bg-blue-50 border border-blue-100">
                        <form action="{{ route('report-types.sections.locations.store', [$reportType, $section]) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-0.5">Pilih Lokasi</label>
                                <select name="location_id" class="block w-full rounded border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">-- Pilih Lokasi --</option>
                                    @foreach ($locations as $loc)
                                        @if (! $loc->section_id || (string) $loc->section_id === (string) $section->id)
                                        @unless ($section->locations->contains($loc->id))
                                        <option value="{{ $loc->id }}">
                                            [{{ $loc->location_number ?? '-' }}] {{ $loc->room->room_name ?? '-' }} (Kelas {{ $loc->room->class ?? '-' }})
                                        </option>
                                        @endunless
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="showLocForm = false" class="px-3 py-1 rounded border border-gray-200 text-xs font-medium bg-white text-gray-600 hover:bg-gray-100">Batal</button>
                                <button type="submit" class="px-3 py-1 rounded bg-green-700 text-white text-xs font-medium hover:bg-indigo-700">Simpan</button>
                            </div>
                        </form>
                    </div>

                    @if ($section->locations->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="text-left px-2 py-1.5 font-semibold text-gray-500">Ruangan</th>
                                    <th class="text-center px-2 py-1.5 font-semibold text-gray-500">Kelas</th>
                                    <th class="text-left px-2 py-1.5 font-semibold text-gray-500">No. Ruangan</th>
                                    <th class="text-left px-2 py-1.5 font-semibold text-gray-500">No. Lokasi</th>
                                    <th class="text-left px-2 py-1.5 font-semibold text-gray-500">Tipe</th>
                                    <th class="text-center px-2 py-1.5 font-semibold text-gray-500">Alert B</th>
                                    <th class="text-center px-2 py-1.5 font-semibold text-gray-500">Action B</th>
                                    <th class="text-center px-2 py-1.5 font-semibold text-gray-500">Alert F</th>
                                    <th class="text-center px-2 py-1.5 font-semibold text-gray-500">Action F</th>
                                    <th class="text-right px-2 py-1.5 font-semibold text-gray-500"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($section->locations as $loc)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-2 py-1.5 text-gray-800 font-medium">{{ $loc->room->room_name ?? '-' }}</td>
                                    <td class="px-2 py-1.5 text-center">
                                        @php $cls = $loc->room->class ?? null; @endphp
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold {{ $cls === 'A' ? 'bg-purple-100 text-purple-700' : ($cls === 'B' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">{{ $cls ?? '-' }}</span>
                                    </td>
                                    <td class="px-2 py-1.5 text-gray-600">{{ $loc->room->room_number ?? '-' }}</td>
                                    <td class="px-2 py-1.5 text-gray-600">{{ $loc->location_number ?? '-' }}</td>
                                    <td class="px-2 py-1.5 text-gray-600">{{ $loc->measurement_type ?? '-' }}</td>
                                    <td class="px-2 py-1.5 text-center text-gray-600">{{ $loc->alert_limit_total ?? '-' }}</td>
                                    <td class="px-2 py-1.5 text-center text-gray-600">{{ $loc->alert_action_total ?? '-' }}</td>
                                    <td class="px-2 py-1.5 text-center text-gray-600">{{ $loc->alert_limit_fungi ?? '-' }}</td>
                                    <td class="px-2 py-1.5 text-center text-gray-600">{{ $loc->alert_action_fungi ?? '-' }}</td>
                                    <td class="px-2 py-1.5 text-right">
                                        <x-buttons.delete-button :action="route('report-types.sections.locations.destroy', [$reportType, $section, $loc])" name="lokasi" size="xs" :showLabel="false" />
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-xs text-gray-400 italic py-2">Belum ada lokasi di seksi ini.</p>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <p class="text-sm text-gray-400 italic">Belum ada seksi. Klik "Tambah Seksi" untuk memulai.</p>
        @endforelse
    </div>
</div>
@endsection
