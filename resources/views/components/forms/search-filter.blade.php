@props(['action' => '', 'placeholder' => 'Cari...', 'resetRoute' => ''])

<form method="GET" action="{{ $action }}" class="mb-4 flex flex-col sm:flex-row gap-3">
    <div class="relative flex-1">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
            </svg>
        </div>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="{{ $placeholder }}"
               class="block w-full rounded-lg border-gray-300 pl-9 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
    </div>

    {{ $slot }}

    <button type="submit"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700 shadow-sm transition-colors">
        Cari
    </button>

    <a href="{{ $resetRoute }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 shadow-sm bg-white text-gray-700 text-sm font-medium hover:bg-gray-100 transition-colors">
        Reset
    </a>
</form>
