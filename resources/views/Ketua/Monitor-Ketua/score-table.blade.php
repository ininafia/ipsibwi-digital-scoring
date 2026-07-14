<style>
    .monitor-table th, .monitor-table td {
        border: 2px solid black;
        padding: 2px;
        text-align: center;
        font-weight: bold;
        font-size: 12px;
        color: black;
    }
    .monitor-table {
        border-collapse: collapse;
        width: 100%;
        height: 100%;
    }
    .thick-border-bottom td {
        border-bottom: 4px solid black !important;
    }
    .main-border-wrap {
        border: 4px solid black;
    }
    /* Event history boxes */
    .evt-box {
        display: inline-block;
        min-width: 18px;
        height: 18px;
        line-height: 18px;
        font-size: 11px;
        font-weight: bold;
        text-align: center;
        margin: 1px;
        border-radius: 2px;
        color: white;
    }
    .evt-sah-blue { background-color: #0000cc; }
    .evt-sah-red { background-color: #cc0000; }
    .evt-sah-yellow { background-color: #daa520; color: black; }
    .evt-tidak-sah { background-color: transparent; color: #666; font-style: italic; text-decoration: line-through; }
    .evt-container {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1px;
        padding: 1px 2px;
    }
</style>

<div class="h-full flex flex-col main-border-wrap bg-white">
    <table class="monitor-table w-full h-full flex-1">
        
        <!-- Header Utama -->
        <tr class="h-[28px]">
            <th colspan="4" class="bg-[#0000cc] text-white uppercase text-[14px] tracking-widest border-b-[3px] border-black">BLUE CORNER</th>
            <th rowspan="2" class="bg-white text-black text-[10px] font-extrabold px-1 w-[50px] border-b-[3px] border-black align-middle">ROUND</th>
            <th colspan="4" class="bg-[#cc0000] text-white uppercase text-[14px] tracking-widest border-b-[3px] border-black">RED CORNER</th>
        </tr>

        <!-- Sub Header -->
        <tr class="bg-white text-black text-[11px] uppercase h-[24px]">
            <th colspan="2" class="w-[80px] border-b-[3px] border-black">TOTAL</th>
            <th colspan="2" class="border-b-[3px] border-black">DETAIL SCORE</th>
            <th colspan="2" class="border-b-[3px] border-black">DETAIL SCORE</th>
            <th colspan="2" class="w-[80px] border-b-[3px] border-black">TOTAL</th>
        </tr>

        <!-- Ronde 1, 2, 3 Loop -->
        @php
            $details = ['JURI 1', 'JURI 2', 'JURI 3', 'SCORE', 'JATUHAN', 'HUKUMAN', 'BINAAN'];
            $detailKeys = ['juri_1', 'juri_2', 'juri_3', 'score', 'jatuhan', 'hukuman', 'binaan'];
        @endphp

        @for($round = 1; $round <= 3; $round++)
            @foreach($details as $index => $detail)
                
                @php 
                    $isLast = ($index == count($details) - 1);
                    $rowClass = ($isLast && $round < 3) ? 'thick-border-bottom' : '';
                    $key = $detailKeys[$index];
                @endphp

                <tr class="{{ $rowClass }} h-[20px]">
                    
                    <!-- Total Kiri (Grand total, rowspan seluruh ronde) -->
                    @if($index == 0)
                        <td rowspan="{{ count($details) }}" class="w-[40px] align-top p-0 border-r-2 border-black {{ $round < 3 ? 'border-b-[4px]' : '' }}">
                            <span id="round-total-blue-{{ $round }}" class="mt-1 block"></span>
                        </td>
                        <td rowspan="3" class="w-[40px] align-middle p-0 border-r-2 border-black">
                            <span id="juri-total-blue-{{ $round }}" class="font-bold"></span>
                        </td>
                    @elseif($index >= 3)
                        <td class="w-[40px] align-top p-0 border-r-2 border-black">
                        </td>
                    @endif
                    
                    <!-- Detail Score Val Kiri -->
                    <td class="w-auto bg-white border-r-2 border-black" id="val-blue-{{ $key }}-r{{ $round }}"></td>
                    
                    <!-- Detail Score Text Kiri -->
                    <td class="uppercase text-right px-3 w-[120px] bg-white whitespace-nowrap border-l-2 border-black">{{ $detail }}</td>

                    <!-- Label Ronde (Rowspan pada index 0) -->
                    @if($index == 0)
                        <td rowspan="{{ count($details) }}" class="text-[18px] font-extrabold bg-white align-middle border-x-[3px] border-black {{ $round < 3 ? 'border-b-[4px]' : '' }}">
                            {{ $round }}
                        </td>
                    @endif

                    <!-- Detail Score Text Kanan -->
                    <td class="uppercase text-left px-3 w-[120px] bg-white whitespace-nowrap border-r-2 border-black">{{ $detail }}</td>
                    
                    <!-- Detail Score Val Kanan -->
                    <td class="w-auto bg-white border-l-2 border-black" id="val-red-{{ $key }}-r{{ $round }}"></td>

                    <!-- Total Kanan -->
                    @if($index == 0)
                        <td rowspan="3" class="w-[40px] align-middle p-0 border-l-2 border-black">
                            <span id="juri-total-red-{{ $round }}" class="font-bold"></span>
                        </td>
                        <td rowspan="{{ count($details) }}" class="w-[40px] align-top p-0 border-l-2 border-black {{ $round < 3 ? 'border-b-[4px]' : '' }}">
                            <span id="round-total-red-{{ $round }}" class="mt-1 block"></span>
                        </td>
                    @elseif($index >= 3)
                        <td class="w-[40px] align-top p-0 border-l-2 border-black">
                        </td>
                    @endif
                    
                </tr>
            @endforeach
        @endfor

    </table>
</div>
