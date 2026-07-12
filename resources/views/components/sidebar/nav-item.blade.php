@php
    $routeName = $item['route'];
    $activePattern = $item['activePattern'] ?? $routeName;
    $isActive = request()->routeIs($activePattern);
    $badge = $item['badge'] ?? null;
    $badgeColor = $item['badgeColor'] ?? 'red';
    $badgeClasses = match ($badgeColor) {
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'green' => 'bg-green-100 text-green-700',
        default => 'bg-red-100 text-red-700',
    };
@endphp

<a
    href="{{ route($routeName) }}"
    @click="sidebarOpen = false"
    @class([
        'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
        'bg-green-50 font-semibold text-green-700' => $isActive,
        'text-gray-500 hover:bg-green-50 hover:text-green-800' => ! $isActive,
    ])
>
    <img src="{{ asset($item['icon']) }}" alt="" class="h-5 w-5 shrink-0" aria-hidden="true">
    <span class="truncate">{{ $item['label'] }}</span>

    @if (! empty($badge))
        <span class="ml-auto inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[11px] font-semibold {{ $badgeClasses }}">
            {{ $badge }}
        </span>
    @endif
</a>
