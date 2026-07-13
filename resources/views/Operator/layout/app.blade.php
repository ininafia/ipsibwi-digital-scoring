<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ env('APP_NAME') }} - Dashboard</title>

    {{-- TAILWIND --}}
    @vite('resources/css/app.css')

    {{-- FONT POPPINS --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    {{-- ALPINE JS --}}
    <script src="https://unpkg.com/alpinejs" defer></script>

    {{-- LUCIDE ICON --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    {{-- FONT AWESOME --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>

    {{-- FAVICON --}}
    @include('Operator.layout.favicon')

</head>

<body
    x-data="{ open: window.innerWidth >= 768 }"
    @resize.window="open = window.innerWidth >= 768"
    class="bg-[#f3f3f3] min-h-screen font-[Poppins] overflow-hidden">

<div class="flex h-screen relative">

    {{-- MOBILE OVERLAY --}}
    <div x-show="open && window.innerWidth < 768" 
         @click="open = false"
         x-transition.opacity
         class="fixed inset-0 bg-black/50 z-30 md:hidden"></div>

    {{-- SIDEBAR --}}
    @if(request()->routeIs('operator.tanding.*'))
        @include('Operator.layout.sidebar-tanding')
    @else
        @include('Operator.layout.sidebar')
    @endif

    {{-- MAIN --}}
    <main class="flex-1 flex flex-col">

        {{-- TOPBAR --}}
        <header
            class="h-[78px] bg-[#f8f8f8] border-b border-gray-200 flex items-center px-6">

            {{-- TOGGLE SIDEBAR --}}
            <button
                @click="open = !open"
                class="text-[#a8e8ff] text-[30px]">

                <i class="fa-solid fa-bars"></i>

            </button>

        </header>

        {{-- CONTENT --}}
        <section class="flex-1 px-6 py-10 overflow-y-auto">

            @yield('content')

        </section>

    </main>

</div>

{{-- INIT LUCIDE --}}
<script>
    lucide.createIcons();
</script>

</body>
</html>