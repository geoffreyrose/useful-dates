<?php

use Carbon\Carbon;
use Tests\ValidAprilFools;
use UsefulDates\UsefulDates;

beforeEach(function (): void {
    $this->usefulDate = new UsefulDates;
    // Set a default date to satisfy add() precondition
    $this->usefulDate->setDate(Carbon::create('2025-01-01'));
    $this->usefulDate->add(ValidAprilFools::class);
});

it('gets useful dates within a number of days (inclusive)', function (): void {
    // Start on 2025-03-30, look ahead 3 days => includes 31st and April 1st
    $this->usefulDate->setDate(Carbon::create('2025-03-30'));

    $list = $this->usefulDate->getUsefulDatesInDays(3);

    expect(array_map(fn ($d) => $d->usefulDate(), $list))
        ->toEqual([Carbon::create('2025-04-01')]);
});

it('gets useful dates within a number of years', function (): void {
    $this->usefulDate->setDate(Carbon::create('2025-01-01'));

    $list = $this->usefulDate->getUsefulDatesInYears(2); // Up to 2027-01-01

    expect(array_map(fn ($d) => $d->usefulDate(), $list))
        ->toEqual([Carbon::create('2025-04-01'), Carbon::create('2026-04-01')]);
});

it('gets the next N useful dates going forward', function (): void {
    $this->usefulDate->setDate(Carbon::create('2025-03-15'));

    $list = $this->usefulDate->getNextUsefulDates(3);

    expect(array_map(fn ($d) => $d->usefulDate(), $list))
        ->toEqual([Carbon::create('2025-04-01'), Carbon::create('2026-04-01'), Carbon::create('2027-04-01')]);
});

it('gets the previous N useful dates going backward', function (): void {
    $this->usefulDate->setDate(Carbon::create('2025-04-02'));

    $list = $this->usefulDate->getPreviousUsefulDates(3);

    // Order is from nearest past backwards
    expect(array_map(fn ($d) => $d->usefulDate(), $list))
        ->toEqual([Carbon::create('2025-04-01'), Carbon::create('2024-04-01'), Carbon::create('2023-04-01')]);
});
