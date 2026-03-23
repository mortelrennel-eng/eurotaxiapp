<?php $__env->startSection('title', 'Driver Management - Euro System'); ?>
<?php $__env->startSection('page-heading', 'Driver Management'); ?>
<?php $__env->startSection('page-subheading', 'Centralized driver records, incentives, and performance analytics'); ?>

<?php $__env->startSection('content'); ?>

    
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="<?php echo e(route('driver-management.index')); ?>">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
                        </div>
                        <input type="text" name="search" value="<?php echo e($search ?? ''); ?>"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none"
                            placeholder="Search by driver name, email, or license...">
                    </div>
                </div>

                <div class="md:w-48">
                    <select name="status" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none">
                        <option value="">All Status</option>
                        <option value="active" <?php echo e(($status_filter ?? '') === 'active' ? 'selected' : ''); ?>>Active</option>
                        <option value="inactive" <?php echo e(($status_filter ?? '') === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 flex items-center gap-2">
                        <i data-lucide="search" class="w-4 h-4"></i> Search
                    </button>
                    <button type="button" onclick="openAddDriverModal()"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add Driver
                    </button>
                </div>
            </div>
        </form>
    </div>

    
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Drivers</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">License</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Incentive This Month</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="openEditDriverModal(<?php echo e($driver->id); ?>)">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($driver->full_name); ?></div>
                                <div class="text-xs text-gray-500"><?php echo e($driver->email); ?></div>
                                <div class="text-[10px] text-gray-400 mt-1">
                                    <span title="Input by <?php echo e($driver->creator_name ?? 'System'); ?>">In: <?php echo e($driver->creator_name ?? 'System'); ?></span>
                                    <?php if(isset($driver->editor_name) && $driver->editor_name): ?>
                                        <span class="ml-1" title="Last edit by <?php echo e($driver->editor_name); ?>">Ed: <?php echo e($driver->editor_name); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php if(!empty($driver->assigned_unit)): ?>
                                    <?php echo e($driver->assigned_unit); ?>

                                <?php else: ?>
                                    <span class="text-gray-400">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e($driver->license_number ?? 'N/A'); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo e($driver->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                    <?php echo e($driver->is_active ? 'Active' : 'Inactive'); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₱<?php echo e(number_format($driver->monthly_incentive ?? 0, 2)); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e($driver->performance_rating ?? 'Good'); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button
                                    type="button"
                                    class="text-blue-600 hover:text-blue-900 mr-3"
                                    onclick="event.stopPropagation(); openDriverDetails(<?php echo e($driver->id); ?>)"
                                >
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                <i data-lucide="users" class="w-10 h-10 mx-auto mb-3 text-gray-300"></i>
                                <p>No drivers found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(isset($drivers) && method_exists($drivers, 'links')): ?>
        <div class="px-6 py-4 border-t border-gray-200">
            <?php echo e($drivers->withQueryString()->links()); ?>

        </div>
        <?php endif; ?>
    </div>

    
    <div id="addDriverModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900" id="driverModalTitle">Add Driver</h3>
                <button type="button" onclick="closeAddDriverModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form id="driverForm" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_method" id="driverFormMethod" value="POST">
                <input type="hidden" name="driver_id" id="editDriverId" value="">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="full_name" id="driverFullName" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                        <input type="tel" name="contact_number" id="driverContact" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Number *</label>
                        <input type="text" name="license_number" id="driverLicense" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Expiry *</label>
                        <input type="date" name="license_expiry" id="driverLicenseExpiry" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date</label>
                        <input type="date" name="hire_date" id="driverHireDate" value="<?php echo e(date('Y-m-d')); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" id="driverAddress" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact</label>
                        <input type="text" name="emergency_contact" id="driverEmergencyContact" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Phone</label>
                        <input type="tel" name="emergency_phone" id="driverEmergencyPhone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Daily Boundary Target</label>
                    <input type="number" name="daily_boundary_target" id="driverBoundaryTarget" step="0.01" value="1100.00" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                </div>

                <div class="flex items-center justify-between mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="is_active" id="editIsActive" class="w-40 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" id="deleteDriverButton" onclick="confirmDeleteDriver()" class="hidden px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
                        <button type="button" onclick="closeAddDriverModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    
    <div id="driverDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <div>
                    <h3 class="text-lg font-bold text-gray-900" id="driverDetailsName">Driver Details</h3>
                    <p class="text-sm text-gray-500" id="driverDetailsSubtitle"></p>
                </div>
                <button onclick="closeDriverDetails()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            
            <div class="px-6 pt-4 border-b">
                <nav class="-mb-px flex space-x-4" aria-label="Tabs">
                    <button type="button" class="driver-tab active border-yellow-500 text-yellow-600 whitespace-nowrap py-2 px-3 border-b-2 text-sm font-medium" data-tab="basic">
                        Basic Info
                    </button>
                    <button type="button" class="driver-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-3 border-b-2 text-sm font-medium" data-tab="license">
                        License & Documents
                    </button>
                    <button type="button" class="driver-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-3 border-b-2 text-sm font-medium" data-tab="incentives">
                        Incentives
                    </button>
                    <button type="button" class="driver-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-3 border-b-2 text-sm font-medium" data-tab="performance">
                        Performance
                    </button>
                    <button type="button" class="driver-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-3 border-b-2 text-sm font-medium" data-tab="insights">
                        Insights
                    </button>
                </nav>
            </div>

            
            <div class="p-6 space-y-6">
                <div class="driver-tab-panel" data-tab-panel="basic">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">Personal & Employment Details</h4>
                    <div id="basicInfoContent" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                        <p class="text-gray-400">Loading...</p>
                    </div>
                </div>

                <div class="driver-tab-panel hidden" data-tab-panel="license">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">License Information</h4>
                    <div id="licenseInfoContent" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                        <p class="text-gray-400">Loading...</p>
                    </div>

                    <div class="mt-6 border-t pt-4">
                        <h5 class="text-sm font-semibold text-gray-800 mb-2">Upload Driver Documents</h5>
                        <p class="text-xs text-gray-500 mb-3">Accepted file types: JPG, PNG, PDF. Uploads replace any existing document for the same type.</p>
                        <form id="driverDocumentsForm" method="POST" enctype="multipart/form-data" class="space-y-3">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="_method" value="POST">
                            <input type="hidden" name="driver_id" id="driverDocumentsDriverId" value="">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Driver's License (Front)</label>
                                    <input type="file" name="license_front" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Driver's License (Back)</label>
                                    <input type="file" name="license_back" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">NBI Clearance</label>
                                    <input type="file" name="nbi_clearance" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Barangay Clearance</label>
                                    <input type="file" name="barangay_clearance" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Medical Certificate</label>
                                    <input type="file" name="medical_certificate" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none">
                                </div>
                            </div>

                            <div class="pt-2 flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                                    Save Documents
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="driver-tab-panel hidden" data-tab-panel="incentives">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">Rule-based Incentives</h4>
                    <p class="text-sm text-gray-600 mb-4">This section shows the breakdown of incentives based on rules like no damage, no absence, complete boundary payments, and no late return.</p>
                    <div id="incentivesContent" class="text-sm text-gray-700">
                        <p class="text-gray-400">Loading incentive data...</p>
                    </div>
                </div>

                <div class="driver-tab-panel hidden" data-tab-panel="performance">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">Performance Analytics</h4>
                    <div id="performanceContent" class="text-sm text-gray-700 space-y-2">
                        <p class="text-gray-400">Loading performance data...</p>
                    </div>
                </div>

                <div class="driver-tab-panel hidden" data-tab-panel="insights">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">System Insights & Recommendations</h4>
                    <div id="insightsContent" class="text-sm text-gray-700 space-y-2">
                        <p class="text-gray-400">Loading insights...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function openAddDriverModal() {
    document.getElementById('driverModalTitle').textContent = 'Add Driver';
    document.getElementById('driverFormMethod').value = 'POST';
    document.getElementById('driverForm').action = '<?php echo e(route('driver-management.store')); ?>';
    document.getElementById('editDriverId').value = '';
    document.getElementById('driverFullName').value = '';
    document.getElementById('driverContact').value = '';
    document.getElementById('driverLicense').value = '';
    document.getElementById('driverLicenseExpiry').value = '';
    document.getElementById('driverHireDate').value = '<?php echo e(date('Y-m-d')); ?>';
    document.getElementById('driverAddress').value = '';
    document.getElementById('driverEmergencyContact').value = '';
    document.getElementById('driverEmergencyPhone').value = '';
    document.getElementById('driverBoundaryTarget').value = '1100.00';
    document.getElementById('editIsActive').value = '1';
    document.getElementById('deleteDriverButton').classList.add('hidden');
    document.getElementById('addDriverModal').classList.remove('hidden');
    lucide.createIcons();
}

function openEditDriverModal(id) {
    fetch('<?php echo e(route('driver-management.index')); ?>/' + id + '?format=json', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('driverModalTitle').textContent = 'Edit Driver';
        document.getElementById('driverFormMethod').value = 'PUT';
        document.getElementById('driverForm').action = '<?php echo e(url('driver-management')); ?>/' + id;
        document.getElementById('editDriverId').value = id;
        document.getElementById('driverFullName').value = data.full_name || '';
        document.getElementById('driverContact').value = data.contact_number || '';
        document.getElementById('driverLicense').value = data.license_number || '';
        document.getElementById('driverLicenseExpiry').value = data.license_expiry || '';
        document.getElementById('driverHireDate').value = data.hire_date || '<?php echo e(date('Y-m-d')); ?>';
        document.getElementById('driverAddress').value = data.address || '';
        document.getElementById('driverEmergencyContact').value = data.emergency_contact || '';
        document.getElementById('driverEmergencyPhone').value = data.emergency_phone || '';
        document.getElementById('driverBoundaryTarget').value = data.daily_boundary_target || '1100.00';
        document.getElementById('editIsActive').value = data.is_active ? '1' : '0';
        document.getElementById('deleteDriverButton').classList.remove('hidden');
        document.getElementById('addDriverModal').classList.remove('hidden');
        lucide.createIcons();
    })
    .catch(() => {
        // Fallback: just show empty edit modal
        document.getElementById('driverModalTitle').textContent = 'Edit Driver';
        document.getElementById('driverFormMethod').value = 'PUT';
        document.getElementById('driverForm').action = '<?php echo e(url('driver-management')); ?>/' + id;
        document.getElementById('editDriverId').value = id;
        document.getElementById('deleteDriverButton').classList.remove('hidden');
        document.getElementById('addDriverModal').classList.remove('hidden');
        lucide.createIcons();
    });
}

function closeAddDriverModal() {
    document.getElementById('addDriverModal').classList.add('hidden');
}

function confirmDeleteDriver() {
    const id = document.getElementById('editDriverId').value;
    const name = document.getElementById('driverFullName').value || 'this driver';
    if (!id) return;
    if (confirm('Are you sure you want to delete ' + name + '?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo e(url('driver-management')); ?>/' + id;
        form.innerHTML = '<?php echo csrf_field(); ?>' +
                         '<input type="hidden" name="_method" value="DELETE">';
        document.body.appendChild(form);
        form.submit();
    }
}

function openDriverDetails(id) {
    const modal = document.getElementById('driverDetailsModal');
    modal.classList.remove('hidden');

    // Reset tab state
    document.querySelectorAll('.driver-tab').forEach(btn => {
        btn.classList.remove('border-yellow-500', 'text-yellow-600', 'active');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    document.querySelectorAll('.driver-tab-panel').forEach(panel => {
        panel.classList.add('hidden');
    });
    const firstTab = document.querySelector('.driver-tab[data-tab="basic"]');
    const firstPanel = document.querySelector('.driver-tab-panel[data-tab-panel="basic"]');
    if (firstTab && firstPanel) {
        firstTab.classList.add('border-yellow-500', 'text-yellow-600', 'active');
        firstTab.classList.remove('border-transparent', 'text-gray-500');
        firstPanel.classList.remove('hidden');
    }

    document.getElementById('driverDocumentsDriverId').value = id;
    document.getElementById('driverDocumentsForm').action = '<?php echo e(url('driver-management/upload-documents')); ?>/' + id;

    // Fetch basic details
    fetch('<?php echo e(route('driver-management.index')); ?>/' + id + '?format=json', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('driverDetailsName').textContent = data.full_name || 'Driver Details';
        document.getElementById('driverDetailsSubtitle').textContent = data.email || '';

        document.getElementById('basicInfoContent').innerHTML = `
            <div>
                <p><span class="font-semibold">Full Name:</span> ${data.full_name || ''}</p>
                <p><span class="font-semibold">Contact:</span> ${data.contact_number || 'N/A'}</p>
                <p><span class="font-semibold">Address:</span> ${data.address || 'N/A'}</p>
                <p><span class="font-semibold">Emergency Contact:</span> ${data.emergency_contact || 'N/A'}</p>
                <p><span class="font-semibold">Emergency Phone:</span> ${data.emergency_phone || 'N/A'}</p>
            </div>
            <div>
                <p><span class="font-semibold">Hire Date:</span> ${data.hire_date || 'N/A'}</p>
                <p><span class="font-semibold">Daily Boundary Target:</span> ₱${data.daily_boundary_target || 'N/A'}</p>
                <p><span class="font-semibold">Status:</span> ${data.is_active ? 'Active' : 'Inactive'}</p>
                <div class="mt-4 pt-2 border-t border-gray-100">
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Record Credit</p>
                    <p class="text-xs text-gray-600"><span class="font-medium text-gray-500">Input by:</span> ${data.creator_name || 'System'}</p>
                    <p class="text-xs text-gray-600"><span class="font-medium text-gray-500">Last Edit:</span> ${data.editor_name || 'System'}</p>
                </div>
            </div>
        `;

        document.getElementById('licenseInfoContent').innerHTML = `
            <div>
                <p><span class="font-semibold">License Number:</span> ${data.license_number || ''}</p>
                <p><span class="font-semibold">License Expiry:</span> ${data.license_expiry || ''}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">License status and reminders will be computed here (e.g., expiring soon).</p>
            </div>
        `;

        lucide.createIcons();
    })
    .catch(() => {
        document.getElementById('basicInfoContent').innerHTML = '<p class="text-red-500">Failed to load details.</p>';
    });

    lucide.createIcons();
}

function closeDriverDetails() {
    document.getElementById('driverDetailsModal').classList.add('hidden');
}

// Tab switching
document.querySelectorAll('.driver-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.driver-tab').forEach(t => {
            t.classList.remove('border-yellow-500', 'text-yellow-600', 'active');
            t.classList.add('border-transparent', 'text-gray-500');
        });
        document.querySelectorAll('.driver-tab-panel').forEach(p => p.classList.add('hidden'));

        tab.classList.add('border-yellow-500', 'text-yellow-600', 'active');
        tab.classList.remove('border-transparent', 'text-gray-500');

        const target = tab.dataset.tab;
        const panel = document.querySelector(`.driver-tab-panel[data-tab-panel="${target}"]`);
        if (panel) panel.classList.remove('hidden');
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\eurotaxisystem\resources\views/driver-management/index.blade.php ENDPATH**/ ?>