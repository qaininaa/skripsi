@extends('layouts.app')

@section('title', 'Detail Jenis Laporan')
@section('page-title', $reportType->annex_number)
@section('avatar-color', 'bg-green-600')
@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('report-types.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke daftar
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('report-types.edit', $reportType) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-green-700 text-white text-sm font-medium hover:bg-indigo-700 shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
            <form action="{{ route('report-types.destroy', $reportType) }}" method="POST" onsubmit="return confirm('Hapus jenis laporan ini?')">
                @csrf
                @method('DELETE')
                <button class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-red-50 text-red-600 text-sm font-medium hover:bg-red-100 border border-red-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    @if (session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-100 rounded-xl text-sm text-green-700 flex items-center gap-2">
        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="px-4 py-3 bg-red-50 border border-red-100 rounded-xl text-sm text-red-700">{{ session('error') }}</div>
    @endif

    {{-- Info Dasar --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4">Informasi Dasar</h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
            <div>
                <dt class="text-gray-500">Kode</dt>
                <dd class="font-medium text-gray-800 font-mono">{{ $reportType->code }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Annex</dt>
                <dd class="font-medium text-gray-800">{{ $reportType->annex_number }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-gray-500">Nama</dt>
                <dd class="font-medium text-gray-800">{{ $reportType->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Instrumen</dt>
                <dd class="font-medium text-gray-800">{{ ucwords(str_replace('_', ' ', $reportType->instrument)) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Frekuensi</dt>
                <dd class="font-medium text-gray-800">{{ $reportType->frequency ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Status</dt>
                <dd>
                    @if ($reportType->is_active)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Aktif</span>
                    @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Nonaktif</span>
                    @endif
                </dd>
            </div>
            @if ($reportType->description)
            <div class="md:col-span-2">
                <dt class="text-gray-500">Deskripsi</dt>
                <dd class="text-gray-800">{{ $reportType->description }}</dd>
            </div>
            @endif
        </dl>

        {{-- Medium Groups --}}
        @if ($reportType->medium_groups)
        <div class="mt-5 pt-4 border-t border-gray-100">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Medium Groups</h4>
            <div class="flex flex-wrap gap-2">
                @foreach ($reportType->medium_groups as $key => $label)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                    {{ $label }} <span class="ml-1.5 text-blue-400 font-mono text-[10px]">({{ $key }})</span>
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Incubators --}}
        @if ($reportType->incubators)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Inkubator</h4>
            <div class="flex flex-wrap gap-2">
                @foreach ($reportType->incubators as $key => $inc)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-100">
                    {{ $inc['label'] }} — min {{ $inc['min_days'] }} hari
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Seksi</label>
                        <input type="text" name="name" placeholder="cth: Settle Plate" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Slug</label>
                        <select name="slug" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Pilih tipe</option>
                            <option value="settle_plate">settle_plate</option>
                            <option value="air_sampler">air_sampler</option>
                            <option value="contact_plate">contact_plate</option>
                            <option value="swab">swab</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Satuan Ukur</label>
                        <input type="text" name="measurement_unit" placeholder="cth: CFU/4hours/plate" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Pengukuran</label>
                        <select name="measurement_type" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Pilih</option>
                            <option value="settle_plate">settle_plate</option>
                            <option value="active_air">active_air</option>
                            <option value="contact_plate">contact_plate</option>
                            <option value="swab">swab</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Max Exposures</label>
                        <input type="number" name="max_exposures" value="4" min="1" max="20" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showAddSection = false" class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-100">Batal</button>
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-green-700 text-white text-xs font-medium hover:bg-indigo-700">Simpan Seksi</button>
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
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold">{{ $section->order }}</span>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800">{{ $section->name }}</h4>
                            <p class="text-xs text-gray-500">{{ $section->slug }} · {{ $section->measurement_unit }} · Max {{ $section->max_exposures }} exp.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="editSection = !editSection" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Edit</button>
                        <form action="{{ route('report-types.sections.destroy', [$reportType, $section]) }}" method="POST" onsubmit="return confirm('Hapus seksi ini beserta semua lokasinya?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                        </form>
                    </div>
                </div>

                {{-- Edit Section Form --}}
                <div x-show="editSection" x-cloak class="px-4 py-3 bg-yellow-50 border-b border-gray-200">
                    <form action="{{ route('report-types.sections.update', [$reportType, $section]) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nama</label>
                                <input type="text" name="name" value="{{ $section->name }}" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Slug</label>
                                <select name="slug" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="settle_plate" {{ $section->slug === 'settle_plate' ? 'selected' : '' }}>settle_plate</option>
                                    <option value="air_sampler" {{ $section->slug === 'air_sampler' ? 'selected' : '' }}>air_sampler</option>
                                    <option value="contact_plate" {{ $section->slug === 'contact_plate' ? 'selected' : '' }}>contact_plate</option>
                                    <option value="swab" {{ $section->slug === 'swab' ? 'selected' : '' }}>swab</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Satuan Ukur</label>
                                <input type="text" name="measurement_unit" value="{{ $section->measurement_unit }}" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Pengukuran</label>
                                <select name="measurement_type" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="settle_plate" {{ $section->measurement_type === 'settle_plate' ? 'selected' : '' }}>settle_plate</option>
                                    <option value="active_air" {{ $section->measurement_type === 'active_air' ? 'selected' : '' }}>active_air</option>
                                    <option value="contact_plate" {{ $section->measurement_type === 'contact_plate' ? 'selected' : '' }}>contact_plate</option>
                                    <option value="swab" {{ $section->measurement_type === 'swab' ? 'selected' : '' }}>swab</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Max Exposures</label>
                                <input type="number" name="max_exposures" value="{{ $section->max_exposures }}" min="1" max="20" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Urutan</label>
                                <input type="number" name="order" value="{{ $section->order }}" min="0" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="editSection = false" class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-100">Batal</button>
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-green-700 text-white text-xs font-medium hover:bg-indigo-700">Simpan</button>
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
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-0.5">S.No</label>
                                    <input type="number" name="s_no" min="1" class="block w-full rounded border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                                <div class="col-span-2 md:col-span-1">
                                    <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-0.5">Nama Ruangan</label>
                                    <input type="text" name="room_name" class="block w-full rounded border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-0.5">Kelas</label>
                                    <select name="class" class="block w-full rounded border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-0.5">No. Ruangan</label>
                                    <input type="text" name="room_number" class="block w-full rounded border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-0.5">No. Lokasi</label>
                                    <input type="text" name="location_number" class="block w-full rounded border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-0.5">Alert Bakteri</label>
                                    <input type="number" name="alert_limit_bacteria" min="0" class="block w-full rounded border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-0.5">Action Bakteri</label>
                                    <input type="number" name="action_limit_bacteria" min="0" class="block w-full rounded border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-0.5">Alert Fungi</label>
                                    <input type="number" name="alert_limit_fungi" min="0" class="block w-full rounded border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-0.5">Action Fungi</label>
                                    <input type="number" name="action_limit_fungi" min="0" class="block w-full rounded border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="showLocForm = false" class="px-3 py-1 rounded border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-100">Batal</button>
                                <button type="submit" class="px-3 py-1 rounded bg-green-700 text-white text-xs font-medium hover:bg-indigo-700">Simpan</button>
                            </div>
                        </form>
                    </div>

                    @if ($section->locations->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="text-left px-2 py-1.5 font-semibold text-gray-500">S.No</th>
                                    <th class="text-left px-2 py-1.5 font-semibold text-gray-500">Ruangan</th>
                                    <th class="text-center px-2 py-1.5 font-semibold text-gray-500">Kelas</th>
                                    <th class="text-left px-2 py-1.5 font-semibold text-gray-500">No. Ruangan</th>
                                    <th class="text-left px-2 py-1.5 font-semibold text-gray-500">No. Lokasi</th>
                                    <th class="text-center px-2 py-1.5 font-semibold text-gray-500">Alert B</th>
                                    <th class="text-center px-2 py-1.5 font-semibold text-gray-500">Action B</th>
                                    <th class="text-center px-2 py-1.5 font-semibold text-gray-500">Alert F</th>
                                    <th class="text-center px-2 py-1.5 font-semibold text-gray-500">Action F</th>
                                    <th class="text-right px-2 py-1.5 font-semibold text-gray-500"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($section->locations->sortBy('s_no') as $loc)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-2 py-1.5 text-gray-600">{{ $loc->s_no }}</td>
                                    <td class="px-2 py-1.5 text-gray-800 font-medium">{{ $loc->room_name }}</td>
                                    <td class="px-2 py-1.5 text-center">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold {{ $loc->class === 'A' ? 'bg-purple-100 text-purple-700' : ($loc->class === 'B' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">{{ $loc->class }}</span>
                                    </td>
                                    <td class="px-2 py-1.5 text-gray-600 font-mono">{{ $loc->room_number ?? '-' }}</td>
                                    <td class="px-2 py-1.5 text-gray-600 font-mono">{{ $loc->location_number ?? '-' }}</td>
                                    <td class="px-2 py-1.5 text-center text-gray-600">{{ $loc->alert_limit_bacteria ?? '-' }}</td>
                                    <td class="px-2 py-1.5 text-center text-gray-600">{{ $loc->action_limit_bacteria ?? '-' }}</td>
                                    <td class="px-2 py-1.5 text-center text-gray-600">{{ $loc->alert_limit_fungi ?? '-' }}</td>
                                    <td class="px-2 py-1.5 text-center text-gray-600">{{ $loc->action_limit_fungi ?? '-' }}</td>
                                    <td class="px-2 py-1.5 text-right">
                                        <form action="{{ route('report-types.sections.locations.destroy', [$reportType, $section, $loc]) }}" method="POST" onsubmit="return confirm('Hapus lokasi ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-400 hover:text-red-600">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
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
