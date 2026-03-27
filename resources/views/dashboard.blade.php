@extends('layouts.app')

@section('title', 'Euro Taxi System | Professional Fleet Management Dashboard')
@section('page-heading', 'Euro Taxi System')
@section('page-subheading', 'Professional taxi fleet management and real-time tracking solutions')

@section('content')
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white rounded-lg shadow card-hover cursor-pointer hover:shadow-lg transition-shadow" onclick="showUnitsModal()">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600">Total Units</p>
                        <p class="text-xl font-bold text-gray-900" data-stat="active_units">{{ $stats['active_units'] }}</p>
                        <p class="text-xs text-gray-500">
                            <span class="text-green-600">{{ $stats['roi_units'] }} ROI Achieved</span> •
                            {{ $stats['coding_units'] }} Coding
                        </p>
                    </div>
                    <div class="p-2 bg-yellow-100 rounded-full">
                        <i data-lucide="car" class="w-5 h-5 text-yellow-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover cursor-pointer hover:shadow-lg transition-shadow" onclick="showDailyBoundaryModal()">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600">Daily Boundary Collection</p>
                        <p class="text-xl font-bold text-gray-900" data-stat="today_boundary">{{ formatCurrency($stats['today_boundary']) }}</p>
                        <p class="text-xs text-gray-500">+8.5% from yesterday</p>
                    </div>
                    <div class="p-2 bg-green-100 rounded-full">
                        <i data-lucide="dollar-sign" class="w-5 h-5 text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover cursor-pointer hover:shadow-lg transition-shadow" onclick="showNetIncomeModal()">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600">Net Income Today</p>
                        <p class="text-xl font-bold text-green-600" data-stat="net_income">{{ formatCurrency($stats['net_income']) }}</p>
                        <p class="text-xs text-gray-500">After all expenses</p>
                    </div>
                    <div class="p-2 bg-green-100 rounded-full">
                        <i data-lucide="trending-up" class="w-5 h-5 text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover cursor-pointer hover:shadow-lg transition-shadow" onclick="showMaintenanceUnitsModal()">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600">Units Under Maintenance</p>
                        <p class="text-xl font-bold text-gray-900" data-stat="maintenance_units">{{ $stats['maintenance_units'] }}</p>
                        <p class="text-xs text-gray-500">2 preventive, 3 breakdown</p>
                    </div>
                    <div class="p-2 bg-orange-100 rounded-full">
                        <i data-lucide="wrench" class="w-5 h-5 text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow card-hover cursor-pointer hover:shadow-lg transition-shadow" onclick="showActiveDriversModal()">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Active Drivers</p>
                        <p class="text-xl font-bold text-gray-900" data-stat="active_drivers">{{ $stats['active_drivers'] }}</p>
                    </div>
                    <div class="p-2 bg-blue-100 rounded-full">
                        <i data-lucide="users" class="w-5 h-5 text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Avg. Daily Boundary/Unit</p>
                        <p class="text-xl font-bold text-gray-900" data-stat="avg_boundary">{{ formatCurrency($stats['avg_boundary']) }}</p>
                    </div>
                    <div class="p-2 bg-green-100 rounded-full">
                        <i data-lucide="dollar-sign" class="w-6 h-6 text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover cursor-pointer hover:shadow-lg transition-shadow" onclick="showCodingUnitsModal()">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Coding Units</p>
                        <p class="text-xl font-bold text-gray-900" data-stat="coding_units">{{ $stats['coding_units'] }}</p>
                    </div>
                    <div class="p-2 bg-purple-100 rounded-full">
                        <i data-lucide="code" class="w-5 h-5 text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Analytics Grid -->
    <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Revenue Trend</h3>
                    <div class="flex gap-2">
                        <button onclick="updateRevenueTrend('7')" id="btn-7days" class="px-2 py-1 text-xs rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            Last 7 Days
                        </button>
                        <button onclick="updateRevenueTrend('30')" id="btn-30days" class="px-2 py-1 text-xs rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors">
                            Last 30 Days
                        </button>
                        <button onclick="updateRevenueTrend('90')" id="btn-90days" class="px-2 py-1 text-xs rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors">
                            Last 3
                        </button>
                        <button onclick="updateRevenueTrend('365')" id="btn-365days" class="px-2 py-1 text-xs rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors">
                            Year
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-4">
                <canvas id="revenueTrendChart" width="400" height="200"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b">
                <h3 class="text-base font-semibold text-gray-900">Unit Performance</h3>
            </div>
            <div class="p-4">
                <canvas id="unitPerformanceChart" width="400" height="200"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b">
                <h3 class="text-base font-semibold text-gray-900">Expense Breakdown</h3>
            </div>
            <div class="p-4">
                <canvas id="expenseBreakdownChart" width="400" height="200"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b">
                <h3 class="text-base font-semibold text-gray-900">Weekly Financial Overview</h3>
            </div>
            <div class="p-4">
                <canvas id="weeklyChart" width="400" height="200"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b">
                <h3 class="text-base font-semibold text-gray-900">Unit Status Distribution</h3>
            </div>
            <div class="p-4">
                <canvas id="unitStatusChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Maintenance Units Modal -->
<div id="maintenanceUnitsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[95vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="p-4 border-b bg-gradient-to-r from-orange-600 via-amber-600 to-yellow-600 flex-shrink-0">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg border border-white/30">
                        <i data-lucide="wrench" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Units Under Maintenance</h3>
                        <p class="text-orange-100 text-xs font-medium">Complete maintenance tracking details</p>
                    </div>
                </div>
                <button onclick="hideMaintenanceUnitsModal()" class="text-white/80 hover:text-white hover:bg-white/20 p-2 rounded-lg transition-all duration-200 backdrop-blur-sm">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <!-- Search and Date Filter -->
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-white/60"></i>
                    </div>
                    <input 
                        type="text" 
                        id="maintenanceSearchInput"
                        placeholder="Search by unit number, plate, or maintenance type..."
                        class="w-full pl-10 pr-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                        onkeyup="filterMaintenanceUnits()"
                    >
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button onclick="clearMaintenanceSearch()" class="text-white/60 hover:text-white transition-colors">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <input 
                    type="date" 
                    id="maintenanceDateFilter"
                    class="px-3 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                    onchange="filterMaintenanceUnits()"
                >
            </div>
        </div>
        
        <div class="flex-1 overflow-hidden flex flex-col min-h-0">
            <!-- Summary Stats -->
            <div class="bg-gradient-to-r from-orange-50 to-amber-50 p-4 border-b border-orange-200 flex-shrink-0">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-orange-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-orange-100 rounded">
                                <i data-lucide="wrench" class="w-4 h-4 text-orange-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-orange-600" id="maintenanceUnitsCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Maintenance</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-blue-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-blue-100 rounded">
                                <i data-lucide="clock" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-blue-600" id="avgMaintenanceDaysCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Avg Days</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-green-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-green-100 rounded">
                                <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-green-600" id="completedMaintenanceCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Completed</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-purple-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-purple-100 rounded">
                                <i data-lucide="alert-circle" class="w-4 h-4 text-purple-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-purple-600" id="pendingMaintenanceCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Pending</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance Units Grid -->
            <div class="flex-1 overflow-y-auto p-4 bg-gray-50 min-h-0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pb-4" id="maintenanceGrid">
                    <!-- Loading State -->
                    <div class="col-span-full text-center py-16">
                        <div class="inline-flex flex-col items-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-orange-600 border-t-transparent mb-4"></div>
                            <span class="text-lg text-gray-600 font-semibold mb-2">Loading maintenance data...</span>
                            <p class="text-sm text-gray-400">Please wait while we fetch maintenance details</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Drivers Modal -->
