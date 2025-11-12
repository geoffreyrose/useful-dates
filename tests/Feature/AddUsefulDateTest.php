<?php

use Carbon\Carbon;
use Tests\ValidAprilFools;
use Tests\InvalidAprilFools;
use UsefulDates\UsefulDates;

beforeEach(function (): void {
    $this->usefulDate = new UsefulDates;
});

it('add valid AprilFools useful date', function (): void {
    $this->usefulDate->setDate(Carbon::create('2025-01-01'));
    $this->usefulDate->add(ValidAprilFools::class);

    $dates = $this->usefulDate->getNextUsefulDates();

    expect(count($dates))->toEqual(1)
        ->and($dates[0]->usefulDate())->toEqual(Carbon::create('2025-04-01'))
        ->and($dates[0]->name)->toEqual('April Fools\' Day');
});

it('add invalid AprilFools useful date', function (): void {
    $this->usefulDate->setDate(Carbon::create('2025-01-01'));
    $this->usefulDate->add(InvalidAprilFools::class);
})->throws(\UsefulDates\Exceptions\InvalidUsefulDateException::class);
