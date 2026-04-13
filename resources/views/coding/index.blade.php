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
@section('page-subheading', "Today: $today_name — Managing number coding restrictions")

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
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-4 w-4 text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search plate..."
                        class="block w-full pl-10 pr-3 py-2 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:outline-none text-sm font-bold text-gray-700">
                </div>
            </div>
            <a href="{{ route('coding.violations') }}" class="w-full md:w-auto px-6 py-2 bg-red-600 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-sm hover:bg-red-700 transition-all flex items-center justify-center gap-2">
                <i data-lucide="history" class="w-4 h-4"></i>
                Violation History
            </a>
        </form>
    </div>

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
                <div class="border rounded-2xl p-4 transition-all {{ $day === $today_name ? 'border-yellow-400 bg-yellow-50/30' : 'border-gray-100 bg-white' }}">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-black text-gray-800 text-sm tracking-tight">{{ $day }}</h4>
                        <div class="flex items-center gap-1">
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-[10px] font-black rounded-full">{{ $day_units->count() }}</span>
                            @if($day === $today_name)
                                <span class="px-2 py-0.5 bg-yellow-400 text-white text-[8px] font-black rounded-full shadow-sm">TODAY</span>
                            @endif
                        </div>
                    </div>
                    <!-- Restricted List Visibility: Maximum 2 units visible, then scroll -->
                    <div class="space-y-2 max-h-[120px] overflow-y-auto pr-1 custom-scrollbar">
                        @forelse($day_units as $u)
                            <div class="text-[11px] p-2 bg-white rounded-xl border border-gray-50 shadow-sm text-gray-700 font-bold text-center hover:border-blue-400 transition-colors cursor-default">
                                <div class="text-blue-600 uppercase">{{ $u->plate_number }}</div>
                            </div>
                        @empty
                            <div class="text-[10px] text-gray-300 italic text-center py-2">No units</div>
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