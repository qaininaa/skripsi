<!DOCTYPE html>
<html class="h-full bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">

<div class="flex min-h-full">
  {{-- Left Side: Image --}}
  <div class="hidden lg:block lg:w-1/2 relative">
    <img src="{{ asset('images/loginImage.png') }}" alt="Login Image" class="absolute inset-0 h-full w-full object-cover">
    {{-- Optional Overlay to match the blueish tint in the screenshot --}}
    <div class="absolute inset-0 bg-indigo-900/20 mix-blend-multiply"></div>
  </div>

  {{-- Right Side: Login Form --}}
  <div class="flex flex-1 flex-col justify-center px-6 py-12 lg:w-1/2 lg:px-24">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
      <!-- <img src="https://www.ethica.co.id/wp-content/uploads/2021/12/Ethica_logo.png" alt="Ethica Industri Farmasi" class="mx-auto w-60 h-auto" /> -->
      <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Masuk ke Akun Anda</h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
      {{-- Session Status --}}
      @if (session('status'))
          <div class="mb-4 text-sm font-medium text-green-600">
              {{ session('status') }}
          </div>
      @endif

      <x-forms.auth-form :action="route('login')" />
      
    </div>
  </div>
</div>

</body>
</html>
