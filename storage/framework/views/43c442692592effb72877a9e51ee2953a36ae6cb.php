<?php $__env->startSection('title', 'Live Tracking - Euro System'); ?>
<?php $__env->startSection('page-heading', 'Live Tracking'); ?>
<?php $__env->startSection('page-subheading', 'Real-time GPS monitoring of all taxi units'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 520px; width: 100%; border-radius: 0.5rem; }
        .unit-item { cursor: pointer; transition: background-color 0.2s; }
        .unit-item:hover { background-color: #fefce8; }
        .unit-item.selected { background-color: #fef9c3; border-left: 3px solid #ca8a04; }
        .unit-panel { height: 520px; overflow-y: auto; }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-4 text-center">
                <p class="text-sm text-gray-500">Total Units</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['total'] ?? 0); ?></p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-4 text-center">
                <p class="text-sm text-gray-500">Active</p>
                <p class="text-2xl font-bold text-green-600"><?php echo e($stats['active'] ?? 0); ?></p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-4 text-center">
                <p class="text-sm text-gray-500">Idle</p>
                <p class="text-2xl font-bold text-yellow-600"><?php echo e($stats['idle'] ?? 0); ?></p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-4 text-center">
                <p class="text-sm text-gray-500">Offline</p>
                <p class="text-2xl font-bold text-red-600"><?php echo e($stats['offline'] ?? 0); ?></p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-4 text-center">
                <p class="text-sm text-gray-500">Avg Speed</p>
                <p class="text-2xl font-bold text-blue-600"><?php echo e(number_format($stats['avg_speed'] ?? 0, 0)); ?> km/h</p>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
                    </div>
                    <input type="text" id="unitSearchInput" onkeyup="filterUnits()"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none"
                        placeholder="Search unit...">
                </div>
            </div>
            <div class="sm:w-40">
                <select id="statusFilterSelect" onchange="filterUnits()" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="idle">Idle</option>
                    <option value="offline">Offline</option>
                </select>
            </div>
            <button type="button" id="autoRefreshBtn" onclick="toggleAutoRefresh()"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span id="autoRefreshText">Auto Refresh: ON</span>
            </button>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div id="map"></div>
            </div>
        </div>

        
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-4 py-3 border-b">
                    <h3 class="font-semibold text-gray-800">Units</h3>
                </div>
                <div class="unit-panel" id="unitList">
                    <?php $__empty_1 = true; $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div
                            class="unit-item p-4 border-b"
                            data-unit-id="<?php echo e($unit->id); ?>"
                            data-unit-number="<?php echo e($unit->unit_number); ?>"
                            data-lat="<?php echo e($unit->latitude ?? (14.5995 + ($loop->index * 0.005))); ?>"
                            data-lng="<?php echo e($unit->longitude ?? (120.9842 + ($loop->index * 0.005))); ?>"
                            data-status="<?php echo e($unit->gps_status ?? 'offline'); ?>"
                            onclick="selectUnit(this)"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900 text-sm"><?php echo e($unit->unit_number); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo e($unit->plate_number); ?></div>
                                    <div class="text-xs text-gray-500">
                                        Driver: <?php echo e($unit->current_driver ?? 'None'); ?>

                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        <?php if(($unit->gps_status ?? 'offline') === 'active'): ?> bg-green-100 text-green-800
                                        <?php elseif(($unit->gps_status ?? 'offline') === 'idle'): ?> bg-yellow-100 text-yellow-800
                                        <?php else: ?> bg-red-100 text-red-800
                                        <?php endif; ?>">
                                        <?php echo e(ucfirst($unit->gps_status ?? 'offline')); ?>

                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <?php echo e(number_format($unit->speed ?? 0, 0)); ?> km/h
                                    </div>
                                </div>
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                Updated: <?php echo e(isset($unit->last_gps_update) ? \Carbon\Carbon::parse($unit->last_gps_update)->diffForHumans() : 'N/A'); ?>

                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="p-8 text-center text-gray-500 text-sm">No units with GPS data</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Unit Details Modal -->
    <div id="unitDetailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-[1000] flex items-center justify-center">
        <div class="relative w-full max-w-md mx-auto my-6 p-6 shadow-xl rounded-xl bg-gray-50 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3">
                <p class="text-2xl font-bold">Unit Details</p>
                <div class="cursor-pointer z-50 rounded-full p-2 hover:bg-gray-200 transition" onclick="closeUnitDetailsModal()">
                    <i data-lucide="x" class="w-6 h-6 text-gray-500"></i>
                </div>
            </div>
            
            <div id="unitDetailsModalContent" class="mt-2 min-h-[200px] flex justify-center items-center">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Map initialization (Centered on Metro Manila)
        const map = L.map('map', {
            center: [14.5995, 120.9842],
            zoom: 12,
            minZoom: 10
        });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Force full re-render after layout is settled (fixes gray/broken tiles)
        setTimeout(() => { map.invalidateSize(); }, 250);

        const markers = {};
        let autoRefreshEnabled = true;
        let refreshInterval = null;

        // Color markers
        function markerIcon(status) {
            const colors = { active: '#22c55e', idle: '#eab308', offline: '#ef4444' };
            const color = colors[status] || '#6b7280';
            return L.divIcon({
                className: '',
                html: `<div style="width:16px;height:16px;border-radius:50%;background:${color};border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,0.3)"></div>`,
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });
        }

        // Add all unit markers
        document.querySelectorAll('.unit-item').forEach(el => {
            const lat = parseFloat(el.dataset.lat);
            const lng = parseFloat(el.dataset.lng);
            const status = el.dataset.status;
            const unitNumber = el.dataset.unitNumber;
            const id = el.dataset.unitId;

            if (!isNaN(lat) && !isNaN(lng)) {
                const marker = L.marker([lat, lng], { icon: markerIcon(status) }).addTo(map);
                marker.bindPopup(`
                    <div class="text-sm">
                        <strong>${unitNumber}</strong><br/>
                        <span style="color:${status === 'active' ? '#22c55e' : status === 'idle' ? '#ca8a04' : '#ef4444'}">${status.charAt(0).toUpperCase() + status.slice(1)}</span><br/>
                        <a href="javascript:void(0)" onclick="viewUnitDetails(${id})" style="color:#2563eb;font-size:0.75rem;">View Details</a>
                    </div>
                `);
                markers[id] = marker;
            }
        });

        function selectUnit(el) {
            document.querySelectorAll('.unit-item').forEach(i => i.classList.remove('selected'));
            el.classList.add('selected');

            const lat = parseFloat(el.dataset.lat);
            const lng = parseFloat(el.dataset.lng);
            const id = el.dataset.unitId;

            if (!isNaN(lat) && !isNaN(lng)) {
                map.setView([lat, lng], 16);
                if (markers[id]) {
                    markers[id].openPopup();
                }
            }
        }

        function filterUnits() {
            const search = document.getElementById('unitSearchInput').value.toLowerCase();
            const status = document.getElementById('statusFilterSelect').value;

            document.querySelectorAll('.unit-item').forEach(el => {
                const unitNum = el.dataset.unitNumber.toLowerCase();
                const unitStatus = el.dataset.status;
                const matchSearch = !search || unitNum.includes(search);
                const matchStatus = !status || unitStatus === status;
                el.style.display = (matchSearch && matchStatus) ? '' : 'none';
            });
        }

        function toggleAutoRefresh() {
            autoRefreshEnabled = !autoRefreshEnabled;
            const btn = document.getElementById('autoRefreshBtn');
            const text = document.getElementById('autoRefreshText');

            if (autoRefreshEnabled) {
                btn.classList.replace('bg-gray-500', 'bg-green-600');
                text.textContent = 'Auto Refresh: ON';
                startRefresh();
            } else {
                btn.classList.replace('bg-green-600', 'bg-gray-500');
                text.textContent = 'Auto Refresh: OFF';
                if (refreshInterval) {
                    clearInterval(refreshInterval);
                    refreshInterval = null;
                }
            }
        }

        function startRefresh() {
            if (refreshInterval) clearInterval(refreshInterval);
            refreshInterval = setInterval(() => {
                if (autoRefreshEnabled) location.reload();
            }, 30000);
        }

        function viewUnitDetails(id) {
            // Stop auto-refresh while viewing
            if (refreshInterval) clearInterval(refreshInterval);

            document.getElementById('unitDetailsModal').classList.remove('hidden');
            document.getElementById('unitDetailsModalContent').innerHTML = `
                <div class="flex justify-center py-10">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
                </div>
            `;
            
            fetch(`<?php echo e(url('/units/details-html')); ?>?id=${id}&type=map`)
                .then(response => {
                    if (!response.ok) throw Error('Network error');
                    return response.text();
                })
                .then(html => {
                    document.getElementById('unitDetailsModalContent').innerHTML = html;
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                })
                .catch(err => {
                    document.getElementById('unitDetailsModalContent').innerHTML = `
                        <div class="p-4 bg-red-50 text-red-600 rounded-lg text-center">
                            Failed to load unit details.
                        </div>
                    `;
                    console.error(err);
                });
        }

        function closeUnitDetailsModal() {
            document.getElementById('unitDetailsModal').classList.add('hidden');
            // Resume auto-refresh
            if (autoRefreshEnabled) startRefresh();
        }

        startRefresh();
    </script>

<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\eurotaxisystem\resources\views/live-tracking/index.blade.php ENDPATH**/ ?>