<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$case = [
    'applicant_name' => 'ROBERT GARCIA',
    'case_no' => '2015-00083 - Franchise',
    'type_of_application' => 'EXTENSION OF VALIDITY',
    'denomination' => 'TAXI AIRCONDITIONED SERVICE',
    'date_filed' => '2022-08-26',
    'expiry_date' => '2027-09-02',
    'status' => 'approved',
    'created_at' => now(),
    'updated_at' => now()
];

$units = [
    ['TOYOTA VIOS', '2NZ6978423', 'NCP1512012488', 'AAA 4591', '2014'],
    ['TOYOTA VIOS', '2NZ7160776', 'NCP1512022508', 'AAQ 1743', '2014'],
    ['TOYOTA VIOS', '2NZ7494105', 'NCP1512043398', 'ABG 7479', '2015'],
    ['TOYOTA VIOS', '2NZ7384223', 'NCP1512036531', 'ALA 3699', '2015'],
    ['TOYOTA VIOS', '2NZ7307868', 'NCP1512031009', 'AAK 9195', '2015'],
    ['TOYOTA VIOS', '2NZ7400896', 'NCP1512037524', 'ABL 6901', '2015'],
    ['TOYOTA VIOS', '2NZ7301579', 'NCP1512030436', 'AEA 9630', '2015'],
    ['TOYOTA VIOS', '2NZ7542383', 'NCP1512046832', 'ABL 1667', '2015'],
    ['TOYOTA VIOS', '2NZ7557953', 'NCP1512048091', 'ABP 2705', '2015'],
    ['TOYOTA VIOS', '2NZ7541411', 'NCP1512046789', 'ABP 7643', '2015'],
    ['TOYOTA VIOS', '2NZ7470861', 'NCP1512042785', 'ABF 7471', '2015'],
    ['TOYOTA VIOS', '2NZ7263141', 'NCP1512028527', 'AOA 8917', '2015'],
    ['TOYOTA VIOS', '2NZ7666502', 'NCP1512055742', 'NBW 7071', '2016'],
    ['TOYOTA VIOS', '1NRX136597', 'PA1B19F31H4026529', 'NBX 4348', '2017'],
    ['TOYOTA VIOS', '1NRX116001', 'PA1B19F35H4021382', 'NAE 7193', '2017'],
    ['TOYOTA VIOS', '1NRX072072', 'PA1B19F3XG4012319', 'NAC 4969', '2017'],
    ['TOYOTA VIOS', '1NRX093367', 'PA1B19F36G4016559', 'NAD 1140', '2014'],
    ['TOYOTA VIOS', '1NRX074746', 'PA1B19F32G4012928', 'NDG 7105', '2017'],
    ['TOYOTA VIOS', '1NRX049858', 'PA1B19F37G4007336', 'DCQ 1551', '2017'],
    ['TOYOTA VIOS', '1NRX128495', 'PA1B19F32H4024496', 'DAD 7555', '2017']
];

DB::transaction(function() use ($case, $units) {
    // Check if it exists
    $existing = DB::table('franchise_cases')->where('case_no', $case['case_no'])->first();
    if ($existing) {
        $caseId = $existing->id;
        DB::table('franchise_cases')->where('id', $caseId)->update($case);
        DB::table('franchise_case_units')->where('franchise_case_id', $caseId)->delete();
        echo "Updated existing case ID: " . $caseId . "\n";
    } else {
        $caseId = DB::table('franchise_cases')->insertGetId($case);
        echo "Created new case ID: " . $caseId . "\n";
    }

    foreach ($units as $u) {
        DB::table('franchise_case_units')->insert([
            'franchise_case_id' => $caseId,
            'make' => $u[0],
            'motor_no' => $u[1],
            'chasis_no' => $u[2],
            'plate_no' => $u[3],
            'year_model' => $u[4],
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    echo "Inserted 20 units for case.\n";
});