<div id="activeDriversModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[95vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="p-4 border-b bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 flex-shrink-0">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg border border-white/30">
                        <i data-lucide="users" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Active Drivers</h3>
                        <p class="text-blue-100 text-xs font-medium">Complete driver management details</p>
                    </div>
                </div>
                <button onclick="hideActiveDriversModal()" class="text-white/80 hover:text-white hover:bg-white/20 p-2 rounded-lg transition-all duration-200 backdrop-blur-sm">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <!-- Search and Date Filter -->
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-white/60"></i>
                    </div>
                    <input 
                        type="text" 
                        id="driversSearchInput"
                        placeholder="Search by name, license, or contact..."
                        class="w-full pl-10 pr-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                        onkeyup="filterActiveDrivers()"
                    >
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button onclick="clearDriversSearch()" class="text-white/60 hover:text-white transition-colors">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <input 
                    type="date" 
                    id="driversDateFilter"
                    class="px-3 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                    onchange="filterActiveDrivers()"
                >
            </div>
        </div>
        
        <div class="flex-1 overflow-hidden flex flex-col min-h-0">
            <!-- Summary Stats -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 border-b border-blue-200 flex-shrink-0">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-blue-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-blue-100 rounded">
                                <i data-lucide="users" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-blue-600" id="activeDriversCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Active</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-green-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-green-100 rounded">
                                <i data-lucide="car" class="w-4 h-4 text-green-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-green-600" id="assignedUnitsCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Assigned</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-orange-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-orange-100 rounded">
                                <i data-lucide="trending-up" class="w-4 h-4 text-orange-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-orange-600" id="avgBoundaryCount">₱0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Avg Daily</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-purple-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-purple-100 rounded">
                                <i data-lucide="star" class="w-4 h-4 text-purple-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-purple-600" id="topPerformersCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Top Performers</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Drivers Grid -->
            <div class="flex-1 overflow-y-auto p-4 bg-gray-50 min-h-0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pb-4" id="activeDriversGrid">
                    <!-- Loading State -->
                    <div class="col-span-full text-center py-16">
                        <div class="inline-flex flex-col items-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent mb-4"></div>
                            <span class="text-lg text-gray-600 font-semibold mb-2">Loading driver data...</span>
                            <p class="text-sm text-gray-400">Please wait while we fetch driver details</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Coding Units Modal -->
<div id="codingUnitsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[95vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="p-4 border-b bg-gradient-to-r from-purple-600 via-pink-600 to-rose-600 flex-shrink-0">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg border border-white/30">
                        <i data-lucide="code" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Coding Units</h3>
                        <p class="text-purple-100 text-xs font-medium">Complete coding unit management details</p>
                    </div>
                </div>
                <button onclick="hideCodingUnitsModal()" class="text-white/80 hover:text-white hover:bg-white/20 p-2 rounded-lg transition-all duration-200 backdrop-blur-sm">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <!-- Search and Date Filter -->
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-white/60"></i>
                    </div>
                    <input 
                        type="text" 
                        id="codingSearchInput"
                        placeholder="Search by unit number, plate, or coding status..."
                        class="w-full pl-10 pr-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                        onkeyup="filterCodingUnits()"
                    >
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button onclick="clearCodingSearch()" class="text-white/60 hover:text-white transition-colors">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <input 
                    type="date" 
                    id="codingDateFilter"
                    class="px-3 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                    onchange="filterCodingUnits()"
                >
            </div>
        </div>
        
        <div class="flex-1 overflow-hidden flex flex-col min-h-0">
            <!-- Summary Stats -->
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-4 border-b border-purple-200 flex-shrink-0">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-purple-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-purple-100 rounded">
                                <i data-lucide="code" class="w-4 h-4 text-purple-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-purple-600" id="codingUnitsCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Coding</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-blue-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-blue-100 rounded">
                                <i data-lucide="calendar" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-blue-600" id="avgCodingDaysCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Avg Days</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-green-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-green-100 rounded">
                                <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-green-600" id="completedCodingCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Completed</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-orange-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-orange-100 rounded">
                                <i data-lucide="alert-circle" class="w-4 h-4 text-orange-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-orange-600" id="pendingCodingCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Pending</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coding Units Grid -->
            <div class="flex-1 overflow-y-auto p-4 bg-gray-50 min-h-0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pb-4" id="codingGrid">
                    <!-- Loading State -->
                    <div class="col-span-full text-center py-16">
                        <div class="inline-flex flex-col items-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-purple-600 border-t-transparent mb-4"></div>
                            <span class="text-lg text-gray-600 font-semibold mb-2">Loading coding data...</span>
                            <p class="text-sm text-gray-400">Please wait while we fetch coding details</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Net Income Modal -->
<div id="netIncomeModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[95vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="p-4 border-b bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 flex-shrink-0">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg border border-white/30">
                        <i data-lucide="trending-up" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Net Income Details</h3>
                        <p class="text-green-100 text-xs font-medium">Complete income and expense breakdown</p>
                    </div>
                </div>
                <button onclick="hideNetIncomeModal()" class="text-white/80 hover:text-white hover:bg-white/20 p-2 rounded-lg transition-all duration-200 backdrop-blur-sm">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <!-- Search and Date Filter -->
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-white/60"></i>
                    </div>
                    <input 
                        type="text" 
                        id="incomeSearchInput"
                        placeholder="Search by description, category, or amount..."
                        class="w-full pl-10 pr-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                        onkeyup="filterIncomeData()"
                    >
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button onclick="clearIncomeSearch()" class="text-white/60 hover:text-white transition-colors">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <input 
                    type="date" 
                    id="incomeDateFilter"
                    class="px-3 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                    onchange="filterIncomeData()"
                >
            </div>
        </div>
        
        <div class="flex-1 overflow-hidden flex flex-col min-h-0">
            <!-- Summary Stats -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-4 border-b border-green-200 flex-shrink-0">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-green-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-green-100 rounded">
                                <i data-lucide="trending-up" class="w-4 h-4 text-green-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-green-600" id="totalIncomeCount">₱0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Total Income</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-red-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-red-100 rounded">
                                <i data-lucide="trending-down" class="w-4 h-4 text-red-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-red-600" id="totalExpenseCount">₱0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Total Expenses</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-blue-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-blue-100 rounded">
                                <i data-lucide="calculator" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-blue-600" id="netIncomeCount">₱0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Net Income</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-purple-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-purple-100 rounded">
                                <i data-lucide="pie-chart" class="w-4 h-4 text-purple-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-purple-600" id="profitMarginCount">0%</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Profit Margin</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Income Data Grid -->
            <div class="flex-1 overflow-y-auto p-4 bg-gray-50 min-h-0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-4" id="incomeGrid">
                    <!-- Loading State -->
                    <div class="col-span-full text-center py-16">
                        <div class="inline-flex flex-col items-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-green-600 border-t-transparent mb-4"></div>
                            <span class="text-lg text-gray-600 font-semibold mb-2">Loading income data...</span>
                            <p class="text-sm text-gray-400">Please wait while we fetch financial details</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daily Boundary Collection Modal -->
