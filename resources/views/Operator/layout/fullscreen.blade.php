<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ env('APP_NAME') }} - @yield('title', 'Play Pertandingan')</title>

    {{-- TAILWIND --}}
    @vite('resources/css/app.css')

    {{-- FONT POPPINS --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    {{-- FONT AWESOME --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>

    {{-- FAVICON --}}
    @include('Operator.layout.favicon')

    {{-- EXTRA HEAD (opsional per-halaman) --}}
    @yield('head')

</head>

<body class="bg-gray-100 min-h-screen font-[Poppins]">

    @yield('content')

    {{-- EXTRA SCRIPT (opsional per-halaman) --}}
    @yield('scripts')

</body>
</html>