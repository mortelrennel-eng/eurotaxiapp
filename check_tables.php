<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "franchise_cases: " . (Schema::hasTable('franchise_cases') ? 'YES' : 'NO') . "\n";
echo "franchise_case_units: " . (Schema::hasTable('franchise_case_units') ? 'YES' : 'NO') . "\n";
