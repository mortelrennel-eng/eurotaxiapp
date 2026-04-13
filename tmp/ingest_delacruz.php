<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$c = [
    'case_no' => '2012-0502',
    'applicant' => 'DELA CRUZ EDUARDO',
    'expiry' => 'February 9, 2026',
    'date_filed' => 'November 8, 2021',
    'type_of_application' => 'Franchise Verification',
    'units' => [
        ['make' => 'TOYOTA VIOS', 'motor_no' => '2NZ7847183', 'chasis_no' => 'NCP1512071757', 'plate_no' => 'ADY2597', 'year_model' => '2016'],
        ['make' => 'TOYOTA VIOS', 'motor_no' => '2NZ7868669', 'chasis_no' => 'NCP1512074065', 'plate_no' => 'ADY2599', 'year_model' => '2016'],
        ['make' => 'TOYOTA VIOS', 'motor_no' => '2NZ7868643', 'chasis_no' => 'NCP1512074063', 'plate_no' => 'ADY2598', 'year_model' => '2016'],
        ['make' => 'TOYOTA VIOS', 'motor_no' => '2NZ7474668', 'chasis_no' => 'NCP1512042968', 'plate_no' => 'ASA6135', 'year_model' => '2015'],
        ['make' => 'TOYOTA VIOS', 'motor_no' => '2NZ7871027', 'chasis_no' => 'NCP1512074362', 'plate_no' => 'NCJ7661', 'year_model' => '2018'],
        ['make' => 'TOYOTA VIOS', 'motor_no' => '1NRX051542', 'chasis_no' => 'PA1B19F37G4007854', 'plate_no' => 'NDC7363', 'year_model' => '2017'],
        ['make' => 'TOYOTA VIOS', 'motor_no' => '1NRX078597', 'chasis_no' => 'PA1B19F33G4013649', 'plate_no' => 'EAA4540', 'year_model' => '2017'],
        ['make' => 'TOYOTA VIOS', 'motor_no' => '1NRX202099', 'chasis_no' => 'PA1B19F36H4042726', 'plate_no' => 'EAA9555', 'year_model' => '2017'],
        ['make' => 'TOYOTA VIOS', 'motor_no' => '1NRX344222', 'chasis_no' => 'PA1B13F32J4064743', 'plate_no' => 'NBR1341', 'year_model' => '2018'],
        ['make' => 'TOYOTA VIOS', 'motor_no' => '1NRX366474', 'chasis_no' => 'PA1B13F36J4070268', 'plate_no' => 'EAB8186', 'year_model' => '2019']
    ]
];

DB::beginTransaction();
try {
    $caseNo = trim($c['case_no']);
    
    // Find existing case
    $exist = DB::table('franchise_cases')
        ->where('case_no', $caseNo)
        ->whereNull('deleted_at')
        ->first();

    $caseId = null;
    if ($exist) {
        $caseId = $exist->id;
        DB::table('franchise_cases')->where('id', $caseId)->update([
            'applicant_name' => $c['applicant'],
            'expiry_date' => Carbon::parse($c['expiry'])->format('Y-m-d')
        ]);
        DB::table('franchise_case_units')->where('franchise_case_id', $caseId)->delete();
        echo "Updated existing case: $caseNo\n";
    } else {
        // Create new case
        $caseId = DB::table('franchise_cases')->insertGetId([
            'applicant_name' => $c['applicant'],
            'case_no' => $caseNo,
            'type_of_application' => $c['type_of_application'],
            'denomination' => 'Taxi Airconditioned Service',
            'date_filed' => Carbon::parse($c['date_filed'])->format('Y-m-d'),
            'expiry_date' => Carbon::parse($c['expiry'])->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "Inserted NEW case: $caseNo\n";
    }

    // Insert 10 units
    $added = 0;
    foreach ($c['units'] as $u) {
        DB::table('franchise_case_units')->insert([
            'franchise_case_id' => $caseId,
            'make' => trim($u['make']),
            'motor_no' => trim($u['motor_no']),
            'chasis_no' => trim($u['chasis_no']),
            'plate_no' => trim($u['plate_no']),
            'year_model' => trim($u['year_model']),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $added++;
    }

    DB::commit();
    echo "SUCCESS: Added $added valid units (Ignored 'Drop.& Subst.' units).\n";
} catch (\Exception $e) {
    DB::rollback();
    echo "ERROR: " . $e->getMessage() . "\n";
}
