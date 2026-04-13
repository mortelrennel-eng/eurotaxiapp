<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SparePart;

$parts = [
    ['name' => 'Toyota Genuine Oil Filter', 'price' => 450.00],
    ['name' => 'Air Filter (Toyota Vios/Hiace)', 'price' => 850.00],
    ['name' => 'Brake Pads Front (Genuine)', 'price' => 2450.00],
    ['name' => 'Brake Shoes Rear', 'price' => 1650.00],
    ['name' => 'Iridium Spark Plugs (Set of 4)', 'price' => 1800.00],
    ['name' => 'Fully Synthetic Engine Oil (4L)', 'price' => 2200.00],
    ['name' => 'Toyota Super Long Life Coolant (1L)', 'price' => 450.00],
    ['name' => 'Toyota Genuine Wiper Blade (Set)', 'price' => 750.00],
    ['name' => 'Fuel Filter (Genuine)', 'price' => 2800.00],
    ['name' => 'Cabin/AC Filter', 'price' => 450.00],
    ['name' => 'Serpentine Belt', 'price' => 950.00],
    ['name' => 'Motolite Gold Battery (NS40)', 'price' => 4800.00],
    ['name' => 'Brake Fluid (500ml)', 'price' => 350.00],
    ['name' => 'ATF / CVT Transmission Fluid (1L)', 'price' => 650.00],
    ['name' => 'Clutch Disc (Genuine)', 'price' => 3200.00],
    ['name' => 'Release Bearing (Genuine)', 'price' => 1200.00],
    ['name' => 'Wheel Hub / Bearing Front', 'price' => 3500.00],
    ['name' => 'Shock Absorber Front (Pair)', 'price' => 5500.00],
    ['name' => 'Shock Absorber Rear (Pair)', 'price' => 4200.00],
    ['name' => 'Tie Rod End (Pair)', 'price' => 1800.00]
];

foreach($parts as $p) {
    SparePart::updateOrCreate(['name' => $p['name']], $p);
}

echo "Successfully seeded " . count($parts) . " parts.";
