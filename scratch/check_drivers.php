<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$units = DB::table('units as u')
    ->leftJoin('drivers as d1', 'u.driver_id', '=', 'd1.id')
    ->leftJoin('drivers as d2', 'u.secondary_driver_id', '=', 'd2.id')
    ->select(
        'u.id', 'u.plate_number',
        DB::raw("TRIM(CONCAT(COALESCE(d1.first_name,''), ' ', COALESCE(d1.last_name,''))) as current_driver"),
        DB::raw("TRIM(CONCAT(COALESCE(d2.first_name,''), ' ', COALESCE(d2.last_name,''))) as secondary_driver")
    )
    ->where('u.plate_number', 'CAV 6803')
    ->get();

print_r($units->toArray());
