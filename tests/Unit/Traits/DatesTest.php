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
