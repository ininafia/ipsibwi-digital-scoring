@extends('Ketua.Layout.app')

@section('title', 'Log Activity Juri')

@section('sidebar')
    @include('Ketua.Layout.sidebar')
@endsection

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Log Activity Juri</h2>
            <p class="text-sm text-gray-500 mt-1">Rekam jejak aktivitas juri per pertandingan.</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('ketua.log-juri') }}" method="GET" class="flex items-center gap-2">
                <select name="babak" class="py-2 pl-3 pr-8 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#4fcfff] focus:border-transparent outline-none cursor-pointer" onchange="this.form.submit()">
                    <option value="">Semua Babak</option>
                    @foreach($availableRounds as $ar)
                        <option value="{{ $ar->id }}" {{ $babakFilter == $ar->id ? 'selected' : '' }}>Babak {{ $ar->babak_ke }}</option>
                    @endforeach
                </select>
            </form>
            <div class="relative ml-2">
                <i class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="searchInput" placeholder="Cari Partai / Juri..." class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#4fcfff] focus:border-transparent outline-none w-56 transition-all">
            </div>
        </div>
    </div>

    <div class="space-y-4" id="logContainerBabak">
        @forelse($groupedLogsBabak as $matchId => $group)
            @include('Ketua.Log-juri.log-item', ['group' => $group, 'showBabak' => true])
        @empty
            <div class="py-16 text-center bg-gray-50 rounded-xl border border-dashed border-gray-300">
                <i class="fa-solid fa-clipboard-list text-5xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-bold text-gray-700">Tidak ada log aktivitas</h3>
                <p class="text-gray-400 mt-1">Belum ada rekam jejak aktivitas juri yang disimpan oleh sistem.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let containers = document.querySelectorAll('.match-item');
        
        containers.forEach(container => {
            let textContext = container.innerText.toLowerCase();
            if (textContext.indexOf(filter) > -1) {
                container.style.display = '';
            } else {
                container.style.display = 'none';
            }
        });
    });
</script>
@endsection
