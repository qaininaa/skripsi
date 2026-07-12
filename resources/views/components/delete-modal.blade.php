@props([
    'title' => 'Hapus Item',
    'warning' => 'Tindakan ini tidak dapat dibatalkan.',
])

<div x-show="showDeleteModal" x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
     @click.self="showDeleteModal = false">
    <div x-show="showDeleteModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
         class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform">
        <div class="flex gap-4 items-start mb-3">
            <div class="flex-shrink-0 w-11 h-11 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
                <p class="mt-1 text-sm text-gray-500">Yakin ingin menghapus "<span class="font-medium text-gray-800" x-text="itemName"></span>"?</p>
            </div>
        </div>
        <p class="text-sm text-gray-400 mb-5 ml-[60px]">{{ $warning }}</p>
        <div class="flex gap-3 justify-end">
            <button @click="showDeleteModal = false" type="button"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Batal
            </button>
            <form :action="deleteAction" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>
