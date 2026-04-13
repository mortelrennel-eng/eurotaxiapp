<?php
// Security check: only allow if a specific key is provided
if (($_GET['key'] ?? '') !== 'eurotaxi_deploy_2026') {
    die('Unauthorized');
}

echo "Starting Deployment...\n";
$output = [];
$status = 0;

// Execute deployment commands
exec('git reset --hard deploy 2>&1', $output, $status);
echo "Reset Status: $status\n";
echo "Reset Output: " . implode("\n", $output) . "\n\n";

$output = [];
exec('php artisan migrate --force 2>&1', $output, $status);
echo "Migrate Status: $status\n";
echo "Migrate Output: " . implode("\n", $output) . "\n\n";

$output = [];
exec('php artisan optimize 2>&1', $output, $status);
echo "Optimize Status: $status\n";

echo "Deployment Finished.";
