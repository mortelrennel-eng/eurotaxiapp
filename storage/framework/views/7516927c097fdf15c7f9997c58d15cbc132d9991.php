

<?php $__env->startSection('title', 'Unit Management - Euro System'); ?>
<?php $__env->startSection('page-heading', 'Unit Management'); ?>
<?php $__env->startSection('page-subheading', 'Manage your fleet of taxi units'); ?>

<?php $__env->startSection('content'); ?>
    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="<?php echo e(route('units.index')); ?>" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="<?php echo e($search); ?>"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none"
                        placeholder="Search by unit number, plate, make, or model...">
                </div>
            </div>
            <div class="md:w-48">
                <select name="status"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none">
                    <option value="">All Status</option>
                    <option value="active" <?php echo e($status_filter === 'active' ? 'selected' : ''); ?>>Active</option>
                    <option value="maintenance" <?php echo e($status_filter === 'maintenance' ? 'selected' : ''); ?>>Maintenance</option>
                    <option value="coding" <?php echo e($status_filter === 'coding' ? 'selected' : ''); ?>>Coding</option>
                    <option value="retired" <?php echo e($status_filter === 'retired' ? 'selected' : ''); ?>>Retired</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4"></i> Search
                </button>
                <button type="button" onclick="document.getElementById('addUnitModal').classList.remove('hidden')"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i> Add Unit
                </button>
                <a href="<?php echo e(route('units.import')); ?>" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                    <i data-lucide="upload" class="w-4 h-4"></i> Import CSV
                </a>
                <a href="<?php echo e(route('units.import')); ?>" 
                   class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-4 h-4"></i> Import Excel
                </a>
            </div>
        </form>
    </div>

    <!-- Units Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Units Management - Euro System</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Info
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle
                            Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Availability</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned
                            Drivers</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Boundary
                            Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Devices
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ROI</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $is_available = (!$unit->driver_id && !$unit->secondary_driver_id) && $unit->status === 'active';
                            $primary_driver = $unit->primary_driver ?? null;
                            $secondary_driver = $unit->secondary_driver ?? null;
                            $total_collected = $unit->total_collected ?? 0;
                            $purchase_cost = $unit->purchase_cost ?? 0;
                            $roi_achieved = $unit->roi_achieved ?? false;
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <div class="text-sm font-bold text-gray-900"><?php echo e($unit->unit_number); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e($unit->plate_number); ?></div>
                                    <?php if($unit->color): ?>
                                        <div class="text-xs text-gray-400">Color: <?php echo e($unit->color); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <div class="text-sm font-medium text-gray-900"><?php echo e($unit->make); ?> <?php echo e($unit->model); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e($unit->year); ?></div>
                                    <div class="flex items-center gap-2 text-xs">
                                        <span
                                            class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full"><?php echo e(ucfirst($unit->unit_type ?? 'new')); ?></span>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">
                                            <i data-lucide="droplet" class="w-3 h-3 inline"></i>
                                            <?php echo e(ucfirst($unit->fuel_status ?? 'full')); ?>

                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2 py-1 text-xs rounded-full <?php echo e($is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                        <?php echo e($is_available ? 'Available' : 'Occupied'); ?>

                                    </span>
                                    <?php if($is_available): ?>
                                        <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                                    <?php else: ?>
                                        <i data-lucide="users" class="w-4 h-4 text-red-600"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <?php if($unit->driver_id && $primary_driver): ?>
                                        <?php $d1 = explode('|', $primary_driver); ?>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-medium text-gray-900">Driver 1:</span>
                                            <span class="text-xs text-gray-700"><?php echo e($d1[0] ?? ''); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-xs text-gray-400">No Driver 1</div>
                                    <?php endif; ?>
                                    <?php if($unit->secondary_driver_id && $secondary_driver): ?>
                                        <?php $d2 = explode('|', $secondary_driver); ?>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-medium text-gray-900">Driver 2:</span>
                                            <span class="text-xs text-gray-700"><?php echo e($d2[0] ?? ''); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-xs text-gray-400">No Driver 2</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full
                                        <?php if($unit->status === 'active'): ?> bg-green-100 text-green-800
                                        <?php elseif($unit->status === 'maintenance'): ?> bg-yellow-100 text-yellow-800
                                        <?php elseif($unit->status === 'coding'): ?> bg-red-100 text-red-800
                                        <?php else: ?> bg-gray-100 text-gray-800
                                        <?php endif; ?>">
                                    <?php echo e(ucfirst($unit->status)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e(formatCurrency($unit->boundary_rate)); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    <?php if($unit->gps_device_count > 0): ?>
                                        <span
                                            class="px-3 py-1 bg-green-100 text-green-800 rounded-full flex items-center gap-1 text-xs font-medium">
                                            <i data-lucide="map-pin" class="w-3 h-3"></i> GPS: <?php echo e($unit->gps_device_count); ?>

                                        </span>
                                    <?php endif; ?>
                                    <?php if($unit->dashcam_device_count > 0): ?>
                                        <span
                                            class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full flex items-center gap-1 text-xs font-medium">
                                            <i data-lucide="camera" class="w-3 h-3"></i> Cam: <?php echo e($unit->dashcam_device_count); ?>

                                        </span>
                                    <?php endif; ?>
                                    <?php if(!$unit->gps_device_count && !$unit->dashcam_device_count): ?>
                                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">No
                                            Devices</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($purchase_cost > 0): ?>
                                    <?php if($roi_achieved): ?>
                                        <span class="text-green-600 flex items-center gap-1">
                                            <i data-lucide="check-circle" class="w-4 h-4"></i> Achieved
                                        </span>
                                    <?php else: ?>
                                        <?php $pct = $purchase_cost > 0 ? ($total_collected / $purchase_cost) * 100 : 0; ?>
                                        <div class="text-gray-600">
                                            <div class="flex items-center gap-1 text-xs">
                                                <i data-lucide="trending-up" class="w-4 h-4"></i>
                                                <?php echo e(number_format($pct, 1)); ?>%
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-gray-400 flex items-center gap-1"><i data-lucide="clock" class="w-4 h-4"></i>
                                        No Cost Set</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex gap-2">
                                    <button onclick="editUnit(<?php echo e($unit->id); ?>)"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit Unit">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="viewUnitDetails(<?php echo e($unit->id); ?>)"
                                        class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="View Details">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>
                                    <form method="POST" action="<?php echo e(route('units.destroy', $unit->id)); ?>"
                                        onsubmit="return confirm('Delete unit <?php echo e($unit->unit_number); ?>?')">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                                            title="Delete Unit">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                <i data-lucide="car" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                                <p>No units found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($pagination['total_pages'] > 1): ?>
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <?php echo e($pagination['total_items']); ?> results / Page <?php echo e($pagination['page']); ?> of
                        <?php echo e($pagination['total_pages']); ?>

                    </div>
                    <div class="flex items-center gap-2">
                        <?php if($pagination['has_prev']): ?>
                            <a href="?page=<?php echo e($pagination['prev_page']); ?>&search=<?php echo e(urlencode($search)); ?>&status=<?php echo e(urlencode($status_filter)); ?>"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </a>
                        <?php endif; ?>
                        <?php for($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++): ?>
                            <a href="?page=<?php echo e($i); ?>&search=<?php echo e(urlencode($search)); ?>&status=<?php echo e(urlencode($status_filter)); ?>"
                                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium
                                           <?php echo e($i === $pagination['page'] ? 'z-10 bg-yellow-50 border-yellow-500 text-yellow-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'); ?>">
                                <?php echo e($i); ?>

                            </a>
                        <?php endfor; ?>
                        <?php if($pagination['has_next']): ?>
                            <a href="?page=<?php echo e($pagination['next_page']); ?>&search=<?php echo e(urlencode($search)); ?>&status=<?php echo e(urlencode($status_filter)); ?>"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    
    <div id="addUnitModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">

            
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 p-6 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-white bg-opacity-20 rounded-lg">
                            <i data-lucide="car" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-white">Add New Unit</h3>
                            <p class="text-yellow-100 text-sm">Enter vehicle information and add devices</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('addUnitModal').classList.add('hidden'); resetAddUnitModal()"
                        class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>

            
            <form method="POST" action="<?php echo e(route('units.store')); ?>" id="addUnitForm" class="p-6">
                <?php echo csrf_field(); ?>

                
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">Basic Information</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Unit Number <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="hash" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" name="unit_number" required
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                    placeholder="e.g., TAXI-001"
                                    oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Plate Number <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="credit-card" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" name="plate_number" id="addPlateNumber" required
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                    placeholder="e.g., ABC 123"
                                    oninput="this.value = this.value.toUpperCase(); addUnitUpdateCoding()">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                            <select name="status" id="addUnitStatus" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                <option value="active">ðŸŸ¢ Active</option>
                                <option value="maintenance">ðŸ”§ Maintenance</option>
                                <option value="coding">ðŸ“ Coding</option>
                                <option value="retired">âš« Retired</option>
                            </select>
                        </div>
                    </div>
                </div>

                
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <i data-lucide="truck" class="w-5 h-5 text-green-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">Vehicle Details</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Make <span class="text-red-500">*</span></label>
                            <input type="text" name="make" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                placeholder="e.g., Toyota, Honda, Nissan"
                                oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Model <span class="text-red-500">*</span></label>
                            <input type="text" name="model" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                placeholder="e.g., Vios, Civic, Sentra"
                                oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Year <span class="text-red-500">*</span></label>
                            <input type="number" name="year" required min="2000" max="<?php echo e(date('Y')); ?>" value="<?php echo e(date('Y')); ?>"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                placeholder="e.g., 2023">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Color</label>
                            <input type="text" name="color" value="White"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                placeholder="e.g., White, Red, Blue">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Unit Type</label>
                            <select name="unit_type"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                <option value="new">ðŸ†• New</option>
                                <option value="used">ðŸ“¦ Used</option>
                                <option value="rented">ðŸ”„ Rented</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Fuel Status</label>
                            <select name="fuel_status"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                <option value="full">â›½ Full</option>
                                <option value="half">â›½ Half</option>
                                <option value="low">â›½ Low</option>
                                <option value="empty">â›½ Empty</option>
                            </select>
                        </div>
                    </div>
                </div>

                
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <i data-lucide="dollar-sign" class="w-5 h-5 text-purple-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">Financial Information</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Boundary Rate <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">₱</span>
                                </div>
                                <input type="number" name="boundary_rate" id="addBoundaryRate" required step="0.01" value="1100.00"
                                    class="w-full pl-8 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                    onblur="this.value = parseFloat(this.value).toFixed(2)">
                            </div>
                            <p class="text-xs text-gray-500">Daily boundary collection target</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Purchase Cost</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">₱</span>
                                </div>
                                <input type="number" name="purchase_cost" step="0.01" value="0.00"
                                    class="w-full pl-8 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                    onblur="this.value = parseFloat(this.value || 0).toFixed(2)">
                            </div>
                            <p class="text-xs text-gray-500">Total purchase amount</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Purchase Date</label>
                            <input type="date" name="purchase_date"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <p class="text-xs text-gray-500">When the unit was purchased</p>
                        </div>
                    </div>
                </div>

                
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <i data-lucide="users" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">Driver Assignment</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Primary Driver</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                    <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="add_driver1_search" autocomplete="off"
                                    placeholder="Start typing to search drivers..."
                                    class="w-full pl-10 pr-10 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                    onkeyup="addUnitFilterDrivers('add_driver1')"
                                    onfocus="addUnitShowDropdown('add_driver1')"
                                    onblur="setTimeout(()=>addUnitHideDropdown('add_driver1'), 200)"
                                    oninput="addUnitFilterDrivers('add_driver1')">
                                <button type="button" onclick="addUnitClearDriver('add_driver1')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i data-lucide="x" class="w-4 h-4 text-gray-400 hover:text-gray-600"></i>
                                </button>
                                <select id="add_driver1" name="driver_id" class="hidden">
                                    <option value="">Select Primary Driver</option>
                                    <?php $__currentLoopData = $all_drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($driver->id); ?>" data-name="<?php echo e($driver->full_name); ?>" data-license="<?php echo e($driver->license_number ?? ''); ?>">
                                            <?php echo e($driver->full_name); ?> - <?php echo e($driver->license_number ?? 'No License'); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <div id="add_driver1_dropdown" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden"></div>
                            </div>
                            <p class="text-xs text-gray-500">Main driver assigned to this unit</p>
                        </div>

                        
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Secondary Driver (Optional)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                    <i data-lucide="user-plus" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="add_driver2_search" autocomplete="off"
                                    placeholder="Start typing to search drivers..."
                                    class="w-full pl-10 pr-10 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                    onkeyup="addUnitFilterDrivers('add_driver2')"
                                    onfocus="addUnitShowDropdown('add_driver2')"
                                    onblur="setTimeout(()=>addUnitHideDropdown('add_driver2'), 200)"
                                    oninput="addUnitFilterDrivers('add_driver2')">
                                <button type="button" onclick="addUnitClearDriver('add_driver2')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i data-lucide="x" class="w-4 h-4 text-gray-400 hover:text-gray-600"></i>
                                </button>
                                <select id="add_driver2" name="secondary_driver_id" class="hidden">
                                    <option value="">Select Secondary Driver</option>
                                    <?php $__currentLoopData = $all_drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($driver->id); ?>" data-name="<?php echo e($driver->full_name); ?>" data-license="<?php echo e($driver->license_number ?? ''); ?>">
                                            <?php echo e($driver->full_name); ?> - <?php echo e($driver->license_number ?? 'No License'); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <div id="add_driver2_dropdown" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden"></div>
                            </div>
                            <p class="text-xs text-gray-500">Backup or relief driver (optional)</p>
                        </div>

                        
                        <div class="pt-2">
                            <button type="button" onclick="addUnitClearDriver('add_driver1'); addUnitClearDriver('add_driver2')"
                                class="w-full bg-red-50 text-red-600 py-2 px-4 rounded-lg hover:bg-red-100 transition-colors flex items-center justify-center gap-2 border border-red-200">
                                <i data-lucide="user-x" class="w-4 h-4"></i> Remove All Drivers
                            </button>
                            <p class="text-xs text-gray-500 mt-1">Clear both driver assignments for this unit</p>
                        </div>
                    </div>
                </div>

                
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-indigo-100 rounded-lg">
                            <i data-lucide="calendar" class="w-5 h-5 text-indigo-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">Coding Information</h4>
                    </div>

                    
                    <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex items-center gap-2 mb-3">
                            <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                            <h5 class="font-semibold text-blue-900">MMDA Coding Schedule (Metro Manila)</h5>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-xs">
                            <div class="flex items-center gap-1"><span class="font-medium">Mon:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">1, 2</span></div>
                            <div class="flex items-center gap-1"><span class="font-medium">Tue:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">3, 4</span></div>
                            <div class="flex items-center gap-1"><span class="font-medium">Wed:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">5, 6</span></div>
                            <div class="flex items-center gap-1"><span class="font-medium">Thu:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">7, 8</span></div>
                            <div class="flex items-center gap-1"><span class="font-medium">Fri:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">9, 0</span></div>
                        </div>
                        <p class="text-xs text-blue-600 mt-2">Based on the last digit of your plate number</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Coding Day</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="calendar" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="addCodingDay" name="coding_day" readonly
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-50"
                                    placeholder="Auto-generated">
                            </div>
                            <p class="text-xs text-gray-500">Automatically calculated from plate number</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Next Coding Date</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="calendar" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="addNextCodingDate" readonly
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-50"
                                    placeholder="Auto-generated">
                            </div>
                            <p class="text-xs text-gray-500">Next scheduled coding date</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Days Until Next Coding</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="clock" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="addDaysUntilCoding" readonly
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-50"
                                    placeholder="Auto-calculated">
                            </div>
                            <p class="text-xs text-gray-500">Days remaining until next coding</p>
                        </div>
                    </div>
                    <div id="addCodingStatusDisplay" class="mt-4"></div>
                </div>

                
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-indigo-100 rounded-lg">
                            <i data-lucide="smartphone" class="w-5 h-5 text-indigo-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">Device Management</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-indigo-400 transition-colors">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="map-pin" class="w-5 h-5 text-indigo-600"></i>
                                    <h5 class="font-semibold text-gray-900">GPS Devices</h5>
                                </div>
                                <button type="button" onclick="addUnitAddGPS()" class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition-colors">
                                    + Add GPS
                                </button>
                            </div>
                            <div id="addGPSDevicesList" class="space-y-2">
                                <p class="text-sm text-gray-500 text-center py-2">No GPS devices added</p>
                            </div>
                        </div>
                        
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-purple-400 transition-colors">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="camera" class="w-5 h-5 text-purple-600"></i>
                                    <h5 class="font-semibold text-gray-900">Dashcam Devices</h5>
                                </div>
                                <button type="button" onclick="addUnitAddDashcam()" class="px-3 py-1 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition-colors">
                                    + Add Dashcam
                                </button>
                            </div>
                            <div id="addDashcamDevicesList" class="space-y-2">
                                <p class="text-sm text-gray-500 text-center py-2">No dashcam devices added</p>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="flex gap-3 mt-6 pt-4 border-t">
                    <button type="submit" class="flex-1 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold flex items-center justify-center gap-2">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i> Add Unit
                    </button>
                    <button type="button" onclick="document.getElementById('addUnitModal').classList.add('hidden'); resetAddUnitModal()"
                        class="flex-1 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="editUnitModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">

            
            <div class="bg-gradient-to-r from-blue-500 to-blue-700 p-6 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-white bg-opacity-20 rounded-lg">
                            <i data-lucide="edit-2" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-white">Edit Unit</h3>
                            <p class="text-blue-100 text-sm">Update vehicle information and settings</p>
                        </div>
                    </div>
                    <button onclick="closeEditUnitModal()"
                        class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>

            
            <form method="POST" id="editUnitForm" class="p-6">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

                
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-blue-100 rounded-lg"><i data-lucide="info" class="w-5 h-5 text-blue-600"></i></div>
                        <h4 class="text-lg font-semibold text-gray-900">Basic Information</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Unit Number <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="hash" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" name="unit_number" id="editUnitNumber" required
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Plate Number <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="credit-card" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" name="plate_number" id="editPlateNumber" required
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    oninput="this.value = this.value.toUpperCase(); editUnitUpdateCoding()">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                            <select name="status" id="editStatus" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="active">ðŸŸ¢ Active</option>
                                <option value="maintenance">ðŸ”§ Maintenance</option>
                                <option value="coding">ðŸ“ Coding</option>
                                <option value="retired">âš« Retired</option>
                            </select>
                        </div>
                    </div>
                </div>

                
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-green-100 rounded-lg"><i data-lucide="truck" class="w-5 h-5 text-green-600"></i></div>
                        <h4 class="text-lg font-semibold text-gray-900">Vehicle Details</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Make <span class="text-red-500">*</span></label>
                            <input type="text" name="make" id="editMake" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Model <span class="text-red-500">*</span></label>
                            <input type="text" name="model" id="editModel" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Year <span class="text-red-500">*</span></label>
                            <input type="number" name="year" id="editYear" min="2000" max="<?php echo e(date('Y')); ?>"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Color</label>
                            <input type="text" name="color" id="editColor"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Unit Type</label>
                            <select name="unit_type" id="editUnitType"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="new">ðŸ†• New</option>
                                <option value="used">ðŸ“¦ Used</option>
                                <option value="rented">ðŸ”„ Rented</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Fuel Status</label>
                            <select name="fuel_status" id="editFuelStatus"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="full">â›½ Full</option>
                                <option value="half">â›½ Half</option>
                                <option value="low">â›½ Low</option>
                                <option value="empty">â›½ Empty</option>
                            </select>
                        </div>
                    </div>
                </div>

                
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-purple-100 rounded-lg"><i data-lucide="dollar-sign" class="w-5 h-5 text-purple-600"></i></div>
                        <h4 class="text-lg font-semibold text-gray-900">Financial Information</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Boundary Rate <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">₱</span>
                                </div>
                                <input type="number" name="boundary_rate" id="editBoundaryRate" step="0.01"
                                    class="w-full pl-8 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    onblur="this.value = parseFloat(this.value || 0).toFixed(2)">
                            </div>
                            <p class="text-xs text-gray-500">Daily boundary collection target</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Purchase Cost</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">₱</span>
                                </div>
                                <input type="number" name="purchase_cost" id="editPurchaseCost" step="0.01"
                                    class="w-full pl-8 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    onblur="this.value = parseFloat(this.value || 0).toFixed(2)">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Purchase Date</label>
                            <input type="date" name="purchase_date" id="editPurchaseDate"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-blue-100 rounded-lg"><i data-lucide="users" class="w-5 h-5 text-blue-600"></i></div>
                        <h4 class="text-lg font-semibold text-gray-900">Driver Assignment</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Primary Driver</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                    <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_driver1_search" autocomplete="off"
                                    placeholder="Start typing to search drivers..."
                                    class="w-full pl-10 pr-10 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    onkeyup="editUnitFilterDrivers('edit_driver1')"
                                    onfocus="editUnitShowDropdown('edit_driver1')"
                                    onblur="setTimeout(()=>editUnitHideDropdown('edit_driver1'), 200)"
                                    oninput="editUnitFilterDrivers('edit_driver1')">
                                <button type="button" onclick="editUnitClearDriver('edit_driver1')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i data-lucide="x" class="w-4 h-4 text-gray-400 hover:text-gray-600"></i>
                                </button>
                                <select id="edit_driver1" name="driver_id" class="hidden">
                                    <option value="">No Driver</option>
                                    <?php $__currentLoopData = $all_drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($d->id); ?>" data-name="<?php echo e($d->full_name); ?>" data-license="<?php echo e($d->license_number ?? ''); ?>">
                                            <?php echo e($d->full_name); ?> - <?php echo e($d->license_number ?? 'No License'); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <div id="edit_driver1_dropdown" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden"></div>
                            </div>
                            <p class="text-xs text-gray-500">Main driver assigned to this unit</p>
                        </div>

                        
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Secondary Driver (Optional)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                    <i data-lucide="user-plus" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_driver2_search" autocomplete="off"
                                    placeholder="Start typing to search drivers..."
                                    class="w-full pl-10 pr-10 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    onkeyup="editUnitFilterDrivers('edit_driver2')"
                                    onfocus="editUnitShowDropdown('edit_driver2')"
                                    onblur="setTimeout(()=>editUnitHideDropdown('edit_driver2'), 200)"
                                    oninput="editUnitFilterDrivers('edit_driver2')">
                                <button type="button" onclick="editUnitClearDriver('edit_driver2')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i data-lucide="x" class="w-4 h-4 text-gray-400 hover:text-gray-600"></i>
                                </button>
                                <select id="edit_driver2" name="secondary_driver_id" class="hidden">
                                    <option value="">No Driver</option>
                                    <?php $__currentLoopData = $all_drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($d->id); ?>" data-name="<?php echo e($d->full_name); ?>" data-license="<?php echo e($d->license_number ?? ''); ?>">
                                            <?php echo e($d->full_name); ?> - <?php echo e($d->license_number ?? 'No License'); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <div id="edit_driver2_dropdown" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden"></div>
                            </div>
                            <p class="text-xs text-gray-500">Backup or relief driver (optional)</p>
                        </div>

                        
                        <div class="pt-2">
                            <button type="button" onclick="editUnitClearDriver('edit_driver1'); editUnitClearDriver('edit_driver2')"
                                class="w-full bg-red-50 text-red-600 py-2 px-4 rounded-lg hover:bg-red-100 transition-colors flex items-center justify-center gap-2 border border-red-200">
                                <i data-lucide="user-x" class="w-4 h-4"></i> Remove All Drivers
                            </button>
                            <p class="text-xs text-gray-500 mt-1">Clear both driver assignments for this unit</p>
                        </div>
                    </div>
                </div>

                
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-indigo-100 rounded-lg"><i data-lucide="calendar" class="w-5 h-5 text-indigo-600"></i></div>
                        <h4 class="text-lg font-semibold text-gray-900">Coding Information</h4>
                    </div>
                    <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex items-center gap-2 mb-3">
                            <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                            <h5 class="font-semibold text-blue-900">MMDA Coding Schedule (Metro Manila)</h5>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-xs">
                            <div class="flex items-center gap-1"><span class="font-medium">Mon:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">1, 2</span></div>
                            <div class="flex items-center gap-1"><span class="font-medium">Tue:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">3, 4</span></div>
                            <div class="flex items-center gap-1"><span class="font-medium">Wed:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">5, 6</span></div>
                            <div class="flex items-center gap-1"><span class="font-medium">Thu:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">7, 8</span></div>
                            <div class="flex items-center gap-1"><span class="font-medium">Fri:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">9, 0</span></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Coding Day</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="calendar" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="editCodingDay" name="coding_day" readonly
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-50"
                                    placeholder="Auto-generated">
                            </div>
                            <p class="text-xs text-gray-500">Auto-calculated from plate number</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Next Coding Date</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="calendar" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="editNextCodingDate" readonly
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-50"
                                    placeholder="Auto-generated">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Days Until Next Coding</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="clock" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="editDaysUntilCoding" readonly
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-50"
                                    placeholder="Auto-calculated">
                            </div>
                        </div>
                    </div>
                    <div id="editCodingStatusDisplay" class="mt-4"></div>
                </div>

                
                <div class="flex gap-3 mt-6 pt-4 border-t">
                    <button type="submit" class="flex-1 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-5 h-5"></i> Update Unit
                    </button>
                    <button type="button" onclick="closeEditUnitModal()"
                        class="flex-1 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="unitDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-white bg-opacity-20 rounded-lg">
                            <i data-lucide="info" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Unit Details</h3>
                            <p class="text-blue-100">Complete unit information and management</p>
                        </div>
                    </div>
                    <button onclick="closeUnitDetailsModal()" class="text-white hover:text-gray-200">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>

            
            <div id="unitDetailsContent" class="p-6">
                
                <div class="text-center py-8">
                    <i data-lucide="loader-2" class="w-8 h-8 mx-auto mb-4 text-gray-300 animate-spin"></i>
                    <p class="text-gray-500">Loading unit details...</p>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function editUnit(id) {
            fetch('<?php echo e(route("units.details")); ?>?id=' + id, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => {
                if (!r.ok) throw new Error('Server returned HTTP ' + r.status);
                return r.json();
            })
            .then(data => {
                // Guard: check for errors
                if (data.error) {
                    alert('Error: ' + data.error);
                    return;
                }
                const unit = data.unit;
                if (!unit) {
                    alert('Unit not found. Please refresh the page and try again.');
                    return;
                }

                // Basic info
                document.getElementById('editUnitNumber').value = unit.unit_number || '';
                document.getElementById('editPlateNumber').value = unit.plate_number || '';
                document.getElementById('editStatus').value = unit.status || 'active';

                // Vehicle
                document.getElementById('editMake').value = unit.make || '';
                document.getElementById('editModel').value = unit.model || '';
                document.getElementById('editYear').value = unit.year || '';
                document.getElementById('editColor').value = unit.color || '';
                document.getElementById('editUnitType').value = unit.unit_type || 'new';
                document.getElementById('editFuelStatus').value = unit.fuel_status || 'full';

                // Financial
                document.getElementById('editBoundaryRate').value = parseFloat(unit.boundary_rate || 0).toFixed(2);
                document.getElementById('editPurchaseCost').value = parseFloat(unit.purchase_cost || 0).toFixed(2);
                document.getElementById('editPurchaseDate').value = unit.purchase_date || '';

                // Drivers - set hidden selects and populate search inputs
                const d1Val = unit.driver_id || '';
                const d2Val = unit.secondary_driver_id || '';
                document.getElementById('edit_driver1').value = d1Val;
                document.getElementById('edit_driver2').value = d2Val;

                // Populate search inputs from select option text
                if (d1Val) {
                    const opt1 = document.querySelector(`#edit_driver1 option[value="${d1Val}"]`);
                    document.getElementById('edit_driver1_search').value = opt1 ? opt1.getAttribute('data-name') + (opt1.getAttribute('data-license') ? ' - ' + opt1.getAttribute('data-license') : '') : '';
                } else {
                    document.getElementById('edit_driver1_search').value = '';
                }
                if (d2Val) {
                    const opt2 = document.querySelector(`#edit_driver2 option[value="${d2Val}"]`);
                    document.getElementById('edit_driver2_search').value = opt2 ? opt2.getAttribute('data-name') + (opt2.getAttribute('data-license') ? ' - ' + opt2.getAttribute('data-license') : '') : '';
                } else {
                    document.getElementById('edit_driver2_search').value = '';
                }

                // Coding info - compute from plate number using top-level coding_day from API
                if (unit.plate_number) {
                    editUnitUpdateCodingFromPlate(unit.plate_number, data.coding_day || unit.coding_day || '');
                } else {
                    document.getElementById('editCodingDay').value = data.coding_day || unit.coding_day || '';
                    document.getElementById('editNextCodingDate').value = '';
                    document.getElementById('editDaysUntilCoding').value = '';
                }

                // Set form action
                document.getElementById('editUnitForm').action = '/units/' + id;

                // Show modal
                document.getElementById('editUnitModal').classList.remove('hidden');
                lucide.createIcons();
            })
            .catch(err => alert('Failed to load unit: ' + err));
        }

        function closeEditUnitModal() {
            document.getElementById('editUnitModal').classList.add('hidden');
            document.getElementById('editCodingStatusDisplay').innerHTML = '';
        }

        // Edit Unit - Searchable Driver Dropdowns
        function editUnitShowDropdown(driverType) {
            editUnitFilterDrivers(driverType);
            document.getElementById(driverType + '_dropdown').classList.remove('hidden');
        }
        function editUnitHideDropdown(driverType) {
            document.getElementById(driverType + '_dropdown').classList.add('hidden');
        }
        function editUnitFilterDrivers(driverType) {
            const searchInput = document.getElementById(driverType + '_search');
            const select = document.getElementById(driverType);
            const dropdown = document.getElementById(driverType + '_dropdown');
            const query = searchInput ? searchInput.value.toLowerCase() : '';
            const options = Array.from(select.options).slice(1);

            let html = '';
            options.forEach(opt => {
                const name = opt.getAttribute('data-name') || '';
                const license = opt.getAttribute('data-license') || '';
                if (!query || name.toLowerCase().includes(query) || license.toLowerCase().includes(query)) {
                    html += `<div class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                 onmousedown="editUnitSelectDriver('${driverType}','${opt.value}','${name.replace(/'/g,"\\'")}','${license.replace(/'/g,"\\'")}')">
                                <div class="font-medium text-gray-900">${name}</div>
                                <div class="text-sm text-gray-500">${license || 'No License'}</div>
                             </div>`;
                }
            });
            dropdown.innerHTML = html || '<p class="px-4 py-3 text-sm text-gray-500">No drivers found</p>';
            dropdown.classList.remove('hidden');
        }
        function editUnitSelectDriver(driverType, value, name, license) {
            document.getElementById(driverType).value = value;
            document.getElementById(driverType + '_search').value = name + (license ? ' - ' + license : '');
            editUnitHideDropdown(driverType);
        }
        function editUnitClearDriver(driverType) {
            document.getElementById(driverType).value = '';
            document.getElementById(driverType + '_search').value = '';
        }

        // Edit Unit - coding helper (shared logic)
        function editUnitGetLastDigit(plateNumber) {
            plateNumber = plateNumber.toUpperCase().trim().replace(/[^A-Z0-9]/g, '');
            if (plateNumber.length > 0) {
                const last = plateNumber.slice(-1);
                if (/[A-Z]/.test(last)) return last.charCodeAt(0) - 64;
                if (/[0-9]/.test(last)) return parseInt(last);
            }
            return null;
        }
        function editUnitUpdateCodingFromPlate(plate, existingCodingDay) {
            const schedule = { Monday:[1,2], Tuesday:[3,4], Wednesday:[5,6], Thursday:[7,8], Friday:[9,0] };
            const lastDigit = editUnitGetLastDigit(plate);
            let codingDay = existingCodingDay || '';
            if (!codingDay) {
                for (const [day, endings] of Object.entries(schedule)) {
                    if (endings.includes(lastDigit)) { codingDay = day; break; }
                }
            }

            const today = new Date();
            const daysOfWeek = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            const todayName = daysOfWeek[today.getDay()];
            let isCodingToday = (todayName === codingDay);
            let daysUntil = 0;
            let nextDate = new Date(today);

            if (!isCodingToday && codingDay) {
                for (let i = 1; i <= 7; i++) {
                    const test = new Date(today);
                    test.setDate(today.getDate() + i);
                    if (daysOfWeek[test.getDay()] === codingDay) { nextDate = test; daysUntil = i; break; }
                }
            }

            document.getElementById('editCodingDay').value = codingDay || '';
            document.getElementById('editNextCodingDate').value = codingDay ? nextDate.toLocaleDateString('en-US') : '';
            document.getElementById('editDaysUntilCoding').value = codingDay ? (isCodingToday ? 0 : daysUntil) : '';

            const display = document.getElementById('editCodingStatusDisplay');
            if (display) {
                if (!codingDay) {
                    display.innerHTML = '';
                } else if (isCodingToday) {
                    display.innerHTML = `<div class="p-3 rounded-lg border-2 border-red-500 bg-red-50 flex items-center gap-2"><i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i><div><p class="text-sm font-semibold text-red-800">CODING TODAY!</p><p class="text-xs text-red-600">This unit is scheduled for coding today (${codingDay})</p></div></div>`;
                } else if (daysUntil === 1) {
                    display.innerHTML = `<div class="p-3 rounded-lg border-2 border-yellow-500 bg-yellow-50 flex items-center gap-2"><i data-lucide="clock" class="w-5 h-5 text-yellow-600"></i><div><p class="text-sm font-semibold text-yellow-800">CODING TOMORROW</p><p class="text-xs text-yellow-600">Next coding: ${codingDay}</p></div></div>`;
                } else {
                    display.innerHTML = `<div class="p-3 rounded-lg border-2 border-blue-400 bg-blue-50 flex items-center gap-2"><i data-lucide="calendar" class="w-5 h-5 text-blue-600"></i><div><p class="text-sm font-semibold text-blue-800">NEXT CODING</p><p class="text-xs text-blue-600">${codingDay} (${daysUntil} days)</p></div></div>`;
                }
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        }
        function editUnitUpdateCoding() {
            const plate = document.getElementById('editPlateNumber')?.value || '';
            if (plate) editUnitUpdateCodingFromPlate(plate, '');
        }

        // =============================================
        // VIEW UNIT DETAILS - Matching backup's 8-tab structure
        // =============================================
        let currentViewUnitId = null;

        function viewUnitDetails(id) {
            currentViewUnitId = id;
            document.getElementById('unitDetailsModal').classList.remove('hidden');

            // Show loading state inside content div (same as backup)
            document.getElementById('unitDetailsContent').innerHTML = `
                <div class="text-center py-8">
                    <i data-lucide="loader-2" class="w-8 h-8 mx-auto mb-4 text-gray-300 animate-spin"></i>
                    <p class="text-gray-500">Loading unit details...</p>
                </div>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();

            fetch('<?php echo e(route("units.details")); ?>?id=' + id, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => {
                if (!r.ok) throw new Error('Server returned HTTP ' + r.status);
                return r.json();
            })
            .then(data => {
                if (data.error) {
                    document.getElementById('unitDetailsContent').innerHTML = `<div class="text-center py-8 text-red-500"><i data-lucide="alert-circle" class="w-12 h-12 mx-auto mb-4"></i><p>${data.error}</p></div>`;
                    lucide.createIcons();
                    return;
                }
                const unit = data.unit;
                if (!unit) {
                    document.getElementById('unitDetailsContent').innerHTML = `<div class="text-center py-8 text-red-500"><i data-lucide="alert-circle" class="w-12 h-12 mx-auto mb-4"></i><p>Unit not found or failed to load.</p></div>`;
                    lucide.createIcons();
                    return;
                }

                const assignedDrivers = data.assigned_drivers || [];
                const roi = data.roi_data || {};
                const maint = data.maintenance_records || [];
                const locInfo = data.location_info || {};
                const dashcam = data.dashcam_info || {};

                // --- Coding calculations (matching backup logic) ---
                const plate = unit.plate_number || '';
                const lastChar = plate.replace(/[^A-Z0-9]/gi, '').slice(-1).toUpperCase();
                const lastDigit = /[0-9]/.test(lastChar) ? parseInt(lastChar) : (lastChar.charCodeAt(0) - 64);
                const codingSchedule = { Monday:[1,2], Tuesday:[3,4], Wednesday:[5,6], Thursday:[7,8], Friday:[9,0] };
                let codingDay = data.coding_day || 'Not Set';
                let nextCodingDate = '', daysUntilCoding = 0;
                const today = new Date();
                const dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                const todayName = dayNames[today.getDay()];
                if (codingDay && codingDay !== 'Not Set') {
                    if (todayName === codingDay) {
                        nextCodingDate = today.toLocaleDateString('en-US', {month:'short', day:'2-digit', year:'numeric'});
                        daysUntilCoding = 0;
                    } else {
                        const cdIdx = dayNames.indexOf(codingDay);
                        let diff = (cdIdx - today.getDay() + 7) % 7;
                        if (diff === 0) diff = 7;
                        const nextDate = new Date(today);
                        nextDate.setDate(today.getDate() + diff);
                        nextCodingDate = nextDate.toLocaleDateString('en-US', {month:'short', day:'2-digit', year:'numeric'});
                        daysUntilCoding = diff;
                    }
                }

                // --- Build the 8-tab HTML matching backup's unit_details_modal.php ---
                const roiPct = parseFloat(roi.roi_percentage || 0);
                const roiColor = roiPct > 0 ? 'green' : 'red';

                let driversOverviewHtml = '';
                if (assignedDrivers.length > 0) {
                    assignedDrivers.forEach(d => {
                        driversOverviewHtml += `<div class="bg-gray-50 p-3 rounded">
                            <div class="font-medium">${d.full_name || ''}</div>
                            <div class="text-sm text-gray-600">${d.license_number || ''}</div>
                            <div class="text-sm text-gray-600">Contact: ${d.contact_number || 'N/A'}</div>
                        </div>`;
                    });
                }

                let driversTabHtml = '';
                if (assignedDrivers.length > 0) {
                    assignedDrivers.forEach(d => {
                        driversTabHtml += `<div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h5 class="font-semibold text-gray-900">${d.full_name || ''}</h5>
                                    <p class="text-sm text-gray-600">License: ${d.license_number || ''}</p>
                                    <p class="text-sm text-gray-600">Contact: ${d.contact_number || 'N/A'}</p>
                                    <p class="text-sm text-gray-600">Email: ${d.email || 'N/A'}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                            </div>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div><span class="text-gray-600">License Number:</span><p class="font-medium">${d.license_number || 'N/A'}</p></div>
                                <div><span class="text-gray-600">Contact:</span><p class="font-medium">${d.contact_number || 'N/A'}</p></div>
                                <div><span class="text-gray-600">Email:</span><p class="font-medium">${d.email || 'N/A'}</p></div>
                                <div><span class="text-gray-600">Daily Target:</span><p class="font-medium">₱${parseFloat(d.daily_boundary_target || 1100).toLocaleString('en-PH', {minimumFractionDigits:2})}</p></div>
                                <div><span class="text-gray-600">Hire Date:</span><p class="font-medium">${d.hire_date || 'Not set'}</p></div>
                                <div><span class="text-gray-600">License Expiry:</span><p class="font-medium">${d.license_expiry || 'Not set'}</p></div>
                            </div>
                        </div>`;
                    });
                } else {
                    driversTabHtml = `<div class="text-center py-8 text-gray-500"><i data-lucide="users" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i><p>No drivers assigned to this unit</p></div>`;
                }

                let boundaryRowsHtml = '';
                if (assignedDrivers.length > 0) {
                    assignedDrivers.forEach(d => {
                        if (d.last_boundary_date) {
                            boundaryRowsHtml += `<tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.last_boundary_date}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.full_name || ''}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.license_number || ''}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">₱${parseFloat(d.boundary_amount || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
                            </tr>`;
                        }
                    });
                }

                let maintHtml = '';
                if (maint.length > 0) {
                    maint.forEach(m => {
                        maintHtml += `<div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h5 class="font-semibold text-gray-900">${m.maintenance_type || m.type || 'Maintenance'}</h5>
                                    <p class="text-sm text-gray-600">${m.date_started || m.date || ''}</p>
                                </div>
                                <div class="text-right"><span class="text-lg font-bold text-orange-600">₱${parseFloat(m.total_cost || m.cost || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span></div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div><span class="text-gray-600">Mechanic:</span><p class="font-medium">${m.mechanic_name || 'N/A'}</p></div>
                                <div><span class="text-gray-600">Status:</span><p class="font-medium">${m.status || 'Unknown'}</p></div>
                                <div class="md:col-span-2"><span class="text-gray-600">Description:</span><p class="font-medium">${m.description || m.notes || 'No description'}</p></div>
                            </div>
                        </div>`;
                    });
                } else {
                    maintHtml = `<div class="text-center py-8 text-gray-500"><i data-lucide="wrench" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i><p>No maintenance records found</p></div>`;
                }

                const roiPrgW = Math.min(100, Math.max(0, roiPct)).toFixed(1);
                const invPerMonth = parseFloat(roi.total_investment || 0) / 12;
                const mthBnd = parseFloat(roi.monthly_revenue || roi.monthly_boundary || 0);
                const bndPrgW = invPerMonth > 0 ? Math.min(100, (mthBnd / invPerMonth) * 100).toFixed(1) : 0;

                document.getElementById('unitDetailsContent').innerHTML = `
                <div class="space-y-6">
                    <!-- Unit Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-lg text-white">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-2xl font-bold">${unit.unit_number || ''}</h3>
                                <p class="text-blue-100">${(unit.make || '') + ' ' + (unit.model || '') + ' (' + (unit.year || '') + ')'}</p>
                                <p class="text-blue-100">Plate: ${unit.plate_number || ''}</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="px-2 py-1 bg-white bg-opacity-20 rounded-full text-xs font-medium">${unit.status ? unit.status.charAt(0).toUpperCase() + unit.status.slice(1) : ''}</span>
                                    <span class="px-2 py-1 bg-white bg-opacity-20 rounded-full text-xs font-medium">${unit.unit_type ? unit.unit_type.charAt(0).toUpperCase() + unit.unit_type.slice(1) : 'Standard'}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold">₱${parseFloat(unit.boundary_rate || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</div>
                                <p class="text-blue-100 text-sm">Daily Boundary Rate</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs Navigation -->
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-4 overflow-x-auto">
                            <button onclick="showTab('overview')" class="tab-btn py-2 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600 whitespace-nowrap" data-tab="overview">Overview</button>
                            <button onclick="showTab('drivers')" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap" data-tab="drivers">Drivers</button>
                            <button onclick="showTab('coding')" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap" data-tab="coding">Coding</button>
                            <button onclick="showTab('boundary')" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap" data-tab="boundary">Boundary</button>
                            <button onclick="showTab('maintenance')" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap" data-tab="maintenance">Maintenance</button>
                            <button onclick="showTab('roi')" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap" data-tab="roi">ROI</button>
                            <button onclick="showTab('location')" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap" data-tab="location">Location</button>
                            <button onclick="showTab('dashcam')" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap" data-tab="dashcam">Dashcam</button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div id="tabContent">
                        <!-- Overview Tab -->
                        <div id="overview-tab" class="tab-content">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                                <div class="bg-white border border-gray-200 rounded-lg p-4"><div class="flex items-center gap-3"><div class="p-2 bg-blue-100 rounded-lg"><i data-lucide="users" class="w-5 h-5 text-blue-600"></i></div><div><p class="text-sm text-gray-600">Drivers</p><p class="text-lg font-bold">${assignedDrivers.length}/2</p></div></div></div>
                                <div class="bg-white border border-gray-200 rounded-lg p-4"><div class="flex items-center gap-3"><div class="p-2 bg-green-100 rounded-lg"><i data-lucide="calendar" class="w-5 h-5 text-green-600"></i></div><div><p class="text-sm text-gray-600">Next Coding</p><p class="text-lg font-bold">${daysUntilCoding === 0 ? 'Today' : daysUntilCoding + 'd'}</p></div></div></div>
                                <div class="bg-white border border-gray-200 rounded-lg p-4"><div class="flex items-center gap-3"><div class="p-2 bg-purple-100 rounded-lg"><i data-lucide="trending-up" class="w-5 h-5 text-purple-600"></i></div><div><p class="text-sm text-gray-600">ROI</p><p class="text-lg font-bold">${roiPct.toFixed(1)}%</p></div></div></div>
                                <div class="bg-white border border-gray-200 rounded-lg p-4"><div class="flex items-center gap-3"><div class="p-2 bg-orange-100 rounded-lg"><i data-lucide="wrench" class="w-5 h-5 text-orange-600"></i></div><div><p class="text-sm text-gray-600">Maintenance</p><p class="text-lg font-bold">${maint.length}</p></div></div></div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2"><i data-lucide="info" class="w-5 h-5"></i> Basic Information</h4>
                                    <div class="space-y-3">
                                        <div class="flex justify-between"><span class="text-gray-600">Unit Number:</span><span class="font-medium">${unit.unit_number || ''}</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Plate Number:</span><span class="font-medium">${unit.plate_number || ''}</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Vehicle:</span><span class="font-medium">${(unit.make || '') + ' ' + (unit.model || '')}</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Year:</span><span class="font-medium">${unit.year || ''}</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Status:</span><span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">${unit.status ? unit.status.charAt(0).toUpperCase() + unit.status.slice(1) : ''}</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Boundary Rate:</span><span class="font-medium">₱${parseFloat(unit.boundary_rate || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span></div>
                                    </div>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2"><i data-lucide="users" class="w-5 h-5"></i> Driver Assignment</h4>
                                    <div class="space-y-3">
                                        <div class="flex justify-between"><span class="text-gray-600">Assigned Drivers:</span><span class="font-medium">${assignedDrivers.length}/2</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Availability:</span><span class="px-2 py-1 text-xs rounded-full ${assignedDrivers.length >= 2 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">${assignedDrivers.length >= 2 ? 'Full' : 'Available'}</span></div>
                                        ${driversOverviewHtml ? '<div class="mt-4 space-y-2">' + driversOverviewHtml + '</div>' : ''}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Drivers Tab -->
                        <div id="drivers-tab" class="tab-content hidden">
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4">Assigned Drivers</h4>
                                <div class="space-y-4">${driversTabHtml}</div>
                            </div>
                        </div>

                        <!-- Coding Tab -->
                        <div id="coding-tab" class="tab-content hidden">
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4">MMDA Coding Schedule</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h5 class="font-medium text-gray-900 mb-3">Current Coding Information</h5>
                                        <div class="space-y-3">
                                            <div class="flex justify-between"><span class="text-gray-600">Coding Day:</span><span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">${codingDay}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Last Digit:</span><span class="font-medium">${lastChar || '-'}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Next Coding:</span><span class="font-medium">${nextCodingDate || '-'}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Days Until Coding:</span><span class="font-medium ${daysUntilCoding === 0 ? 'text-red-600' : 'text-green-600'}">${daysUntilCoding === 0 ? 'Today' : daysUntilCoding + ' days'}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Coding Time:</span><span class="font-medium">7:00 AM - 10:00 AM</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Coding Status:</span><span class="px-2 py-1 text-xs rounded-full ${daysUntilCoding === 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">${daysUntilCoding === 0 ? 'Coding Today' : 'No Coding'}</span></div>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="font-medium text-gray-900 mb-3">MMDA Coding Schedule</h5>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between p-2 bg-blue-50 rounded"><span>Monday</span><span class="font-medium">1, 2</span></div>
                                            <div class="flex justify-between p-2 bg-green-50 rounded"><span>Tuesday</span><span class="font-medium">3, 4</span></div>
                                            <div class="flex justify-between p-2 bg-yellow-50 rounded"><span>Wednesday</span><span class="font-medium">5, 6</span></div>
                                            <div class="flex justify-between p-2 bg-orange-50 rounded"><span>Thursday</span><span class="font-medium">7, 8</span></div>
                                            <div class="flex justify-between p-2 bg-red-50 rounded"><span>Friday</span><span class="font-medium">9, 0</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Boundary Tab -->
                        <div id="boundary-tab" class="tab-content hidden">
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4">Boundary Collection History</h4>
                                ${boundaryRowsHtml ? `<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Driver</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">License</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th></tr></thead><tbody class="bg-white divide-y divide-gray-200">${boundaryRowsHtml}</tbody></table></div>` : '<div class="text-center py-8 text-gray-500"><i data-lucide="dollar-sign" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i><p>No boundary collection history found</p></div>'}
                            </div>
                        </div>

                        <!-- Maintenance Tab -->
                        <div id="maintenance-tab" class="tab-content hidden">
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4">Maintenance Records</h4>
                                <div class="space-y-4">${maintHtml}</div>
                            </div>
                        </div>

                        <!-- ROI Tab -->
                        <div id="roi-tab" class="tab-content hidden">
                            <div class="space-y-6">
                                <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 rounded-lg text-white">
                                    <h4 class="text-xl font-bold mb-4">ROI Analysis</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div><p class="text-purple-100">Total Investment</p><p class="text-2xl font-bold">₱${parseFloat(roi.total_investment || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</p></div>
                                        <div><p class="text-purple-100">Total Revenue</p><p class="text-2xl font-bold">₱${parseFloat(roi.total_revenue || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</p></div>
                                        <div><p class="text-purple-100">Total Expenses</p><p class="text-2xl font-bold">₱${parseFloat(roi.total_expenses || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</p></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                                        <h4 class="text-lg font-semibold text-gray-900 mb-4">ROI Metrics</h4>
                                        <div class="space-y-4">
                                            <div class="flex justify-between items-center"><span class="text-gray-600">ROI Percentage</span><span class="text-lg font-bold text-${roiColor}-600">${roiPct.toFixed(1)}%</span></div>
                                            <div class="flex justify-between items-center"><span class="text-gray-600">Payback Period</span><span class="text-lg font-bold text-blue-600">${parseFloat(roi.payback_period || 0).toFixed(1)} months</span></div>
                                            <div class="flex justify-between items-center"><span class="text-gray-600">Monthly Revenue</span><span class="text-lg font-bold text-green-600">₱${parseFloat(roi.monthly_revenue || roi.monthly_boundary || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span></div>
                                            <div class="flex justify-between items-center"><span class="text-gray-600">Monthly Expenses</span><span class="text-lg font-bold text-red-600">₱${parseFloat(roi.monthly_expenses || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span></div>
                                        </div>
                                    </div>
                                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                                        <h4 class="text-lg font-semibold text-gray-900 mb-4">ROI Progress</h4>
                                        <div class="space-y-4">
                                            <div>
                                                <div class="flex justify-between items-center mb-2"><span class="text-sm text-gray-600">ROI Achievement</span><span class="text-sm font-medium">${roiPct.toFixed(1)}%</span></div>
                                                <div class="w-full bg-gray-200 rounded-full h-4"><div class="bg-gradient-to-r from-purple-500 to-purple-600 h-4 rounded-full" style="width:${roiPrgW}%"></div></div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between items-center mb-2"><span class="text-sm text-gray-600">Base Boundary to Achieve ROI</span><span class="text-sm font-medium">₱${invPerMonth.toLocaleString('en-PH', {minimumFractionDigits:2})}/month</span></div>
                                                <div class="w-full bg-gray-200 rounded-full h-4"><div class="bg-gradient-to-r from-green-500 to-green-600 h-4 rounded-full" style="width:${bndPrgW}%"></div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Tab -->
                        <div id="location-tab" class="tab-content hidden">
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4">Location Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h5 class="font-medium text-gray-900 mb-3">Current Location</h5>
                                        <div class="space-y-3">
                                            <div class="flex justify-between"><span class="text-gray-600">Location:</span><span class="font-medium">${locInfo.current_location || 'Not Available'}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Last Update:</span><span class="font-medium">${locInfo.last_location_update || 'Never'}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">GPS Status:</span><span class="px-2 py-1 text-xs rounded-full ${locInfo.gps_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${locInfo.gps_enabled ? 'Enabled' : 'Disabled'}</span></div>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="font-medium text-gray-900 mb-3">Map View</h5>
                                        <div class="bg-gray-100 rounded-lg h-64 flex items-center justify-center"><div class="text-center text-gray-500"><i data-lucide="map" class="w-12 h-12 mx-auto mb-2"></i><p>Map integration coming soon</p></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dashcam Tab -->
                        <div id="dashcam-tab" class="tab-content hidden">
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4">Dashcam Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h5 class="font-medium text-gray-900 mb-3">Device Status</h5>
                                        <div class="space-y-3">
                                            <div class="flex justify-between"><span class="text-gray-600">Dashcam Status:</span><span class="px-2 py-1 text-xs rounded-full ${dashcam.dashcam_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${dashcam.dashcam_enabled ? 'Enabled' : 'Disabled'}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Connection Status:</span><span class="px-2 py-1 text-xs rounded-full ${dashcam.dashcam_status === 'Online' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">${dashcam.dashcam_status || 'Offline'}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Last Recording:</span><span class="font-medium">${dashcam.last_recording || 'Never'}</span></div>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="font-medium text-gray-900 mb-3">Storage Information</h5>
                                        <div class="space-y-3">
                                            <div class="flex justify-between"><span class="text-gray-600">Storage Used:</span><span class="font-medium">${parseFloat(dashcam.storage_used || 0).toFixed(2)} GB</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Total Storage:</span><span class="font-medium">${parseFloat(dashcam.storage_total || 32).toFixed(2)} GB</span></div>
                                            <div>
                                                <div class="flex justify-between items-center mb-2"><span class="text-sm text-gray-600">Storage Usage</span><span class="text-sm font-medium">${(dashcam.storage_total || 32) > 0 ? ((dashcam.storage_used || 0) / (dashcam.storage_total || 32) * 100).toFixed(1) : 0}%</span></div>
                                                <div class="w-full bg-gray-200 rounded-full h-4"><div class="bg-gradient-to-r from-blue-500 to-blue-600 h-4 rounded-full" style="width:${(dashcam.storage_total || 32) > 0 ? Math.min(100, (dashcam.storage_used || 0) / (dashcam.storage_total || 32) * 100).toFixed(1) : 0}%"></div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <h5 class="font-medium text-gray-900 mb-3">Recent Recordings</h5>
                                    <div class="bg-gray-100 rounded-lg h-32 flex items-center justify-center"><div class="text-center text-gray-500"><i data-lucide="video" class="w-8 h-8 mx-auto mb-2"></i><p>Video integration coming soon</p></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                `;

                // Re-init lucide icons
                if (typeof lucide !== 'undefined') lucide.createIcons();
                // Show overview tab by default
                setTimeout(() => { showTab('overview'); }, 50);
            })
            .catch(err => {
                document.getElementById('unitDetailsContent').innerHTML = `
                    <div class="text-center py-8">
                        <i data-lucide="alert-circle" class="w-12 h-12 mx-auto mb-4 text-red-500"></i>
                        <p class="text-red-500">Failed to load unit details</p>
                    </div>
                `;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        }

        function closeUnitDetailsModal() {
            document.getElementById('unitDetailsModal').classList.add('hidden');
        }

        // showTab() - matches backup's exact tab switching logic
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            const selectedTab = document.getElementById(tabName + '-tab');
            if (selectedTab) selectedTab.classList.remove('hidden');
            const activeBtn = document.querySelector('[data-tab="' + tabName + '"]');
            if (activeBtn) {
                activeBtn.classList.remove('border-transparent', 'text-gray-500');
                activeBtn.classList.add('border-blue-500', 'text-blue-600');
            }
            setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 100);
        }
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// =============================================
// ADD UNIT MODAL - Driver Searchable Dropdown
// =============================================
function addUnitShowDropdown(driverType) {
    addUnitFilterDrivers(driverType);
    document.getElementById(driverType + '_dropdown').classList.remove('hidden');
}
function addUnitHideDropdown(driverType) {
    document.getElementById(driverType + '_dropdown').classList.add('hidden');
}
function addUnitFilterDrivers(driverType) {
    const searchInput = document.getElementById(driverType + '_search');
    const select = document.getElementById(driverType);
    const dropdown = document.getElementById(driverType + '_dropdown');
    const query = searchInput ? searchInput.value.toLowerCase() : '';
    const options = Array.from(select.options).slice(1);

    let html = '';
    options.forEach(opt => {
        const name = opt.getAttribute('data-name') || '';
        const license = opt.getAttribute('data-license') || '';
        const display = name + ' - ' + license;
        if (!query || name.toLowerCase().includes(query) || license.toLowerCase().includes(query)) {
            html += `<div class="px-4 py-3 hover:bg-yellow-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                         onmousedown="addUnitSelectDriver('${driverType}','${opt.value}','${name.replace(/'/g,"\\'")}','${license.replace(/'/g,"\\'")}')">
                        <div class="font-medium text-gray-900">${name}</div>
                        <div class="text-sm text-gray-500">${license || 'No License'}</div>
                     </div>`;
        }
    });
    dropdown.innerHTML = html || '<p class="px-4 py-3 text-sm text-gray-500">No drivers found</p>';
    dropdown.classList.remove('hidden');
}
function addUnitSelectDriver(driverType, value, name, license) {
    document.getElementById(driverType).value = value;
    document.getElementById(driverType + '_search').value = name + (license ? ' - ' + license : '');
    addUnitHideDropdown(driverType);
}
function addUnitClearDriver(driverType) {
    document.getElementById(driverType).value = '';
    document.getElementById(driverType + '_search').value = '';
}

// =============================================
// ADD UNIT MODAL - Auto Coding Calculation
// =============================================
function addUnitGetLastDigit(plateNumber) {
    plateNumber = plateNumber.toUpperCase().trim().replace(/[^A-Z0-9]/g, '');
    if (plateNumber.length > 0) {
        const last = plateNumber.slice(-1);
        if (/[A-Z]/.test(last)) return last.charCodeAt(0) - 64;
        if (/[0-9]/.test(last)) return parseInt(last);
    }
    return null;
}
function addUnitUpdateCoding() {
    const plate = document.getElementById('addPlateNumber')?.value || '';
    if (!plate) return;

    const schedule = { Monday:[1,2], Tuesday:[3,4], Wednesday:[5,6], Thursday:[7,8], Friday:[9,0] };
    const lastDigit = addUnitGetLastDigit(plate);
    let codingDay = '';
    for (const [day, endings] of Object.entries(schedule)) {
        if (endings.includes(lastDigit)) { codingDay = day; break; }
    }

    const today = new Date();
    const daysOfWeek = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const todayName = daysOfWeek[today.getDay()];
    let isCodingToday = (todayName === codingDay);
    let daysUntil = 0;
    let nextDate = new Date(today);

    if (!isCodingToday && codingDay) {
        for (let i = 1; i <= 7; i++) {
            const test = new Date(today);
            test.setDate(today.getDate() + i);
            if (daysOfWeek[test.getDay()] === codingDay) { nextDate = test; daysUntil = i; break; }
        }
    }

    // Boundary rate by coding day
    const rates = { Monday:1200, Tuesday:1100, Wednesday:1150, Thursday:1050, Friday:1300 };
    if (codingDay && rates[codingDay]) {
        document.getElementById('addBoundaryRate').value = rates[codingDay].toFixed(2);
    }

    document.getElementById('addCodingDay').value = codingDay || '';
    document.getElementById('addNextCodingDate').value = codingDay ? nextDate.toLocaleDateString('en-US') : '';
    document.getElementById('addDaysUntilCoding').value = codingDay ? (isCodingToday ? 0 : daysUntil) : '';

    // Auto-set status to coding if today is coding day
    if (isCodingToday) {
        document.getElementById('addUnitStatus').value = 'coding';
    }

    // Update coding status display
    const display = document.getElementById('addCodingStatusDisplay');
    if (!codingDay) {
        display.innerHTML = '<div class="p-3 rounded-lg border-2 border-gray-300 bg-gray-50 flex items-center gap-2"><i data-lucide="info" class="w-5 h-5 text-gray-500"></i><div><p class="text-sm font-semibold text-gray-800">NO CODING SCHEDULE</p><p class="text-xs text-gray-500">Plate number does not match MMDA schedule</p></div></div>';
    } else if (isCodingToday) {
        display.innerHTML = `<div class="p-3 rounded-lg border-2 border-red-500 bg-red-50 flex items-center gap-2"><i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i><div><p class="text-sm font-semibold text-red-800">CODING TODAY!</p><p class="text-xs text-red-600">This unit is scheduled for coding today (${codingDay})</p></div></div>`;
    } else if (daysUntil === 1) {
        display.innerHTML = `<div class="p-3 rounded-lg border-2 border-yellow-500 bg-yellow-50 flex items-center gap-2"><i data-lucide="clock" class="w-5 h-5 text-yellow-600"></i><div><p class="text-sm font-semibold text-yellow-800">CODING TOMORROW</p><p class="text-xs text-yellow-600">Next coding: ${codingDay}</p></div></div>`;
    } else {
        display.innerHTML = `<div class="p-3 rounded-lg border-2 border-blue-400 bg-blue-50 flex items-center gap-2"><i data-lucide="calendar" class="w-5 h-5 text-blue-600"></i><div><p class="text-sm font-semibold text-blue-800">NEXT CODING</p><p class="text-xs text-blue-600">${codingDay} (${daysUntil} days)</p></div></div>`;
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

// =============================================
// ADD UNIT MODAL - GPS/Dashcam Devices
// =============================================
let addUnitGPS = [], addUnitDashcam = [];

function addUnitAddGPS() {
    const id = prompt('Enter GPS Device ID:');
    if (id && id.trim()) {
        addUnitGPS.push({ id: id.trim() });
        addUnitRenderGPS();
    }
}
function addUnitAddDashcam() {
    const id = prompt('Enter Dashcam Device ID:');
    if (id && id.trim()) {
        addUnitDashcam.push({ id: id.trim() });
        addUnitRenderDashcam();
    }
}
function addUnitRemoveGPS(index) { addUnitGPS.splice(index, 1); addUnitRenderGPS(); }
function addUnitRemoveDashcam(index) { addUnitDashcam.splice(index, 1); addUnitRenderDashcam(); }
function addUnitRenderGPS() {
    const list = document.getElementById('addGPSDevicesList');
    if (!addUnitGPS.length) { list.innerHTML = '<p class="text-sm text-gray-500 text-center py-2">No GPS devices added</p>'; return; }
    list.innerHTML = addUnitGPS.map((d, i) => `
        <div class="flex items-center justify-between p-2 bg-indigo-50 rounded-lg">
            <div class="flex items-center gap-2"><i data-lucide="map-pin" class="w-4 h-4 text-indigo-600"></i>
                <span class="text-sm font-medium">${d.id}</span>
                <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded-full">Active</span>
            </div>
            <button type="button" onclick="addUnitRemoveGPS(${i})" class="text-red-500 hover:text-red-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <input type="hidden" name="gps_devices[]" value="${d.id}">
    `).join('');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function addUnitRenderDashcam() {
    const list = document.getElementById('addDashcamDevicesList');
    if (!addUnitDashcam.length) { list.innerHTML = '<p class="text-sm text-gray-500 text-center py-2">No dashcam devices added</p>'; return; }
    list.innerHTML = addUnitDashcam.map((d, i) => `
        <div class="flex items-center justify-between p-2 bg-purple-50 rounded-lg">
            <div class="flex items-center gap-2"><i data-lucide="camera" class="w-4 h-4 text-purple-600"></i>
                <span class="text-sm font-medium">${d.id}</span>
                <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded-full">Active</span>
            </div>
            <button type="button" onclick="addUnitRemoveDashcam(${i})" class="text-red-500 hover:text-red-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <input type="hidden" name="dashcam_devices[]" value="${d.id}">
    `).join('');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

// Reset the Add Unit modal
function resetAddUnitModal() {
    document.getElementById('addUnitForm')?.reset();
    addUnitClearDriver('add_driver1');
    addUnitClearDriver('add_driver2');
    document.getElementById('addCodingDay').value = '';
    document.getElementById('addNextCodingDate').value = '';
    document.getElementById('addDaysUntilCoding').value = '';
    document.getElementById('addCodingStatusDisplay').innerHTML = '';
    addUnitGPS = []; addUnitDashcam = [];
    addUnitRenderGPS(); addUnitRenderDashcam();
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\eurotaxisystem\resources\views/units/index.blade.php ENDPATH**/ ?>