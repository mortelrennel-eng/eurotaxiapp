<?php
/** @var \Illuminate\Support\Collection $units */
/** @var array $coding_calendar */
/** @var string $date */
/** @var string $search */
/** @var string $today_name */
?>


<?php $__env->startSection('title', 'Coding Management - Euro System'); ?>
<?php $__env->startSection('page-heading', 'Coding Schedule Management'); ?>
<?php $__env->startSection('page-subheading', "Today: <?php echo e($today_name); ?> — Managing number coding restrictions"); ?>

<?php $__env->startSection('content'); ?>
    <!-- Date Filter -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="<?php echo e(route('coding.index')); ?>" class="flex flex-col md:flex-row gap-4">
            <div class="md:w-48">
                <input type="date" name="date" value="<?php echo e($date); ?>"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:outline-none">
            </div>
            <div class="flex-1">
                <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search unit or plate..."
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:outline-none">
            </div>
            <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                <i data-lucide="search" class="w-4 h-4 inline mr-1"></i> Filter
            </button>
        </form>
    </div>

    <!-- Today's Coding Units -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b flex items-center gap-2">
            <i data-lucide="calendar" class="w-5 h-5 text-yellow-600"></i>
            <h3 class="text-lg font-semibold text-gray-900">Coding Today (<?php echo e($today_name); ?>)</h3>
            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full"><?php echo e($units->count()); ?> units</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plate Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Make / Model</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Driver 1</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Driver 2</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900"><?php echo e($unit->unit_number); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700"><?php echo e($unit->plate_number); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600"><?php echo e($unit->make); ?> <?php echo e($unit->model); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo e($unit->driver1_name ?? '—'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo e($unit->driver2_name ?? '—'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Coding</span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-4 text-green-300"></i>
                                <p>No units on coding today</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Weekly Coding Calendar -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Weekly Coding Calendar</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-5 gap-4">
            <?php $__currentLoopData = $coding_calendar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day => $day_units): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border rounded-lg p-4 <?php echo e($day === $today_name ? 'border-yellow-500 bg-yellow-50' : ''); ?>">
                    <div class="flex items-center gap-2 mb-3">
                        <h4 class="font-semibold text-gray-800"><?php echo e($day); ?></h4>
                        <span
                            class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full"><?php echo e($day_units->count()); ?></span>
                        <?php if($day === $today_name): ?>
                            <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 text-xs rounded-full">TODAY</span>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-1">
                        <?php $__empty_1 = true; $__currentLoopData = $day_units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="text-xs p-2 bg-white rounded border text-gray-700">
                                <div class="font-medium"><?php echo e($u->unit_number); ?></div>
                                <div class="text-gray-500"><?php echo e($u->plate_number); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-xs text-gray-400 italic">No units</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\eurotaxisystem\resources\views/coding/index.blade.php ENDPATH**/ ?>