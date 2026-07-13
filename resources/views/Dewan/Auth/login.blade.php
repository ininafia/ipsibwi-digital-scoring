@extends('Operator.Auth.layout')

@section('content')

<div class="bg-white w-[420px] max-w-[90%] rounded-2xl shadow-md px-6 py-8 sm:px-10 sm:py-12">

    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 text-center">
        Login
    </h1>

    {{-- ERROR VALIDASI --}}
    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-600 text-sm rounded-md">
            <ul class="list-disc ml-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM LOGIN --}}
    <form method="POST" action="{{ url('/login') }}">
        @csrf

        <!-- Username -->
        <div class="relative mb-5">
            <i class="fa-regular fa-user absolute left-4 top-1/2 -translate-y-1/2 text-sky-400"></i>

            <input type="text"
                name="username"
                value="{{ old('username') }}"
                placeholder="Masukkan username"
                class="w-full h-11 pl-12 pr-3 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-300">
        </div>

        <!-- Password -->
        <div class="relative mb-6">

            <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-sky-400"></i>

            <input type="password"
                id="password"
                name="password"
                placeholder="Masukkan password"
                class="w-full h-11 pl-12 pr-10 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-300">

            <!-- ICON TOGGLE PASSWORD -->
            <button type="button"
                onclick="togglePassword()"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">

                <i id="eyeIcon" class="fa-regular fa-eye"></i>
            </button>
        </div>

        <!-- BUTTON LOGIN -->
        <button type="submit"
            class="w-full h-11 rounded-md text-white text-lg font-semibold 
                   bg-gradient-to-r from-sky-300 to-sky-500 
                   hover:from-sky-400 hover:to-sky-600 transition">
            Login
        </button>

        <!-- LINK -->
        <a href="#"
           class="block text-center mt-4 text-gray-400 text-sm hover:text-gray-500">
            Lupa Password?
        </a>

    </form>

</div>

{{-- SCRIPT TOGGLE PASSWORD --}}
<script>
function togglePassword() {
    const password = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');

    if (password.type === "password") {
        password.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        password.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>

@endsection