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

it('updates currentUsefulDate on existing dates when setting date', function (): void {
    class TrackDateUpdate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Track Date';
            $this->is_repeated = true;
            $this->repeat_frequency = \UsefulDates\Enums\RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 5, 1, 0, 0, 0);
        }
    }

    $this->usefulDate->setDate('2025-01-01');
    $this->usefulDate->add(TrackDateUpdate::class);

    // Change the date - should update all usefulDates
    $this->usefulDate->setDate('2025-06-01');

    expect($this->usefulDate->date)->toEqual(Carbon::create('2025-06-01'));
});
