@props(['action' => '', 'placeholder' => 'Cari...', 'resetRoute' => ''])

<form action="{{ $action }}" method="POST" class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
        @csrf

        <div>
          <label for="username" class="block text-sm/6 font-medium text-gray-900">Username</label>
          <div class="mt-2">
            <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-emerald-600 sm:text-sm/6" />
          </div>
          @error('username')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <div class="flex items-center justify-between">
            <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
          </div>
          <div class="mt-2 relative" x-data="{ show: false }">
            <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
              class="block w-full rounded-md bg-white px-3 py-1.5 pr-10 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-emerald-600 sm:text-sm/6" />
            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none">
              <img x-show="!show" src="{{ asset('icons/eye.svg') }}" alt="Tampilkan password" class="w-4 h-4">
              <img x-show="show" src="{{ asset('icons/eye-slash.svg') }}" alt="Sembunyikan password" class="w-4 h-4">
            </button>
          </div>
          @error('password')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <button type="submit" :disabled="loading" class="flex w-full justify-center rounded-md bg-emerald-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-sm hover:bg-emerald-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 disabled:opacity-70 disabled:cursor-not-allowed transition-all">
            <span x-show="!loading">Masuk</span>
            <span x-show="loading" class="flex items-center gap-2">
              <img src="{{ asset('icons/spinner.svg') }}" alt="" class="animate-spin w-4 h-4">
              Memeriksa...
            </span>
          </button>
        </div>
      </form>