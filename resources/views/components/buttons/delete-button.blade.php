@props(['action', 'name', 'size' => 'sm', 'type' => 'button', 'showLabel' => true])

@php
    $sizeClasses = match($size) {
        'xs' => 'w-3 h-3',
        'sm' => 'w-3.5 h-3.5',
        'md' => 'w-4 h-4',
        'lg' => 'w-5 h-5',
    };
    $buttonClasses = match($size) {
        'xs' => 'text-red-400 hover:text-red-600',
        'sm' => 'text-red-400 hover:text-red-600',
        'md' => 'text-red-500 hover:text-red-700',
        'lg' => 'text-red-600 hover:text-red-800',
    };
@endphp

<button type="{{ $type }}"
        data-action="{{ $action }}"
        data-name="{{ $name }}"
        @click="deleteAction = $el.dataset.action; itemName = $el.dataset.name; showDeleteModal = true"
        class="btn-action-delete {{ $buttonClasses }}">
    <svg class="{{ $sizeClasses }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
    </svg>
    @if ($showLabel)
        Hapus
    @endif
</button>
