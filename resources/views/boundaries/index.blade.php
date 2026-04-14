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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @if (empty($boundariesArray))
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i data-lucide="dollar-sign" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                            <p>No boundary records found</p>
                        </td>
                    </tr>
                @else
                    @foreach ($boundariesArray as $boundary)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDate($boundary['date']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $boundary['plate_number'] }}</div>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatCurrency($boundary['actual_boundary'] ?? 0) }}
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
                                            <span class="px-1.5 py-0.5 bg-green-100 text-green-700 text-[9px] font-bold rounded border border-green-200 uppercase tracking-tight" title="Recorded within 24 hours of last shift">Incentive Earned</span>
                                        @else
                                            <span class="px-1.5 py-0.5 bg-red-100 text-red-700 text-[9px] font-bold rounded border border-red-200 uppercase tracking-tight" title="Recorded after 24 hours limit - Late Turn">Late Turn / No Incentive</span>
                                        @endif
                                    </div>
                                @endif
                                @if ($boundary['shortage'] > 0)
                                    <div class="text-xs text-red-600 mt-1">Shortage: {{ formatCurrency($boundary['shortage']) }}</div>
                                @elseif ($boundary['excess'] > 0)
                                    <div class="text-xs text-blue-600 mt-1">Excess: {{ formatCurrency($boundary['excess']) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button
                                    type="button"
                                    onclick="editBoundary({{ $boundary['id'] }})"
                                    class="text-yellow-600 hover:text-yellow-900 mr-3"
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
                                     data-deadline="{{ $unit['shift_deadline_at'] }}">
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
                                         data-shortage="{{ $driver['net_shortage'] ?? 0 }}">
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Boundary Amount *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-xs">₱</span>
                        </div>
                        <input type="number" name="boundary_amount" id="boundaryAmount" required step="0.01" min="0" 
                               readonly tabindex="-1"
                               class="w-full pl-7 px-2 py-1.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed focus:ring-0 focus:border-gray-300"
                               title="This target is inherited from Unit Management (adjusted for date)">
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
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" id="notes" rows="2" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"></textarea>
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

@endsection

@push('scripts')
<script>
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
        // Calculate adjusted boundary based on date and coding day
        const adjustedBoundary = calculateAdjustedBoundary(rate, codingDay, dateInput.value);
        document.getElementById('boundaryAmount').value = adjustedBoundary;
        document.getElementById('actualBoundary').value = adjustedBoundary;
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
        // Recalculate adjusted boundary based on new date
        const adjustedBoundary = calculateAdjustedBoundary(rate, codingDay, this.value);
        document.getElementById('boundaryAmount').value = adjustedBoundary;
        document.getElementById('actualBoundary').value = adjustedBoundary;
    }
});

