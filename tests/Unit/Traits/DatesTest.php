<?php

use Carbon\Carbon;
use Tests\ValidAprilFools;
use UsefulDates\UsefulDates;

beforeEach(function (): void {
    $this->usefulDate = new UsefulDates;
    $this->usefulDate->setDate(Carbon::create('2025-01-15'));
    $this->usefulDate->add(ValidAprilFools::class);
});

it('returns useful dates for a provided year', function (): void {
    $list = $this->usefulDate->getUsefulDatesByYear(2028);

    expect(count($list))->toBeGreaterThan(0)
        ->and($list[0]->usefulDate())->toEqual(Carbon::create('2028-04-01'));
});

it('defaults to the internal date year when none provided', function (): void {
    // Internal year is 2025
    $list = $this->usefulDate->getUsefulDatesByYear();

    expect(count($list))->toBeGreaterThan(0)
        ->and($list[0]->usefulDate())->toEqual(Carbon::create('2025-04-01'));
});

it('adds a simple date using addDate method', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-06-15'));
    $ud->addDate('My Birthday', Carbon::create('1990-06-15'));

    expect($ud->isUsefulDate())->toBeTrue();

    $dates = $ud->getUsefulDate();
    expect(count($dates))->toEqual(1)
        ->and($dates[0]->name)->toEqual('My Birthday')
        ->and($dates[0]->usefulDate())->toEqual(Carbon::create('2025-06-15'));
});
