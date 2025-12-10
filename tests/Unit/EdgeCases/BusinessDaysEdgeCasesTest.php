<?php

use Carbon\Carbon;
use UsefulDates\Exceptions\InvalidDayException;
use UsefulDates\UsefulDates;

it('throws InvalidDayException for invalid day number -1', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    expect(fn () => $ud->setBusinessDays([-1]))
        ->toThrow(InvalidDayException::class);
});

it('throws InvalidDayException for invalid day number 7', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    expect(fn () => $ud->setBusinessDays([7]))
        ->toThrow(InvalidDayException::class);
});

it('throws InvalidDayException for invalid day number 999', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    expect(fn () => $ud->setBusinessDays([1, 2, 999]))
        ->toThrow(InvalidDayException::class);
});

it('allows custom business days including Sunday (0)', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-05')); // Sunday
    $ud->setBusinessDays([0, 1, 2, 3, 4]); // Sun-Thu

    expect($ud->isBusinessDay())->toBeTrue();
});

it('allows custom business days including Saturday (6)', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-04')); // Saturday
    $ud->setBusinessDays([2, 3, 4, 5, 6]); // Tue-Sat

    expect($ud->isBusinessDay())->toBeTrue();
});

it('handles single business day configuration', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-06')); // Monday
    $ud->setBusinessDays([1]); // Only Monday

    expect($ud->isBusinessDay())->toBeTrue();

    $ud->setDate(Carbon::create('2025-01-07')); // Tuesday
    expect($ud->isBusinessDay())->toBeFalse();
});

it('handles all days as business days', function (): void {
    $ud = new UsefulDates;
    $ud->setBusinessDays([0, 1, 2, 3, 4, 5, 6]); // All days

    $ud->setDate(Carbon::create('2025-01-05')); // Sunday
    expect($ud->isBusinessDay())->toBeTrue();

    $ud->setDate(Carbon::create('2025-01-04')); // Saturday
    expect($ud->isBusinessDay())->toBeTrue();
});

it('handles no business days configuration', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-06')); // Monday
    $ud->setBusinessDays([]); // No business days

    expect($ud->isBusinessDay())->toBeFalse();
});

it('nextBusinessDay handles no business days configuration', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-06')); // Monday
    $ud->setBusinessDays([]); // No business days

    expect($ud->nextBusinessDay())->toBeNull();
});

it('prevBusinessDay handles no business days configuration', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-06')); // Monday
    $ud->setBusinessDays([]); // No business days

    expect($ud->prevBusinessDay())->toBeNull();
});

it('nextBusinessDay skips multiple non-business days', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-03')); // Friday
    $ud->setBusinessDays([1, 2, 3, 4, 5]); // Mon-Fri

    $next = $ud->nextBusinessDay();

    // Should skip Saturday and Sunday to Monday
    expect($next->format('Y-m-d'))->toEqual('2025-01-06');
});

it('prevBusinessDay skips multiple non-business days', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-06')); // Monday
    $ud->setBusinessDays([1, 2, 3, 4, 5]); // Mon-Fri

    $prev = $ud->prevBusinessDay();

    // Should skip Sunday and Saturday to Friday
    expect($prev->format('Y-m-d'))->toEqual('2025-01-03');
});

it('nextBusinessDay works with custom business days', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01')); // Wednesday
    $ud->setBusinessDays([0, 6]); // Only Sunday and Saturday

    $next = $ud->nextBusinessDay();

    expect($next->dayOfWeek)->toBeIn([0, 6]);
});

it('prevBusinessDay works with custom business days', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-06')); // Monday
    $ud->setBusinessDays([0, 6]); // Only Sunday and Saturday

    $prev = $ud->prevBusinessDay();

    expect($prev->dayOfWeek)->toBeIn([0, 6]);
});

it('todayOrNextBusinessDay returns today when today is business day', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-06')); // Monday
    $ud->setBusinessDays([1, 2, 3, 4, 5]); // Mon-Fri

    $result = $ud->todayOrNextBusinessDay();

    expect($result->format('Y-m-d'))->toEqual('2025-01-06');
});

it('todayOrNextBusinessDay returns next business day when today is not', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-04')); // Saturday
    $ud->setBusinessDays([1, 2, 3, 4, 5]); // Mon-Fri

    $result = $ud->todayOrNextBusinessDay();

    expect($result->format('Y-m-d'))->toEqual('2025-01-06'); // Monday
});

it('todayOrPreviousBusinessDay returns today when today is business day', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-06')); // Monday
    $ud->setBusinessDays([1, 2, 3, 4, 5]); // Mon-Fri

    $result = $ud->todayOrPreviousBusinessDay();

    expect($result->format('Y-m-d'))->toEqual('2025-01-06');
});

it('todayOrPreviousBusinessDay returns previous business day when today is not', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-05')); // Sunday
    $ud->setBusinessDays([1, 2, 3, 4, 5]); // Mon-Fri

    $result = $ud->todayOrPreviousBusinessDay();

    expect($result->format('Y-m-d'))->toEqual('2025-01-03'); // Friday
});

it('isStandardBusinessDays returns true for Mon-Fri', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->setBusinessDays([1, 2, 3, 4, 5]);

    expect($ud->isStandardBusinessDays())->toBeTrue();
});

it('isStandardBusinessDays returns false for custom days', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->setBusinessDays([0, 1, 2, 3, 4]);

    expect($ud->isStandardBusinessDays())->toBeFalse();
});

it('isStandardBusinessDays returns false for different order', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->setBusinessDays([5, 4, 3, 2, 1]); // Reversed

    expect($ud->isStandardBusinessDays())->toBeFalse();
});

it('getBusinessDays returns configured days', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->setBusinessDays([0, 2, 4, 6]);

    expect($ud->getBusinessDays())->toEqual([0, 2, 4, 6]);
});

it('isBusinessDay with null parameter uses context date', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-06')); // Monday
    $ud->setBusinessDays([1, 2, 3, 4, 5]);

    expect($ud->isBusinessDay(null))->toBeTrue();
});

it('isBusinessDay with explicit date parameter', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-06')); // Monday (context)
    $ud->setBusinessDays([1, 2, 3, 4, 5]);

    $saturday = Carbon::create('2025-01-04');

    expect($ud->isBusinessDay($saturday))->toBeFalse();
});
