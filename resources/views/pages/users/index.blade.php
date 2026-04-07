@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page-title', 'Manajemen Pengguna')
@section('content')

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
            {{ session('error') }}
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

    {{-- Header + Tombol Tambah --}}
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Daftar Pengguna</h2>
            <p class="text-sm text-gray-500 mt-0.5">Kelola akun pengguna aplikasi.</p>
        </div>
        <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-green-700 text-white text-sm font-medium hover:bg-green-800 transition-colors shadow-sm sm:whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
            </svg>
            Tambah Pengguna
        </a>
    </div>

    {{-- Filter & Pencarian --}}
    <form method="GET" action="{{ route('users.index') }}" class="mb-4 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama atau username..."
                   class="block w-full rounded-lg border-gray-300 pl-9 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
        </div>
        <select name="role"
                class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="">Semua Role</option>
            <option value="super"      {{ request('role') === 'super'      ? 'selected' : '' }}>Super</option>
            <option value="admin"      {{ request('role') === 'admin'      ? 'selected' : '' }}>Admin</option>
            <option value="analis"     {{ request('role') === 'analis'     ? 'selected' : '' }}>Analis</option>
            <option value="supervisor" {{ request('role') === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
            <option value="manajer"    {{ request('role') === 'manajer'    ? 'selected' : '' }}>Manajer</option>
        </select>
        <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 shadow-sm transition-colors">
            Filter
        </button>
        <button type="button" onclick="window.location.href='{{ route('users.index') }}'"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 shadow-sm bg-white text-gray-700 text-sm font-medium hover:bg-gray-200 transition-colors">
             Reset
        </button>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Bergabung</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    @php
                                        $avatarColor = match($user->role) {
                                            'super' => 'bg-indigo-500',
                                            'admin' => 'bg-emerald-500',
                                            'supervisor' => 'bg-orange-500',
                                            'manajer' => 'bg-blue-500',
                                            default => 'bg-gray-400',
                                        };
                                    @endphp
                                    <div class="h-8 w-8 rounded-full {{ $avatarColor }} flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="text-sm font-medium text-gray-800">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-sm text-gray-500">{{ $user->username ?? '—' }}</td>
                            <td class="px-6 py-3.5">
                                @php
                                    $badgeClass = match($user->role) {
                                        'super' => 'bg-indigo-100 text-indigo-700',
                                        'admin' => 'bg-emerald-100 text-emerald-700',
                                        'supervisor' => 'bg-orange-100 text-orange-700',
                                        'manajer' => 'bg-blue-100 text-blue-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-sm text-gray-400">{{ $user->created_at?->format('d M Y') ?? '—' }}</td>
                            <td class="px-6 py-3.5">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('users.edit', $user) }}" class="btn-action-edit">
                                        Edit
                                    </a>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action-delete">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                Belum ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users instanceof \Illuminate\Contracts\Pagination\Paginator || $users instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

@endsection
