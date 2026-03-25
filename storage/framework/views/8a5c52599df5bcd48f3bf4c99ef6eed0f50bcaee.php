<?php $__env->startSection('title', 'Driver Behavior - Euro System'); ?>
<?php $__env->startSection('page-heading', 'Driver Behavior Monitoring'); ?>
<?php $__env->startSection('page-subheading', 'Track and analyze driver performance and incidents'); ?>

<?php $__env->startSection('content'); ?>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Incidents (30 days)</p>
                        <p class="text-2xl"><?php echo e($stats['incidents_30_days'] ?? 0); ?></p>
                        <p class="text-xs text-gray-500 mt-1">Total recorded</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <i data-lucide="alert-triangle" class="h-8 w-8 text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Critical Incidents</p>
                        <p class="text-2xl text-red-600"><?php echo e($stats['by_severity']['critical'] ?? 0); ?></p>
                        <p class="text-xs text-gray-500 mt-1">Requires attention</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <i data-lucide="alert-circle" class="h-8 w-8 text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">High Severity</p>
                        <p class="text-2xl text-orange-600"><?php echo e($stats['by_severity']['high'] ?? 0); ?></p>
                        <p class="text-xs text-gray-500 mt-1">Monitor closely</p>
                    </div>
                    <div class="p-3 bg-orange-100 rounded-full">
                        <i data-lucide="alert-triangle" class="h-8 w-8 text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Low Severity</p>
                        <p class="text-2xl text-blue-600"><?php echo e($stats['by_severity']['low'] ?? 0); ?></p>
                        <p class="text-xs text-gray-500 mt-1">Minor issues</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i data-lucide="info" class="h-8 w-8 text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="<?php echo e(route('driver-behavior.index')); ?>">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
                        </div>
                        <input
                            type="text"
                            name="search"
                            value="<?php echo e($search ?? ''); ?>"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none"
                            placeholder="Search by unit, driver, or description...">
                    </div>
                </div>

                <div class="sm:w-40">
                    <select name="type" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none">
                        <option value="">All Types</option>
                        <option value="speeding" <?php echo e(($type_filter ?? '') === 'speeding' ? 'selected' : ''); ?>>Speeding</option>
                        <option value="hard_braking" <?php echo e(($type_filter ?? '') === 'hard_braking' ? 'selected' : ''); ?>>Hard Braking</option>
                        <option value="rapid_acceleration" <?php echo e(($type_filter ?? '') === 'rapid_acceleration' ? 'selected' : ''); ?>>Rapid Acceleration</option>
                        <option value="cornering" <?php echo e(($type_filter ?? '') === 'cornering' ? 'selected' : ''); ?>>Cornering</option>
                        <option value="idle" <?php echo e(($type_filter ?? '') === 'idle' ? 'selected' : ''); ?>>Idle</option>
                        <option value="other" <?php echo e(($type_filter ?? '') === 'other' ? 'selected' : ''); ?>>Other</option>
                    </select>
                </div>

                <div class="sm:w-40">
                    <select name="severity" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none">
                        <option value="">All Severities</option>
                        <option value="critical" <?php echo e(($severity_filter ?? '') === 'critical' ? 'selected' : ''); ?>>Critical</option>
                        <option value="high" <?php echo e(($severity_filter ?? '') === 'high' ? 'selected' : ''); ?>>High</option>
                        <option value="medium" <?php echo e(($severity_filter ?? '') === 'medium' ? 'selected' : ''); ?>>Medium</option>
                        <option value="low" <?php echo e(($severity_filter ?? '') === 'low' ? 'selected' : ''); ?>>Low</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 flex items-center gap-2">
                        <i data-lucide="search" class="w-4 h-4"></i> Search
                    </button>
                    <button type="button" onclick="openIncidentModal()"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i> Record Incident
                    </button>
                </div>
            </div>
        </form>
    </div>

    
    <?php if(isset($stats['incident_types']) && count($stats['incident_types']) > 0): ?>
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Incident Types (Last 30 Days)</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <?php $__currentLoopData = $stats['incident_types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="text-center p-4 border rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600"><?php echo e($type['count']); ?></div>
                    <div class="text-sm text-gray-600"><?php echo e(ucfirst(str_replace('_', ' ', $type['incident_type']))); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $incidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">
                                    <div><?php echo e(\Carbon\Carbon::parse($incident->timestamp)->format('M d, Y')); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo e(\Carbon\Carbon::parse($incident->timestamp)->format('h:i A')); ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">
                                    <div class="font-medium"><?php echo e($incident->unit_number ?? 'N/A'); ?></div>
                                    <div class="text-gray-500"><?php echo e($incident->plate_number ?? ''); ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e($incident->driver_name ?? 'N/A'); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $incident->incident_type))); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    <?php if($incident->severity === 'critical'): ?> bg-red-100 text-red-800
                                    <?php elseif($incident->severity === 'high'): ?> bg-orange-100 text-orange-800
                                    <?php elseif($incident->severity === 'medium'): ?> bg-yellow-100 text-yellow-800
                                    <?php else: ?> bg-blue-100 text-blue-800
                                    <?php endif; ?>">
                                    <?php echo e(ucfirst($incident->severity)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    <?php echo e(Str::limit($incident->description, 50)); ?>

                                    <?php if(strlen($incident->description) > 50): ?>
                                        <span class="text-gray-500 cursor-pointer" onclick="showFullDescription('<?php echo e(addslashes($incident->description)); ?>')">...more</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if($incident->video_url): ?>
                                    <a href="<?php echo e($incident->video_url); ?>" target="_blank" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i data-lucide="video" class="w-4 h-4"></i>
                                    </a>
                                <?php endif; ?>
                                <form method="POST" action="<?php echo e(route('driver-behavior.destroy', $incident->id)); ?>" class="inline"
                                    onsubmit="return confirm('Delete this incident?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i data-lucide="alert-triangle" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                                <p>No incidents recorded</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <?php if(isset($incidents) && method_exists($incidents, 'links')): ?>
        <div class="px-6 py-4 border-t border-gray-200">
            <?php echo e($incidents->withQueryString()->links()); ?>

        </div>
        <?php endif; ?>
    </div>

    
    <div id="incidentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-[480px] shadow-lg rounded-lg bg-white max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Record New Incident</h3>
                <button onclick="closeIncidentModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="<?php echo e(route('driver-behavior.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unit *</label>
                            <select name="unit_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                <option value="">Select Unit</option>
                                <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($unit->id); ?>"><?php echo e($unit->unit_number); ?> - <?php echo e($unit->plate_number); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Driver *</label>
                            <select name="driver_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                <option value="">Select Driver</option>
                                <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($driver->id); ?>"><?php echo e($driver->full_name ?? $driver->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Incident Type *</label>
                            <select name="incident_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                <option value="">Select Type</option>
                                <option value="speeding">Speeding</option>
                                <option value="hard_braking">Hard Braking</option>
                                <option value="rapid_acceleration">Rapid Acceleration</option>
                                <option value="cornering">Cornering</option>
                                <option value="idle">Idle</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Severity *</label>
                            <select name="severity" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                <option value="">Select Severity</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                        <textarea name="description" required rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                            <input type="number" name="latitude" step="0.000001" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500" placeholder="14.5547">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                            <input type="number" name="longitude" step="0.000001" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500" placeholder="121.0244">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Video URL</label>
                        <input type="url" name="video_url" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500" placeholder="https://example.com/video.mp4">
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="flex-1 bg-yellow-600 text-white py-2 px-4 rounded-lg hover:bg-yellow-700">
                        Save Incident
                    </button>
                    <button type="button" onclick="closeIncidentModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="descriptionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Full Description</h3>
                <button onclick="closeDescriptionModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <p id="fullDescription" class="text-gray-700"></p>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function openIncidentModal() {
    document.getElementById('incidentModal').classList.remove('hidden');
    lucide.createIcons();
}

function closeIncidentModal() {
    document.getElementById('incidentModal').classList.add('hidden');
}

function showFullDescription(description) {
    document.getElementById('fullDescription').textContent = description;
    document.getElementById('descriptionModal').classList.remove('hidden');
    lucide.createIcons();
}

function closeDescriptionModal() {
    document.getElementById('descriptionModal').classList.add('hidden');
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\eurotaxisystem\resources\views/driver-behavior/index.blade.php ENDPATH**/ ?>