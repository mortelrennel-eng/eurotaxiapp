@php
/** @var \Illuminate\Support\Collection $units */
/** @var array $coding_calendar */
/** @var string $date */
/** @var string $search */
/** @var string $today_name */
@endphp
@extends('layouts.app')

@section('title', 'Coding Management - Euro System')
@section('page-heading', 'Coding Schedule Management')
@section('page-subheading', "Today: $today_name — Managing number coding days")

@section('content')
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #eab308; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #ca8a04; }
    </style>

    <!-- Date Filter & Actions -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" action="{{ route('coding.index') }}" class="flex flex-col md:flex-row gap-4 items-center">
            <div class="w-full md:w-48">
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                    class="block w-full px-4 py-2 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:outline-none text-sm font-bold text-gray-700">
            </div>
            <div class="flex-1 w-full">
                <div class="relative" id="searchContainer">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-4 w-4 text-gray-400"></i>
                    </div>
                    <input type="text" name="search" id="plateSearch" autocomplete="off" value="{{ $search }}" placeholder="Search plate..."
                        class="block w-full pl-10 pr-3 py-2 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:outline-none text-sm font-bold text-gray-700">
                    
                    <!-- Industry Standard Suggestions Dropdown -->
                    <div id="suggestionsDropdown" class="hidden absolute z-50 mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                        <div id="suggestionsList" class="max-h-60 overflow-y-auto custom-scrollbar"></div>
                        <div id="noResults" class="hidden p-4 text-center">
                            <i data-lucide="search-x" class="w-8 h-8 mx-auto mb-2 text-gray-300"></i>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Not Found</p>
                        </div>
                    </div>
                </div>
            </div>


        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('plateSearch');
            const dropdown = document.getElementById('suggestionsDropdown');
            const list = document.getElementById('suggestionsList');
            const noResults = document.getElementById('noResults');
            let debounceTimer;

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                const query = this.value.trim();

                if (query.length < 2) {
                    dropdown.classList.add('hidden');
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetchSuggestions(query);
                }, 300);
            });

            async function fetchSuggestions(query) {
                try {
                    const response = await fetch(`{{ route('coding.suggestions') }}?q=${encodeURIComponent(query)}`);
                    const data = await response.json();

                    renderSuggestions(data);
                } catch (error) {
                    console.error('Error fetching suggestions:', error);
                }
            }

            function renderSuggestions(items) {
                list.innerHTML = '';
                dropdown.classList.remove('hidden');

                if (items.length === 0) {
                    list.classList.add('hidden');
                    noResults.classList.remove('hidden');
                    return;
                }

                list.classList.remove('hidden');
                noResults.classList.add('hidden');

                items.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'px-4 py-3 hover:bg-gray-50 cursor-pointer flex items-center justify-between border-b border-gray-50 last:border-0 transition-colors group';
                    
                    const dayColors = {
                        'Monday': 'bg-red-100 text-red-600 border-red-200',
                        'Tuesday': 'bg-blue-100 text-blue-600 border-blue-200',
                        'Wednesday': 'bg-yellow-100 text-yellow-600 border-yellow-200',
                        'Thursday': 'bg-orange-100 text-orange-600 border-orange-200',
                        'Friday': 'bg-purple-100 text-purple-600 border-purple-200'
                    };
                    const colorClass = dayColors[item.coding_day] || 'bg-gray-100 text-gray-600 border-gray-200';

                    div.innerHTML = `
                        <div class="font-black text-gray-800 group-hover:text-blue-600 transition-colors uppercase tracking-tight">${item.plate_number}</div>
                        <div class="px-2 py-0.5 ${colorClass} text-[9px] font-black rounded-full border border-gray-100 uppercase tracking-widest">${item.coding_day}</div>
                    `;

                    div.addEventListener('click', () => {
                        searchInput.value = item.plate_number;
                        searchInput.form.submit();
                    });

                    list.appendChild(div);
                });
                
                // Refresh Lucide icons if needed
                if(window.lucide) {
                    lucide.createIcons();
                }
            }

            // Close dropdown on click outside
            document.addEventListener('click', function(e) {
                if (!document.getElementById('searchContainer').contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        });
    </script>

    <!-- Weekly Coding Calendar (Moved to Top) -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50/50 flex justify-between items-center">
            <h3 class="font-black text-gray-800 text-sm flex items-center gap-2">
                <i data-lucide="calendar-range" class="w-4 h-4 text-yellow-600"></i>
                Weekly Coding Calendar
            </h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-5 gap-4">
            @foreach($coding_calendar as $day => $day_units)
                <div class="border rounded-2xl p-4 transition-all duration-300 {{ $day === $today_name ? 'border-blue-400 bg-gradient-to-br from-blue-50 to-white shadow-[0_0_15px_rgba(59,130,246,0.3)] transform -translate-y-1 relative overflow-hidden' : 'border-gray-100 bg-gradient-to-br from-gray-50/80 to-white shadow-inner hover:shadow-md hover:-translate-y-0.5' }}">
                    @if($day === $today_name)
                        <div class="absolute top-0 right-0 w-16 h-16 bg-blue-400 blur-[30px] opacity-20 -mr-8 -mt-8 pointer-events-none"></div>
                    @endif
                    <div class="flex items-center justify-between mb-4 relative z-10">
                        <h4 class="font-black {{ $day === $today_name ? 'text-blue-800' : 'text-gray-800' }} text-sm tracking-tight">{{ $day }}</h4>
                        <div class="flex items-center gap-1">
                            <span class="px-2 py-0.5 bg-white shadow-sm border border-gray-100 text-gray-500 text-[10px] font-black rounded-full">{{ $day_units->count() }}</span>
                            @if($day === $today_name)
                                <span class="px-2 py-0.5 bg-blue-600 text-white text-[8px] font-black rounded-full shadow-md shadow-blue-200">TODAY</span>
                            @endif
                        </div>
                    </div>
                    <!-- Coding List Visibility: Maximum 2 units visible, then scroll -->
                    <div class="space-y-2 max-h-[120px] overflow-y-auto pr-1 custom-scrollbar relative z-10">
                        @forelse($day_units as $u)
                            <div class="text-[11px] px-3 py-1.5 bg-blue-50/80 backdrop-blur-sm rounded-full border border-blue-100 shadow-sm text-blue-700 font-black text-center hover:bg-blue-100 hover:shadow-md hover:border-blue-300 hover:scale-[1.02] transition-all cursor-pointer">
                                <span class="uppercase tracking-widest">{{ $u->plate_number }}</span>
                            </div>
                        @empty
                            <div class="text-[10px] text-gray-400 italic text-center py-2 bg-gray-50/50 rounded-xl border border-dashed border-gray-200">No units</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Today's Coding Units -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="calendar-check" class="w-5 h-5 text-yellow-600"></i>
                <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Coding Today ({{ $today_name }})</h3>
                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-[10px] font-black rounded-full">{{ $today_units->count() }} units</span>
            </div>
        </div>
        
        <!-- Table Scroll: Maximum 5 units visible (approx 450px) -->
        <div class="max-h-[450px] overflow-y-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/50 sticky top-0 z-10 backdrop-blur-sm">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Plate Number</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Make / Model</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Driver 1</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Driver 2</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse($today_units as $unit)
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap font-black text-gray-900 group-hover:text-blue-600 transition-colors">{{ $unit->plate_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-500">{{ $unit->make }} {{ $unit->model }}</td>
                            <td class="px-6 py-2 whitespace-nowrap text-xs text-gray-400">{{ $unit->driver1_name ?? '—' }}</td>
                            <td class="px-6 py-2 whitespace-nowrap text-xs text-gray-400">{{ $unit->driver2_name ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 text-[9px] font-black uppercase tracking-widest rounded-full bg-red-50 text-red-600 border border-red-100 animate-pulse">
                                    Coding
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-green-100">
                                    <i data-lucide="shield-check" class="w-8 h-8 text-green-500"></i>
                                </div>
                                <p class="text-sm font-black text-gray-400 uppercase tracking-widest">No units on coding today</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection