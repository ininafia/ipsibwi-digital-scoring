<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dewan IPSI')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body x-data="{ open: window.innerWidth >= 1024 }" @resize.window="open = window.innerWidth >= 1024" class="bg-[#f4f4f4] min-h-screen font-[Poppins]">

<div class="flex min-h-screen relative overflow-hidden">

    <!-- OVERLAY (Mobile) -->
    <div x-show="open" 
         @click="open = false" 
         class="fixed inset-0 bg-black/50 z-40 lg:hidden"
         x-transition.opacity
         x-cloak>
    </div>

    <!-- SIDEBAR -->
    @yield('sidebar')

    <!-- MAIN -->
    <main class="flex-1">

        <!-- NAVBAR -->
        @include('Dewan.dashboard-dewan.navbar')

        <!-- CONTENT -->
        <section class="px-6 pt-10">
            @yield('content')
        </section>

    </main>

</div>

</body>
</html>
