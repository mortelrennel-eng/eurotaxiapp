<?php $__env->startSection('title', 'Boundaries - Euro System'); ?>
<?php $__env->startSection('page-heading', 'Boundary Collection'); ?>
<?php $__env->startSection('page-subheading', 'Track daily boundary payments from drivers'); ?>

<?php $__env->startSection('content'); ?>
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">Boundaries</h2>
        <p class="text-gray-600">Boundary collection system is working.</p>
        
        <div class="mt-6">
            <p>Total Records: <?php echo e($boundaries->count()); ?></p>
            <p>Search: <?php echo e($search); ?></p>
            <p>Date From: <?php echo e($date_from); ?></p>
            <p>Date To: <?php echo e($date_to); ?></p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\eurotaxisystem\resources\views/boundaries/index_simple.blade.php ENDPATH**/ ?>