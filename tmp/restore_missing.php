<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Add back NCR 2018-4-2015-02364 
$caseId = DB::table('franchise_cases')->insertGetId([
    'applicant_name' => 'RQG TRANSPORT',
    'case_no' => 'NCR 2018-4-2015-02364',
    'type_of_application' => 'Extension of Validity',
    'denomination' => 'Taxi Airconditioned Service',
    'date_filed' => Carbon::parse('October 31, 2023')->subYears(5)->format('Y-m-d'),
    'expiry_date' => Carbon::parse('October 31, 2023')->format('Y-m-d'),
    'created_at' => now(),
    'updated_at' => now(),
]);

DB::table('franchise_case_units')->insert([
    'franchise_case_id' => $caseId,
    'make' => 'TOYOTA VIOS',
    'motor_no' => '1NRX507225',
    'chasis_no' => 'PA1B13F31K4102013',
    'plate_no' => 'NEF 4940',
    'year_model' => '2020',
    'created_at' => now(),
    'updated_at' => now()
]);
echo "Restored missing unit NEF 4940!\n";