<div id="dailyBoundaryModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[95vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="p-4 border-b bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 flex-shrink-0">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg border border-white/30">
                        <i data-lucide="calendar" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Daily Boundary Collections</h3>
                        <p class="text-green-100 text-xs font-medium">Complete boundary collection details</p>
                    </div>
                </div>
                <button onclick="hideDailyBoundaryModal()" class="text-white/80 hover:text-white hover:bg-white/20 p-2 rounded-lg transition-all duration-200 backdrop-blur-sm">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <!-- Search and Date Filter -->
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-white/60"></i>
                    </div>
                    <input 
                        type="text" 
                        id="boundarySearchInput"
                        placeholder="Search by unit number, driver, or amount..."
                        class="w-full pl-10 pr-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                        onkeyup="filterBoundaryCollections()"
                    >
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button onclick="clearBoundarySearch()" class="text-white/60 hover:text-white transition-colors">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <input 
                    type="date" 
                    id="boundaryDateFilter"
                    class="px-3 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                    onchange="filterBoundaryCollections()"
                >
            </div>
        </div>
        
        <div class="flex-1 overflow-hidden flex flex-col min-h-0">
            <!-- Summary Stats -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-4 border-b border-green-200 flex-shrink-0">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-green-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-green-100 rounded">
                                <i data-lucide="calendar" class="w-4 h-4 text-green-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-green-600" id="totalBoundaryCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Total Collections</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-green-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-blue-100 rounded">
                                <i data-lucide="car" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-blue-600" id="uniqueUnitsCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Unique Units</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-green-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-purple-100 rounded">
                                <i data-lucide="user" class="w-4 h-4 text-purple-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-purple-600" id="uniqueDriversCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Unique Drivers</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-green-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-yellow-100 rounded">
                                <i data-lucide="trending-up" class="w-4 h-4 text-yellow-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-yellow-600" id="totalBoundaryAmount">₱0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Total Amount</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boundary Collections Grid -->
            <div class="flex-1 overflow-y-auto p-4 bg-gray-50 min-h-0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pb-4" id="boundaryGrid">
                    <!-- Loading State -->
                    <div class="col-span-full text-center py-16">
                        <div class="inline-flex flex-col items-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-green-600 border-t-transparent mb-4"></div>
                            <span class="text-lg text-gray-600 font-semibold mb-2">Loading boundary collections...</span>
                            <p class="text-sm text-gray-400">Please wait while we fetch collection details</p>
                        </div>
                    </div>
                </div>
            </div>
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
                            <h3 class="text-lg font-bold text-white leading-tight">Units Overview</h3>
                            <p class="text-blue-100 text-[10px] font-medium mt-0.5">Fleet Management Dashboard</p>
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
            </div>
            
            <div class="flex-1 overflow-hidden flex flex-col min-h-0">
                <!-- Compact Summary Stats -->
                <div class="bg-gradient-to-r from-gray-50 to-blue-50 p-3 border-b border-gray-200 flex-shrink-0">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="bg-white rounded-lg p-2 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-2">
                                <div class="p-1 bg-blue-100 rounded">
                                    <i data-lucide="car" class="w-3.5 h-3.5 text-blue-600"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-base font-bold text-blue-600 leading-tight" id="totalUnitsCount">0</div>
                                    <div class="text-[10px] text-gray-500 uppercase tracking-tight font-bold">Total</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-2 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-2">
                                <div class="p-1 bg-green-100 rounded">
                                    <i data-lucide="activity" class="w-3.5 h-3.5 text-green-600"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-base font-bold text-green-600 leading-tight" id="activeUnitsCount">0</div>
                                    <div class="text-[10px] text-gray-500 uppercase tracking-tight font-bold">Active</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-2 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-2">
                                <div class="p-1 bg-yellow-100 rounded">
                                    <i data-lucide="trending-up" class="w-3.5 h-3.5 text-yellow-600"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-base font-bold text-yellow-600 leading-tight" id="roiUnitsCount">0</div>
                                    <div class="text-[10px] text-gray-500 uppercase tracking-tight font-bold">ROI</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-2 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-2">
                                <div class="p-1 bg-purple-100 rounded">
                                    <i data-lucide="pie-chart" class="w-3.5 h-3.5 text-purple-600"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-base font-bold text-purple-600 leading-tight" id="avgRoiCount">0%</div>
                                    <div class="text-[10px] text-gray-500 uppercase tracking-tight font-bold">Avg ROI</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Units Grid with Maximum Space -->
                <div class="flex-1 overflow-y-auto p-4 bg-gray-50 min-h-0">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-4 pb-4" id="unitsGrid">
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
        // Register Chart.js datalabels plugin
        Chart.register(ChartDataLabels);
        
        // Debug: Check if Chart.js is loaded
        console.log('Chart.js loaded:', typeof Chart !== 'undefined');
        console.log('ChartDataLabels loaded:', typeof ChartDataLabels !== 'undefined');
        
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
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ₱' + value.toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    },
                    datalabels: {
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 14
                        },
                        formatter: (value, ctx) => {
                            const sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = sum > 0 ? ((value / sum) * 100).toFixed(1) : 0;
                            return percentage > 0 ? percentage + '%' : '';
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

        // Maintenance Units Modal Functions
        function showMaintenanceUnitsModal() {
            document.getElementById('maintenanceUnitsModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            loadMaintenanceUnitsData();
        }
        
        function hideMaintenanceUnitsModal() {
            document.getElementById('maintenanceUnitsModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function loadMaintenanceUnitsData() {
            fetch('/api/maintenance-units')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayMaintenanceUnitsData(data);
                    } else {
                        showMaintenanceError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading maintenance units data:', error);
                    showMaintenanceError('Error loading maintenance units data. Please try again.');
                });
        }
        
        function displayMaintenanceUnitsData(data) {
            const grid = document.getElementById('maintenanceGrid');
            const units = data.units || [];
            const stats = data.stats || {};
            
            // Update summary stats
            document.getElementById('maintenanceUnitsCount').textContent = stats.total_maintenance || 0;
            document.getElementById('avgMaintenanceDaysCount').textContent = stats.avg_maintenance_days || 0;
            document.getElementById('completedMaintenanceCount').textContent = stats.completed_maintenance || 0;
            document.getElementById('pendingMaintenanceCount').textContent = stats.pending_maintenance || 0;
            
            // Store original data for filtering
            window.originalMaintenanceData = units;
            window.currentFilteredMaintenanceData = units;
            
            // Render maintenance units
            renderMaintenanceUnits(units);
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function renderMaintenanceUnits(units) {
            const grid = document.getElementById('maintenanceGrid');
            
            if (units.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-20">
                        <div class="inline-flex flex-col items-center">
                            <div class="p-4 bg-gray-100 rounded-full mb-4">
                                <i data-lucide="wrench" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <span class="text-xl text-gray-600 font-semibold mb-2">No maintenance units found</span>
                            <p class="text-sm text-gray-400">Try adjusting your search or date filter</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            grid.innerHTML = units.map(unit => `
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border-l-4 border-orange-500 hover:scale-102">
                    <div class="p-4">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-orange-100 rounded-lg">
                                    <i data-lucide="wrench" class="w-4 h-4 text-orange-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">${unit.unit_number}</h4>
                                    <span class="text-xs text-gray-500">${unit.plate_number || 'N/A'}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-orange-600">${unit.maintenance_type || 'Unknown'}</div>
                                <div class="text-xs text-gray-500">${unit.start_date || 'N/A'}</div>
                            </div>
                        </div>
                        
                        <!-- Maintenance Details -->
                        <div class="bg-gray-50 rounded-lg p-3 mb-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">Status: ${unit.status || 'Unknown'}</span>
                                <span class="text-xs text-gray-600">${unit.estimated_completion || 'N/A'}</span>
                            </div>
                            <div class="text-xs text-gray-600">
                                <span class="font-medium">Description:</span> ${unit.description || 'No description available'}
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                ${unit.start_date || 'No start date'}
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="check-circle" class="w-3 h-3"></i>
                                ${unit.status || 'Unknown'}
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function filterMaintenanceUnits() {
            const searchTerm = document.getElementById('maintenanceSearchInput').value.toLowerCase();
            const dateFilter = document.getElementById('maintenanceDateFilter').value;
            
            let filteredUnits = window.originalMaintenanceData || [];
            
            // Apply date filter
            if (dateFilter) {
                filteredUnits = filteredUnits.filter(unit => {
                    return unit.start_date === dateFilter;
                });
            }
            
            // Apply search filter
            if (searchTerm) {
                filteredUnits = filteredUnits.filter(unit => {
                    const searchableText = [
                        unit.unit_number || '',
                        unit.plate_number || '',
                        unit.maintenance_type || '',
                        unit.status || '',
                        unit.description || '',
                        unit.start_date || '',
                        unit.estimated_completion || ''
                    ].join(' ').toLowerCase();
                    
                    return searchableText.includes(searchTerm);
                });
            }
            
            window.currentFilteredMaintenanceData = filteredUnits;
            renderMaintenanceUnits(filteredUnits);
        }
        
        function clearMaintenanceSearch() {
            document.getElementById('maintenanceSearchInput').value = '';
            document.getElementById('maintenanceDateFilter').value = '';
            filterMaintenanceUnits();
        }
        
        function showMaintenanceError(message, debugInfo = null) {
            const grid = document.getElementById('maintenanceGrid');
            const debugHtml = debugInfo ? `
                <div class="mt-4 p-3 bg-gray-100 rounded-lg text-xs">
                    <h4 class="font-bold text-gray-700 mb-2">Debug Information:</h4>
                    <pre class="text-gray-600 whitespace-pre-wrap">${JSON.stringify(debugInfo, null, 2)}</pre>
                </div>
            ` : '';
            
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-red-100 rounded-full mb-4">
                            <i data-lucide="alert-circle" class="w-8 h-8 text-red-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Error Loading Maintenance Data</span>
                        <p class="text-sm text-gray-400 mb-4">${message}</p>
                        <div class="flex gap-2">
                            <button onclick="loadMaintenanceUnitsData()" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                                <i data-lucide="refresh-cw" class="w-4 h-4 inline mr-2"></i>
                                Retry
                            </button>
                            <button onclick="testMaintenanceAPI()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i data-lucide="bug" class="w-4 h-4 inline mr-2"></i>
                                Test API
                            </button>
                        </div>
                        ${debugHtml}
                    </div>
                </div>
            `;
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function testMaintenanceAPI() {
            const grid = document.getElementById('maintenanceGrid');
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-blue-100 rounded-full mb-4">
                            <i data-lucide="bug" class="w-8 h-8 text-blue-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Testing API Connection</span>
                        <p class="text-sm text-gray-400 mb-4">Checking API endpoint...</p>
                        <div class="w-64 bg-gray-200 rounded-full h-2 mb-4">
                            <div class="bg-blue-600 h-2 rounded-full animate-pulse" style="width: 60%"></div>
                        </div>
                    </div>
                </div>
            `;
            
            // Test the API endpoint
            fetch('/api/maintenance-units')
                .then(response => {
                    console.log('API Response Status:', response.status);
                    console.log('API Response Headers:', response.headers);
                    return response.text();
                })
                .then(text => {
                    console.log('API Response Text:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed API Data:', data);
                        showMaintenanceError('API Test Complete - Check Console for Details', {
                            response_status: 'success',
                            data_keys: Object.keys(data),
                            data: data
                        });
                    } catch (parseError) {
                        console.log('JSON Parse Error:', parseError);
                        showMaintenanceError('API Test Complete - JSON Parse Error', {
                            response_status: 'parse_error',
                            raw_response: text.substring(0, 500) + (text.length > 500 ? '...' : ''),
                            parse_error: parseError.message
                        });
                    }
                })
                .catch(error => {
                    console.log('API Fetch Error:', error);
                    showMaintenanceError('API Test Complete - Fetch Error', {
                        response_status: 'fetch_error',
                        error: error.message,
                        stack: error.stack
                    });
                });
        }

        // Active Drivers Modal Functions
        function showActiveDriversModal() {
            document.getElementById('activeDriversModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            loadActiveDriversData();
        }
        
        function hideActiveDriversModal() {
            document.getElementById('activeDriversModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function loadActiveDriversData() {
            fetch('/api/active-drivers')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayActiveDriversData(data);
                    } else {
                        showActiveDriversError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading active drivers data:', error);
                    showActiveDriversError('Error loading active drivers data. Please try again.');
                });
        }
        
        function displayActiveDriversData(data) {
            const grid = document.getElementById('activeDriversGrid');
            const drivers = data.drivers || [];
            const stats = data.stats || {};
            
            // Update summary stats
            document.getElementById('activeDriversCount').textContent = stats.active_drivers || 0;
            document.getElementById('assignedUnitsCount').textContent = stats.assigned_units || 0;
            document.getElementById('avgBoundaryCount').textContent = '₱' + (stats.avg_boundary || 0).toLocaleString();
            document.getElementById('topPerformersCount').textContent = stats.top_performers || 0;
            
            // Store original data for filtering
            window.originalActiveDriversData = drivers;
            window.currentFilteredActiveDriversData = drivers;
            
            // Render active drivers
            renderActiveDrivers(drivers);
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function renderActiveDrivers(drivers) {
            const grid = document.getElementById('activeDriversGrid');
            
            if (drivers.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-20">
                        <div class="inline-flex flex-col items-center">
                            <div class="p-4 bg-gray-100 rounded-full mb-4">
                                <i data-lucide="users" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <span class="text-xl text-gray-600 font-semibold mb-2">No active drivers found</span>
                            <p class="text-sm text-gray-400">Try adjusting your search or date filter</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            grid.innerHTML = drivers.map(driver => `
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border-l-4 border-blue-500 hover:scale-102">
                    <div class="p-4">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <i data-lucide="users" class="w-4 h-4 text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">${driver.name || 'Unknown'}</h4>
                                    <span class="text-xs text-gray-500">${driver.license_number || 'N/A'}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-blue-600">${driver.assigned_units || 0}</div>
                                <div class="text-xs text-gray-500">Units Assigned</div>
                            </div>
                        </div>
                        
                        <!-- Driver Details -->
                        <div class="bg-gray-50 rounded-lg p-3 mb-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">Contact: ${driver.phone || 'N/A'}</span>
                                <span class="text-xs text-gray-600">${driver.email || 'N/A'}</span>
                            </div>
                            <div class="text-xs text-gray-600">
                                <span class="font-medium">Address:</span> ${driver.address || 'No address available'}
                            </div>
                        </div>
                        
                        <!-- Performance Stats -->
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full ${driver.performance_rating === 'excellent' ? 'bg-green-500' : driver.performance_rating === 'good' ? 'bg-yellow-500' : driver.performance_rating === 'average' ? 'bg-orange-500' : 'bg-gray-400'} animate-pulse"></div>
                                <span class="text-xs font-medium text-gray-600">
                                    ${driver.performance_rating ? driver.performance_rating.charAt(0).toUpperCase() + driver.performance_rating.slice(1) : 'Unknown'}
                                </span>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-blue-600">₱${driver.total_boundary ? driver.total_boundary.toLocaleString() : '0'}</div>
                                <div class="text-xs text-gray-500">Total Collected</div>
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                ${driver.hire_date || 'No hire date'}
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="check-circle" class="w-3 h-3"></i>
                                Active
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function filterActiveDrivers() {
            const searchTerm = document.getElementById('driversSearchInput').value.toLowerCase();
            const dateFilter = document.getElementById('driversDateFilter').value;
            
            let filteredDrivers = window.originalActiveDriversData || [];
            
            // Apply date filter
            if (dateFilter) {
                filteredDrivers = filteredDrivers.filter(driver => {
                    return driver.hire_date === dateFilter;
                });
            }
            
            // Apply search filter
            if (searchTerm) {
                filteredDrivers = filteredDrivers.filter(driver => {
                    const searchableText = [
                        driver.name || '',
                        driver.license_number || '',
                        driver.phone || '',
                        driver.email || '',
                        driver.address || '',
                        driver.performance_rating || '',
                        driver.total_boundary ? driver.total_boundary.toString() : '',
                        driver.assigned_units ? driver.assigned_units.toString() : '',
                        driver.hire_date || ''
                    ].join(' ').toLowerCase();
                    
                    return searchableText.includes(searchTerm);
                });
            }
            
            window.currentFilteredActiveDriversData = filteredDrivers;
            renderActiveDrivers(filteredDrivers);
        }
        
        function clearDriversSearch() {
            document.getElementById('driversSearchInput').value = '';
            document.getElementById('driversDateFilter').value = '';
            filterActiveDrivers();
        }
        
        function showActiveDriversError(message, debugInfo = null) {
            const grid = document.getElementById('activeDriversGrid');
            const debugHtml = debugInfo ? `
                <div class="mt-4 p-3 bg-gray-100 rounded-lg text-xs">
                    <h4 class="font-bold text-gray-700 mb-2">Debug Information:</h4>
                    <pre class="text-gray-600 whitespace-pre-wrap">${JSON.stringify(debugInfo, null, 2)}</pre>
                </div>
            ` : '';
            
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-red-100 rounded-full mb-4">
                            <i data-lucide="alert-circle" class="w-8 h-8 text-red-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Error Loading Driver Data</span>
                        <p class="text-sm text-gray-400 mb-4">${message}</p>
                        <div class="flex gap-2">
                            <button onclick="loadActiveDriversData()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i data-lucide="refresh-cw" class="w-4 h-4 inline mr-2"></i>
                                Retry
                            </button>
                            <button onclick="testActiveDriversAPI()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i data-lucide="bug" class="w-4 h-4 inline mr-2"></i>
                                Test API
                            </button>
                        </div>
                        ${debugHtml}
                    </div>
                </div>
            `;
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function testActiveDriversAPI() {
            const grid = document.getElementById('activeDriversGrid');
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-blue-100 rounded-full mb-4">
                            <i data-lucide="bug" class="w-8 h-8 text-blue-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Testing API Connection</span>
                        <p class="text-sm text-gray-400 mb-4">Checking API endpoint...</p>
                        <div class="w-64 bg-gray-200 rounded-full h-2 mb-4">
                            <div class="bg-blue-600 h-2 rounded-full animate-pulse" style="width: 60%"></div>
                        </div>
                    </div>
                </div>
            `;
            
            // Test the API endpoint
            fetch('/api/active-drivers')
                .then(response => {
                    console.log('API Response Status:', response.status);
                    console.log('API Response Headers:', response.headers);
                    return response.text();
                })
                .then(text => {
                    console.log('API Response Text:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed API Data:', data);
                        showActiveDriversError('API Test Complete - Check Console for Details', {
                            response_status: 'success',
                            data_keys: Object.keys(data),
                            data: data
                        });
                    } catch (parseError) {
                        console.log('JSON Parse Error:', parseError);
                        showActiveDriversError('API Test Complete - JSON Parse Error', {
                            response_status: 'parse_error',
                            raw_response: text.substring(0, 500) + (text.length > 500 ? '...' : ''),
                            parse_error: parseError.message
                        });
                    }
                })
                .catch(error => {
                    console.log('API Fetch Error:', error);
                    showActiveDriversError('API Test Complete - Fetch Error', {
                        response_status: 'fetch_error',
                        error: error.message,
                        stack: error.stack
                    });
                });
        }

        // Coding Units Modal Functions
        function showCodingUnitsModal() {
            document.getElementById('codingUnitsModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            loadCodingUnitsData();
        }
        
        function hideCodingUnitsModal() {
            document.getElementById('codingUnitsModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function loadCodingUnitsData() {
            fetch('/api/coding-units')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayCodingUnitsData(data);
                    } else {
                        showCodingError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading coding units data:', error);
                    showCodingError('Error loading coding units data. Please try again.');
                });
        }
        
        function displayCodingUnitsData(data) {
            const grid = document.getElementById('codingGrid');
            const units = data.units || [];
            const stats = data.stats || {};
            
            // Update summary stats
            document.getElementById('codingUnitsCount').textContent = stats.total_coding || 0;
            document.getElementById('avgCodingDaysCount').textContent = stats.avg_coding_days || 0;
            document.getElementById('completedCodingCount').textContent = stats.completed_coding || 0;
            document.getElementById('pendingCodingCount').textContent = stats.pending_coding || 0;
            
            // Store original data for filtering
            window.originalCodingUnitsData = units;
            window.currentFilteredCodingUnitsData = units;
            
            // Render coding units
            renderCodingUnits(units);
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function renderCodingUnits(units) {
            const grid = document.getElementById('codingGrid');
            
            if (units.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-20">
                        <div class="inline-flex flex-col items-center">
                            <div class="p-4 bg-gray-100 rounded-full mb-4">
                                <i data-lucide="code" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <span class="text-xl text-gray-600 font-semibold mb-2">No coding units found</span>
                            <p class="text-sm text-gray-400">Try adjusting your search or date filter</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            grid.innerHTML = units.map(unit => `
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border-l-4 border-purple-500 hover:scale-102">
                    <div class="p-4">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-100 rounded-lg">
                                    <i data-lucide="code" class="w-4 h-4 text-purple-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">${unit.unit_number}</h4>
                                    <span class="text-xs text-gray-500">${unit.plate_number || 'N/A'}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-purple-600">${unit.coding_type || 'Unknown'}</div>
                                <div class="text-xs text-gray-500">${unit.start_date || 'N/A'}</div>
                            </div>
                        </div>
                        
                        <!-- Coding Details -->
                        <div class="bg-gray-50 rounded-lg p-3 mb-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">Status: ${unit.status || 'Unknown'}</span>
                                <span class="text-xs text-gray-600">${unit.estimated_completion || 'N/A'}</span>
                            </div>
                            <div class="text-xs text-gray-600">
                                <span class="font-medium">Description:</span> ${unit.description || 'No description available'}
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                ${unit.start_date || 'No start date'}
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="check-circle" class="w-3 h-3"></i>
                                ${unit.status || 'Unknown'}
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function filterCodingUnits() {
            const searchTerm = document.getElementById('codingSearchInput').value.toLowerCase();
            const dateFilter = document.getElementById('codingDateFilter').value;
            
            let filteredUnits = window.originalCodingUnitsData || [];
            
            // Apply date filter
            if (dateFilter) {
                filteredUnits = filteredUnits.filter(unit => {
                    return unit.start_date === dateFilter;
                });
            }
            
            // Apply search filter
            if (searchTerm) {
                filteredUnits = filteredUnits.filter(unit => {
                    const searchableText = [
                        unit.unit_number || '',
                        unit.plate_number || '',
                        unit.coding_type || '',
                        unit.status || '',
                        unit.description || '',
                        unit.start_date || '',
                        unit.estimated_completion || ''
                    ].join(' ').toLowerCase();
                    
                    return searchableText.includes(searchTerm);
                });
            }
            
            window.currentFilteredCodingUnitsData = filteredUnits;
            renderCodingUnits(filteredUnits);
        }
        
        function clearCodingSearch() {
            document.getElementById('codingSearchInput').value = '';
            document.getElementById('codingDateFilter').value = '';
            filterCodingUnits();
        }
        
        function showCodingError(message, debugInfo = null) {
            const grid = document.getElementById('codingGrid');
            const debugHtml = debugInfo ? `
                <div class="mt-4 p-3 bg-gray-100 rounded-lg text-xs">
                    <h4 class="font-bold text-gray-700 mb-2">Debug Information:</h4>
                    <pre class="text-gray-600 whitespace-pre-wrap">${JSON.stringify(debugInfo, null, 2)}</pre>
                </div>
            ` : '';
            
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-red-100 rounded-full mb-4">
                            <i data-lucide="alert-circle" class="w-8 h-8 text-red-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Error Loading Coding Data</span>
                        <p class="text-sm text-gray-400 mb-4">${message}</p>
                        <div class="flex gap-2">
                            <button onclick="loadCodingUnitsData()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                <i data-lucide="refresh-cw" class="w-4 h-4 inline mr-2"></i>
                                Retry
                            </button>
                            <button onclick="testCodingUnitsAPI()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i data-lucide="bug" class="w-4 h-4 inline mr-2"></i>
                                Test API
                            </button>
                        </div>
                        ${debugHtml}
                    </div>
                </div>
            `;
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function testCodingUnitsAPI() {
            const grid = document.getElementById('codingGrid');
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-blue-100 rounded-full mb-4">
                            <i data-lucide="bug" class="w-8 h-8 text-blue-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Testing API Connection</span>
                        <p class="text-sm text-gray-400 mb-4">Checking API endpoint...</p>
                        <div class="w-64 bg-gray-200 rounded-full h-2 mb-4">
                            <div class="bg-blue-600 h-2 rounded-full animate-pulse" style="width: 60%"></div>
                        </div>
                    </div>
                </div>
            `;
            
            // Test the API endpoint
            fetch('/api/coding-units')
                .then(response => {
                    console.log('API Response Status:', response.status);
                    console.log('API Response Headers:', response.headers);
                    return response.text();
                })
                .then(text => {
                    console.log('API Response Text:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed API Data:', data);
                        showCodingError('API Test Complete - Check Console for Details', {
                            response_status: 'success',
                            data_keys: Object.keys(data),
                            data: data
                        });
                    } catch (parseError) {
                        console.log('JSON Parse Error:', parseError);
                        showCodingError('API Test Complete - JSON Parse Error', {
                            response_status: 'parse_error',
                            raw_response: text.substring(0, 500) + (text.length > 500 ? '...' : ''),
                            parse_error: parseError.message
                        });
                    }
                })
                .catch(error => {
                    console.log('API Fetch Error:', error);
                    showCodingError('API Test Complete - Fetch Error', {
                        response_status: 'fetch_error',
                        error: error.message,
                        stack: error.stack
                    });
                });
        }

        // Net Income Modal Functions
        function showNetIncomeModal() {
            document.getElementById('netIncomeModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            loadIncomeData();
        }
        
        function hideNetIncomeModal() {
            document.getElementById('netIncomeModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function loadIncomeData() {
            fetch('/api/net-income-details')
                .then(response => {
                    // Check if response is HTML (error page) or JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('text/html')) {
                        return response.text().then(text => {
                            throw new Error('API returned HTML instead of JSON. This usually means a Laravel error occurred. Check the Laravel logs for details.');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayIncomeData(data);
                    } else {
                        showIncomeError(data.message, data.debug_info || null);
                    }
                })
                .catch(error => {
                    console.error('Error loading income data:', error);
                    showIncomeError('Error loading income data. Please try again.', {
                        fetch_error: error.message,
                        stack: error.stack
                    });
                });
        }
        
        function displayIncomeData(data) {
            const grid = document.getElementById('incomeGrid');
            const incomeData = data.income_data || [];
            const stats = data.stats || {};
            
            // Update summary stats
            document.getElementById('totalIncomeCount').textContent = '₱' + (stats.total_income || 0).toLocaleString();
            document.getElementById('totalExpenseCount').textContent = '₱' + (stats.total_expenses || 0).toLocaleString();
            document.getElementById('netIncomeCount').textContent = '₱' + (stats.net_income || 0).toLocaleString();
            document.getElementById('profitMarginCount').textContent = (stats.profit_margin || 0).toFixed(1) + '%';
            
            // Store original data for filtering
            window.originalIncomeData = incomeData;
            window.currentFilteredIncomeData = incomeData;
            
            // Render income data
            renderIncomeData(incomeData);
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function renderIncomeData(incomeData) {
            const grid = document.getElementById('incomeGrid');
            
            if (incomeData.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-20">
                        <div class="inline-flex flex-col items-center">
                            <div class="p-4 bg-gray-100 rounded-full mb-4">
                                <i data-lucide="trending-up" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <span class="text-xl text-gray-600 font-semibold mb-2">No income data found</span>
                            <p class="text-sm text-gray-400">Try adjusting your search or date filter</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            grid.innerHTML = incomeData.map(item => `
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border-l-4 ${item.type === 'income' ? 'border-green-500' : 'border-red-500'} hover:scale-102">
                    <div class="p-4">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2 ${item.type === 'income' ? 'bg-green-100' : 'bg-red-100'} rounded-lg">
                                    <i data-lucide="${item.type === 'income' ? 'trending-up' : 'trending-down'}" class="w-4 h-4 ${item.type === 'income' ? 'text-green-600' : 'text-red-600'}"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">${item.description || 'Unknown'}</h4>
                                    <span class="text-xs text-gray-500">${item.category || 'General'}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold ${item.type === 'income' ? 'text-green-600' : 'text-red-600'}">
                                    ${item.type === 'income' ? '+' : '-'}₱${Math.abs(item.amount).toLocaleString()}
                                </div>
                                <div class="text-xs text-gray-500">${item.date}</div>
                            </div>
                        </div>
                        
                        <!-- Details -->
                        <div class="bg-gray-50 rounded-lg p-3 mb-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">Type: ${item.type === 'income' ? 'Income' : 'Expense'}</span>
                                <span class="text-xs text-gray-600">${item.source || 'Unknown'}</span>
                            </div>
                            ${item.reference ? `
                                <div class="text-xs text-gray-600">
                                    Reference: ${item.reference}
                                </div>
                            ` : ''}
                        </div>
                        
                        <!-- Footer -->
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                ${item.date}
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="check-circle" class="w-3 h-3"></i>
                                Verified
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function filterIncomeData() {
            const searchTerm = document.getElementById('incomeSearchInput').value.toLowerCase();
            const dateFilter = document.getElementById('incomeDateFilter').value;
            
            let filteredData = window.originalIncomeData || [];
            
            // Apply date filter
            if (dateFilter) {
                filteredData = filteredData.filter(item => {
                    return item.date === dateFilter;
                });
            }
            
            // Apply search filter
            if (searchTerm) {
                filteredData = filteredData.filter(item => {
                    const searchableText = [
                        item.description || '',
                        item.category || '',
                        item.type || '',
                        item.source || '',
                        item.reference || '',
                        item.amount ? item.amount.toString() : '',
                        item.date || ''
                    ].join(' ').toLowerCase();
                    
                    return searchableText.includes(searchTerm);
                });
            }
            
            window.currentFilteredIncomeData = filteredData;
            renderIncomeData(filteredData);
        }
        
        function clearIncomeSearch() {
            document.getElementById('incomeSearchInput').value = '';
            document.getElementById('incomeDateFilter').value = '';
            filterIncomeData();
        }
        
        function showIncomeError(message, debugInfo = null) {
            const grid = document.getElementById('incomeGrid');
            const debugHtml = debugInfo ? `
                <div class="mt-4 p-3 bg-gray-100 rounded-lg text-xs">
                    <h4 class="font-bold text-gray-700 mb-2">Debug Information:</h4>
                    <pre class="text-gray-600 whitespace-pre-wrap">${JSON.stringify(debugInfo, null, 2)}</pre>
                </div>
            ` : '';
            
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-red-100 rounded-full mb-4">
                            <i data-lucide="alert-circle" class="w-8 h-8 text-red-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Error Loading Income Data</span>
                        <p class="text-sm text-gray-400 mb-4">${message}</p>
                        <div class="flex gap-2">
                            <button onclick="loadIncomeData()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <i data-lucide="refresh-cw" class="w-4 h-4 inline mr-2"></i>
                                Retry
                            </button>
                            <button onclick="testIncomeAPI()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i data-lucide="bug" class="w-4 h-4 inline mr-2"></i>
                                Test API
                            </button>
                        </div>
                        ${debugHtml}
                    </div>
                </div>
            `;
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function testIncomeAPI() {
            const grid = document.getElementById('incomeGrid');
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-blue-100 rounded-full mb-4">
                            <i data-lucide="bug" class="w-8 h-8 text-blue-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Testing API Connection</span>
                        <p class="text-sm text-gray-400 mb-4">Checking API endpoint...</p>
                        <div class="w-64 bg-gray-200 rounded-full h-2 mb-4">
                            <div class="bg-blue-600 h-2 rounded-full animate-pulse" style="width: 60%"></div>
                        </div>
                    </div>
                </div>
            `;
            
            // Test the API endpoint
            fetch('/api/net-income-details')
                .then(response => {
                    console.log('API Response Status:', response.status);
                    console.log('API Response Headers:', response.headers);
                    console.log('Content-Type:', response.headers.get('content-type'));
                    
                    // Check if response is HTML (error page) or JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('text/html')) {
                        return response.text().then(text => {
                            throw new Error('API returned HTML instead of JSON. This usually means a Laravel error occurred. Response: ' + text.substring(0, 200) + '...');
                        });
                    }
                    
                    return response.text();
                })
                .then(text => {
                    console.log('API Response Text:', text);
                    console.log('Response Length:', text.length);
                    console.log('First 100 chars:', text.substring(0, 100));
                    
                    // Check if response starts with HTML
                    if (text.trim().startsWith('<')) {
                        throw new Error('API returned HTML instead of JSON. Response starts with: ' + text.substring(0, 100) + '...');
                    }
                    
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed API Data:', data);
                        showIncomeError('API Test Complete - Check Console for Details', {
                            response_status: 'success',
                            data_keys: Object.keys(data),
                            data: data
                        });
                    } catch (parseError) {
                        console.log('JSON Parse Error:', parseError);
                        console.log('Raw Response:', text);
                        showIncomeError('API Test Complete - JSON Parse Error', {
                            response_status: 'parse_error',
                            raw_response: text.substring(0, 500) + (text.length > 500 ? '...' : ''),
                            parse_error: parseError.message,
                            response_length: text.length,
                            first_chars: text.substring(0, 100)
                        });
                    }
                })
                .catch(error => {
                    console.log('API Fetch Error:', error);
                    showIncomeError('API Test Complete - Fetch Error', {
                        response_status: 'fetch_error',
                        error: error.message,
                        stack: error.stack
                    });
                });
        }

        // Daily Boundary Collection Modal Functions
        function showDailyBoundaryModal() {
            document.getElementById('dailyBoundaryModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            loadBoundaryCollections();
        }
        
        function hideDailyBoundaryModal() {
            document.getElementById('dailyBoundaryModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function loadBoundaryCollections() {
            fetch('/api/daily-boundary-collections')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayBoundaryCollections(data);
                    } else {
                        showBoundaryError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading boundary collections:', error);
                    showBoundaryError('Error loading boundary collections. Please try again.');
                });
        }
        
        function displayBoundaryCollections(data) {
            const grid = document.getElementById('boundaryGrid');
            const collections = data.collections || [];
            const stats = data.stats || {};
            
            // Update summary stats
            document.getElementById('totalBoundaryCount').textContent = stats.total_collections || 0;
            document.getElementById('uniqueUnitsCount').textContent = stats.unique_units || 0;
            document.getElementById('uniqueDriversCount').textContent = stats.unique_drivers || 0;
            document.getElementById('totalBoundaryAmount').textContent = '₱' + (stats.total_amount || 0).toLocaleString();
            
            // Store original data for filtering
            window.originalBoundaryData = collections;
            window.currentFilteredBoundaryData = collections;
            
            // Render boundary collections
            renderBoundaryCollections(collections);
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function renderBoundaryCollections(collections) {
            const grid = document.getElementById('boundaryGrid');
            
            if (collections.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-20">
                        <div class="inline-flex flex-col items-center">
                            <div class="p-4 bg-gray-100 rounded-full mb-4">
                                <i data-lucide="calendar" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <span class="text-xl text-gray-600 font-semibold mb-2">No boundary collections found</span>
                            <p class="text-sm text-gray-400">Try adjusting your search or date filter</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            grid.innerHTML = collections.map(collection => `
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border-l-4 border-green-500 hover:scale-102">
                    <div class="p-4">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-100 rounded-lg">
                                    <i data-lucide="car" class="w-4 h-4 text-green-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">${collection.unit_number}</h4>
                                    <span class="text-xs text-gray-500">${collection.plate_number || 'N/A'}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-green-600">₱${collection.boundary_amount.toLocaleString()}</div>
                                <div class="text-xs text-gray-500">${collection.date}</div>
                            </div>
                        </div>
                        
                        <!-- Driver Information -->
                        <div class="bg-gray-50 rounded-lg p-3 mb-3">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="user" class="w-4 h-4 text-gray-600"></i>
                                <span class="text-sm font-medium text-gray-900">Driver: ${collection.driver_name || 'N/A'}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i data-lucide="clock" class="w-4 h-4 text-gray-600"></i>
                                <span class="text-xs text-gray-600">Time: ${collection.time || 'N/A'}</span>
                            </div>
                        </div>
                        
                        <!-- Collection Details -->
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <i data-lucide="map-pin" class="w-3 h-3"></i>
                                ${collection.location || 'Main Office'}
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="check-circle" class="w-3 h-3"></i>
                                Verified
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function filterBoundaryCollections() {
            const searchTerm = document.getElementById('boundarySearchInput').value.toLowerCase();
            const dateFilter = document.getElementById('boundaryDateFilter').value;
            
            let filteredCollections = window.originalBoundaryData || [];
            
            // Apply date filter
            if (dateFilter) {
                filteredCollections = filteredCollections.filter(collection => {
                    return collection.date === dateFilter;
                });
            }
            
            // Apply search filter
            if (searchTerm) {
                filteredCollections = filteredCollections.filter(collection => {
                    const searchableText = [
                        collection.unit_number || '',
                        collection.plate_number || '',
                        collection.driver_name || '',
                        collection.boundary_amount ? collection.boundary_amount.toString() : '',
                        collection.date || '',
                        collection.time || '',
                        collection.location || ''
                    ].join(' ').toLowerCase();
                    
                    return searchableText.includes(searchTerm);
                });
            }
            
            window.currentFilteredBoundaryData = filteredCollections;
            renderBoundaryCollections(filteredCollections);
        }
        
        function clearBoundarySearch() {
            document.getElementById('boundarySearchInput').value = '';
            document.getElementById('boundaryDateFilter').value = '';
            filterBoundaryCollections();
        }
        
        function showBoundaryError(message) {
            const grid = document.getElementById('boundaryGrid');
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-red-100 rounded-full mb-4">
                            <i data-lucide="alert-circle" class="w-8 h-8 text-red-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Error Loading Collections</span>
                        <p class="text-sm text-gray-400 mb-4">${message}</p>
                        <button onclick="loadBoundaryCollections()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <i data-lucide="refresh-cw" class="w-4 h-4 inline mr-2"></i>
                            Retry
                        </button>
                    </div>
                </div>
            `;
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
                <div class="bg-white rounded-lg shadow border-t-2 ${statusColors[unit.status] || 'border-gray-200'} hover:shadow-md transition-shadow">
                    <div class="p-2">
                        <!-- Summary Header -->
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <i data-lucide="car" class="w-3 h-3 text-gray-400"></i>
                                <h4 class="text-sm font-bold text-gray-900 truncate">${unit.unit_number}</h4>
                            </div>
                            <span class="px-1.5 py-0.5 text-[9px] font-bold rounded-full ${statusColors[unit.status] || 'bg-gray-100'} uppercase">
                                ${unit.status}
                            </span>
                        </div>
                        
                        <!-- Essential Stats -->
                        <div class="grid grid-cols-2 gap-2 text-center py-1 bg-gray-50 rounded">
                            <div>
                                <div class="text-[8px] text-gray-500 uppercase font-bold tracking-tighter">Total Coll.</div>
                                <div class="text-xs font-bold text-green-600">₱${unit.total_boundary ? unit.total_boundary.toLocaleString() : '0'}</div>
                            </div>
                            <div>
                                <div class="text-[8px] text-gray-500 uppercase font-bold tracking-tighter">ROI</div>
                                <div class="text-xs font-bold ${unit.roi_percentage >= 100 ? 'text-blue-600' : 'text-gray-900'}">${unit.roi_percentage.toFixed(1)}%</div>
                            </div>
                        </div>

                        <!-- Mini Footer -->
                        <div class="mt-1.5 flex items-center justify-between text-[8px] font-bold text-gray-400 uppercase tracking-tighter">
                            <span>ID: ${unit.plate_number || 'N/A'}</span>
                            <span class="${unit.today_boundary > 0 ? 'text-blue-500' : ''}">
                                ${unit.today_boundary > 0 ? `+₱${unit.today_boundary.toLocaleString()}` : 'No Daily'}
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
            
            // Remove any existing database data indicators to save space
            const indicator = grid.parentNode.querySelector('.data-source-indicator');
            if (indicator) indicator.remove();
            
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
            const activeFilter = document.querySelector('.filter-tag.active');
            
            let filteredUnits = window.originalUnitsData || [];
            
            // Apply status filter
            if (activeFilter) {
                if (activeFilter.dataset.status) {
                    filteredUnits = filteredUnits.filter(unit => unit.status === activeFilter.dataset.status);
                } else if (activeFilter.dataset.month) {
                    // Filter by month - get boundaries from that month
                    filteredUnits = filteredUnits.filter(unit => {
                        return unit.last_activity && unit.last_activity.includes(activeFilter.dataset.month);
                    });
                }
            }
            
            // Apply search filter
            if (searchTerm) {
                filteredUnits = filteredUnits.filter(unit => {
                    const searchableText = [
                        unit.unit_number || '',
                        unit.plate_number || '',
                        unit.status || '',
                        unit.driver_name || '',
                        unit.performance_rating || '',
                        unit.roi_percentage >= 100 ? 'excellent profitable' : 
                        unit.roi_percentage >= 75 ? 'good' : 
                        unit.roi_percentage >= 50 ? 'average growing' : 'growing investment',
                        unit.boundary_rate ? unit.boundary_rate.toString() : '',
                        unit.total_boundary ? unit.total_boundary.toString() : '',
                        unit.today_boundary ? unit.today_boundary.toString() : '',
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
        
        function filterByMonth(month) {
            // Update active filter tag
            document.querySelectorAll('.filter-tag').forEach(tag => {
                tag.classList.remove('active', 'bg-white/40');
                if (tag.dataset.month === month) {
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
