@extends('layouts.app')

@section('title', 'Boundary Management - Euro System')
@section('page-heading', 'Boundary Management')
@section('page-subheading', 'Track daily boundary collections and payments')

@section('content')

<!-- Search and Filters -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form class="flex flex-col sm:flex-row gap-4" method="GET" action="{{ route('boundaries.index') }}">
        <div class="flex-1">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
                </div>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ $search }}"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none"
                    placeholder="Search by plate number or driver..."
                >
            </div>
        </div>
        
        <div class="sm:w-40">
            <input
                type="date"
                id="date_filter"
                name="date"
                value="{{ $date_filter }}"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none"
            >
        </div>
        
        <div class="sm:w-40">
            <select
                id="status_filter"
                name="status"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none"
            >
                <option value="">All Status</option>
                <option value="pending" {{ $status_filter === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="paid" {{ $status_filter === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="shortage" {{ $status_filter === 'shortage' ? 'selected' : '' }}>Shortage</option>
                <option value="excess" {{ $status_filter === 'excess' ? 'selected' : '' }}>Excess</option>
            </select>
        </div>
        
        <div class="flex gap-2">
            <a href="{{ route('boundary-rules.index') }}"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 flex items-center gap-2 border border-gray-200 transition-all font-semibold"
                title="Manage Year-Based Pricing Rules"
            >
                <i data-lucide="settings" class="w-4 h-4"></i>
                Pricing Rules
            </a>
            
            <button type="button"
                onclick="addBoundary()"
                class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 flex items-center gap-2 shadow-sm font-bold"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Boundary
            </button>
        </div>
    </form>
</div>

<!-- Boundaries Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plate Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Boundary</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hulog (Debt)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @if (empty($boundariesArray))
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <i data-lucide="dollar-sign" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                            <p>No boundary records found</p>
                        </td>
                    </tr>
                @else
                    @foreach ($boundariesArray as $boundary)
                        <tr class="hover:bg-yellow-50 cursor-pointer transition-all border-l-4 border-transparent hover:border-yellow-400 group"
                            onclick="openViewBoundary({{ $boundary['id'] }})">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 group-hover:text-yellow-700 font-medium transition-colors">
                                {{ formatDate($boundary['date']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm font-bold text-gray-900 group-hover:text-yellow-700 transition-colors">{{ $boundary['plate_number'] }}</span>
                                    <i data-lucide="external-link" class="w-3 h-3 text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $boundary['driver_name'] ?? 'Unassigned' }}
                                    @if(!empty($boundary['is_extra_driver']))
                                        <span class="ml-1 px-1.5 py-0.5 bg-orange-100 text-orange-700 text-[9px] font-bold rounded border border-orange-300 uppercase tracking-tight">Extra Driver</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-gray-500 mt-1">
                                    <span title="Input by {{ $boundary['creator_name'] ?? 'System' }}">In: {{ $boundary['creator_name'] ?? 'System' }}</span>
                                    @if(isset($boundary['editor_name']) && $boundary['editor_name'])
                                        <span class="ml-2" title="Last edit by {{ $boundary['editor_name'] }}">Ed: {{ $boundary['editor_name'] }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-sm text-gray-900 font-bold">{{ formatCurrency($boundary['boundary_amount']) }}</span>
                                    @if(isset($boundary['rate_label']) && ($boundary['rate_type'] ?? 'regular') !== 'regular')
                                        <span class="text-[9px] font-black uppercase tracking-tighter px-1 rounded-sm mt-0.5 w-fit
                                            @if($boundary['rate_type'] === 'coding') bg-red-100 text-red-600 border border-red-200
                                            @elseif($boundary['rate_type'] === 'discount') bg-blue-100 text-blue-600 border border-blue-200
                                            @else bg-gray-100 text-gray-500 @endif">
                                            {{ $boundary['rate_label'] }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                {{ formatCurrency($boundary['actual_boundary'] ?? 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(($boundary['damage_payment'] ?? 0) > 0)
                                    <div class="text-sm font-black text-red-600">
                                        {{ formatCurrency($boundary['damage_payment']) }}
                                    </div>
                                    <span class="text-[9px] font-bold text-red-400 uppercase tracking-tighter">Debt Payment</span>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusClass = 'bg-yellow-100 text-yellow-800';
                                    if ($boundary['status'] === 'paid') $statusClass = 'bg-green-100 text-green-800';
                                    if ($boundary['status'] === 'shortage') $statusClass = 'bg-red-100 text-red-800';
                                    if ($boundary['status'] === 'excess') $statusClass = 'bg-blue-100 text-blue-800';
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                    {{ ucfirst($boundary['status']) }}
                                </span>
                                @if (isset($boundary['has_incentive']))
                                    <div class="mt-1">
                                        @if ($boundary['has_incentive'])
                                            <span class="px-1.5 py-0.5 bg-green-100 text-green-700 text-[9px] font-bold rounded border border-green-200 uppercase tracking-tight" title="Incentive eligible">Incentive Earned</span>
                                        @else
                                            @php
                                                $notes_lc = strtolower($boundary['notes'] ?? '');
                                                $is_damage_case = str_contains($notes_lc, 'vehicle damaged') || str_contains($notes_lc, 'maintenance') || str_contains($notes_lc, 'breakdown');
                                            @endphp
                                            @if ($is_damage_case)
                                                <span class="px-1.5 py-0.5 bg-red-100 text-red-700 text-[9px] font-bold rounded border border-red-200 uppercase tracking-tight" title="No incentive due to vehicle damage or breakdown">No Incentive</span>
                                            @else
                                                <span class="px-1.5 py-0.5 bg-red-100 text-red-700 text-[9px] font-bold rounded border border-red-200 uppercase tracking-tight" title="Recorded after the shift deadline — Late Turn">Late Turn / No Incentive</span>
                                            @endif
                                        @endif
                                    </div>
                                @endif
                                @if ($boundary['shortage'] > 0)
                                    <div class="text-xs text-red-600 mt-1">Shortage: {{ formatCurrency($boundary['shortage']) }}</div>
                                @elseif ($boundary['excess'] > 0)
                                    <div class="text-xs text-blue-600 mt-1">Excess: {{ formatCurrency($boundary['excess']) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" onclick="event.stopPropagation()">
                                <button
                                    type="button"
                                    onclick="editBoundary({{ $boundary['id'] }})"
                                    class="text-yellow-600 hover:text-yellow-900 p-1 rounded hover:bg-yellow-100 transition"
                                    title="Edit Boundary"
                                >
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if ($pagination['total_pages'] > 1)
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
                @if ($pagination['has_prev'])
                    <a href="?page={{ $pagination['prev_page'] }}&search={{ urlencode($search) }}&date={{ urlencode($date_filter) }}&status={{ urlencode($status_filter) }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
                @endif
                @if ($pagination['has_next'])
                    <a href="?page={{ $pagination['next_page'] }}&search={{ urlencode($search) }}&date={{ urlencode($date_filter) }}&status={{ urlencode($status_filter) }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
                @endif
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">{{ $pagination['offset'] + 1 }}</span> to 
                        <span class="font-medium">{{ min($pagination['offset'] + $pagination['items_per_page'], $pagination['total_items']) }}</span> of 
                        <span class="font-medium">{{ $pagination['total_items'] }}</span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        @if ($pagination['has_prev'])
                            <a href="?page={{ $pagination['prev_page'] }}&search={{ urlencode($search) }}&date={{ urlencode($date_filter) }}&status={{ urlencode($status_filter) }}" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </a>
                        @endif
                        
                        @php
                        $start_page = max(1, $pagination['page'] - 2);
                        $end_page = min($pagination['total_pages'], $pagination['page'] + 2);
                        @endphp
                        
                        @for ($i = $start_page; $i <= $end_page; $i++)
                            <a href="?page={{ $i }}&search={{ urlencode($search) }}&date={{ urlencode($date_filter) }}&status={{ urlencode($status_filter) }}" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium {{ $i === $pagination['page'] ? 'z-10 bg-yellow-50 border-yellow-500 text-yellow-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' }}">
                                {{ $i }}
                            </a>
                        @endfor
                        
                        @if ($pagination['has_next'])
                            <a href="?page={{ $pagination['next_page'] }}&search={{ urlencode($search) }}&date={{ urlencode($date_filter) }}&status={{ urlencode($status_filter) }}" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        @endif
                    </nav>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Boundary Modal -->
<div id="boundaryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-sm p-3 max-h-[95vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Add Boundary Record</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <form id="boundaryForm" method="POST" action="{{ route('boundaries.store') }}">
            @csrf
            <input type="hidden" name="action" id="formAction" value="add_boundary">
            <input type="hidden" name="id" id="boundaryId">
            
            <div class="space-y-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit *</label>
                    <div class="relative">
                        <input type="text" id="unitDisplay" required 
                               class="w-full px-2 py-1.5 border border-gray-300 rounded-lg bg-white cursor-pointer focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                               placeholder="Type to search units...">
                        <input type="hidden" name="unit_id" id="unitId" required>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
                        </div>
                        
                        <!-- Unit Dropdown -->
                        <div id="unit_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-32 overflow-y-auto hidden">
                            @foreach ($units as $unit)
                                <div class="unit-option px-2 py-1 hover:bg-yellow-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                     data-id="{{ $unit['id'] }}"
                                     data-name="{{ $unit['plate_number'] }}"
                                     data-plate="{{ $unit['plate_number'] }}"
                                     data-year="{{ $unit['year'] ?? 0 }}"
                                     data-model="{{ $unit['make_model'] ?? '' }}"
                                     data-rate="{{ $unit['boundary_rate'] ?? 0 }}"
                                     data-coding-day="{{ $unit['coding_day'] ?? '' }}"
                                     data-primary-id="{{ $unit['driver_id'] }}"
                                     data-secondary-id="{{ $unit['secondary_driver_id'] }}"
                                     data-expected-id="{{ $unit['current_turn_driver_id'] }}"
                                     data-deadline="{{ $unit['shift_deadline_at'] }}"
                                     data-swapped-at="{{ $unit['last_swapping_at'] }}">
                                    <div class="font-medium text-xs">{{ $unit['plate_number'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $unit['make_model'] ?? 'N/A' }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Driver *</label>
                    <div class="relative">
                        <input type="text" id="driverDisplay" required 
                               class="w-full px-2 py-1.5 border border-gray-300 rounded-lg bg-white cursor-pointer focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                               placeholder="Type to search drivers...">
                        <input type="hidden" name="driver_id" id="driverId" required>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
                        </div>
                        
                        <!-- Driver Dropdown -->
                        <div id="driver_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-32 overflow-y-auto hidden">
                            <!-- All drivers option -->
                            <div class="driver-option px-2 py-1 hover:bg-yellow-50 cursor-pointer border-b border-gray-100"
                                 data-id="all"
                                 data-name="All Drivers"
                                 data-unit=""
                                 data-plate="">
                                <div class="font-medium text-xs">All Drivers</div>
                                <div class="text-xs text-gray-500">Show all available drivers</div>
                            </div>
                            <!-- Unit-specific drivers block removed -->
                            <div class="all-drivers-list">
                                @foreach ($all_drivers as $driver)
                                    <div class="driver-option px-2 py-1 hover:bg-yellow-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                         data-id="{{ $driver['id'] }}"
                                         data-name="{{ $driver['name'] }}"
                                         data-unit="{{ $driver['current_unit'] }}"
                                         data-plate="{{ $driver['current_plate'] }}"
                                         data-shortage="{{ $driver['net_shortage'] ?? 0 }}"
                                         data-has-accident-debt="{{ ($driver['has_accident_debt'] ?? 0) > 0 ? 'true' : 'false' }}"
                                         data-accident-debt-amount="{{ $driver['total_accident_debt'] ?? 0 }}">
                                        <div class="font-medium text-xs">{{ $driver['name'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $driver['current_plate'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <!-- Hidden data block removed -->
                        </div>
                    </div>
                </div>

                {{-- Extra Driver Alert --}}
                <div id="extraDriverAlert" class="hidden mt-1 px-3 py-2 bg-orange-50 border border-orange-300 rounded-lg flex items-start gap-2">
                    <span class="text-orange-500 mt-0.5">⚠️</span>
                    <div>
                        <p class="text-xs font-bold text-orange-700">Extra Driver Detected</p>
                        <p class="text-[11px] text-orange-600">This driver is not regularly assigned to this unit. The record will be marked as <strong>Extra Driver</strong>.</p>
                    </div>
                </div>

                {{-- Past Shortage Alert --}}
                <div id="shortageBalanceAlert" class="hidden mt-1 px-3 py-2 bg-red-50 border border-red-300 rounded-lg">
                    <div class="flex items-start gap-2 mb-1">
                        <span class="text-red-500 mt-0.5">🚨</span>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-red-700">Past Balance Due</p>
                            <p class="text-[11px] text-red-600">This driver has an unpaid balance of <strong id="shortageBalanceAmount">₱0.00</strong> from previous shortages.</p>
                        </div>
                    </div>
                    <button type="button" onclick="payFullBalance()" 
                            class="w-full py-1 bg-red-600 text-white text-[10px] uppercase font-bold rounded hover:bg-red-700 transition-colors shadow-sm">
                        Pay Full Balance & Clear Debt
                    </button>
                    <input type="hidden" id="rawShortageAmount" value="0">
                </div>

                {{-- Shift Turn & Incentive Info --}}
                <div id="shiftInfoGroup" class="hidden mt-2 p-2 rounded-lg border flex flex-col gap-1 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] uppercase font-black tracking-widest text-gray-400">Current Turn</span>
                        <div id="incentiveStatusBadge"></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 bg-yellow-100 rounded-lg">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-yellow-600" id="turnIcon"></i>
                        </div>
                        <div class="flex flex-col">
                            <span id="expectedDriverName" class="text-sm font-bold text-gray-900 leading-tight">Driver Name</span>
                            <span id="shiftTimer" class="text-[10px] text-gray-500 font-medium tracking-tight">Shift started 0h ago</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                    <input type="date" name="date" id="date" required value="{{ date('Y-m-d') }}" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 font-bold">Target Boundary Amount *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-xs">₱</span>
                        </div>
                        <input type="number" name="boundary_amount" id="boundaryAmount" required step="0.01" min="0" 
                               class="w-full pl-7 px-2 py-1.5 border-2 border-yellow-200 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 font-bold"
                               title="Target boundary for this shift. Defaults to unit rate (adjusted for day).">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 font-bold">Actual Boundary Collected *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-blue-600 font-bold text-xs">₱</span>
                        </div>
                        <input type="number" name="actual_boundary" id="actualBoundary" required step="0.01" min="0" 
                               class="w-full pl-7 px-2 py-1.5 border-2 border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-bold text-blue-800"
                               placeholder="0.00">
                    </div>
                </div>
                
                <div id="damagePaymentContainer" class="hidden mt-3 p-3 bg-red-50 border border-red-200 rounded-xl space-y-2">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 bg-red-100 rounded-lg">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-black text-red-400 uppercase tracking-widest">Pending Accident Debt</span>
                            <span id="accidentDebtBalanceLabel" class="text-sm font-black text-red-700 leading-tight">₱0.00</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-red-700 uppercase mb-1.5">Damage Payment (Hulog)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-red-400 font-bold font-mono">₱</span>
                            <input type="number" name="damage_payment" id="damage_payment" step="0.01" min="0" value="0"
                                class="w-full pl-7 pr-3 py-2 bg-white border border-red-200 rounded-lg text-sm font-black text-red-900 focus:ring-2 focus:ring-red-500 focus:outline-none" placeholder="0.00">
                        </div>
                        <p class="text-[9px] text-red-500 font-bold mt-1 uppercase tracking-tighter italic leading-tight">* This amount will be deducted from the oldest pending accident debt.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" id="notes" rows="2" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                </div>

                <div class="mt-3 border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm">
                    <div class="bg-gray-50 px-3 py-2 border-b border-gray-200">
                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Exception Controls</span>
                    </div>
                    <div class="p-2 space-y-0.5">
                        <label class="flex items-center gap-3 cursor-pointer p-2 rounded hover:bg-orange-50 transition-colors group">
                            <input type="checkbox" name="past_cutoff" id="past_cutoff" value="1" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-800 group-hover:text-orange-700 leading-none mb-0.5 transition-colors">Past 10:00 AM Cut-off (Late / No Incentive)</span>
                                <span class="text-[10px] text-orange-600 leading-tight">Remittance recorded past the 10:00 AM deadline. Voids driver incentive.</span>
                            </div>
                        </label>

                        <div class="h-px bg-gray-100 my-1 mx-2 hidden"></div>

                        <label class="hidden items-center gap-3 cursor-pointer p-2 rounded hover:bg-red-50 transition-colors group">
                            <input type="checkbox" name="is_absent" id="is_absent" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-800 group-hover:text-red-700 leading-none mb-0.5 transition-colors">Absent / No Show</span>
                                <span class="text-[10px] text-red-600 leading-tight">Pilot did not show up or check in for the shift. Voids incentive & logs violation.</span>
                            </div>
                        </label>

                        <div class="h-px bg-gray-100 my-1 mx-2"></div>

                        <label class="flex items-center gap-3 cursor-pointer p-2 rounded hover:bg-gray-50 transition-colors">
                            <input type="checkbox" name="needs_maintenance_half" id="needsMaintenanceHalfCheck" value="1" class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500 needs-maintenance-opt">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-800 leading-none mb-0.5">Broke Down During Shift (Prorated Hourly)</span>
                                <span class="text-[10px] text-gray-500 leading-tight">Accurate computation based on actual hours driven since handover.</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer p-2 rounded hover:bg-gray-50 transition-colors">
                            <input type="checkbox" name="needs_maintenance_zero" id="needsMaintenanceZeroCheck" value="1" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 needs-maintenance-opt">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-800 leading-none mb-0.5">Broke Down Immediately (<= 2 hrs)</span>
                                <span class="text-[10px] text-gray-500 leading-tight">Vehicle broke down within 2 hours of deployment. Sets target boundary to 0 (Free).</span>
                            </div>
                        </label>

                        <div class="h-px bg-gray-100 my-1 mx-2"></div>

                        <label class="flex items-center gap-3 cursor-pointer p-2 rounded hover:bg-red-50 transition-colors group">
                            <input type="checkbox" name="vehicle_damaged" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-800 group-hover:text-red-700 leading-none mb-0.5 transition-colors">Vehicle Damaged (No Incentive)</span>
                                <span class="text-[10px] text-red-500 leading-tight">Automatic violation: Voids driver incentive due to vehicle damage.</span>
                            </div>
                        </label>

                        <!-- Calculation Transparency Box -->
                        <div id="breakdownComputationDraft" class="hidden mt-2 p-2 bg-blue-50 border border-blue-100 rounded-lg">
                            <div class="flex items-center gap-2 mb-1">
                                <i data-lucide="calculator" class="w-3 h-3 text-blue-600"></i>
                                <span class="text-[10px] font-bold text-blue-700 uppercase tracking-wider">Breakdown Computation</span>
                            </div>
                            <div id="breakdownMathDisplay" class="text-[11px] text-blue-800 font-medium leading-tight whitespace-pre-line">
                                <!-- Injected calculation here -->
                            </div>
                            <input type="hidden" id="calculatedHours" name="hours_driven">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 flex gap-3">
                <button type="submit" class="flex-1 bg-yellow-600 text-white py-2 px-4 rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    Save
                </button>
                <button type="button" onclick="closeModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- View Boundary Info Modal --}}
<div id="viewBoundaryModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm transition-all">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden max-h-[90vh] flex flex-col">
        {{-- Header --}}
        <div class="bg-yellow-600 p-6 text-white shrink-0">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span id="vb_statusBadge" class="px-2 py-0.5 bg-black/20 rounded text-[10px] font-black uppercase tracking-widest"></span>
                        <span id="vb_incentiveBadge" class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest"></span>
                    </div>
                    <h3 id="vb_plate" class="text-3xl font-black tracking-tighter uppercase"></h3>
                    <p id="vb_driver" class="text-yellow-100 font-bold text-sm uppercase mt-1"></p>
                </div>
                <button onclick="closeViewBoundary()" class="p-2 hover:bg-white/10 rounded-full transition">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6 overflow-y-auto flex-1">
            <div class="grid grid-cols-2 gap-4 mb-5">
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Date</p>
                    <p id="vb_date" class="text-sm font-bold text-gray-800"></p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Submitted By</p>
                    <p id="vb_creator" class="text-sm font-bold text-gray-800"></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-5">
                <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-100">
                    <p class="text-[10px] font-black text-yellow-600 uppercase tracking-widest mb-1">Target Boundary</p>
                    <p id="vb_boundaryAmount" class="text-xl font-black text-yellow-800"></p>
                    <p id="vb_rateLabel" class="text-[10px] text-yellow-600 font-bold mt-0.5"></p>
                </div>
                <div class="bg-green-50 rounded-xl p-4 border border-green-100">
                    <p class="text-[10px] font-black text-green-600 uppercase tracking-widest mb-1">Actual Collected</p>
                    <p id="vb_actualBoundary" class="text-xl font-black text-green-800"></p>
                </div>
            </div>

            <div id="vb_damagePaymentRow" class="mb-5 p-4 rounded-xl border bg-red-50 border-red-200 hidden">
                <p class="text-[10px] font-black text-red-600 uppercase tracking-widest mb-1">Damage Payment (Hulog)</p>
                <p id="vb_damagePayment" class="text-lg font-black text-red-700"></p>
                <p class="text-[9px] text-red-500 font-bold uppercase tracking-tighter italic">* Deducted from accident debt</p>
            </div>


            <div id="vb_differenceRow" class="mb-5 p-4 rounded-xl border hidden">
                <p class="text-[10px] font-black uppercase tracking-widest mb-1" id="vb_diffLabel"></p>
                <p id="vb_diffAmount" class="text-lg font-black"></p>
            </div>

            {{-- Exception Details (parsed from system flags) --}}
            <div id="vb_exceptionsRow" class="mb-5 hidden">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Exception Details</p>
                <div id="vb_exceptionCards" class="space-y-2"></div>
            </div>

            {{-- Dispatcher Notes (user-typed only) --}}
            <div id="vb_notesRow" class="mb-5 hidden">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Dispatcher Notes</p>
                <div id="vb_notes" class="p-4 bg-gray-50 rounded-xl text-sm text-gray-700 italic border-l-4 border-yellow-300 leading-relaxed"></div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 p-4 shrink-0 flex justify-end gap-3 border-t">
            <button onclick="closeViewBoundary()"
                class="px-6 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-100 text-sm font-bold uppercase tracking-tight transition shadow-sm">
                Close
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Boundary records keyed by ID for the view modal
const boundaryRecords = @json(collect($boundariesArray)->keyBy('id'));

function openViewBoundary(id) {
    const r = boundaryRecords[id];
    if (!r) return;

    // Header
    document.getElementById('vb_plate').innerText = r.plate_number || '—';
    document.getElementById('vb_driver').innerText = (r.driver_name || 'Unassigned') + (r.is_extra_driver ? ' • Extra Driver' : '');

    // Status badge
    const statusColors = { paid: 'bg-green-500', shortage: 'bg-red-500', excess: 'bg-blue-500' };
    const sBadge = document.getElementById('vb_statusBadge');
    sBadge.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest ' + (statusColors[r.status] || 'bg-gray-500');
    sBadge.innerText = r.status || 'unknown';

    // Incentive badge
    const iBadge = document.getElementById('vb_incentiveBadge');
    if (r.has_incentive !== null && r.has_incentive !== undefined) {
        iBadge.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest ' + (r.has_incentive ? 'bg-white/30' : 'bg-red-700');
        iBadge.innerText = r.has_incentive ? 'Incentive Earned' : 'No Incentive';
    } else {
        iBadge.className = 'hidden';
        iBadge.innerText = '';
    }

    // Details
    document.getElementById('vb_date').innerText = r.date || '—';
    document.getElementById('vb_creator').innerText = r.creator_name || 'System';
    document.getElementById('vb_boundaryAmount').innerText = '₱' + parseFloat(r.boundary_amount || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
    document.getElementById('vb_actualBoundary').innerText = '₱' + parseFloat(r.actual_boundary || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
    document.getElementById('vb_rateLabel').innerText = r.rate_label || '';

    // Damage Payment (Hulog)
    const damageRow = document.getElementById('vb_damagePaymentRow');
    const damagePayment = parseFloat(r.damage_payment || 0);
    if (damagePayment > 0) {
        damageRow.classList.remove('hidden');
        document.getElementById('vb_damagePayment').innerText = '₱' + damagePayment.toLocaleString('en-PH', {minimumFractionDigits: 2});
    } else {
        damageRow.classList.add('hidden');
    }

    // Shortage / Excess
    const diffRow = document.getElementById('vb_differenceRow');
    const shortage = parseFloat(r.shortage || 0);
    const excess = parseFloat(r.excess || 0);
    if (shortage > 0) {
        diffRow.className = 'mb-5 p-4 rounded-xl border bg-red-50 border-red-200';
        document.getElementById('vb_diffLabel').className = 'text-[10px] font-black uppercase tracking-widest mb-1 text-red-500';
        document.getElementById('vb_diffLabel').innerText = 'Shortage';
        document.getElementById('vb_diffAmount').className = 'text-lg font-black text-red-700';
        document.getElementById('vb_diffAmount').innerText = '₱' + shortage.toLocaleString('en-PH', {minimumFractionDigits: 2});
    } else if (excess > 0) {
        diffRow.className = 'mb-5 p-4 rounded-xl border bg-blue-50 border-blue-200';
        document.getElementById('vb_diffLabel').className = 'text-[10px] font-black uppercase tracking-widest mb-1 text-blue-500';
        document.getElementById('vb_diffLabel').innerText = 'Excess / Overpaid';
        document.getElementById('vb_diffAmount').className = 'text-lg font-black text-blue-700';
        document.getElementById('vb_diffAmount').innerText = '₱' + excess.toLocaleString('en-PH', {minimumFractionDigits: 2});
    } else {
        diffRow.className = 'mb-5 p-4 rounded-xl border hidden';
    }

    // Parse and display exception flags + clean user notes
    const rawNotes = (r.notes || '').trim();

    // --- Map of bracket tags → display info ---
    const exceptionDefs = [
        {
            tag: '[Automatic Violation: Vehicle Damaged]',
            label: 'Vehicle Damaged',
            sub: 'Driver incentive automatically voided.',
            icon: '⚠️',
            color: 'bg-red-50 border-red-300 text-red-800',
        },
        {
            tag: '[Unit Sent to Maintenance - Shift Schedule Paused (No Boundary)]',
            label: 'Broke Down Immediately (No Boundary)',
            sub: 'Unit broke down upon deployment. Boundary set to ₱0.00. Shift schedule paused. Pending maintenance created.',
            icon: '🔧',
            color: 'bg-orange-50 border-orange-300 text-orange-800',
        },
        {
            tag: '[Unit Sent to Maintenance - Shift Schedule Paused (Half Boundary)]',
            label: 'Broke Down During Shift (Half Boundary)',
            sub: 'Unit broke down mid-shift. Boundary halved. Shift schedule paused. Pending maintenance created.',
            icon: '🔧',
            color: 'bg-yellow-50 border-yellow-300 text-yellow-800',
        },
        {
            tag: '[Set New Boundary Schedule]',
            label: 'Boundary Schedule Reset',
            sub: 'Shift schedule was manually reset to the current time.',
            icon: '🔄',
            color: 'bg-blue-50 border-blue-300 text-blue-800',
        },
    ];

    let cleanNotes = rawNotes;
    const foundExceptions = [];

    exceptionDefs.forEach(def => {
        if (rawNotes.includes(def.tag)) {
            foundExceptions.push(def);
            cleanNotes = cleanNotes.replace(def.tag, '').trim();
        }
    });

    // Show exception cards
    const exceptRow = document.getElementById('vb_exceptionsRow');
    const exceptionCards = document.getElementById('vb_exceptionCards');
    if (foundExceptions.length > 0) {
        exceptionCards.innerHTML = foundExceptions.map(ex => `
            <div class="flex items-start gap-3 p-3 rounded-xl border ${ex.color}">
                <span class="text-lg leading-none mt-0.5">${ex.icon}</span>
                <div>
                    <p class="text-xs font-black uppercase tracking-wide leading-none mb-1">${ex.label}</p>
                    <p class="text-[11px] leading-snug opacity-80">${ex.sub}</p>
                </div>
            </div>
        `).join('');
        exceptRow.classList.remove('hidden');
    } else {
        exceptionCards.innerHTML = '';
        exceptRow.classList.add('hidden');
    }

    // Show user notes (cleaned)
    const notesRow = document.getElementById('vb_notesRow');
    const userNotes = cleanNotes.replace(/^\s+|\s+$/g, '');
    if (userNotes) {
        notesRow.classList.remove('hidden');
        document.getElementById('vb_notes').innerText = userNotes;
    } else {
        notesRow.classList.add('hidden');
    }

    document.getElementById('viewBoundaryModal').classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeViewBoundary() {
    document.getElementById('viewBoundaryModal').classList.add('hidden');
}

// Close on backdrop click
document.getElementById('viewBoundaryModal').addEventListener('click', function(e) {
    if (e.target === this) closeViewBoundary();
});

// Search and filter functionality
document.getElementById('search').addEventListener('input', function() {
    applyFilters();
});

document.getElementById('date_filter').addEventListener('change', function() {
    applyFilters();
});

document.getElementById('status_filter').addEventListener('change', function() {
    applyFilters();
});

function applyFilters() {
    const search = document.getElementById('search').value;
    const date = document.getElementById('date_filter').value;
    const status = document.getElementById('status_filter').value;
    const params = new URLSearchParams();
    
    if (search) params.append('search', search);
    if (date) params.append('date', date);
    if (status) params.append('status', status);
    
    window.location.href = '?' + params.toString();
}

// Auto-fill boundary amount and refresh drivers when unit is selected
document.getElementById('unitId').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (!selectedOption.value) return;
    const rate = parseFloat(selectedOption.getAttribute('data-rate'));
    const codingDay = selectedOption.getAttribute('data-coding-day');
    const dateInput = document.getElementById('date');
    
    if (rate) {
        // Source of Truth: Get the smart rate based on year, plate, and date selected
        const suggestedRate = getSmartTargetRate(selectedOption.getAttribute('data-year'), selectedOption.getAttribute('data-plate'), rate, dateInput.value);
        document.getElementById('boundaryAmount').value = suggestedRate;
        document.getElementById('boundaryAmount').dataset.originalTarget = suggestedRate;
        document.getElementById('actualBoundary').value = suggestedRate;
    }
    
    // Clear driver display and reset dropdown to show new unit's drivers
    const driverDisplay = document.getElementById('driverDisplay');
    if (driverDisplay) {
        driverDisplay.value = '';
        document.getElementById('driverId').value = '';
        // Refresh driver dropdown to show suggested drivers at top
        if (typeof filterDrivers === 'function') {
            filterDrivers('');
        }
    }
});

// Auto-recalculate boundary when date changes
document.getElementById('date').addEventListener('change', function() {
    const unitSelect = document.getElementById('unitId');
    if(unitSelect.selectedIndex < 0) return;
    const selectedOption = unitSelect.options[unitSelect.selectedIndex];
    const rate = parseFloat(selectedOption.getAttribute('data-rate'));
    const codingDay = selectedOption.getAttribute('data-coding-day');
    
    if (rate) {
        // Source of Truth: Recalculate smart rate based on new date
        const suggestedRate = getSmartTargetRate(selectedOption.getAttribute('data-year'), selectedOption.getAttribute('data-plate'), rate, this.value);
        document.getElementById('boundaryAmount').value = suggestedRate;
        document.getElementById('boundaryAmount').dataset.originalTarget = suggestedRate;
        document.getElementById('actualBoundary').value = suggestedRate;
        
        // Refresh breakdown if active
        updateBreakdownComputation();
    }
});

function getSmartTargetRate(year, plate, customRate, dateStr) {
    const rules = window.boundaryRules || [];
    const yr = parseInt(year) || 0;
    const rate = parseFloat(customRate) || 0;
    const date = dateStr ? new Date(dateStr) : new Date();
    
    // Find rule for the year
    const rule = rules.find(r => yr >= r.start_year && yr <= r.end_year);
    
    // Base rate priority: Custom -> Rule -> Default
    const base = rate > 0 ? rate : (rule ? parseFloat(rule.regular_rate) : 1100);
    
    // Day of week
    const dayOfWeek = date.getDay(); // 0 = Sunday, 6 = Saturday
    const dayName = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][dayOfWeek];
    
    // Detect coding day
    const codingDay = deriveCodingDay(plate);
    
    // 1. Coding Day Check (Overrides weekends)
    if (codingDay && dayName.toLowerCase() === codingDay.toLowerCase()) {
        if (rule && rule.coding_rate > 0) return parseFloat(rule.coding_rate).toFixed(2);
        return (base / 2).toFixed(2);
    }
    
    // 2. Weekend Check
    if (dayOfWeek === 6) { // Saturday
        const disc = rule ? parseFloat(rule.sat_discount) : 100;
        return (base - disc).toFixed(2);
    }
    if (dayOfWeek === 0) { // Sunday
        const disc = rule ? parseFloat(rule.sun_discount) : 200;
        return (base - disc).toFixed(2);
    }
    
    // 3. Regular Day
    return base.toFixed(2);
}

// Unit dropdown functionality
function initializeUnitDropdown() {
    const unitDisplay = document.getElementById('unitDisplay');
    const unitDropdown = document.getElementById('unit_dropdown');
    const unitOptions = document.querySelectorAll('.unit-option');
    
    if (unitDisplay && unitDropdown) {
        // Show dropdown on focus
        unitDisplay.addEventListener('focus', function() {
            filterUnits('');
            unitDropdown.classList.remove('hidden');
        });
        
        // Filter units on input
        unitDisplay.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterUnits(searchTerm);
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!unitDisplay.contains(e.target) && !unitDropdown.contains(e.target)) {
                unitDropdown.classList.add('hidden');
            }
        });
        
        // Handle unit selection - use mousedown to fire before document click hide
        unitOptions.forEach(option => {
            option.addEventListener('mousedown', function(e) {
                e.preventDefault(); // Prevent focus loss
                const unitId = this.getAttribute('data-id');
                const unitName = this.getAttribute('data-name');
                const unitPlate = this.getAttribute('data-plate');
                
                // Store primary and secondary driver IDs for suggestion
                const primaryId = this.getAttribute('data-primary-id');
                const secondaryId = this.getAttribute('data-secondary-id');
                const plate = this.getAttribute('data-plate');
                const year = this.getAttribute('data-year');
                const customRate = this.getAttribute('data-rate');

                document.getElementById('unitId').value = unitId;
                unitDisplay.value = unitPlate;
                unitDropdown.classList.add('hidden');

                // Reset extra driver alert when unit changes
                const alertBox = document.getElementById('extraDriverAlert');
                if (alertBox) alertBox.classList.add('hidden');
                document.getElementById('driverId').value = '';
                document.getElementById('driverDisplay').value = '';

                // Source of Truth: Get the smart rate
                const suggestedRate = getSmartTargetRate(year, plate, customRate, document.getElementById('date').value);
                const boundaryInput = document.getElementById('boundaryAmount');
                if (boundaryInput) {
                    boundaryInput.value = suggestedRate;
                    boundaryInput.dataset.originalTarget = suggestedRate;
                    
                    const needsMaintenanceHalfCheck = document.getElementById('needsMaintenanceHalfCheck');
                    const needsMaintenanceZeroCheck = document.getElementById('needsMaintenanceZeroCheck');
                    
                    if (needsMaintenanceZeroCheck && needsMaintenanceZeroCheck.checked) {
                        boundaryInput.value = '0.00';
                        document.getElementById('actualBoundary').value = '0.00';
                    } else if (needsMaintenanceHalfCheck && needsMaintenanceHalfCheck.checked) {
                        const halfLimit = (parseFloat(suggestedRate) / 2).toFixed(2);
                        boundaryInput.value = halfLimit;
                        document.getElementById('actualBoundary').value = halfLimit;
                    } else {
                        document.getElementById('actualBoundary').value = suggestedRate;
                    }
                }

                // New: Handle Swapping & Shift turn data
                updateShiftInfo(this);

                unitDisplay.setAttribute('data-primary-id', primaryId || '');
                unitDisplay.setAttribute('data-secondary-id', secondaryId || '');

                // Trigger change event
                document.getElementById('unitId').dispatchEvent(new Event('change'));
            });
        });
    }
}

function filterUnits(searchTerm) {
    const unitOptions = document.querySelectorAll('.unit-option');
    const unitDropdown = document.getElementById('unit_dropdown');
    
    let hasResults = false;
    unitOptions.forEach(option => {
        const unitName = option.getAttribute('data-name').toLowerCase();
        const unitPlate = option.getAttribute('data-plate').toLowerCase();
        const unitModel = (option.getAttribute('data-model') || '').toLowerCase();
        
        if (unitName.includes(searchTerm) || unitPlate.includes(searchTerm) || unitModel.includes(searchTerm)) {
            option.style.display = 'block';
            hasResults = true;
        } else {
            option.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    let noResultsMsg = unitDropdown.querySelector('.no-results');
    if (!hasResults) {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results px-2 py-1 text-xs text-gray-500 text-center';
            noResultsMsg.textContent = 'No units found';
            unitDropdown.appendChild(noResultsMsg);
        }
    } else if (noResultsMsg) {
        noResultsMsg.remove();
    }
}

// Driver dropdown functionality  
function initializeDriverDropdown() {
    const driverDisplay = document.getElementById('driverDisplay');
    const driverDropdown = document.getElementById('driver_dropdown');
    const driverOptions = document.querySelectorAll('.driver-option');
    
    if (driverDisplay && driverDropdown) {
        // Show dropdown on focus
        driverDisplay.addEventListener('focus', function() {
            filterDrivers('');
            driverDropdown.classList.remove('hidden');
        });
        
        // Filter drivers on input
        driverDisplay.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterDrivers(searchTerm);
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!driverDisplay.contains(e.target) && !driverDropdown.contains(e.target)) {
                driverDropdown.classList.add('hidden');
            }
        });
        
        // Handle driver selection
        driverOptions.forEach(option => {
            option.addEventListener('mousedown', function(e) {
                e.preventDefault();
                const driverId = this.getAttribute('data-id');
                const driverName = this.getAttribute('data-name');
                const shortage = parseFloat(this.getAttribute('data-shortage') || 0);

                document.getElementById('driverId').value = driverId;
                driverDisplay.value = driverName;
                driverDropdown.classList.add('hidden');

                // Check if extra driver (not assigned to selected unit)
                const unitIdInput = document.getElementById('unitId');
                const unitOption = document.querySelector(`.unit-option[data-id="${unitIdInput.value}"]`);
                const primaryId = unitOption ? unitOption.getAttribute('data-primary-id') : '';
                const secondaryId = unitOption ? unitOption.getAttribute('data-secondary-id') : '';
                
                const extraAlert = document.getElementById('extraDriverAlert');
                // Show extra driver alert if:
                // - a real driver is selected (not 'all')
                // - AND they are NOT the primary or secondary driver of this unit
                // - OR the unit has no assigned driver at all (any assignment is 'extra')
                const isExtra = driverId && driverId !== 'all' && (
                    !primaryId || // no assigned driver
                    (driverId !== primaryId && driverId !== secondaryId) // not a regular driver
                );
                if (isExtra) {
                    extraAlert.classList.remove('hidden');
                } else {
                    extraAlert.classList.add('hidden');
                }

                // Handle Shortage Balance Alert
                const shortageAlert = document.getElementById('shortageBalanceAlert');
                const shortageAmountSpan = document.getElementById('shortageBalanceAmount');
                const rawShortageInput = document.getElementById('rawShortageAmount');

                if (shortage > 0) {
                    shortageAlert.classList.remove('hidden');
                    shortageAmountSpan.textContent = "₱" + shortage.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    rawShortageInput.value = shortage;
                } else {
                    shortageAlert.classList.add('hidden');
                    rawShortageInput.value = 0;
                }

                // Toggle Damage Payment field visibility based on accident debt
                const hasAccidentDebt = this.getAttribute('data-has-accident-debt') === 'true';
                const accidentDebtAmount = parseFloat(this.getAttribute('data-accident-debt-amount') || 0);
                const damageContainer = document.getElementById('damagePaymentContainer');
                const debtLabel = document.getElementById('accidentDebtBalanceLabel');

                if (hasAccidentDebt && accidentDebtAmount > 0) {
                    damageContainer.classList.remove('hidden');
                    if (debtLabel) debtLabel.textContent = "₱" + accidentDebtAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else {
                    damageContainer.classList.add('hidden');
                    document.getElementById('damage_payment').value = 0;
                }

                document.getElementById('driverId').dispatchEvent(new Event('change'));
            });
        });
    }
}

function payFullBalance() {
    const dailyTarget = parseFloat(document.getElementById('boundaryAmount').value || 0);
    const pastShortage = parseFloat(document.getElementById('rawShortageAmount').value || 0);
    const actualCollectedInput = document.getElementById('actualBoundary');

    const totalToPay = dailyTarget + pastShortage;
    actualCollectedInput.value = totalToPay.toFixed(2);
    
    // Visual feedback/confirmation
    actualCollectedInput.classList.add('ring-4', 'ring-green-400');
    setTimeout(() => actualCollectedInput.classList.remove('ring-4', 'ring-green-400'), 1000);
}

function filterDrivers(searchTerm) {
    const driverOptions = document.querySelectorAll('.driver-option');
    const driverDropdown = document.getElementById('driver_dropdown');
    const allDriversList = document.querySelector('.all-drivers-list');
    
    if (allDriversList) {
        allDriversList.style.display = 'flex';
        allDriversList.style.flexDirection = 'column';
    }
    
    const unitDisplay = document.getElementById('unitDisplay');
    const primaryId = unitDisplay.getAttribute('data-primary-id');
    const secondaryId = unitDisplay.getAttribute('data-secondary-id');
    
    let hasResults = false;
    driverOptions.forEach(option => {
        const driverName = option.getAttribute('data-name').toLowerCase();
        const driverUnit = (option.getAttribute('data-unit') || '').toLowerCase();
        const driverPlate = (option.getAttribute('data-plate') || '').toLowerCase();
        const driverIdAttr = option.getAttribute('data-id');
        
        if (driverName.includes(searchTerm) || driverUnit.includes(searchTerm) || driverPlate.includes(searchTerm)) {
            option.style.display = 'block';
            
            // Don't modify "All Drivers" styling
            if (option.getAttribute('data-id') === 'all') {
                hasResults = true;
                return;
            }
            
            // Match via primary or secondary driver ID for strict suggestion
            const isSuggested = driverIdAttr && (driverIdAttr == primaryId || driverIdAttr == secondaryId);

            if (isSuggested) {
                option.style.order = '-1';
                option.classList.remove('hover:bg-yellow-50');
                option.classList.add('bg-green-50', 'border-l-4', 'border-green-500', 'hover:bg-green-100');
                
                let nameDiv = option.querySelector('.font-medium');
                if (nameDiv && !option.querySelector('.suggested-badge')) {
                    nameDiv.innerHTML += ' <span class="suggested-badge ml-2 px-1.5 py-0.5 bg-green-500 text-white text-[10px] rounded-full shadow-sm font-bold">Recommended</span>';
                }
            } else {
                option.style.order = '0';
                option.classList.remove('bg-green-50', 'border-l-4', 'border-green-500', 'hover:bg-green-100');
                option.classList.add('hover:bg-yellow-50');
                let badge = option.querySelector('.suggested-badge');
                if (badge) badge.remove();
            }
            hasResults = true;
        } else {
            option.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    let noResultsMsg = driverDropdown.querySelector('.no-results');
    if (!hasResults) {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results px-2 py-1 text-xs text-gray-500 text-center';
            noResultsMsg.textContent = 'No drivers found';
            driverDropdown.appendChild(noResultsMsg);
        }
    } else if (noResultsMsg) {
        noResultsMsg.remove();
    }
}

// Modal functions
function addBoundary() {
    document.getElementById('modalTitle').textContent = 'Add Boundary Record';
    document.getElementById('formAction').value = 'add_boundary';
    document.getElementById('boundaryForm').reset();

    const unitDisplay = document.getElementById('unitDisplay');
    if (unitDisplay) {
        unitDisplay.value = '';
        unitDisplay.removeAttribute('data-primary-id');
        unitDisplay.removeAttribute('data-secondary-id');
    }

    // Hide alerts on fresh open
    const extraAlert = document.getElementById('extraDriverAlert');
    const shortageAlert = document.getElementById('shortageBalanceAlert');
    const damageContainer = document.getElementById('damagePaymentContainer');
    if (extraAlert) extraAlert.classList.add('hidden');
    if (shortageAlert) shortageAlert.classList.add('hidden');
    if (damageContainer) damageContainer.classList.add('hidden');

    document.getElementById('date').value = new Date().toLocaleDateString('en-CA');

    // Auto-check the Past 10:00 AM Cut-off if current time is >= 10:00 AM
    const pastCutoffCheckbox = document.getElementById('past_cutoff');
    if (pastCutoffCheckbox) {
        pastCutoffCheckbox.checked = new Date().getHours() >= 10;
    }

    document.getElementById('boundaryModal').classList.remove('is-editing');
    document.getElementById('breakdownComputationDraft').classList.add('hidden');
    document.getElementById('boundaryModal').classList.remove('hidden');
    lucide.createIcons();
}

function editBoundary(id) {
    // Find the boundary data directly from the page
    const boundaryData = @json($boundariesArray);
    const boundary = boundaryData.find(b => b.id == id);
    
    if (boundary) {
        document.getElementById('modalTitle').textContent = 'Edit Boundary Record';
        document.getElementById('formAction').value = 'update_boundary';
        document.getElementById('boundaryId').value = boundary.id;
        document.getElementById('unitId').value = boundary.unit_id;
        document.getElementById('driverId').value = boundary.driver_id;
        document.getElementById('date').value = boundary.date;
        document.getElementById('boundaryAmount').value = boundary.boundary_amount;
        document.getElementById('actualBoundary').value = boundary.actual_boundary || '';
        document.getElementById('damage_payment').value = boundary.damage_payment || 0;
        document.getElementById('notes').value = boundary.notes || '';
        
        // Handle Damage Payment Visibility for Edit
        const damageContainer = document.getElementById('damagePaymentContainer');
        const driverOption = document.querySelector(`.driver-option[data-id="${boundary.driver_id}"]`);
        const hasAccidentDebt = driverOption && driverOption.getAttribute('data-has-accident-debt') === 'true';
        
        if (hasAccidentDebt || (parseFloat(boundary.damage_payment || 0) > 0)) {
            if (damageContainer) damageContainer.classList.remove('hidden');
        } else {
            if (damageContainer) damageContainer.classList.add('hidden');
        }
        
        // Hide alerts on fresh open
        const extraAlert = document.getElementById('extraDriverAlert');
        const shortageAlert = document.getElementById('shortageBalanceAlert');
        if (extraAlert) extraAlert.classList.add('hidden');
        if (shortageAlert) shortageAlert.classList.add('hidden');

        // Set Unit Display (guaranteed to fill required field even if inactive)
        const unitDisplay = document.getElementById('unitDisplay');
        unitDisplay.value = boundary.plate_number || 'Unknown Unit';
        
        const unitOption = document.querySelector(`.unit-option[data-id="${boundary.unit_id}"]`);
        if (unitOption) {
            const pId = unitOption.getAttribute('data-primary-driver');
            const sId = unitOption.getAttribute('data-secondary-driver');
            unitDisplay.setAttribute('data-primary-id', pId || '');
            unitDisplay.setAttribute('data-secondary-id', sId || '');
            
            // Critical for computation box
            const swappedAt = unitOption.getAttribute('data-swapped-at');
            document.getElementById('boundaryModal').setAttribute('data-current-swap', swappedAt || '');

            // Set original target from unit rate for calculations
            const unitRate = unitOption.getAttribute('data-rate');
            document.getElementById('boundaryAmount').dataset.originalTarget = unitRate;
        }

        // Set Driver Display (guaranteed to fill required field even if inactive)
        const driverDisplay = document.getElementById('driverDisplay');
        driverDisplay.value = boundary.driver_name || 'Unknown Driver';

        // Allow editing boundary amount if needed
        document.getElementById('boundaryAmount').readOnly = false;

        // Parse existing exception rules from notes
        const notesLc = (boundary.notes || '').toLowerCase();
        
        // Uncheck all first
        const pastCutoffEl = document.getElementById('past_cutoff');
        const damagedEl = document.querySelector('input[name="vehicle_damaged"]');
        const halfMaintEl = document.getElementById('needsMaintenanceHalfCheck');
        const zeroMaintEl = document.getElementById('needsMaintenanceZeroCheck');
        
        if (pastCutoffEl) pastCutoffEl.checked = false;
        if (damagedEl) damagedEl.checked = false;
        if (halfMaintEl) halfMaintEl.checked = false;
        if (zeroMaintEl) zeroMaintEl.checked = false;

        // Re-check based on existing data
        // Absent check removed per user request
        if (notesLc.includes('past 10:00 am') && pastCutoffEl) {
            pastCutoffEl.checked = true;
        }
        if (notesLc.includes('vehicle damaged') && damagedEl) {
            damagedEl.checked = true;
        }
        if ((notesLc.includes('half boundary') || notesLc.includes('broke down during') || notesLc.includes('hrs x')) && halfMaintEl) {
            halfMaintEl.checked = true;
        }
        if ((notesLc.includes('no boundary') || notesLc.includes('immediately')) && notesLc.includes('maintenance') && zeroMaintEl) {
            zeroMaintEl.checked = true;
        }
        
        // Refresh breakdown calculation display
        updateBreakdownComputation();

        document.getElementById('boundaryModal').classList.add('is-editing');
        document.getElementById('boundaryModal').classList.remove('hidden');
        lucide.createIcons();
    } else {
        alert('Boundary record not found');
    }
}

function closeModal() {
    document.getElementById('boundaryModal').classList.add('hidden');
}

window.boundaryRules = @json($boundary_rules ?? []);

// Legacy calculateAutomatedRate function replaced by getSmartTargetRate for accuracy and date-sync.

function deriveCodingDay(plate) {
    if (!plate) return null;
    const cleanPlate = plate.toString().trim();
    let lastChar = cleanPlate.slice(-1);
    
    if (isNaN(parseInt(lastChar))) {
        // Find last numeric char
        const matches = cleanPlate.match(/\d/g);
        if (matches) lastChar = matches[matches.length - 1];
        else return null;
    }
    
    const lastDigit = parseInt(lastChar);
    const mapping = {
        'Monday': [1, 2],
        'Tuesday': [3, 4],
        'Wednesday': [5, 6],
        'Thursday': [7, 8],
        'Friday': [9, 0]
    };
    
    for (const [day, digits] of Object.entries(mapping)) {
        if (digits.includes(lastDigit)) return day;
    }
    return null;
}

function updateShiftInfo(unitElement) {
    const expectedId = unitElement.getAttribute('data-expected-id') || '0';
    const deadline = unitElement.getAttribute('data-deadline');
    
    const shiftInfoGroup = document.getElementById('shiftInfoGroup');
    const driverNameLabel = document.getElementById('expectedDriverName');
    const shiftTimerLabel = document.getElementById('shiftTimer');
    const badgeContainer = document.getElementById('incentiveStatusBadge');
    
    // Find expected driver name
    const driverOption = document.querySelector(`.driver-option[data-id="${expectedId}"]`);
    const expectedName = driverOption ? driverOption.getAttribute('data-name') : 'Unknown Driver';
    
    // Auto-select expected driver
    if (expectedId && expectedId !== '0') {
        document.getElementById('driverId').value = expectedId;
        document.getElementById('driverDisplay').value = expectedName;
        
        // Trigger alerts check
        const shortage = parseFloat(driverOption ? driverOption.getAttribute('data-shortage') : 0);
        if (typeof triggerDriverAlerts === 'function') {
            triggerDriverAlerts(expectedId, shortage);
        }
    }

    // Calculate precision time based on STRICT DEADLINE
    if (deadline) {
        const deadlineDate = new Date(deadline);
        // Format absolute shifting time (e.g., 2:00 PM)
        const formatOptions = { hour: 'numeric', minute: 'numeric', hour12: true };
        const absoluteTimeStr = deadlineDate.toLocaleTimeString('en-US', formatOptions);

        const now = new Date();
        const diffMs = deadlineDate - now;
        const isPast = diffMs < 0;
        const absDiff = Math.abs(diffMs);
        
        const diffHours = Math.floor(absDiff / (1000 * 60 * 60));
        const diffMins = Math.floor((absDiff % (1000 * 60 * 60)) / (1000 * 60));
        
        if (isPast) {
            const STALE_THRESHOLD_HRS = 24;
            if (diffHours < STALE_THRESHOLD_HRS) {
                // Genuinely late for this shift (within last 24hrs)
                shiftTimerLabel.innerHTML = `<span class="flex flex-col"><span class="text-red-600 font-black">LATE RETURN: ${diffHours}h ${diffMins}m Ago</span><span class="text-gray-400">Shifting Time: <strong>${absoluteTimeStr}</strong></span></span>`;
                shiftInfoGroup.classList.add('border-red-200', 'bg-red-50');
                shiftInfoGroup.classList.remove('border-green-200', 'bg-green-50', 'border-orange-200', 'bg-orange-50');
                // Removed red "NO INCENTIVE" badge per user request. Late return is just a note, not an incentive void.
                badgeContainer.innerHTML = '<span class="px-1.5 py-0.5 bg-green-100 text-green-700 text-[9px] font-bold rounded-full border border-green-300 uppercase tracking-tighter shadow-sm">INCENTIVE ELIGIBLE</span>';
            } else {
                // Stale schedule (>24h) — hide the shift info block entirely, no confusing labels
                shiftInfoGroup.classList.add('hidden');
                return; // Exit early, don't show the block
            }
        } else {
            shiftTimerLabel.innerHTML = `<span class="flex flex-col"><span class="text-green-600 font-bold">${diffHours}h ${diffMins}m remaining</span><span class="text-gray-400 mt-0.5">Shifting Time: <strong>${absoluteTimeStr}</strong></span></span>`;
            shiftInfoGroup.classList.add('border-green-200', 'bg-green-50');
            shiftInfoGroup.classList.remove('border-red-200', 'bg-red-50');
            badgeContainer.innerHTML = '<span class="px-1.5 py-0.5 bg-green-100 text-green-700 text-[9px] font-bold rounded-full border border-green-300 uppercase tracking-tighter shadow-sm">INCENTIVE ELIGIBLE</span>';
        }
    } else {
        shiftTimerLabel.innerHTML = '<span class="text-gray-500">Shift schedule not yet initialized</span>';
        badgeContainer.innerHTML = '<span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 text-[9px] font-bold rounded-full border border-blue-300 uppercase tracking-tighter">NEW PATTERN</span>';
    }

    const swappedAt = unitElement.getAttribute('data-swapped-at');
    document.getElementById('boundaryModal').setAttribute('data-current-swap', swappedAt || '');

    shiftInfoGroup.classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
    
    // Refresh computation if any breakdown check is already active
    updateBreakdownComputation();
}

function updateBreakdownComputation() {
    const modal = document.getElementById('boundaryModal');
    const swappedAt = modal.getAttribute('data-current-swap');
    const dailyRate = parseFloat(document.getElementById('boundaryAmount').dataset.originalTarget || 0);
    
    const amtInput = document.getElementById('boundaryAmount');
    const actInput = document.getElementById('actualBoundary');
    const mathDisplay = document.getElementById('breakdownMathDisplay');
    const compBox = document.getElementById('breakdownComputationDraft');
    
    const halfCheck = document.getElementById('needsMaintenanceHalfCheck');
    const zeroCheck = document.getElementById('needsMaintenanceZeroCheck');

    if (!halfCheck || !zeroCheck || (!halfCheck.checked && !zeroCheck.checked)) {
        if (compBox) compBox.classList.add('hidden');
        
        // ROBUST RESET: Always return to original suggested rate if unselected
        if (amtInput && amtInput.dataset.originalTarget) {
             const original = parseFloat(amtInput.dataset.originalTarget).toFixed(2);
             if (amtInput.value !== original) {
                 amtInput.value = original;
                 if (actInput) actInput.value = original;
             }
        }
        return;
    }

    let swapDate;
    if (swappedAt) {
        swapDate = new Date(swappedAt);
    } else {
        // FALLBACK: If no handover timestamp exists, assume it started at 10:00 AM of the record date
        // or 10:00 AM yesterday if it's currently past 10:00 AM today.
        const recordDateVal = document.getElementById('date').value;
        swapDate = new Date(recordDateVal + 'T10:00:00');
        // If the resulting "start" is in the future relative to now, assume it started yesterday
        if (swapDate > new Date()) {
            swapDate.setDate(swapDate.getDate() - 1);
        }
    }

    const now = new Date();
    const diffMs = now - swapDate;
    const rawHours = diffMs / (1000 * 60 * 60);
    const hoursDriven = Math.max(0, rawHours);
    
    // SMART CAP: A shift is max 24 hours. Anything beyond is a backlog, but for a 
    // single daily boundary record, we cap the prorated charge to one full day.
    const cappedHours = Math.min(24, hoursDriven);
    const hoursDisplay = hoursDriven > 24 ? `24.00 (Capped from ${hoursDriven.toFixed(2)})` : hoursDriven.toFixed(2);
    
    const hourlyRate = (dailyRate / 24);
    let prorated = hourlyRate * cappedHours;
    
    // FINAL SAFETY CAP: Never exceed the original daily target
    if (prorated > dailyRate) prorated = dailyRate;
    const proratedStr = prorated.toFixed(2);

    compBox.classList.remove('hidden');
    document.getElementById('calculatedHours').value = cappedHours.toFixed(2);

    if (zeroCheck.checked) {
        if (cappedHours <= 2) {
            mathDisplay.innerHTML = `<span class="flex justify-between"><span>Driven:</span> <span class="font-bold text-green-700">${hoursDisplay} hrs (<= 2hr)</span></span>
                                     <span class="flex justify-between border-t border-blue-100 mt-1 pt-1"><span>Target:</span> <span class="font-bold text-green-700">₱0.00 (Free Boundary)</span></span>`;
            amtInput.value = '0.00';
            actInput.value = '0.00';
        } else {
            mathDisplay.innerHTML = `<span class="flex justify-between"><span>Driven:</span> <span class="font-bold text-red-600">${hoursDisplay} hrs (> 2hr)</span></span>
                                     <span class="flex justify-between border-t border-blue-100 mt-1 pt-1"><span>Target (Hourly):</span> <span class="font-bold text-red-700">₱${parseFloat(proratedStr).toLocaleString()}</span></span>`;
            amtInput.value = proratedStr;
            actInput.value = proratedStr;
        }
    } else if (halfCheck.checked) {
        mathDisplay.innerHTML = `<span class="flex justify-between"><span>Driven:</span> <span class="font-bold">${hoursDisplay} hrs</span></span>
                                 <span class="flex justify-between"><span>Rate:</span> <span>₱${hourlyRate.toFixed(2)}/hr</span></span>
                                 <span class="flex justify-between border-t border-blue-100 mt-1 pt-1"><span>Hourly Target:</span> <span class="font-bold text-blue-700">₱${parseFloat(proratedStr).toLocaleString()}</span></span>`;
        amtInput.value = proratedStr;
        actInput.value = proratedStr;
    }
}

function triggerDriverAlerts(driverId, shortage) {
    const unitId = document.getElementById('unitId').value;
    const unitOption = document.querySelector(`.unit-option[data-id="${unitId}"]`);
    const primaryId = unitOption ? unitOption.getAttribute('data-primary-id') : '';
    const secondaryId = unitOption ? unitOption.getAttribute('data-secondary-id') : '';

    const extraAlert = document.getElementById('extraDriverAlert');
    if (driverId && driverId !== 'all' && primaryId && driverId !== primaryId && driverId !== secondaryId) {
        if (extraAlert) extraAlert.classList.remove('hidden');
    } else {
        if (extraAlert) extraAlert.classList.add('hidden');
    }

    const shortageAlert = document.getElementById('shortageBalanceAlert');
    const shortageAmountSpan = document.getElementById('shortageBalanceAmount');
    if (shortage > 0) {
        if (shortageAlert) shortageAlert.classList.remove('hidden');
        if (shortageAmountSpan) shortageAmountSpan.textContent = "₱" + shortage.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        const rawInput = document.getElementById('rawShortageAmount');
        if (rawInput) rawInput.value = shortage;
    } else {
        if (shortageAlert) shortageAlert.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    initializeUnitDropdown();
    initializeDriverDropdown();
    
    // Sync manual target changes with dataset and actual boundary
    const amtInput = document.getElementById('boundaryAmount');
    if (amtInput) {
        amtInput.addEventListener('input', function() {
            const val = this.value || '0.00';
            // Update originalTarget so maintenance math uses this as the new base
            this.dataset.originalTarget = val;
            
            const actualInput = document.getElementById('actualBoundary');
            if (actualInput) actualInput.value = val;
            
            // Re-run breakdown math if active to use the new base
            updateBreakdownComputation();
        });
    }

    // Handle Needs Maintenance logic dynamically (Half vs Zero)
    const needsMaintenanceHalfCheck = document.getElementById('needsMaintenanceHalfCheck');
    const needsMaintenanceZeroCheck = document.getElementById('needsMaintenanceZeroCheck');
    
    function applyMaintenanceLogic(triggerElem) {
        if (!amtInput || !amtInput.dataset.originalTarget) return;
        
        // Ensure mutual exclusivity
        if (triggerElem === needsMaintenanceHalfCheck && needsMaintenanceHalfCheck.checked) {
            if(needsMaintenanceZeroCheck) needsMaintenanceZeroCheck.checked = false;
        } else if (triggerElem === needsMaintenanceZeroCheck && needsMaintenanceZeroCheck.checked) {
            if(needsMaintenanceHalfCheck) needsMaintenanceHalfCheck.checked = false;
        }

        if (!needsMaintenanceHalfCheck.checked && !needsMaintenanceZeroCheck.checked) {
            // Revert to original
            let original = parseFloat(amtInput.dataset.originalTarget);
            amtInput.value = original.toFixed(2);
            document.getElementById('actualBoundary').value = original.toFixed(2);
            document.getElementById('breakdownComputationDraft').classList.add('hidden');
        } else {
            updateBreakdownComputation();
        }
    }

    if (needsMaintenanceHalfCheck) {
        needsMaintenanceHalfCheck.addEventListener('change', function() { applyMaintenanceLogic(this); });
    }
    if (needsMaintenanceZeroCheck) {
        needsMaintenanceZeroCheck.addEventListener('change', function() { applyMaintenanceLogic(this); });
    }
});
</script>
@endpush