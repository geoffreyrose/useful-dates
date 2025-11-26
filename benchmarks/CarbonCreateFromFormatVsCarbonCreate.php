<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Carbon\Carbon;

$startTime = microtime(true);
for ($i = 0; $i < 100000; $i++) {
    Carbon::createFromFormat('Y-m-d', '2025-01-01');
}
$endTime = microtime(true);
echo '(createFromFormat) Execution time: ' . ($endTime - $startTime) . ' seconds';

echo "\n";

$startTime = microtime(true);
for ($i = 0; $i < 100000; $i++) {
    Carbon::create(2025, 1, 1);
}
$endTime = microtime(true);
echo '(create) Execution time: ' . ($endTime - $startTime) . ' seconds';
