<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$totalUnits = DB::table('units')->whereNull('deleted_at')->count();

$filled = DB::table('units')
    ->whereNull('deleted_at')
    ->whereNotNull('motor_no')
    ->where('motor_no', '!=', '')
    ->whereNotNull('chassis_no')
    ->where('chassis_no', '!=', '')
    ->count();

$blank = DB::table('units')
    ->whereNull('deleted_at')
    ->where(function($query) {
        $query->whereNull('motor_no')
              ->orWhere('motor_no', '')
              ->orWhereNull('chassis_no')
              ->orWhere('chassis_no', '');
    })
    ->count();

echo "Total Units: " . $totalUnits . "\n";
echo "Filled (May Motor at Chassis): " . $filled . "\n";
echo "Blank (Walang Motor or Chassis): " . $blank . "\n";

// List the blank ones 
if ($blank > 0) {
    echo "\nBlank Units Plate Numbers:\n";
    $blankUnits = DB::table('units')
        ->whereNull('deleted_at')
        ->where(function($query) {
            $query->whereNull('motor_no')
                  ->orWhere('motor_no', '')
                  ->orWhereNull('chassis_no')
                  ->orWhere('chassis_no', '');
        })
        ->get(['plate_number']);
    foreach($blankUnits as $bu) {
        echo "- " . $bu->plate_number . "\n";
    }
}
