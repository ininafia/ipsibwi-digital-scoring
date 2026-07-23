                        <div class="overflow-x-auto pb-4">
                            <div class="relative min-w-[700px] pt-2">
                                
                                <!-- Axis Line with arrows -->
                                <div class="absolute top-[14px] left-[130px] right-0 h-[2px] bg-[#1e3a8a] z-0 flex items-center justify-between">
                                    <div class="-ml-1 text-[#1e3a8a]"><i class="fa-solid fa-caret-left"></i></div>
                                    <div class="-mr-1 text-[#1e3a8a]"><i class="fa-solid fa-caret-right"></i></div>
                                </div>
                                
                                <!-- Header Row (Axis Points) -->
                                <div class="flex items-end pl-[130px] pr-8 gap-2 relative z-10 mb-8">
                                    @foreach($clusters as $cluster)
                                        <div class="flex flex-col items-center flex-shrink-0 w-[95px] relative group">
                                            <!-- Vertical Dotted Line connecting down through all rows -->
                                            <div class="absolute top-[14px] left-1/2 -ml-[1px] h-[260px] border-l-2 border-dotted border-blue-300 -z-10"></div>
                                            
                                            <!-- Tick mark (Vertical line) -->
                                            <div class="w-1 h-3 bg-[#1e3a8a] relative z-10"></div>
                                            
                                            <!-- Time text -->
                                            <div class="text-[10px] font-bold text-[#1e3a8a] mt-1 relative z-10 bg-white px-1">{{ \Carbon\Carbon::parse($cluster['time'])->format('H:i:s') }}</div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Juri 1 Row -->
                                <div class="flex items-center mb-8 relative z-10">
                                    <div class="w-[130px] sticky left-0 z-30 bg-white flex items-center flex-shrink-0 h-[50px]">
                                        <div class="w-[90px] bg-[#0f172a] text-white rounded-lg flex flex-col items-center justify-center py-1.5 shadow-sm">
                                            <i class="fa-solid fa-user mb-1"></i>
                                            <span class="text-[10px] font-bold uppercase tracking-wider">Juri 1</span>
                                        </div>
                                    </div>
                                    <div class="relative flex-1 flex items-center h-full">
                                        <!-- Horizontal Line -->
                                        <div class="absolute left-0 right-0 h-px bg-gray-300 -z-10"></div>
                                        <div class="flex items-center gap-2 relative z-10 pr-8 w-full">
                                            @foreach($clusters as $cluster)
                                                <div class="w-[95px] flex justify-center flex-shrink-0">
                                                    @if(isset($cluster['events']['juri_1']))
                                                        @include('Ketua.Log-juri.event-box', ['log' => $cluster['events']['juri_1']])
                                                    @else
                                                        <div class="h-8"></div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Juri 2 Row -->
                                <div class="flex items-center mb-8 relative z-10">
                                    <div class="w-[130px] sticky left-0 z-30 bg-white flex items-center flex-shrink-0 h-[50px]">
                                        <div class="w-[90px] bg-[#0f172a] text-white rounded-lg flex flex-col items-center justify-center py-1.5 shadow-sm">
                                            <i class="fa-solid fa-user mb-1"></i>
                                            <span class="text-[10px] font-bold uppercase tracking-wider">Juri 2</span>
                                        </div>
                                    </div>
                                    <div class="relative flex-1 flex items-center h-full">
                                        <!-- Horizontal Line -->
                                        <div class="absolute left-0 right-0 h-px bg-gray-300 -z-10"></div>
                                        <div class="flex items-center gap-2 relative z-10 pr-8 w-full">
                                            @foreach($clusters as $cluster)
                                                <div class="w-[95px] flex justify-center flex-shrink-0">
                                                    @if(isset($cluster['events']['juri_2']))
                                                        @include('Ketua.Log-juri.event-box', ['log' => $cluster['events']['juri_2']])
                                                    @else
                                                        <div class="h-8"></div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Juri 3 Row -->
                                <div class="flex items-center mb-2 relative z-10">
                                    <div class="w-[130px] sticky left-0 z-30 bg-white flex items-center flex-shrink-0 h-[50px]">
                                        <div class="w-[90px] bg-[#0f172a] text-white rounded-lg flex flex-col items-center justify-center py-1.5 shadow-sm">
                                            <i class="fa-solid fa-user mb-1"></i>
                                            <span class="text-[10px] font-bold uppercase tracking-wider">Juri 3</span>
                                        </div>
                                    </div>
                                    <div class="relative flex-1 flex items-center h-full">
                                        <!-- Horizontal Line -->
                                        <div class="absolute left-0 right-0 h-px bg-gray-300 -z-10"></div>
                                        <div class="flex items-center gap-2 relative z-10 pr-8 w-full">
                                            @foreach($clusters as $cluster)
                                                <div class="w-[95px] flex justify-center flex-shrink-0">
                                                    @if(isset($cluster['events']['juri_3']))
                                                        @include('Ketua.Log-juri.event-box', ['log' => $cluster['events']['juri_3']])
                                                    @else
                                                        <div class="h-8"></div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Legend (dipindah ke luar overflow-x-auto agar tidak ikut bergeser & ke kiri) -->
                        <div class="mt-2 mb-2 border border-gray-200 rounded-xl p-4 bg-gray-50 flex flex-wrap items-center justify-start gap-6 max-w-fit shadow-sm">
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('images/icons/pukul 1.png') }}" alt="Pukulan" class="w-8 h-8 object-contain invert">
                                <span class="text-sm font-semibold text-gray-700">= Pukulan (+1)</span>
                            </div>
                            <div class="w-px h-8 bg-gray-300 hidden md:block"></div>
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('images/icons/tendang 2.png') }}" alt="Tendangan" class="w-8 h-8 object-contain invert">
                                <span class="text-sm font-semibold text-gray-700">= Tendangan (+2)</span>
                            </div>
                            <div class="w-px h-8 bg-gray-300 hidden md:block"></div>
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 bg-green-500 rounded-full border border-white shadow-sm"></div>
                                <span class="text-sm font-semibold text-gray-700">= Sah</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 bg-red-500 rounded-full border border-white shadow-sm"></div>
                                <span class="text-sm font-semibold text-gray-700">= Tidak Sah</span>
                            </div>
                        </div>
