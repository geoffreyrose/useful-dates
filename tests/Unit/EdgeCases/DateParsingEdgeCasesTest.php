<?php

use Carbon\Carbon;
use UsefulDates\Exceptions\InvalidDateException;
use UsefulDates\UsefulDates;

it('handles invalid date string throws InvalidDateException', function (): void {
    $ud = new UsefulDates;

    expect(fn () => $ud->setDate('this is not a date'))
        ->toThrow(InvalidDateException::class);
});

it('handles empty string date throws InvalidDateException', function (): void {
    $ud = new UsefulDates;

    expect(fn () => $ud->setDate(''))
        ->toThrow(InvalidDateException::class);
});

it('handles DateTime object conversion', function (): void {
    $ud = new UsefulDates;
    $datetime = new DateTime('2025-06-15 14:30:00');

    $ud->setDate($datetime);

    expect($ud->date->format('Y-m-d'))->toEqual('2025-06-15');
});

it('handles various date string formats', function (string $dateString): void {
    $ud = new UsefulDates;

    $ud->setDate($dateString);

    expect($ud->date)->toBeInstanceOf(Carbon::class);
})->with([
    '2025-12-31',
    '2025/12/31',
    'December 31, 2025',
    '31 Dec 2025',
    'tomorrow',
    'next Monday',
    '+1 week',
]);

it('handles leap year dates', function (): void {
    $ud = new UsefulDates;

    $ud->setDate('2024-02-29'); // Valid leap year date

    expect($ud->date->format('Y-m-d'))->toEqual('2024-02-29');
});

it('handles timezone normalization to UTC', function (): void {
    $ud = new UsefulDates;

    $pstDate = Carbon::create('2025-06-15 14:30:00', 'America/Los_Angeles');
    $ud->setDate($pstDate);

    expect($ud->date->timezone->getName())->toEqual('UTC');
});

it('handles year boundaries correctly', function (): void {
    $ud = new UsefulDates;

    // Last second of the year
    $ud->setDate('2024-12-31 23:59:59');
    expect($ud->date->year)->toEqual(2024);

    // First second of the year
    $ud->setDate('2025-01-01 00:00:00');
    expect($ud->date->year)->toEqual(2025);
});

it('handles extreme past dates', function (): void {
    $ud = new UsefulDates;

    $ud->setDate('1900-01-01');

    expect($ud->date->year)->toEqual(1900);
});

it('handles extreme future dates', function (): void {
    $ud = new UsefulDates;

    $ud->setDate('2999-12-31');

    expect($ud->date->year)->toEqual(2999);
});
