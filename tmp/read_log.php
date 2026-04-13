<?php
$log = file_get_contents(__DIR__ . '/../storage/logs/laravel.log');
echo substr($log, -3000);
