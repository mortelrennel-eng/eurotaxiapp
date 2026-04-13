<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$franchiseUnits = DB::table('franchise_case_units')->get();

$updated = 0;
foreach ($franchiseUnits as $fu) {
    if (!$fu->plate_no) continue;

    // Check if the case is deleted
    $case = DB::table('franchise_cases')->where('id', $fu->franchise_case_id)->whereNull('deleted_at')->first();
    if (!$case) continue;

    $updatedRows = DB::table('units')
        ->where('plate_number', $fu->plate_no)
        ->update([
            'motor_no' => $fu->motor_no,
            'chassis_no' => $fu->chasis_no
        ]);
        
    if ($updatedRows > 0) {
        $updated++;
    }
}

echo "Successfully updated $updated units with precise Franchise motor and chassis data!\n";
