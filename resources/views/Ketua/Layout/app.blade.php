<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Ketua Pertandingan')</title>

    @vite('resources/css/app.css')

    <!-- FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <!-- ALPINE JS -->
    <script src="https://unpkg.com/alpinejs" defer></script>

    <!-- FONT AWESOME -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body x-data="{ open: window.innerWidth >= 1024 }"
      @resize.window="open = window.innerWidth >= 1024"
      class="bg-[#f4f4f4] min-h-screen font-[Poppins]">

<div class="flex min-h-screen">



    <!-- SIDEBAR -->
    @hasSection('sidebar')
        @yield('sidebar')
    @else
        @include('Ketua.Layout.sidebar')
    @endif

    <!-- MAIN -->
    <main class="flex-1 min-w-0">

        <!-- NAVBAR -->
        @include('Ketua.dashboard-ketua.navbar')

        <!-- CONTENT -->
        <section class="px-3 sm:px-6 pt-6 sm:pt-10 min-w-0">

            @yield('content')

        </section>

    </main>

</div>

</body>
</html>
