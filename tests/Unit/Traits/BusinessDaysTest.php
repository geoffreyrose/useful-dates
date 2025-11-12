<?php

use Carbon\Carbon;
use UsefulDates\UsefulDates;

beforeEach(function (): void {
    $this->usefulDate = new UsefulDates;
    $this->usefulDate->setDate(Carbon::create('2025-03-31')); // Monday
});

it('has standard business days by default', function (): void {
    expect($this->usefulDate->businessDays)->toEqual([1, 2, 3, 4, 5])
        ->and($this->usefulDate->isStandardBusinessDays())->toBeTrue();
});

it('can set and get custom business days', function (): void {
    $this->usefulDate->setBusinessDays([0, 1, 2, 3, 4]);

    expect($this->usefulDate->getBusinessDays())->toEqual([0, 1, 2, 3, 4])
        ->and($this->usefulDate->isStandardBusinessDays())->toBeFalse();
});

it('throws on invalid business day values', function (): void {
    $this->usefulDate->setBusinessDays([1, 2, 7]);
})->throws(\UsefulDates\Exceptions\InvalidDayException::class);

it('determines if a date is a business day (paramless uses internal date)', function (): void {
    // 2025-03-31 is Monday (1)
    // 2025-04-05 is Saturday (6)
    expect($this->usefulDate->isBusinessDay())->toBeTrue()
        ->and($this->usefulDate->isBusinessDay(Carbon::create('2025-04-05')))->toBeFalse();
});

it('gets next business day correctly skipping weekends', function (): void {
    // Set to Friday
    $this->usefulDate->setDate(Carbon::create('2025-04-04')); // Friday

    $next = $this->usefulDate->nextBusinessDay();

    expect($next)->toEqual(Carbon::create('2025-04-07')) // Monday
        ->and($next->dayOfWeek)->toEqual(1);
});

it('gets previous business day correctly skipping weekends', function (): void {
    // Set to Monday
    $this->usefulDate->setDate(Carbon::create('2025-04-07'));

    $prev = $this->usefulDate->prevBusinessDay();

    expect($prev)->toEqual(Carbon::create('2025-04-04')) // Friday
        ->and($prev->dayOfWeek)->toEqual(5);
});
