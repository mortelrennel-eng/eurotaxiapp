<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$case = DB::table('franchise_cases')->where('case_no', '2015-00083 - Franchise')->first();

if ($case) {
    echo "Found Case ID: " . $case->id . "\n";
    $units = DB::table('franchise_case_units')->where('franchise_case_id', $case->id)->get();
    echo "Total Units: " . $units->count() . "\n";
    $plates = $units->pluck('plate_no')->toArray();
    print_r($plates);

    // Missing: AAK 9196
    if (!in_array('AAK 9196', $plates)) {
        echo "Adding AAK 9196...\n";
        DB::table('franchise_case_units')->insert([
            'franchise_case_id' => $case->id,
            'make' => 'TOYOTA VIOS',
            'motor_no' => '2NZ7307868',
            'chasis_no' => 'NCP1512031009',
            'plate_no' => 'AAK 9196',
            'year_model' => '2015',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "Added successfully!\n";
    }
} else {
    echo "Case not found!\n";
}
