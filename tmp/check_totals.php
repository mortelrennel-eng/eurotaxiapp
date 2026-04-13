<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$c = \Illuminate\Support\Facades\DB::table('franchise_cases')->whereNull('deleted_at')->get();
foreach ($c as $case) {
    $uc = \Illuminate\Support\Facades\DB::table('franchise_case_units')->where('franchise_case_id', $case->id)->count();
    echo $case->case_no . ' (' . $case->applicant_name . '): ' . $uc . "\n";
}
echo 'Total cases: ' . count($c) . "\n";
echo 'Total units: ' . \Illuminate\Support\Facades\DB::table('franchise_case_units')
    ->join('franchise_cases', 'franchise_cases.id', '=', 'franchise_case_units.franchise_case_id')
    ->whereNull('franchise_cases.deleted_at')
    ->count() . "\n";
