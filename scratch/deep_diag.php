<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$u = DB::table('units')->where('plate_number', 'ABP 7643')->first();
$d1 = $u->driver_id ? DB::table('drivers')->where('id', $u->driver_id)->first() : null;
$d2 = $u->secondary_driver_id ? DB::table('drivers')->where('id', $u->secondary_driver_id)->first() : null;

echo "UNIT: " . $u->plate_number . "\n";
echo "D1 ID: " . ($u->driver_id ?? 'NULL') . " NAME: " . ($d1 ? $d1->first_name . " " . $d1->last_name : 'N/A') . "\n";
echo "D2 ID: " . ($u->secondary_driver_id ?? 'NULL') . " NAME: " . ($d2 ? $d2->first_name . " " . $d2->last_name : 'N/A') . "\n";

$units = DB::table('units as u')
    ->leftJoin('drivers as d1', 'u.driver_id', '=', 'd1.id')
    ->leftJoin('drivers as d2', 'u.secondary_driver_id', '=', 'd2.id')
    ->select(
        'u.plate_number',
        DB::raw("TRIM(CONCAT(COALESCE(d1.first_name,''), ' ', COALESCE(d1.last_name,''))) as current_driver"),
        DB::raw("TRIM(CONCAT(COALESCE(d2.first_name,''), ' ', COALESCE(d2.last_name,''))) as secondary_driver")
    )
    ->where('u.plate_number', 'ABP 7643')
    ->get();

echo "QUERY RESULT:\n";
print_r($units->toArray());
