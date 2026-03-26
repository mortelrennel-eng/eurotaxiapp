<div class="space-y-4">
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4 rounded-lg text-white">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="text-xl font-bold">{{ $unit->unit_number }}</h3>
                <p class="text-blue-100 text-sm">{{ ($unit->make ?? '') . ' ' . ($unit->model ?? '') . ' (' . ($unit->year ?? '') . ')' }}</p>
                <p class="text-blue-100 text-sm">Plate: {{ $unit->plate_number }}</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="px-2 py-1 bg-white bg-opacity-20 rounded-full text-xs font-medium">
                        {{ ucfirst($unit->status ?? '') }}
                    </span>
                    <span class="px-2 py-1 bg-white bg-opacity-20 rounded-full text-xs font-medium">
                        {{ ucfirst($unit->unit_type ?? 'Standard') }}
                    </span>
                </div>
            </div>
            <div class="text-right">
                <div class="text-xl font-bold">₱{{ number_format((float) ($unit->boundary_rate ?? 0), 2) }}</div>
                <p class="text-blue-100 text-xs">Daily Boundary Rate</p>
            </div>
        </div>
    </div>

    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <button onclick="showTab('overview')" class="tab-btn py-2 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600" data-tab="overview">
                Overview
            </button>
            <button onclick="showTab('drivers')" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="drivers">
                Drivers
            </button>
            <button onclick="showTab('coding')" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="coding">
                Coding
            </button>
            <button onclick="showTab('boundary')" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="boundary">
                Boundary
            </button>
            <button onclick="showTab('maintenance')" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="maintenance">
                Maintenance
            </button>
            <button onclick="showTab('roi')" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="roi">
                ROI
            </button>
            <button onclick="showTab('location')" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="location">
                Location
            </button>
            <button onclick="showTab('dashcam')" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="dashcam">
                Dashcam
            </button>
        </nav>
    </div>

    <div id="tabContent">
        <div id="overview-tab" class="tab-content">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                <div class="bg-white border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <i data-lucide="users" class="w-4 h-4 text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Drivers</p>
                            <p class="text-lg font-bold">{{ count($assigned_drivers) }}/2</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <i data-lucide="map-pin" class="w-4 h-4 text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="text-lg font-bold">{{ ucfirst($unit->status ?? '') }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <i data-lucide="droplet" class="w-4 h-4 text-yellow-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Fuel</p>
                            <p class="text-lg font-bold">{{ ucfirst($unit->fuel_status ?? 'Full') }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <i data-lucide="calendar" class="w-4 h-4 text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Next Coding</p>
                            <p class="text-lg font-bold">{{ $next_coding_date ?? 'Not Set' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="info" class="w-5 h-5"></i>
                        Basic Information
                    </h4>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Unit Number:</span>
                            <span class="font-medium">{{ $unit->unit_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Plate Number:</span>
                            <span class="font-medium">{{ $unit->plate_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Vehicle:</span>
                            <span class="font-medium">{{ ($unit->make ?? '') . ' ' . ($unit->model ?? '') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Year:</span>
                            <span class="font-medium">{{ $unit->year }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                {{ ucfirst($unit->status ?? '') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Boundary Rate:</span>
                            <span class="font-medium">₱{{ number_format((float) ($unit->boundary_rate ?? 0), 2) }}</span>
                        </div>
                        <div class="pt-3 border-t border-gray-100 mt-3">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="text-[10px] text-gray-500 uppercase font-semibold block">Input by</span>
                                    <span class="text-xs font-medium text-gray-900">{{ $unit->created_by_name ?? 'System' }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-gray-500 uppercase font-semibold block">Last Edit</span>
                                    <span class="text-xs font-medium text-gray-900">{{ $unit->updated_by_name ?? 'System' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="users" class="w-5 h-5"></i>
                        Driver Assignment
                    </h4>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Assigned Drivers:</span>
                            <span class="font-medium">{{ count($assigned_drivers) }}/2</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Availability:</span>
                            <span class="px-2 py-1 text-xs rounded-full {{ count($assigned_drivers) >= 2 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ count($assigned_drivers) >= 2 ? 'Full' : 'Available' }}
                            </span>
                        </div>
                        @if(!empty($assigned_drivers))
                            <div class="mt-4 space-y-2">
                                @foreach($assigned_drivers as $driver)
                                    <div class="bg-gray-50 p-3 rounded">
                                        <div class="font-medium">{{ $driver->full_name }}</div>
                                        <div class="text-sm text-gray-600">{{ $driver->license_number }}</div>
                                        <div class="text-sm text-gray-600">Contact: {{ $driver->contact_number ?? 'N/A' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div id="drivers-tab" class="tab-content hidden">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Assigned Drivers</h4>
                @if(!empty($assigned_drivers))
                    <div class="space-y-4">
                        @foreach($assigned_drivers as $driver)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h5 class="font-semibold text-gray-900">{{ $driver->full_name }}</h5>
                                        <p class="text-sm text-gray-600">License: {{ $driver->license_number }}</p>
                                        <p class="text-sm text-gray-600">Contact: {{ $driver->contact_number ?? 'N/A' }}</p>
                                        <p class="text-sm text-gray-600">Email: {{ $driver->email ?? 'N/A' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                                    </div>
                                </div>
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-600">License Number:</span>
                                        <p class="font-medium">{{ $driver->license_number ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Contact:</span>
                                        <p class="font-medium">{{ $driver->contact_number ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Email:</span>
                                        <p class="font-medium">{{ $driver->email ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Daily Target:</span>
                                        <p class="font-medium">₱{{ number_format((float) ($driver->daily_boundary_target ?? 1100), 2) }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Hire Date:</span>
                                        <p class="font-medium">{{ !empty($driver->hire_date) ? \Carbon\Carbon::parse($driver->hire_date)->format('M d, Y') : 'Not set' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">License Expiry:</span>
                                        <p class="font-medium">{{ !empty($driver->license_expiry) ? \Carbon\Carbon::parse($driver->license_expiry)->format('M d, Y') : 'Not set' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i data-lucide="users" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                        <p>No drivers assigned to this unit</p>
                    </div>
                @endif
            </div>
        </div>

        <div id="coding-tab" class="tab-content hidden">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">MMDA Coding Schedule</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h5 class="font-medium text-gray-900 mb-3">Current Coding Information</h5>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Coding Day:</span>
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                    {{ $coding_day }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Last Digit:</span>
                                <span class="font-medium">{{ substr($unit->plate_number ?? '', -1) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Next Coding:</span>
                                <span class="font-medium">{{ $next_coding_date }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Days Until Coding:</span>
                                <span class="font-medium {{ ($days_until_coding ?? 0) === 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ ($days_until_coding ?? 0) === 0 ? 'Today' : ($days_until_coding . ' days') }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Coding Time:</span>
                                <span class="font-medium">7:00 AM - 10:00 AM</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Coding Status:</span>
                                <span class="px-2 py-1 text-xs rounded-full {{ ($days_until_coding ?? 0) === 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ($days_until_coding ?? 0) === 0 ? 'Coding Today' : 'No Coding' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h5 class="font-medium text-gray-900 mb-3">MMDA Coding Schedule</h5>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between p-2 bg-blue-50 rounded">
                                <span>Monday</span>
                                <span class="font-medium">1, 2</span>
                            </div>
                            <div class="flex justify-between p-2 bg-green-50 rounded">
                                <span>Tuesday</span>
                                <span class="font-medium">3, 4</span>
                            </div>
                            <div class="flex justify-between p-2 bg-yellow-50 rounded">
                                <span>Wednesday</span>
                                <span class="font-medium">5, 6</span>
                            </div>
                            <div class="flex justify-between p-2 bg-orange-50 rounded">
                                <span>Thursday</span>
                                <span class="font-medium">7, 8</span>
                            </div>
                            <div class="flex justify-between p-2 bg-red-50 rounded">
                                <span>Friday</span>
                                <span class="font-medium">9, 0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="boundary-tab" class="tab-content hidden">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Boundary Collection History</h4>
                @if(!empty($boundary_history))
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Driver</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($boundary_history as $bh)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ !empty($bh->date) ? \Carbon\Carbon::parse($bh->date)->format('M d, Y') : '' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $bh->full_name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                            ₱{{ number_format((float) ($bh->boundary_amount ?? 0), 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ ucfirst($bh->status ?? '') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i data-lucide="dollar-sign" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                        <p>No boundary collection history found</p>
                    </div>
                @endif
            </div>
        </div>

        <div id="maintenance-tab" class="tab-content hidden">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Maintenance Records</h4>
                @if(!empty($maintenance_records))
                    <div class="space-y-4">
                        @foreach($maintenance_records as $maintenance)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h5 class="font-semibold text-gray-900">{{ $maintenance->maintenance_type ?? 'Maintenance' }}</h5>
                                        <p class="text-sm text-gray-600">{{ !empty($maintenance->date_started) ? \Carbon\Carbon::parse($maintenance->date_started)->format('M d, Y') : '' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-lg font-bold text-orange-600">₱{{ number_format((float) ($maintenance->cost ?? 0), 2) }}</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div class="md:col-span-2">
                                        <span class="text-gray-600">Description:</span>
                                        <p class="font-medium">{{ $maintenance->description ?? 'No description' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i data-lucide="wrench" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                        <p>No maintenance records found</p>
                    </div>
                @endif
            </div>
        </div>

        <div id="roi-tab" class="tab-content hidden">
            <div class="space-y-6">
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 rounded-lg text-white">
                    <h4 class="text-xl font-bold mb-4">ROI Analysis</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-purple-100">Total Investment</p>
                            <p class="text-2xl font-bold">₱{{ number_format((float) ($roi_data['total_investment'] ?? 0), 2) }}</p>
                        </div>
                        <div>
                            <p class="text-purple-100">Total Revenue</p>
                            <p class="text-2xl font-bold">₱{{ number_format((float) ($roi_data['total_revenue'] ?? 0), 2) }}</p>
                        </div>
                        <div>
                            <p class="text-purple-100">Total Expenses</p>
                            <p class="text-2xl font-bold">₱{{ number_format((float) ($roi_data['total_expenses'] ?? 0), 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">ROI Metrics</h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">ROI Percentage</span>
                                <span class="text-lg font-bold {{ ($roi_data['roi_percentage'] ?? 0) > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format((float) ($roi_data['roi_percentage'] ?? 0), 1) }}%
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Payback Period</span>
                                <span class="text-lg font-bold text-blue-600">
                                    {{ number_format((float) ($roi_data['payback_period'] ?? 0), 1) }} months
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Monthly Revenue</span>
                                <span class="text-lg font-bold text-green-600">
                                    ₱{{ number_format((float) ($roi_data['monthly_revenue'] ?? 0), 2) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Monthly Expenses</span>
                                <span class="text-lg font-bold text-red-600">
                                    ₱{{ number_format((float) ($roi_data['monthly_expenses'] ?? 0), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">ROI Progress</h4>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">ROI Achievement</span>
                                    <span class="text-sm font-medium">{{ number_format((float) ($roi_data['roi_percentage'] ?? 0), 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-4">
                                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-4 rounded-full" style="width: {{ min(100, max(0, (float) ($roi_data['roi_percentage'] ?? 0))) }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">Base Boundary to Achieve ROI</span>
                                    <span class="text-sm font-medium">₱{{ number_format(((float) ($roi_data['total_investment'] ?? 0)) / 12, 2) }}/month</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-4">
                                    @php
                                        $investment_per_month = ((float) ($roi_data['total_investment'] ?? 0)) / 12;
                                        $monthly_boundary = (float) ($roi_data['monthly_boundary'] ?? 0);
                                        $progress_percentage = $investment_per_month > 0 ? min(100, ($monthly_boundary / $investment_per_month) * 100) : 0;
                                    @endphp
                                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-4 rounded-full" style="width: {{ $progress_percentage }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="location-tab" class="tab-content hidden">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Location Information</h4>
                <div class="space-y-4">
                    {{-- Info Row --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex justify-between bg-gray-50 rounded-lg px-4 py-3">
                            <span class="text-gray-600 text-sm">Location:</span>
                            <span class="font-medium text-sm">{{ $location_info['current_location'] }}</span>
                        </div>
                        <div class="flex justify-between bg-gray-50 rounded-lg px-4 py-3">
                            <span class="text-gray-600 text-sm">Last Update:</span>
                            <span class="font-medium text-sm">{{ $location_info['last_location_update'] }}</span>
                        </div>
                        <div class="flex justify-between bg-gray-50 rounded-lg px-4 py-3 items-center">
                            <span class="text-gray-600 text-sm">GPS Status:</span>
                            <span class="px-2 py-1 text-xs rounded-full {{ ($location_info['gps_enabled'] || !empty($unit->gps_link)) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ($location_info['gps_enabled'] || !empty($unit->gps_link)) ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                    </div>

                    {{-- Map Viewer --}}
                    <div class="relative rounded-lg overflow-hidden border border-gray-200" style="height: 380px; background: #f3f4f6;">
                        @if(!empty($unit->gps_link))
                            <iframe
                                src="{{ $unit->gps_link }}"
                                style="position:absolute; top:0; left:0; width:142.85%; height:142.85%; border:none; transform:scale(0.7); transform-origin:top left;"
                                allowfullscreen
                            ></iframe>
                        @else
                            <div class="flex flex-col items-center justify-center h-full text-gray-400">
                                <i data-lucide="link-2-off" class="w-12 h-12 mb-3 text-gray-300"></i>
                                <p class="text-base font-medium text-gray-600">No GPS Link</p>
                                <p class="text-sm mt-1 text-gray-400">This unit does not have a TracksolidPro share link connected.</p>
                                <a href="{{ route('units.index') }}" class="mt-4 inline-flex items-center gap-1 text-blue-600 text-sm hover:underline">
                                    <i data-lucide="settings" class="w-4 h-4"></i> Manage Unit Settings
                                </a>
                            </div>
                        @endif
                    </div>

                    @if(!empty($location_info['coordinates']))
                        <div class="flex justify-between bg-gray-50 rounded-lg px-4 py-3">
                            <span class="text-gray-600 text-sm">Coordinates:</span>
                            <span class="font-medium text-sm">{{ $location_info['coordinates'] }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div id="dashcam-tab" class="tab-content hidden">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Dashcam Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h5 class="font-medium text-gray-900 mb-3">Device Status</h5>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Dashcam Status:</span>
                                <span class="px-2 py-1 text-xs rounded-full {{ ($dashcam_info['dashcam_enabled'] ?? false) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ($dashcam_info['dashcam_enabled'] ?? false) ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Connection Status:</span>
                                <span class="px-2 py-1 text-xs rounded-full {{ ($dashcam_info['dashcam_status'] ?? '') === 'Online' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $dashcam_info['dashcam_status'] ?? 'Offline' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Last Recording:</span>
                                <span class="font-medium">{{ $dashcam_info['last_recording'] ?? 'Never' }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h5 class="font-medium text-gray-900 mb-3">Storage Information</h5>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Storage Used:</span>
                                <span class="font-medium">{{ number_format((float) ($dashcam_info['storage_used'] ?? 0), 2) }} GB</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Storage:</span>
                                <span class="font-medium">{{ number_format((float) ($dashcam_info['storage_total'] ?? 0), 2) }} GB</span>
                            </div>
                            <div>
                                @php
                                    $storage_total = (float) ($dashcam_info['storage_total'] ?? 0);
                                    $storage_used = (float) ($dashcam_info['storage_used'] ?? 0);
                                    $storage_pct = $storage_total > 0 ? ($storage_used / $storage_total) * 100 : 0;
                                @endphp
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">Storage Usage</span>
                                    <span class="text-sm font-medium">{{ number_format($storage_pct, 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-4">
                                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-4 rounded-full" style="width: {{ $storage_total > 0 ? min(100, $storage_pct) : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <h5 class="font-medium text-gray-900 mb-3">Recent Recordings</h5>
                    <div class="bg-gray-100 rounded-lg h-32 flex items-center justify-center">
                        <div class="text-center text-gray-500">
                            <i data-lucide="video" class="w-8 h-8 mx-auto mb-2"></i>
                            <p>Video integration coming soon</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-blue-500', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    const activeBtn = document.querySelector('[data-tab="' + tabName + '"]');
    activeBtn.classList.remove('border-transparent', 'text-gray-500');
    activeBtn.classList.add('border-blue-500', 'text-blue-600');
}
</script>
