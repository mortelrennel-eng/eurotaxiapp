<?php $__env->startSection('title', 'Dashboard - Euro System'); ?>
<?php $__env->startSection('page-heading', 'Dashboard Overview'); ?>
<?php $__env->startSection('page-subheading', 'Real-time operational metrics and alerts'); ?>

<?php $__env->startSection('content'); ?>
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white rounded-lg shadow card-hover cursor-pointer hover:shadow-lg transition-shadow" onclick="showUnitsModal()">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Units</p>
                        <p class="text-2xl font-bold text-gray-900" data-stat="active_units"><?php echo e($stats['active_units']); ?></p>
                        <p class="text-xs text-gray-500">
                            <span class="text-green-600"><?php echo e($stats['roi_units']); ?> ROI Achieved</span> •
                            <?php echo e($stats['coding_units']); ?> Coding
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
                        <p class="text-2xl font-bold text-gray-900" data-stat="today_boundary"><?php echo e(formatCurrency($stats['today_boundary'])); ?></p>
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
                        <p class="text-2xl font-bold text-green-600" data-stat="net_income"><?php echo e(formatCurrency($stats['net_income'])); ?></p>
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
                        <p class="text-2xl font-bold text-gray-900" data-stat="maintenance_units"><?php echo e($stats['maintenance_units']); ?></p>
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
                        <p class="text-2xl font-bold text-gray-900" data-stat="active_drivers"><?php echo e($stats['active_drivers']); ?></p>
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
                        <p class="text-2xl font-bold text-gray-900" data-stat="avg_boundary"><?php echo e(formatCurrency($stats['avg_boundary'])); ?></p>
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
                        <p class="text-2xl font-bold text-gray-900" data-stat="coding_units"><?php echo e($stats['coding_units']); ?></p>
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
            <?php if($alerts->isEmpty()): ?>
                <p class="text-gray-500 text-center py-4">No active alerts</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-start gap-3 p-3 rounded-lg border
                                    <?php if(in_array($alert->severity, ['high', 'critical'])): ?> bg-red-50 border-red-200
                                    <?php elseif($alert->severity === 'medium'): ?> bg-yellow-50 border-yellow-200
                                    <?php elseif($alert->severity === 'low'): ?> bg-blue-50 border-blue-200
                                    <?php else: ?> bg-gray-50 border-gray-200
                                    <?php endif; ?>">
                            <div class="mt-0.5">
                                <?php if(in_array($alert->severity, ['high', 'critical'])): ?>
                                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                                <?php elseif($alert->severity === 'medium'): ?>
                                    <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-600"></i>
                                <?php else: ?>
                                    <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900"><?php echo e($alert->message); ?></p>
                                <span class="text-xs text-gray-500 capitalize"><?php echo e($alert->alert_type); ?></span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
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
    <div id="unitsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-7xl w-full mx-4 h-[90vh] flex flex-col">
            <div class="p-6 border-b bg-gradient-to-r from-yellow-50 to-orange-50 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-yellow-600 rounded-lg">
                            <i data-lucide="car" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Units Overview</h3>
                            <p class="text-sm text-gray-600">Complete fleet management dashboard</p>
                        </div>
                    </div>
                    <button onclick="hideUnitsModal()" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
            
            <div class="flex-1 overflow-hidden flex flex-col">
                <!-- Summary Stats -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 border-b flex-shrink-0">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600" id="totalUnitsCount">0</div>
                            <div class="text-xs text-gray-600 uppercase tracking-wide">Total Units</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600" id="activeUnitsCount">0</div>
                            <div class="text-xs text-gray-600 uppercase tracking-wide">Active</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600" id="roiUnitsCount">0</div>
                            <div class="text-xs text-gray-600 uppercase tracking-wide">ROI Achieved</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600" id="avgRoiCount">0%</div>
                            <div class="text-xs text-gray-600 uppercase tracking-wide">Avg ROI</div>
                        </div>
                    </div>
                </div>

                <!-- Units Grid -->
                <div class="flex-1 overflow-y-auto p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5" id="unitsGrid">
                        <!-- Loading State -->
                        <div class="col-span-full text-center py-12">
                            <div class="inline-flex flex-col items-center">
                                <div class="animate-spin rounded-full h-12 w-12 border-4 border-yellow-600 border-t-transparent mb-4"></div>
                                <span class="text-lg text-gray-500 font-medium">Loading units data...</span>
                                <p class="text-sm text-gray-400 mt-2">Please wait while we fetch your fleet information</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="<?php echo e(asset('js/realtime-dashboard.js')); ?>"></script>
    <script>
        // Weekly Financial Chart
        const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
        const weeklyData = <?php echo json_encode($weekly_data, 15, 512) ?>;
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
        const unitStatusData = <?php echo json_encode($unit_status_data, 15, 512) ?>;
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
        const revenueTrendData = <?php echo json_encode($revenue_trend, 15, 512) ?>;
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
        const unitPerformanceData = <?php echo json_encode($unit_performance, 15, 512) ?>;
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
        const expenseBreakdownData = <?php echo json_encode($expense_breakdown, 15, 512) ?>;
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
                <div class="col-span-full text-center py-8">
                    <div class="inline-flex items-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-yellow-600 border-t-transparent"></div>
                        <span class="ml-2 text-gray-500">Loading units...</span>
                    </div>
                </div>
            `;
            
            // Fetch units data
            fetch('/api/units-overview')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayUnitsData(data.units);
                    } else {
                        grid.innerHTML = '<div class="col-span-full text-center py-8 text-red-500">Error loading units data</div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading units:', error);
                    grid.innerHTML = '<div class="col-span-full text-center py-8 text-red-500">Error loading units data</div>';
                });
        }

        function displayUnitsData(units) {
            const grid = document.getElementById('unitsGrid');
            
            if (!units || units.length === 0) {
                grid.innerHTML = '<div class="col-span-full text-center py-12"><div class="text-gray-500 text-lg">No units found</div></div>';
                return;
            }
            
            // Update summary statistics
            const totalUnits = units.length;
            const activeUnits = units.filter(u => u.status === 'Active').length;
            const roiUnits = units.filter(u => u.roi_achieved).length;
            const avgRoi = units.reduce((sum, u) => sum + u.roi_percentage, 0) / units.length;
            
            document.getElementById('totalUnitsCount').textContent = totalUnits;
            document.getElementById('activeUnitsCount').textContent = activeUnits;
            document.getElementById('roiUnitsCount').textContent = roiUnits;
            document.getElementById('avgRoiCount').textContent = avgRoi.toFixed(1) + '%';
            
            const statusColors = {
                'active': 'bg-gradient-to-br from-green-50 to-emerald-50 text-green-800 border-green-300 shadow-green-100',
                'maintenance': 'bg-gradient-to-br from-red-50 to-rose-50 text-red-800 border-red-300 shadow-red-100', 
                'coding': 'bg-gradient-to-br from-yellow-50 to-amber-50 text-yellow-800 border-yellow-300 shadow-yellow-100',
                'retired': 'bg-gradient-to-br from-gray-50 to-slate-50 text-gray-800 border-gray-300 shadow-gray-100'
            };
            
            const statusIcons = {
                'active': '<i data-lucide="check-circle-2" class="w-5 h-5"></i>',
                'maintenance': '<i data-lucide="wrench" class="w-5 h-5"></i>',
                'coding': '<i data-lucide="calendar" class="w-5 h-5"></i>',
                'retired': '<i data-lucide="x-circle" class="w-5 h-5"></i>'
            };
            
            const statusGradients = {
                'active': 'from-green-500 to-emerald-600',
                'maintenance': 'from-red-500 to-rose-600', 
                'coding': 'from-yellow-500 to-amber-600',
                'retired': 'from-gray-500 to-slate-600'
            };
            
            grid.innerHTML = units.map(unit => `
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border-l-4 ${statusColors[unit.status] || 'border-gray-200'} hover:scale-102">
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <h4 class="text-lg font-bold text-gray-900 truncate">${unit.unit_number}</h4>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusColors[unit.status] || 'bg-gray-100'} flex items-center gap-1 flex-shrink-0">
                                        ${statusIcons[unit.status] || ''}
                                        <span class="truncate">${unit.status.charAt(0).toUpperCase() + unit.status.slice(1)}</span>
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-600 font-medium text-xs">Daily:</span>
                                        <span class="text-gray-900 font-bold">₱${unit.boundary_rate ? unit.boundary_rate.toLocaleString() : '0'}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-600 font-medium text-xs">Total:</span>
                                        <span class="text-green-600 font-bold">₱${unit.total_boundary ? unit.total_boundary.toLocaleString() : '0'}</span>
                                    </div>
                                    ${unit.purchase_cost ? `
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-600 font-medium text-xs">Cost:</span>
                                            <span class="text-gray-900 font-bold">₱${unit.purchase_cost.toLocaleString()}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-600 font-medium text-xs">ROI:</span>
                                            <div class="flex-1 min-w-0">
                                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                                    <div class="h-full bg-gradient-to-r ${statusGradients[unit.status]} rounded-full transition-all duration-700 ease-out" style="width: ${Math.min(100, unit.roi_percentage)}%"></div>
                                                </div>
                                            </div>
                                            <span class="text-sm font-bold ${unit.roi_percentage >= 100 ? 'text-green-600' : unit.roi_percentage >= 50 ? 'text-yellow-600' : 'text-gray-600'}">${unit.roi_percentage.toFixed(1)}%</span>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 ml-3">
                                <div class="flex flex-col items-center text-sm">
                                    <div class="flex items-center gap-1 text-gray-500 mb-1">
                                        ${statusIcons[unit.status] || ''}
                                        <span class="font-medium text-xs">${unit.status.charAt(0).toUpperCase() + unit.status.slice(1)}</span>
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        <i data-lucide="trending-up" class="w-3 h-3 inline mr-1"></i>
                                        ${unit.roi_percentage >= 100 ? 'ROI Achieved' : unit.roi_percentage >= 75 ? 'Excellent' : unit.roi_percentage >= 50 ? 'Good' : 'Growing'}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        ${unit.purchase_cost ? `
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="calendar" class="w-3 h-3"></i>
                                        ${unit.roi_percentage >= 100 ? 'Investment Recovered' : Math.ceil((100 - unit.roi_percentage) / 100 * 30) + ' days to ROI'}
                                    </span>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `).join('');
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\eurotaxisystem\resources\views/dashboard.blade.php ENDPATH**/ ?>