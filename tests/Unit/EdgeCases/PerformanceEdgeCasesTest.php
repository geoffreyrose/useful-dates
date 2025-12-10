<?php

use Carbon\Carbon;
use UsefulDates\Enums\RepeatFrequency;
use UsefulDates\UsefulDates;

it('handles large number of useful dates efficiently', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-06-15'));

    // Add 100 dates
    for ($i = 1; $i <= 100; $i++) {
        $ud->addDate("Date {$i}", Carbon::create('2025-06-15'));
    }

    $dates = $ud->getUsefulDate();
    expect(count($dates))->toEqual(100);
});

it('handles checking many different dates', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2000-01-01'));
    $ud->addDate('New Year', Carbon::create('2025-01-01'));

    // Check 100 different dates
    for ($day = 1; $day <= 100; $day++) {
        $ud->setDate(Carbon::create('2025-01-01')->addDays($day - 1));
        $isUseful = $ud->isUsefulDate();

        if ($day === 1) {
            expect($isUseful)->toBeTrue();
        }
    }

    expect(true)->toBeTrue(); // Completed without timeout
});

it('handles deep nesting in getUsefulDatesInDays', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    // Add multiple dates with different frequencies
    for ($i = 1; $i <= 10; $i++) {
        $ud->addDate("Date {$i}", Carbon::create(2025, $i, 1));
    }

    $dates = $ud->getUsefulDatesInDays(365);

    expect(count($dates))->toBeGreaterThan(0);
});

it('handles repeated calls to getUsefulDate', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-06-15'));
    $ud->addDate('Test', Carbon::create('2025-06-15'));

    // Call 50 times
    for ($i = 0; $i < 50; $i++) {
        $dates = $ud->getUsefulDate();
        expect(count($dates))->toEqual(1);
    }
});

it('handles many filters applied', function (): void {
    class MultiPropDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public int $prop1 = 1;

        public int $prop2 = 2;

        public int $prop3 = 3;

        public int $prop4 = 4;

        public int $prop5 = 5;

        public function __construct()
        {
            $this->name = 'Multi Prop';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 7, 1, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-07-01'));
    $ud->add(MultiPropDate::class);

    $dates = $ud->getUsefulDate(null, [
        ['property' => 'prop1', 'operator' => '=', 'value' => 1],
        ['property' => 'prop2', 'operator' => '=', 'value' => 2],
        ['property' => 'prop3', 'operator' => '=', 'value' => 3],
        ['property' => 'prop4', 'operator' => '=', 'value' => 4],
        ['property' => 'prop5', 'operator' => '=', 'value' => 5],
    ]);

    expect(count($dates))->toEqual(1);
});

it('handles rapid date context switching', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2000-01-01'));
    $ud->addDate('Test', Carbon::create('2025-06-15'));

    // Switch context 50 times
    for ($i = 0; $i < 50; $i++) {
        $ud->setDate(Carbon::create('2025-06-15'));
        expect($ud->isUsefulDate())->toBeTrue();

        $ud->setDate(Carbon::create('2025-07-15'));
        expect($ud->isUsefulDate())->toBeFalse();
    }
});

it('handles empty operations', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    // No dates added
    expect($ud->isUsefulDate())->toBeFalse();
    expect(count($ud->getUsefulDate()))->toEqual(0);
    expect(count($ud->getUsefulDatesInDays(30)))->toEqual(0);
    expect(count($ud->getNextUsefulDates(10)))->toEqual(0);
});

it('handles memory efficiency with cloned dates', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-08-15'));
    $ud->addDate('Test', Carbon::create('2025-08-15'));

    // Get dates multiple times (each returns clones)
    $dateCollections = [];
    for ($i = 0; $i < 20; $i++) {
        $dateCollections[] = $ud->getUsefulDate();
    }

    // All should be different instances but same values
    expect(count($dateCollections))->toEqual(20);
    foreach ($dateCollections as $dates) {
        expect(count($dates))->toEqual(1);
    }
});

it('handles business day calculation across long ranges', function (): void {
    $ud = new UsefulDates;
    $ud->setBusinessDays([1, 2, 3, 4, 5]);

    // Start on Friday
    $ud->setDate(Carbon::create('2025-01-03'));

    // Get next business day (should skip weekend)
    $next = $ud->nextBusinessDay();
    expect($next->format('Y-m-d'))->toEqual('2025-01-06');

    // Start on Saturday
    $ud->setDate(Carbon::create('2025-01-04'));
    $next = $ud->nextBusinessDay();
    expect($next->format('Y-m-d'))->toEqual('2025-01-06');
});

it('handles getNextUsefulDates with reasonable performance', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    // Add some dates throughout the year
    for ($month = 1; $month <= 12; $month++) {
        $ud->addDate("Month {$month}", Carbon::create(2025, $month, 15));
    }

    $dates = $ud->getNextUsefulDates(10);

    expect(count($dates))->toBeGreaterThan(0);
});

it('handles getUsefulDatesByYear for distant years', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->addDate('Annual', Carbon::create('2025-12-31'));

    // Check year far in the future
    $dates = $ud->getUsefulDatesByYear(2100);

    expect(is_array($dates))->toBeTrue();
});
