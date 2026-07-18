@php
    $actionText = 'Lainnya';
    $pts = '';
    $icon = '';
    $color = 'text-gray-700';
    $border = 'border-gray-300';
    $bgColor = 'bg-white';
    
    if ($log->technique === 'punch') {
        $actionText = 'Pukulan';
        $pts = '(+1)';
        $icon = asset('images/icons/pukul 1.png');
    } elseif ($log->technique === 'kick') {
        $actionText = 'Tendangan';
        $pts = '(+2)';
        $icon = asset('images/icons/tendang 2.png');
    }
    
    if ($log->athlete === 'blue') {
        $color = 'text-blue-700';
        $border = 'border-blue-400';
        $bgColor = 'bg-blue-50';
    } elseif ($log->athlete === 'red') {
        $color = 'text-red-600';
        $border = 'border-red-400';
        $bgColor = 'bg-red-50';
    }

    $timeStr = \Carbon\Carbon::parse($log->created_at)->format('H:i:s');
@endphp

<div class="bg-white border {{ $border }} rounded-lg p-0.5 shadow-sm flex items-center gap-0.5 relative w-[90px] transition-all hover:shadow-md cursor-help" title="{{ $log->description }} | Status: {{ $log->status_text }}">
    
    <!-- Waktu individual box -->
    <div class="bg-white px-0.5 text-[7px] font-bold text-[#1e3a8a] border border-blue-200 rounded flex-shrink-0">
        {{ $timeStr }}
    </div>
    
    @if($icon)
        <img src="{{ $icon }}" alt="{{ $actionText }}" class="w-3 h-3 object-contain flex-shrink-0 invert">
    @endif
    
    <div class="flex flex-col justify-center flex-1 overflow-hidden">
        <span class="text-[6.5px] font-bold uppercase leading-none text-gray-700 truncate text-left">{{ $actionText }}</span>
        <span class="text-[7px] font-bold text-black leading-none mt-0.5 text-left">{{ $pts }}</span>
    </div>

    <!-- Status indicator (dot) -->
    @if($log->status_text == 'Sah')
        <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-green-500 rounded-full border border-white shadow-sm" title="Sah"></div>
    @elseif($log->status_text == 'Menunggu Validasi')
        <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-yellow-400 rounded-full border border-white shadow-sm" title="Menunggu Validasi"></div>
    @else
        <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full border border-white shadow-sm" title="{{ $log->status_text }}"></div>
    @endif
</div>
