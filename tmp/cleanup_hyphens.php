<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Find and delete the dummy cases with hyphens that overlap with our perfect space-separated cases
DB::table('franchise_cases')->where('case_no', 'NCR-2018-4-2015-02365')->delete();
DB::table('franchise_cases')->where('case_no', 'NCR-2018-4-2015-02370')->delete();
DB::table('franchise_cases')->where('case_no', 'NCR-2018-4-2015-02364')->delete(); // Wait, let's check if there is a space counterpart

$c1 = DB::table('franchise_cases')->where('case_no', 'NCR-2018-4-2015-02364')->first();
if ($c1) {
    // Actually in my data script, I used the dash for 02364.
    // Let's standardise to space
    DB::table('franchise_cases')->where('id', $c1->id)->update(['case_no' => 'NCR 2018-4-2015-02364']);
    echo "Corrected 02364 to space formatting.\n";
}

echo "Duplicate hyphenated dummy cases deleted successfully!\n";
