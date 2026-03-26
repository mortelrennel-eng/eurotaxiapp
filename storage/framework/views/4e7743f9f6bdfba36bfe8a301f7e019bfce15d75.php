<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    
    <!-- Base Asset URL -->
    <meta name="asset-url" content="<?php echo e(asset('')); ?>">

    <title><?php echo e(config('app.name', 'Euro Taxi System')); ?></title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo e(asset('favicon_euro_transparent.png')); ?>?v=1.5">
    <link rel="icon" type="image/png" href="<?php echo e(asset('favicon_euro_transparent.png')); ?>?v=1.5">
    <link rel="icon" type="image/png" href="/public/favicon_euro_transparent.png?v=1.5">
    <link rel="apple-touch-icon" href="<?php echo e(asset('favicon_euro_transparent.png')); ?>?v=1.5">
    <link rel="manifest" href="<?php echo e(asset('manifest.json')); ?>?v=1.5">
    <link rel="manifest" href="/public/manifest.json?v=1.5">

    <!-- Tailwind CSS -->
    <script>
        // Silence Tailwind CDN production warning
        if (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            window.tailwind = { config: { silent: true } };
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
    </style>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Custom CSS -->
    <link href="<?php echo e(asset('assets/app.css')); ?>" rel="stylesheet">
    <?php echo $__env->yieldPushContent('styles'); ?>

    <!-- Custom JS -->
    <script src="<?php echo e(asset('assets/app.js')); ?>"></script>

    <!-- Chart.js for Dashboard -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <?php if(auth()->guard()->check()): ?>
        <?php
            // Notifications for header bell
            $headerNotifications = [];
            $headerNotificationCount = 0;

        ?>

        <!-- Main Layout -->
        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <aside class="w-16 lg:w-60 bg-white shadow-lg flex-shrink-0 transition-all duration-300 overflow-x-hidden">
                <div class="h-full flex flex-col">
                    <!-- Logo -->
                    <div class="p-2 lg:p-4 border-b flex flex-col items-center">
                        <img src="<?php echo e(asset('uploads/logo.png')); ?>" alt="Euro System Logo" class="h-8 lg:h-12 w-auto mb-1">
                        <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold hidden lg:block">Fleet Management</p>
                    </div>

                    <!-- Navigation -->
                    <nav class="flex-1 p-2 lg:p-4 space-y-1 overflow-y-auto overflow-x-hidden">
                        <a href="<?php echo e(route('dashboard')); ?>"
                            class="sidebar-item flex items-center justify-center lg:justify-start lg:gap-2.5 px-0 lg:px-4 py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('dashboard') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="layout-dashboard" class="w-5 lg:w-4 h-5 lg:h-4"></i>
                            <span class="text-sm hidden lg:block">Dashboard</span>
                        </a>

                        <a href="<?php echo e(route('units.index')); ?>"
                            class="sidebar-item flex items-center justify-center lg:justify-start lg:gap-2.5 px-0 lg:px-4 py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('units.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="car" class="w-5 lg:w-4 h-5 lg:h-4"></i>
                            <span class="text-sm hidden lg:block">Unit Management</span>
                        </a>

                        <a href="<?php echo e(route('driver-management.index')); ?>"
                            class="sidebar-item flex items-center justify-center lg:justify-start lg:gap-2.5 px-0 lg:px-4 py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('driver-management.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="users" class="w-5 lg:w-4 h-5 lg:h-4"></i>
                            <span class="text-sm hidden lg:block">Driver Management</span>
                        </a>

                        <a href="<?php echo e(route('live-tracking.index')); ?>"
                            class="sidebar-item flex items-center justify-center lg:justify-start lg:gap-2.5 px-0 lg:px-4 py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('live-tracking.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="map-pin" class="w-5 lg:w-4 h-5 lg:h-4"></i>
                            <span class="text-sm hidden lg:block">Live Tracking</span>
                        </a>

                        <a href="<?php echo e(route('decision-management.index')); ?>"
                            class="sidebar-item flex items-center justify-center lg:justify-start lg:gap-2.5 px-0 lg:px-4 py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('decision-management.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="file-text" class="w-5 lg:w-4 h-5 lg:h-4"></i>
                            <span class="text-sm hidden lg:block">Franchise</span>
                        </a>

                        <a href="<?php echo e(route('boundaries.index')); ?>"
                            class="sidebar-item flex items-center justify-center lg:justify-start lg:gap-2.5 px-0 lg:px-4 py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('boundaries.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="dollar-sign" class="w-5 lg:w-4 h-5 lg:h-4"></i>
                            <span class="text-sm hidden lg:block">Boundaries</span>
                        </a>

                        <a href="<?php echo e(route('maintenance.index')); ?>"
                            class="sidebar-item flex items-center justify-center lg:justify-start lg:gap-2.5 px-0 lg:px-4 py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('maintenance.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="wrench" class="w-5 lg:w-4 h-5 lg:h-4"></i>
                            <span class="text-sm hidden lg:block">Maintenance</span>
                        </a>

                        <a href="<?php echo e(route('coding.index')); ?>"
                            class="sidebar-item flex items-center justify-center lg:justify-start lg:gap-2.5 px-0 lg:px-4 py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('coding.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="calendar" class="w-5 lg:w-4 h-5 lg:h-4"></i>
                            <span class="text-sm hidden lg:block">Coding Management</span>
                        </a>

                        <a href="<?php echo e(route('driver-behavior.index')); ?>"
                            class="sidebar-item flex items-center justify-center lg:justify-start lg:gap-2.5 px-0 lg:px-4 py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('driver-behavior.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="alert-triangle" class="w-5 lg:w-4 h-5 lg:h-4"></i>
                            <span class="text-sm hidden lg:block">Driver Behavior</span>
                        </a>

                        <a href="<?php echo e(route('office-expenses.index')); ?>"
                            class="sidebar-item flex items-center justify-center lg:justify-start lg:gap-2.5 px-0 lg:px-4 py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('office-expenses.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="receipt" class="w-5 lg:w-4 h-5 lg:h-4"></i>
                            <span class="text-sm hidden lg:block">Office Expenses</span>
                        </a>

                        <a href="<?php echo e(route('salary.index')); ?>"
                            class="sidebar-item flex items-center justify-center lg:justify-start lg:gap-2.5 px-0 lg:px-4 py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('salary.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="calculator" class="w-5 lg:w-4 h-5 lg:h-4"></i>
                            <span class="text-sm hidden lg:block">Salary Management</span>
                        </a>

                        <a href="<?php echo e(route('analytics.index')); ?>"
                            class="sidebar-item flex items-center justify-center lg:justify-start lg:gap-2.5 px-0 lg:px-4 py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('analytics.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="bar-chart" class="w-4 h-4"></i>
                            <span class="text-sm hidden lg:block">Analytics</span>
                        </a>


                        <a href="<?php echo e(route('unit-profitability.index')); ?>"
                            class="sidebar-item flex items-center justify-center lg:justify-start lg:gap-2.5 px-0 lg:px-4 py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('unit-profitability.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="trending-up" class="w-5 lg:w-4 h-5 lg:h-4"></i>
                            <span class="text-sm hidden lg:block">Unit Profitability</span>
                        </a>

                        <a href="<?php echo e(route('staff.index')); ?>"
                            class="sidebar-item flex items-center justify-center lg:justify-start lg:gap-2.5 px-0 lg:px-4 py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('staff.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="user-cog" class="w-5 lg:w-4 h-5 lg:h-4"></i>
                            <span class="text-sm hidden lg:block">Staff Records</span>
                        </a>
                    </nav>

                    <!-- User Menu -->
                    <div class="p-2 lg:p-4 border-t">
                        <a href="<?php echo e(route('my-account')); ?>" 
                           class="flex items-center justify-center lg:justify-start lg:gap-3 mb-3 p-1 lg:p-2 rounded-lg hover:bg-gray-50 transition-colors group">
                            <div
                                class="w-8 h-8 lg:w-10 lg:h-10 bg-yellow-600 rounded-full flex items-center justify-center text-white font-semibold group-hover:bg-yellow-700 transition-colors overflow-hidden flex-shrink-0 border border-gray-100">
                                <?php if(auth()->user()->profile_image): ?>
                                    <?php
                                        $imagePath = str_replace('resources/', '', auth()->user()->profile_image);
                                        $isIcon = str_contains($imagePath, 'image/') && !str_contains($imagePath, 'storage/');
                                    ?>
                                    <?php if($isIcon): ?>
                                        <img src="<?php echo e(asset($imagePath)); ?>" alt="Profile" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <img src="<?php echo e(asset('storage/' . auth()->user()->profile_image)); ?>" alt="Profile" class="w-full h-full object-cover">
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php echo e(strtoupper(substr(auth()->user()->full_name ?? 'U', 0, 1))); ?>

                                <?php endif; ?>
                            </div>
                            <div class="flex-1 hidden lg:block">
                                <p class="text-sm font-medium text-gray-900 group-hover:text-yellow-700 transition-colors"><?php echo e(auth()->user()->full_name ?? 'User'); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e(ucfirst(auth()->user()->role ?? 'user')); ?></p>
                            </div>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400 group-hover:text-yellow-600 transition-colors hidden lg:block"></i>
                        </a>
                        <a href="<?php echo e(route('logout')); ?>"
                            class="flex items-center justify-center lg:justify-start lg:gap-2 px-1 lg:px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg w-full"
                            onclick="event.preventDefault(); if(confirm('Are you sure you want to logout?')) { window.location.href = '<?php echo e(route('logout')); ?>'; }">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span class="hidden lg:block">Logout</span>
                        </a>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 flex flex-col overflow-hidden">
                <!-- Top Bar -->
                <header class="bg-white shadow-sm border-b px-6 py-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-900"><?php echo $__env->yieldContent('page-heading', 'Dashboard'); ?></h2>
                            <?php if (! empty(trim($__env->yieldContent('page-subheading')))): ?>
                                <p class="text-sm text-gray-500 mt-1"><?php echo $__env->yieldContent('page-subheading'); ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center gap-4">
                            <!-- Notification Bell -->
                            <div class="relative">
                                <button id="notificationBell"
                                    class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                    <i data-lucide="bell" class="w-5 h-5"></i>
                                    <?php if($headerNotificationCount > 0): ?>
                                        <span
                                            class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] leading-[18px] rounded-full text-center">
                                            <?php echo e($headerNotificationCount); ?>

                                        </span>
                                    <?php endif; ?>
                                </button>

                                <div id="notificationDropdown"
                                    class="hidden absolute right-0 mt-2 w-80 bg-white shadow-lg rounded-lg border border-gray-200 z-50">
                                    <div class="px-4 py-2 border-b flex items-center justify-between">
                                        <span class="text-sm font-semibold text-gray-800">Notifications</span>
                                        <span class="text-xs text-gray-500"><?php echo e($headerNotificationCount); ?> item(s)</span>
                                    </div>
                                    <div class="max-h-80 overflow-y-auto" id="notificationList">
                                        <?php if(empty($headerNotifications)): ?>
                                            <div class="px-4 py-4 text-sm text-gray-500 text-center">No notifications.</div>
                                        <?php else: ?>
                                            <?php $__currentLoopData = $headerNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="notification-item px-4 py-3 border-b last:border-b-0 hover:bg-gray-50 flex items-start gap-2"
                                                    data-type="<?php echo e($n['type']); ?>" <?php if(isset($n['id'])): ?> data-id="<?php echo e($n['id']); ?>"
                                                    <?php endif; ?>>
                                                    <a href="<?php echo e($n['url'] ?? '#'); ?>" class="flex-1 flex gap-3 min-w-0">

                                                        <div class="mt-0.5 flex-shrink-0">
                                                            <?php if($n['type'] === 'case_expiry'): ?>
                                                                <i data-lucide="file-warning" class="w-4 h-4 text-yellow-600"></i>
                                                            <?php elseif($n['type'] === 'coding_today'): ?>
                                                                <i data-lucide="car-front" class="w-4 h-4 text-blue-600"></i>
                                                            <?php else: ?>
                                                                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-xs font-semibold text-gray-800 truncate">
                                                                <?php echo e($n['title']); ?></p>
                                                            <p class="text-xs text-gray-600 mt-0.5 line-clamp-2"><?php echo e($n['message']); ?>

                                                            </p>
                                                        </div>
                                                    </a>
                                                    <button type="button"
                                                        class="ml-1 text-gray-400 hover:text-gray-600 flex-shrink-0"
                                                        onclick="dismissNotification(this);">
                                                        <span class="sr-only">Dismiss</span>
                                                        <i data-lucide="x" class="w-3 h-3"></i>
                                                    </button>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Date/Time -->
                            <div class="text-right">
                                <p class="text-[13px] font-medium text-gray-900"><?php echo e(date('l, F j, Y')); ?></p>
                                <p class="text-[11px] text-gray-500"><?php echo e(date('h:i A')); ?></p>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <div class="flex-1 overflow-y-auto p-4">
                    
                    <?php $__currentLoopData = ['success', 'error', 'warning', 'info']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(session($type)): ?>
                            <div class="alert-slide mb-4 p-4 rounded-lg border
                                    <?php if($type === 'success'): ?> bg-green-50 border-green-200 text-green-800
                                    <?php elseif($type === 'error'): ?> bg-red-50 border-red-200 text-red-800
                                    <?php elseif($type === 'warning'): ?> bg-yellow-50 border-yellow-200 text-yellow-800
                                    <?php else: ?> bg-blue-50 border-blue-200 text-blue-800
                                    <?php endif; ?>">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="<?php if($type === 'success'): ?> check-circle <?php elseif($type === 'error'): ?> x-circle <?php elseif($type === 'warning'): ?> alert-triangle <?php else: ?> info <?php endif; ?>"
                                        class="w-5 h-5"></i>
                                    <span><?php echo e(session($type)); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    
                    <?php if($errors->any()): ?>
                        <div class="alert-slide mb-4 p-4 rounded-lg border bg-red-50 border-red-200 text-red-800">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="x-circle" class="w-5 h-5"></i>
                                <span class="font-semibold">Please fix the following errors:</span>
                            </div>
                            <ul class="list-disc list-inside text-sm">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </main>
        </div>

    <?php else: ?>
        <!-- Login/Signup Layout -->
        <div class="min-h-screen bg-gradient-to-br from-yellow-50 to-orange-50 flex items-center justify-center p-4">
            <div class="w-full max-w-md">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Initialize Lucide icons -->
    <script>
        lucide.createIcons();
    </script>

    <!-- Common JavaScript -->
    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert-slide');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);

        // Common AJAX function
        async function makeRequest(url, options = {}) {
            try {
                const response = await fetch(url, {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        ...options.headers
                    },
                    ...options
                });
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return await response.json();
            } catch (error) {
                console.error('Request failed:', error);
                throw error;
            }
        }

        function updateNotificationCount() {
            const list = document.getElementById('notificationList');
            const countSpan = document.querySelector('#notificationDropdown .border-b span.text-xs');
            const badge = document.querySelector('#notificationBell span');
            const count = list ? list.querySelectorAll('.notification-item').length : 0;
            if (countSpan) countSpan.textContent = count + ' item(s)';
            if (badge) {
                if (count > 0) {
                    badge.textContent = count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
        }

        function dismissNotification(button) {
            event.stopPropagation();
            const item = button.closest('.notification-item');
            if (!item) return;
            const type = item.getAttribute('data-type');
            const id = item.getAttribute('data-id');
            item.remove();
            updateNotificationCount();
            if (type === 'system' && id) {
                fetch('<?php echo e(route("notifications.dismiss")); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: 'id=' + encodeURIComponent(id)
                }).catch(err => console.error('Failed to dismiss:', err));
            }
        }


    </script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html><?php /**PATH C:\xampp\htdocs\eurotaxisystem\resources\views/layouts/app.blade.php ENDPATH**/ ?>