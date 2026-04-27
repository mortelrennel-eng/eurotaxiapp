<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$column = DB::select("SHOW COLUMNS FROM maintenance LIKE 'status'")[0];
echo "Type: " . $column->Type . "\n";
echo "Default: " . $column->Default . "\n";
echo "Null: " . $column->Null . "\n";
