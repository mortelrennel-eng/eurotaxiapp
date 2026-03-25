<?php
// migration_runner.php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Add status column
    if (!\Illuminate\Support\Facades\Schema::hasColumn('franchise_cases', 'status')) {
        \Illuminate\Support\Facades\DB::statement("
            ALTER TABLE `franchise_cases` 
            ADD COLUMN `status` ENUM('pending', 'approved', 'denied', 'expired') DEFAULT 'pending' AFTER `expiry_date`
        ");
        echo "Status column added successfully\n";
    }
    
    // Add unit_id column
    if (!\Illuminate\Support\Facades\Schema::hasColumn('franchise_cases', 'unit_id')) {
        \Illuminate\Support\Facades\DB::statement("
            ALTER TABLE `franchise_cases` 
            ADD COLUMN `unit_id` INT NULL AFTER `denomination`
        ");
        echo "Unit ID column added successfully\n";
    }
    
    // Add notes column
    if (!\Illuminate\Support\Facades\Schema::hasColumn('franchise_cases', 'notes')) {
        \Illuminate\Support\Facades\DB::statement("
            ALTER TABLE `franchise_cases` 
            ADD COLUMN `notes` TEXT NULL AFTER `status`
        ");
        echo "Notes column added successfully\n";
    }
    
    echo "Database migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
