<?php

require_once __DIR__ . '/../vendor/autoload.php';

$usefulDate = new UsefulDates\UsefulDates;
$usefulDate->setDate(\Carbon\Carbon::create('2025-01-01'));
$usefulDate->addDate("Patrick Star's Birthday", \Carbon\Carbon::createFromFormat('Y-m-d', '1999-08-17'), startYear: 1999);
$usefulDate->addDate('April Fools Day', \Carbon\Carbon::createFromFormat('Y-m-d', '1582-04-01'), startYear: 1582);

// time getting the next 1000 useful dates
$startTime = microtime(true);
$list = $usefulDate->getUsefulDatesInYears(500);
foreach ($list as $t) {
    $test = $t->name . ' ' . $t->usefulDate() . ' ' . $t->daysAway() . '<br>';
    // echo $test;
}
$endTime = microtime(true);
echo 'Execution time: ' . ($endTime - $startTime) . ' seconds';
