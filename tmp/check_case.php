<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$case = DB::table('franchise_cases')->where('case_no', '2015-00083 - Franchise')->first();
if ($case) {
    echo "Found Case: " . $case->case_no . "\n";
    $units = DB::table('franchise_case_units')->where('franchise_case_id', $case->id)->get();
    echo "Units count: " . $units->count() . "\n";
} else {
    echo "Case not found!\n";
}