function calculateAdjustedBoundary(baseRate, codingDay, dateStr) {
    if (!dateStr || !baseRate) return baseRate;
    
    const date = new Date(dateStr);
    const dayOfWeek = date.getDay(); // 0 = Sunday, 6 = Saturday
    const dayName = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'][dayOfWeek];
    
    let adjustedRate = baseRate;
    
    // Check if it's coding day
    if (codingDay && codingDay.toLowerCase() === dayName) {
        adjustedRate = baseRate * 0.5; // 50% on coding day
    }
    // Check for Saturday adjustments
    else if (dayOfWeek === 6) { // Saturday
        adjustedRate = baseRate - 100;
    }
    // Check for Sunday adjustments
    else if (dayOfWeek === 0) { // Sunday
        adjustedRate = baseRate - 200;
    }
    
    // Ensure boundary doesn't go below zero
    return Math.max(0, adjustedRate);
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

                // Auto-calculate suggested rate
                const suggestedRate = calculateAutomatedRate(year, plate, customRate);
                const boundaryInput = document.getElementById('boundaryAmount');
                if (boundaryInput) {
                    boundaryInput.value = suggestedRate;
                    document.getElementById('actualBoundary').value = suggestedRate;
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
                if (driverId && driverId !== 'all' && primaryId && driverId !== primaryId && driverId !== secondaryId) {
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
    if (extraAlert) extraAlert.classList.add('hidden');
    if (shortageAlert) shortageAlert.classList.add('hidden');

    document.getElementById('date').value = new Date().toLocaleDateString('en-CA');
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
        document.getElementById('notes').value = boundary.notes || '';
        
        // Find unit data to set primary/secondary driver IDs for suggestion
        const unitOption = document.querySelector(`.unit-option[data-id="${boundary.unit_id}"]`);
        const unitDisplay = document.getElementById('unitDisplay');
        if (unitOption && unitDisplay) {
            const unitName = unitOption.getAttribute('data-name');
            const unitPlate = unitOption.getAttribute('data-plate');
            unitDisplay.value = `${unitPlate}`;
            
            const pId = unitOption.getAttribute('data-primary-driver');
            const sId = unitOption.getAttribute('data-secondary-driver');
            unitDisplay.setAttribute('data-primary-id', pId || '');
            unitDisplay.setAttribute('data-secondary-id', sId || '');
        }

        // Set driver display name
        const driverOption = document.querySelector(`.driver-option[data-id="${boundary.driver_id}"]`);
        if (driverOption) {
            const name = driverOption.getAttribute('data-name');
            document.getElementById('driverDisplay').value = name;
        }
        
        // Keep boundary amount read-only as per source-of-truth requirement
        document.getElementById('boundaryAmount').readOnly = true;
        
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

function calculateAutomatedRate(year, plate, customRate) {
    const rules = window.boundaryRules;
    const yr = parseInt(year) || 0;
    const rate = parseFloat(customRate) || 0;

    // Find rule for the year
    const rule = rules.find(r => yr >= r.start_year && yr <= r.end_year);
    
    // Base rate
    const base = rate > 0 ? rate : (rule ? parseFloat(rule.regular_rate) : 1100);
    
    // Detect coding day
    const codingDay = deriveCodingDay(plate);
    const today = new Date().toLocaleDateString('en-US', { weekday: 'long' });
    
    if (codingDay && today === codingDay) {
        if (rule && rule.coding_rate > 0) return parseFloat(rule.coding_rate);
        return base / 2;
    }
    
    if (today === 'Saturday') {
        const disc = rule ? parseFloat(rule.sat_discount) : 100;
        return base - disc;
    }
    
    if (today === 'Sunday') {
        const disc = rule ? parseFloat(rule.sun_discount) : 200;
        return base - disc;
    }
    
    return base;
}

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
        const now = new Date();
        const diffMs = deadlineDate - now;
        const isPast = diffMs < 0;
        const absDiff = Math.abs(diffMs);
        
        const diffHours = Math.floor(absDiff / (1000 * 60 * 60));
        const diffMins = Math.floor((absDiff % (1000 * 60 * 60)) / (1000 * 60));
        
        if (isPast) {
            shiftTimerLabel.innerHTML = `<span class="text-red-600 font-black">OVERDUE BY ${diffHours}h ${diffMins}m</span>`;
            shiftInfoGroup.classList.add('border-red-200', 'bg-red-50');
            shiftInfoGroup.classList.remove('border-green-200', 'bg-green-50');
            badgeContainer.innerHTML = '<span class="px-1.5 py-0.5 bg-red-100 text-red-700 text-[9px] font-bold rounded-full border border-red-300 uppercase tracking-tighter shadow-sm animate-pulse">NO INCENTIVE</span>';
        } else {
            shiftTimerLabel.innerHTML = `<span class="text-green-600 font-bold">${diffHours}h ${diffMins}m remaining</span> until deadline`;
            shiftInfoGroup.classList.add('border-green-200', 'bg-green-50');
            shiftInfoGroup.classList.remove('border-red-200', 'bg-red-50');
            badgeContainer.innerHTML = '<span class="px-1.5 py-0.5 bg-green-100 text-green-700 text-[9px] font-bold rounded-full border border-green-300 uppercase tracking-tighter shadow-sm">INCENTIVE ELIGIBLE</span>';
        }
    } else {
        shiftTimerLabel.textContent = 'Shift schedule not yet initialized';
        badgeContainer.innerHTML = '<span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 text-[9px] font-bold rounded-full border border-blue-300 uppercase tracking-tighter">NEW PATTERN</span>';
    }

    shiftInfoGroup.classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
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
    
    // Sync actual boundary with boundary amount when changed
    const amtInput = document.getElementById('boundaryAmount');
    if (amtInput) {
        amtInput.addEventListener('input', function() {
            const val = this.value || '0.00';
            const actualInput = document.getElementById('actualBoundary');
            if (actualInput) actualInput.value = val;
        });
    }
});
</script>
@endpush