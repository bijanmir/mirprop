{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900">
  <div class="min-h-screen">
    {{-- Top nav (minimal) --}}
    <nav class="bg-white border-b">
      <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="{{ url('/') }}" class="font-semibold">{{ config('app.name') }}</a>
        @auth
          <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-sm">Dashboard</a>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button class="text-sm">Log out</button>
            </form>
          </div>
        @endauth
      </div>
    </nav>

    {{-- Page content --}}
    <main>
      @yield('content')
    </main>
  </div>
</body>
</html>
