<?php

use Carbon\Carbon;
use Tests\ValidAprilFools;
use UsefulDates\Enums\RepeatFrequency;
use UsefulDates\UsefulDates;

beforeEach(function (): void {
    $this->usefulDate = new UsefulDates;
});

it('detects a useful date on the given day', function (): void {
    $this->usefulDate->setDate(Carbon::create('2025-04-01'));
    $this->usefulDate->add(ValidAprilFools::class);

    expect($this->usefulDate->isUsefulDate())->toBeTrue();

    $dates = $this->usefulDate->getUsefulDate();
    expect(count($dates))->toEqual(1)
        ->and($dates[0]->name)->toEqual("April Fools' Day")
        ->and($dates[0]->usefulDate())->toEqual(Carbon::create('2025-04-01'));
});

it('returns false when the day is not a useful date', function (): void {
    $this->usefulDate->setDate(Carbon::create('2025-04-02'));
    $this->usefulDate->add(ValidAprilFools::class);

    expect($this->usefulDate->isUsefulDate())->toBeFalse()
        ->and($this->usefulDate->getUsefulDate())->toEqual([]);
});

it('returns multiple useful dates when more than one matches the day', function (): void {
    class AlsoAprilFirst extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Also Apr 1';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(1900, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 4, 1, 0, 0, 0);
        }
    }

    $this->usefulDate->setDate(Carbon::create('2025-04-01'));
    $this->usefulDate->add(ValidAprilFools::class);
    $this->usefulDate->add(AlsoAprilFirst::class);

    $dates = $this->usefulDate->getUsefulDate();

    expect(count($dates))->toEqual(2)
        ->and(array_map(fn ($d) => $d->usefulDate()->toDateString(), $dates))
        ->toEqual([Carbon::create('2025-04-01'), Carbon::create('2025-04-01')])
        ->and([$dates[0]->name, $dates[1]->name])
        ->toEqual(["April Fools' Day", 'Also Apr 1']);
});
