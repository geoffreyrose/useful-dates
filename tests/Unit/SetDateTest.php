<?php

use Carbon\Carbon;
use UsefulDates\UsefulDates;

beforeEach(function (): void {
    $this->usefulDate = new UsefulDates;
});

it('sets date with Carbon', function (): void {
    $this->usefulDate->setDate(Carbon::create('2025-04-01'));

    expect($this->usefulDate->date)->toEqual(Carbon::create('2025-04-01'));
});

it('sets date with DateTime', function (): void {
    $this->usefulDate->setDate(new DateTime('2025-04-01'));

    expect($this->usefulDate->date)->toEqual(Carbon::create('2025-04-01'));
});

it('sets date with string', function (): void {
    $this->usefulDate->setDate('2025-04-01');

    expect($this->usefulDate->date)->toEqual(Carbon::create('2025-04-01'));
});

it('throws on invalid date', function (): void {
    $this->usefulDate->setDate('invalid-date');
})->throws(\UsefulDates\Exceptions\InvalidDateException::class);
