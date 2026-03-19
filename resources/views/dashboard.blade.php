@extends('layouts.app')

@section('title', 'Dashboard - Euro System')
@section('page-heading', 'Dashboard Overview')
@section('page-subheading', 'Real-time operational metrics and alerts')

@section('content')
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white rounded-lg shadow card-hover cursor-pointer hover:shadow-lg transition-shadow" onclick="showUnitsModal()">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Units</p>
                        <p class="text-2xl font-bold text-gray-900" data-stat="active_units">{{ $stats['active_units'] }}</p>
                        <p class="text-xs text-gray-500">
                            <span class="text-green-600">{{ $stats['roi_units'] }} ROI Achieved</span> •
                            {{ $stats['coding_units'] }} Coding
                        </p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i data-lucide="car" class="w-6 h-6 text-yellow-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Daily Boundary Collection</p>
                        <p class="text-2xl font-bold text-gray-900" data-stat="today_boundary">{{ formatCurrency($stats['today_boundary']) }}</p>
                        <p class="text-xs text-gray-500">+8.5% from yesterday</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <i data-lucide="dollar-sign" class="w-6 h-6 text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Net Income Today</p>
                        <p class="text-2xl font-bold text-green-600" data-stat="net_income">{{ formatCurrency($stats['net_income']) }}</p>
                        <p class="text-xs text-gray-500">After all expenses</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i data-lucide="trending-up" class="w-6 h-6 text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Units Under Maintenance</p>
                        <p class="text-2xl font-bold text-gray-900" data-stat="maintenance_units">{{ $stats['maintenance_units'] }}</p>
                        <p class="text-xs text-gray-500">2 preventive, 3 breakdown</p>
                    </div>
                    <div class="p-3 bg-orange-100 rounded-full">
                        <i data-lucide="wrench" class="w-6 h-6 text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Active Drivers</p>
                        <p class="text-2xl font-bold text-gray-900" data-stat="active_drivers">{{ $stats['active_drivers'] }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i data-lucide="users" class="w-8 h-8 text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Avg. Daily Boundary/Unit</p>
                        <p class="text-2xl font-bold text-gray-900" data-stat="avg_boundary">{{ formatCurrency($stats['avg_boundary']) }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <i data-lucide="dollar-sign" class="w-8 h-8 text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Coding Units</p>
                        <p class="text-2xl font-bold text-gray-900" data-stat="coding_units">{{ $stats['coding_units'] }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i data-lucide="code" class="w-8 h-8 text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Section -->
    <div class="mt-6 bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">System Alerts &amp; Notifications</h3>
        </div>
        <div class="p-6" data-alerts-container>
            @if($alerts->isEmpty())
                <p class="text-gray-500 text-center py-4">No active alerts</p>
            @else
                <div class="space-y-3">
                    @foreach($alerts as $alert)
                        <div class="flex items-start gap-3 p-3 rounded-lg border
                                    @if(in_array($alert->severity, ['high', 'critical'])) bg-red-50 border-red-200
                                    @elseif($alert->severity === 'medium') bg-yellow-50 border-yellow-200
                                    @elseif($alert->severity === 'low') bg-blue-50 border-blue-200
                                    @else bg-gray-50 border-gray-200
                                    @endif">
                            <div class="mt-0.5">
                                @if(in_array($alert->severity, ['high', 'critical']))
                                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                                @elseif($alert->severity === 'medium')
                                    <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-600"></i>
                                @else
                                    <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900">{{ $alert->message }}</p>
                                <span class="text-xs text-gray-500 capitalize">{{ $alert->alert_type }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Main Analytics Grid -->
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Revenue Trend</h3>
                    <div class="flex gap-2">
                        <button onclick="updateRevenueTrend('7')" id="btn-7days" class="px-3 py-1 text-sm rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            Last 7 Days
                        </button>
                        <button onclick="updateRevenueTrend('30')" id="btn-30days" class="px-3 py-1 text-sm rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors">
                            Last 30 Days
                        </button>
                        <button onclick="updateRevenueTrend('90')" id="btn-90days" class="px-3 py-1 text-sm rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors">
                            Last 3 Months
                        </button>
                        <button onclick="updateRevenueTrend('365')" id="btn-365days" class="px-3 py-1 text-sm rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors">
                            Last Year
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <canvas id="revenueTrendChart" width="400" height="250"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Unit Performance</h3>
            </div>
            <div class="p-6">
                <canvas id="unitPerformanceChart" width="400" height="250"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Expense Breakdown</h3>
            </div>
            <div class="p-6">
                <canvas id="expenseBreakdownChart" width="400" height="250"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Weekly Financial Overview</h3>
            </div>
            <div class="p-6">
                <canvas id="weeklyChart" width="400" height="250"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Unit Status Distribution</h3>
            </div>
            <div class="p-6">
                <canvas id="unitStatusChart" width="400" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Units Overview Modal -->
    <div id="unitsModal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl max-w-7xl w-full mx-4 h-[95vh] flex flex-col border border-gray-100">
            <!-- Compact Header with Search -->
            <div class="p-4 border-b bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 flex-shrink-0">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg border border-white/30">
                            <i data-lucide="car" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Units Overview</h3>
                            <p class="text-blue-100 text-xs font-medium">Complete fleet management dashboard</p>
                        </div>
                    </div>
                    <button onclick="hideUnitsModal()" class="text-white/80 hover:text-white hover:bg-white/20 p-2 rounded-lg transition-all duration-200 backdrop-blur-sm">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                
                <!-- Compact Search Bar -->
                <div class="relative mb-3">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-white/60"></i>
                    </div>
                    <input 
                        type="text" 
                        id="unitSearchInput"
                        placeholder="Search units by number, status, or performance..."
                        class="w-full pl-10 pr-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                        onkeyup="filterUnits()"
                    >
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button onclick="clearSearch()" class="text-white/60 hover:text-white transition-colors">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Compact Filter Tags -->
                <div class="flex items-center gap-2" id="filterTags">
                    <button onclick="filterByStatus('all')" class="px-2 py-1 bg-white/20 backdrop-blur-sm border border-white/30 rounded-full text-white text-xs font-medium hover:bg-white/30 transition-colors filter-tag active" data-status="all">
                        All Units
                    </button>
                    <button onclick="filterByStatus('active')" class="px-2 py-1 bg-white/20 backdrop-blur-sm border border-white/30 rounded-full text-white text-xs font-medium hover:bg-white/30 transition-colors filter-tag" data-status="active">
                        Active
                    </button>
                    <button onclick="filterByStatus('maintenance')" class="px-2 py-1 bg-white/20 backdrop-blur-sm border border-white/30 rounded-full text-white text-xs font-medium hover:bg-white/30 transition-colors filter-tag" data-status="maintenance">
                        Maintenance
                    </button>
                    <button onclick="filterByStatus('coding')" class="px-2 py-1 bg-white/20 backdrop-blur-sm border border-white/30 rounded-full text-white text-xs font-medium hover:bg-white/30 transition-colors filter-tag" data-status="coding">
                        Coding
                    </button>
                    <button onclick="filterByStatus('retired')" class="px-2 py-1 bg-white/20 backdrop-blur-sm border border-white/30 rounded-full text-white text-xs font-medium hover:bg-white/30 transition-colors filter-tag" data-status="retired">
                        Retired
                    </button>
                </div>
            </div>
            
            <div class="flex-1 overflow-hidden flex flex-col min-h-0">
                <!-- Compact Summary Stats -->
                <div class="bg-gradient-to-r from-gray-50 to-blue-50 p-4 border-b border-gray-200 flex-shrink-0">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 bg-blue-100 rounded">
                                    <i data-lucide="car" class="w-4 h-4 text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-blue-600" id="totalUnitsCount">0</div>
                                    <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Total Units</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 bg-green-100 rounded">
                                    <i data-lucide="activity" class="w-4 h-4 text-green-600"></i>
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-green-600" id="activeUnitsCount">0</div>
                                    <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Active</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 bg-yellow-100 rounded">
                                    <i data-lucide="trending-up" class="w-4 h-4 text-yellow-600"></i>
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-yellow-600" id="roiUnitsCount">0</div>
                                    <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">ROI Achieved</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 bg-purple-100 rounded">
                                    <i data-lucide="pie-chart" class="w-4 h-4 text-purple-600"></i>
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-purple-600" id="avgRoiCount">0%</div>
                                    <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Avg ROI</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Units Grid with Maximum Space -->
                <div class="flex-1 overflow-y-auto p-4 bg-gray-50 min-h-0">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 pb-4" id="unitsGrid">
                        <!-- Enhanced Loading State -->
                        <div class="col-span-full text-center py-16">
                            <div class="inline-flex flex-col items-center">
                                <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent mb-4"></div>
                                <span class="text-lg text-gray-600 font-semibold mb-2">Loading units data...</span>
                                <p class="text-sm text-gray-400">Please wait while we fetch your fleet information</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/realtime-dashboard.js') }}"></script>
    <script>
        // Weekly Financial Chart
        const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
        const weeklyData = @json($weekly_data);
        window.weeklyChart = new Chart(weeklyCtx, {
            type: 'line',
            data: {
                labels: weeklyData.map(d => d.day),
                datasets: [
                    { label: 'Boundary', data: weeklyData.map(d => d.boundary), borderColor: '#eab308', backgroundColor: 'rgba(234,179,8,0.1)', borderWidth: 2, tension: 0.4 },
                    { label: 'Expenses', data: weeklyData.map(d => d.expenses), borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.1)', borderWidth: 2, tension: 0.4 },
                    { label: 'Net Income', data: weeklyData.map(d => d.net), borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.1)', borderWidth: 2, tension: 0.4 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: function (value) { return '₱' + value.toLocaleString(); } } }
                }
            }
        });

        // Unit Status Chart
        const unitStatusCtx = document.getElementById('unitStatusChart').getContext('2d');
        const unitStatusData = @json($unit_status_data);
        window.unitStatusChart = new Chart(unitStatusCtx, {
            type: 'bar',
            data: {
                labels: unitStatusData.map(d => d.status),
                datasets: [{ label: 'Units', data: unitStatusData.map(d => d.count), backgroundColor: '#eab308', borderColor: '#ca8a04', borderWidth: 1 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        // Revenue Trend Chart
        const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
        const revenueTrendData = @json($revenue_trend);
        window.revenueTrendChart = new Chart(revenueTrendCtx, {
            type: 'line',
            data: {
                labels: revenueTrendData.map(d => d.date),
                datasets: [{
                    label: 'Daily Revenue',
                    data: revenueTrendData.map(d => d.revenue),
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34,197,94,0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        ticks: { callback: function (value) { return '₱' + value.toLocaleString(); } }
                    }
                }
            }
        });

        // Unit Performance Chart
        const unitPerformanceCtx = document.getElementById('unitPerformanceChart').getContext('2d');
        const unitPerformanceData = @json($unit_performance);
        window.unitPerformanceChart = new Chart(unitPerformanceCtx, {
            type: 'bar',
            data: {
                labels: unitPerformanceData.map(d => d.unit),
                datasets: [
                    {
                        label: 'Actual Performance',
                        data: unitPerformanceData.map(d => d.performance),
                        backgroundColor: '#3b82f6',
                        borderColor: '#2563eb',
                        borderWidth: 1
                    },
                    {
                        label: 'Target',
                        data: unitPerformanceData.map(d => d.target),
                        backgroundColor: '#ef4444',
                        borderColor: '#dc2626',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { 
                        beginAtZero: true,
                        ticks: { callback: function (value) { return '₱' + value.toLocaleString(); } }
                    }
                }
            }
        });

        // Expense Breakdown Chart
        const expenseBreakdownCtx = document.getElementById('expenseBreakdownChart').getContext('2d');
        const expenseBreakdownData = @json($expense_breakdown);
        window.expenseBreakdownChart = new Chart(expenseBreakdownCtx, {
            type: 'doughnut',
            data: {
                labels: expenseBreakdownData.map(d => d.category),
                datasets: [{
                    data: expenseBreakdownData.map(d => d.amount),
                    backgroundColor: [
                        '#ef4444',
                        '#f59e0b',
                        '#10b981',
                        '#3b82f6',
                        '#8b5cf6',
                        '#ec4899'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                return label + ': ₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Revenue Trend Period Selection
        function updateRevenueTrend(period) {
            // Update button styles
            document.querySelectorAll('[id^="btn-"]').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white', 'hover:bg-blue-700');
                btn.classList.add('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
            });
            
            // Highlight active button
            const activeBtn = document.getElementById('btn-' + period + 'days');
            if (activeBtn) {
                activeBtn.classList.remove('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
                activeBtn.classList.add('bg-blue-600', 'text-white', 'hover:bg-blue-700');
            }
            
            // Fetch new data
            fetch(`/api/revenue-trend?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && window.revenueTrendChart) {
                        window.revenueTrendChart.data.labels = data.data.map(d => d.date);
                        window.revenueTrendChart.data.datasets[0].data = data.data.map(d => d.revenue);
                        window.revenueTrendChart.update('none');
                    }
                })
                .catch(error => console.error('Error updating revenue trend:', error));
        }

        // Units Modal Functions
        function showUnitsModal() {
            const modal = document.getElementById('unitsModal');
            const grid = document.getElementById('unitsGrid');
            
            if (modal && grid) {
                modal.classList.remove('hidden');
                loadUnitsData();
            }
        }

        function hideUnitsModal() {
            const modal = document.getElementById('unitsModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function loadUnitsData() {
            const grid = document.getElementById('unitsGrid');
            
            // Show loading state
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="animate-spin rounded-full h-16 w-16 border-4 border-blue-600 border-t-transparent mb-6"></div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Loading units data...</span>
                        <p class="text-sm text-gray-400">Please wait while we fetch your fleet information</p>
                    </div>
                </div>
            `;
            
            fetch('/api/units-overview')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Units data received:', data);
                    if (data.success) {
                        displayUnitsData(data);
                    } else {
                        throw new Error(data.message || 'Unknown error occurred');
                    }
                })
                .catch(error => {
                    console.error('Error loading units:', error);
                    grid.innerHTML = `
                        <div class="col-span-full text-center py-20">
                            <div class="inline-flex flex-col items-center">
                                <div class="p-4 bg-red-100 rounded-full mb-4">
                                    <i data-lucide="alert-circle" class="w-8 h-8 text-red-600"></i>
                                </div>
                                <span class="text-xl text-red-600 font-semibold mb-2">Error Loading Units</span>
                                <p class="text-sm text-gray-400 mb-4">${error.message}</p>
                                <button onclick="loadUnitsData()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                                    Try Again
                                </button>
                            </div>
                        </div>
                    `;
                    
                    // Re-initialize Lucide icons for error state
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
        }

        function renderUnits(units, statusColors, statusIcons, statusGradients) {
            const grid = document.getElementById('unitsGrid');
            
            if (units.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-20">
                        <div class="inline-flex flex-col items-center">
                            <div class="p-4 bg-gray-100 rounded-full mb-4">
                                <i data-lucide="search" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <span class="text-xl text-gray-600 font-semibold mb-2">No units found</span>
                            <p class="text-sm text-gray-400">Try adjusting your search or filters</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            grid.innerHTML = units.map(unit => `
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border-t-4 ${statusColors[unit.status] || 'border-gray-200'} hover:scale-102">
                    <!-- Compact Card Header -->
                    <div class="p-3 pb-2">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="p-1.5 bg-gradient-to-r ${statusGradients[unit.status]} rounded-lg">
                                        <i data-lucide="car" class="w-4 h-4 text-white"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-lg font-bold text-gray-900 truncate">${unit.unit_number}</h4>
                                        <span class="px-2 py-0.5 text-xs font-bold rounded-full ${statusColors[unit.status] || 'bg-gray-100'} inline-flex items-center gap-1 mt-1">
                                            ${statusIcons[unit.status] || ''}
                                            <span>${unit.status.charAt(0).toUpperCase() + unit.status.slice(1)}</span>
                                        </span>
                                    </div>
                                </div>
                                ${unit.plate_number ? `
                                    <div class="text-xs text-gray-500 truncate">
                                        <i data-lucide="shield" class="w-3 h-3 inline mr-1"></i>
                                        ${unit.plate_number}
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                        
                        <!-- Compact Financial Metrics -->
                        <div class="bg-gray-50 rounded-lg p-3 mb-2">
                            <div class="grid grid-cols-2 gap-2">
                                <div class="text-center">
                                    <div class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1">Daily</div>
                                    <div class="text-sm font-bold text-gray-900">₱${unit.boundary_rate ? unit.boundary_rate.toLocaleString() : '0'}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1">Total</div>
                                    <div class="text-sm font-bold text-green-600">₱${unit.total_boundary ? unit.total_boundary.toLocaleString() : '0'}</div>
                                </div>
                            </div>
                            ${unit.today_boundary > 0 ? `
                                <div class="text-xs text-center mt-1 text-blue-600 font-medium">
                                    Today: ₱${unit.today_boundary.toLocaleString()}
                                </div>
                            ` : ''}
                        </div>
                        
                        <!-- Compact Purchase Cost & ROI -->
                        ${unit.purchase_cost ? `
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-3 mb-2">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <div class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1">Cost</div>
                                        <div class="text-sm font-bold text-gray-900">₱${unit.purchase_cost.toLocaleString()}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1">ROI</div>
                                        <div class="text-lg font-bold ${unit.roi_percentage >= 100 ? 'text-green-600' : unit.roi_percentage >= 50 ? 'text-yellow-600' : 'text-gray-600'}">${unit.roi_percentage.toFixed(1)}%</div>
                                    </div>
                                </div>
                                <div class="w-full bg-white rounded-full h-2 overflow-hidden shadow-inner">
                                    <div class="h-full bg-gradient-to-r ${statusGradients[unit.status]} rounded-full transition-all duration-700 ease-out shadow-sm" style="width: ${Math.min(100, unit.roi_percentage)}%"></div>
                                </div>
                            </div>
                        ` : ''}
                        
                        <!-- Driver Information -->
                        ${unit.driver_name && unit.driver_name !== 'N/A' ? `
                            <div class="flex items-center gap-2 mb-2 text-xs text-gray-600">
                                <i data-lucide="user" class="w-3 h-3"></i>
                                <span class="truncate">${unit.driver_name}</span>
                            </div>
                        ` : ''}
                        
                        <!-- Compact Performance Status -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full ${unit.roi_percentage >= 100 ? 'bg-green-500' : unit.roi_percentage >= 75 ? 'bg-yellow-500' : unit.roi_percentage >= 50 ? 'bg-orange-500' : 'bg-gray-400'} animate-pulse"></div>
                                <span class="text-xs font-medium text-gray-600">
                                    ${unit.performance_rating ? unit.performance_rating.charAt(0).toUpperCase() + unit.performance_rating.slice(1) : 'Growing'}
                                </span>
                            </div>
                            <div class="text-xs text-gray-400">
                                ${unit.roi_percentage >= 100 ? '✓ ROI Achieved' : 
                                  unit.days_to_roi === 0 ? 'Almost there!' :
                                  unit.days_to_roi === 999 ? 'No recent activity' :
                                  unit.days_to_roi <= 30 ? `${unit.days_to_roi} days` :
                                  unit.days_to_roi <= 60 ? `${unit.days_to_roi} days` :
                                  unit.days_to_roi <= 90 ? `${unit.days_to_roi} days` :
                                  unit.days_to_roi <= 180 ? `${unit.days_to_roi} days` :
                                  `${unit.days_to_roi}+ days`}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Compact Card Footer -->
                    <div class="px-3 py-2 bg-gray-50 border-t border-gray-100">
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                ${unit.last_activity ? new Date(unit.last_activity).toLocaleDateString() : 'No activity'}
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="trending-up" class="w-3 h-3"></i>
                                ${unit.roi_percentage >= 100 ? 'Profit' : 'Invest'}
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        function displayUnitsData(data) {
            const grid = document.getElementById('unitsGrid');
            const units = data.units || [];
            const stats = data.stats || {};
            
            // Store original units data for filtering
            window.originalUnitsData = units;
            window.currentFilteredUnits = units;
            
            // Update summary stats with real data
            document.getElementById('totalUnitsCount').textContent = stats.total_units || 0;
            document.getElementById('activeUnitsCount').textContent = stats.active_units || 0;
            document.getElementById('roiUnitsCount').textContent = stats.roi_units || 0;
            document.getElementById('avgRoiCount').textContent = stats.avg_roi ? stats.avg_roi.toFixed(1) + '%' : '0%';
            
            // Remove any existing data source indicator
            const existingIndicator = grid.parentNode.querySelector('.data-source-indicator');
            if (existingIndicator) {
                existingIndicator.remove();
            }
            
            // Add single data source indicator
            const dataSourceIndicator = document.createElement('div');
            dataSourceIndicator.className = 'data-source-indicator text-xs text-gray-400 text-center mb-2';
            dataSourceIndicator.innerHTML = `
                <i data-lucide="database" class="w-3 h-3 inline mr-1"></i>
                Real Database Data • Last Updated: ${data.last_updated || 'Unknown'}
            `;
            
            // Insert data source indicator before grid
            const gridParent = grid.parentNode;
            gridParent.insertBefore(dataSourceIndicator, grid);
            
            const statusColors = {
                'active': 'bg-green-100 text-green-800 border-green-200',
                'maintenance': 'bg-red-100 text-red-800 border-red-200',
                'coding': 'bg-yellow-100 text-yellow-800 border-yellow-200',
                'retired': 'bg-gray-100 text-gray-800 border-gray-200'
            };
            
            const statusIcons = {
                'active': '<i data-lucide="check-circle" class="w-3 h-3"></i>',
                'maintenance': '<i data-lucide="wrench" class="w-3 h-3"></i>',
                'coding': '<i data-lucide="code" class="w-3 h-3"></i>',
                'retired': '<i data-lucide="x-circle" class="w-3 h-3"></i>'
            };
            
            const statusGradients = {
                'active': 'from-green-500 to-emerald-600',
                'maintenance': 'from-red-500 to-rose-600', 
                'coding': 'from-yellow-500 to-amber-600',
                'retired': 'from-gray-500 to-slate-600'
            };
            
            renderUnits(units, statusColors, statusIcons, statusGradients);
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        // Search and Filter Functions
        function filterUnits() {
            const searchTerm = document.getElementById('unitSearchInput').value.toLowerCase();
            const activeFilter = document.querySelector('.filter-tag.active').dataset.status;
            
            let filteredUnits = window.originalUnitsData || [];
            
            // Apply status filter
            if (activeFilter !== 'all') {
                filteredUnits = filteredUnits.filter(unit => unit.status === activeFilter);
            }
            
            // Apply search filter
            if (searchTerm) {
                filteredUnits = filteredUnits.filter(unit => {
                    const searchableText = [
                        unit.unit_number || '',
                        unit.status || '',
                        unit.roi_percentage >= 100 ? 'excellent profitable' : 
                        unit.roi_percentage >= 75 ? 'good' : 
                        unit.roi_percentage >= 50 ? 'average growing' : 'growing investment',
                        unit.boundary_rate ? unit.boundary_rate.toString() : '',
                        unit.total_boundary ? unit.total_boundary.toString() : '',
                        unit.purchase_cost ? unit.purchase_cost.toString() : ''
                    ].join(' ').toLowerCase();
                    
                    return searchableText.includes(searchTerm);
                });
            }
            
            window.currentFilteredUnits = filteredUnits;
            
            // Re-render with current filters
            const statusColors = {
                'active': 'bg-green-100 text-green-800 border-green-200',
                'maintenance': 'bg-red-100 text-red-800 border-red-200',
                'coding': 'bg-yellow-100 text-yellow-800 border-yellow-200',
                'retired': 'bg-gray-100 text-gray-800 border-gray-200'
            };
            
            const statusIcons = {
                'active': '<i data-lucide="check-circle" class="w-3 h-3"></i>',
                'maintenance': '<i data-lucide="wrench" class="w-3 h-3"></i>',
                'coding': '<i data-lucide="code" class="w-3 h-3"></i>',
                'retired': '<i data-lucide="x-circle" class="w-3 h-3"></i>'
            };
            
            const statusGradients = {
                'active': 'from-green-500 to-emerald-600',
                'maintenance': 'from-red-500 to-rose-600', 
                'coding': 'from-yellow-500 to-amber-600',
                'retired': 'from-gray-500 to-slate-600'
            };
            
            renderUnits(filteredUnits, statusColors, statusIcons, statusGradients);
        }
        
        function filterByStatus(status) {
            // Update active filter tag
            document.querySelectorAll('.filter-tag').forEach(tag => {
                tag.classList.remove('active', 'bg-white/40');
                if (tag.dataset.status === status) {
                    tag.classList.add('active', 'bg-white/40');
                }
            });
            
            // Apply filter
            filterUnits();
        }
        
        function clearSearch() {
            document.getElementById('unitSearchInput').value = '';
            filterUnits();
        }
    </script>
@endpush
