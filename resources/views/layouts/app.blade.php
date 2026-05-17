<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div x-data="{ sidebarOpen: false, showDeleteModal: false, deleteAction: null, itemName: null }">

    <div class="flex h-screen overflow-hidden">

        {{-- Mobile backdrop --}}
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden"
        ></div>

        {{-- Sidebar --}}
        <aside
            id="app-sidebar"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-30 w-64 transform transition-transform duration-300 ease-in-out lg:static lg:z-auto lg:translate-x-0 lg:flex-shrink-0 h-screen"
            :aria-hidden="sidebarOpen ? 'false' : 'true'"
        >
            @include('components.sidebar.sidebar')
        </aside>

        {{-- Main Content --}}
        <div class="flex flex-col flex-1 overflow-hidden min-w-0">

            {{-- Top Bar --}}
            @include('layouts.header')

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>

        </div>
    </div>

    {{-- Global delete modal: di luar overflow-hidden agar fixed inset-0 cover full screen --}}
    <x-modals.delete-modal />

    </div>{{-- end x-data wrapper --}}
    @stack('scripts')
</body>
</html>
