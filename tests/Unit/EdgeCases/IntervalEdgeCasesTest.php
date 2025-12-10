<?php

use Carbon\Carbon;
use UsefulDates\Enums\RepeatFrequency;
use UsefulDates\UsefulDates;

it('handles getUsefulDatesInDays with 0 days', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-06-15'));
    $ud->addDate('Today', Carbon::create('2025-06-15'));

    $dates = $ud->getUsefulDatesInDays(0);

    expect(count($dates))->toEqual(1);
});

it('handles getUsefulDatesInDays with negative days', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-06-15'));
    $ud->addDate('Test', Carbon::create('2025-06-15'));

    // Negative days should give us dates in the past (end date before start)
    $dates = $ud->getUsefulDatesInDays(-5);

    expect(count($dates))->toEqual(0);
});

it('handles getUsefulDatesInDays with very large day count', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->addDate('Future', Carbon::create('2030-01-01'));

    $dates = $ud->getUsefulDatesInDays(3650); // ~10 years

    expect(count($dates))->toBeGreaterThan(0);
});

it('handles getUsefulDatesInDays spanning multiple years with YEARLY frequency', function (): void {
    class MultiYearDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Multi Year';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 1, 1, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2024-12-01'));
    $ud->add(MultiYearDate::class);

    $dates = $ud->getUsefulDatesInDays(400); // Span into 2026

    // Should find Jan 1 for 2025 and 2026 (and possibly 2027 due to +1 year buffer)
    expect(count($dates))->toBeGreaterThanOrEqual(2);
});

it('handles getUsefulDatesInDays with MONTHLY frequency across year boundary', function (): void {
    class MonthlyYearBoundary extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Monthly Year Boundary';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::MONTHLY;
            $this->start_date = Carbon::create(2024, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, $this->currentDate->month, 1, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2024-11-01'));
    $ud->add(MonthlyYearBoundary::class);

    $dates = $ud->getUsefulDatesInDays(90); // Nov, Dec, Jan, Feb

    expect(count($dates))->toBeGreaterThanOrEqual(3);
});

it('handles getUsefulDatesInDays with CUSTOM frequency iterating every day', function (): void {
    class CustomEveryDay extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Custom Every Day';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::CUSTOM;
            $this->start_date = Carbon::create(2025, 1, 1, 0, 0, 0);
        }

        public function date(): ?Carbon
        {
            // Return a date for every day
            return $this->currentDate->copy();
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->add(CustomEveryDay::class);

    $dates = $ud->getUsefulDatesInDays(10);

    // Should get 11 dates (day 0 through day 10 inclusive)
    expect(count($dates))->toEqual(11);
});

it('handles getUsefulDatesInDays with CUSTOM frequency selective days', function (): void {
    class CustomSelectiveDays extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Custom Selective';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::CUSTOM;
            $this->start_date = Carbon::create(2025, 1, 1, 0, 0, 0);
        }

        public function date(): ?Carbon
        {
            // Only Mondays
            if ($this->currentDate->dayOfWeek === 1) {
                return $this->currentDate->copy();
            }

            return null;
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01')); // Wednesday
    $ud->add(CustomSelectiveDays::class);

    $dates = $ud->getUsefulDatesInDays(14); // 2 weeks

    // Should get 2-3 Mondays
    expect(count($dates))->toBeGreaterThanOrEqual(2);
});

it('handles getUsefulDatesInYears with 0 years', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-06-15'));
    $ud->addDate('Today', Carbon::create('2025-06-15'));

    $dates = $ud->getUsefulDatesInYears(0);

    // 0 years means just today
    expect(count($dates))->toEqual(1);
});

it('handles getUsefulDatesInYears with large year count', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->addDate('Annual', Carbon::create('2025-01-01'));

    $dates = $ud->getUsefulDatesInYears(5);

    // Should get the annual date
    expect(count($dates))->toBeGreaterThan(0);
});
//
it('handles getNextUsefulDates with 0 count', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-06-15'));
    $ud->addDate('Test', Carbon::create('2025-06-20'));

    $dates = $ud->getNextUsefulDates(0);

    expect(count($dates))->toEqual(0);
});

it('handles getNextUsefulDates with count larger than available dates', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->addDate('Only Date', Carbon::create('2025-06-15'));

    $dates = $ud->getNextUsefulDates(10);

    expect(count($dates))->toEqual(10);
});
//
it('handles getNextUsefulDates with filters', function (): void {
    class FilteredNextDateTrue extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public bool $important = false;

        public function __construct()
        {
            $this->name = 'Filtered Next True';
            $this->important = true;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 9, 1, 0, 0, 0);
        }
    }

    class FilteredNextDateFalse extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public bool $important = false;

        public function __construct()
        {
            $this->name = 'Filtered Next False';
            $this->important = false;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 9, 1, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->add(FilteredNextDateTrue::class);
    $ud->add(FilteredNextDateFalse::class);

    $dates = $ud->getNextUsefulDates(5, [
        ['property' => 'important', 'operator' => '=', 'value' => true],
    ]);

    expect(count($dates))->toBeGreaterThan(0);
    foreach ($dates as $date) {
        expect($date->important)->toBeTrue();
    }
});
//
it('handles getUsefulDatesByYear with no matching dates', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->addDate('Other Year', Carbon::create('2026-06-15'), startYear: 2026);

    $dates = $ud->getUsefulDatesByYear(2025);

    expect(count($dates))->toEqual(0);
});

it('handles getUsefulDatesByYear with multiple dates in same year', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->addDate('Date 1', Carbon::create('2025-01-15'));
    $ud->addDate('Date 2', Carbon::create('2025-06-15'));
    $ud->addDate('Date 3', Carbon::create('2025-12-15'));

    $dates = $ud->getUsefulDatesByYear(2025);

    expect(count($dates))->toEqual(3);
});
//
it('handles getUsefulDatesByYear with filters', function (): void {
    class YearlyFilteredA extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public string $category;

        public function __construct()
        {
            $this->name = 'Category A';
            $this->category = 'A';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 10, 1, 0, 0, 0);
        }
    }

    class YearlyFilteredB extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public string $category;

        public function __construct()
        {
            $this->name = 'Category B';
            $this->category = 'B';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 10, 1, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->add(YearlyFilteredA::class);
    $ud->add(YearlyFilteredB::class);

    $dates = $ud->getUsefulDatesByYear(2025, [
        ['property' => 'category', 'operator' => '=', 'value' => 'A'],
    ]);

    expect(count($dates))->toEqual(1)
        ->and($dates[0]->category)->toEqual('A');
});
//
it('handles getUsefulDatesInDays with custom start date', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->addDate('Test', Carbon::create('2025-02-15'));

    $customStart = Carbon::create('2025-02-01');
    $dates = $ud->getUsefulDatesInDays(30, $customStart);

    expect(count($dates))->toEqual(1);
});

it('handles getUsefulDatesInDays with date exactly on boundary', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->addDate('Start', Carbon::create('2025-01-01'));
    $ud->addDate('End', Carbon::create('2025-01-10'));

    $dates = $ud->getUsefulDatesInDays(9); // Day 0 to day 9 inclusive

    // Should include both start and end
    expect(count($dates))->toEqual(2);
});

it('handles empty useful dates list', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    $dates = $ud->getUsefulDatesInDays(30);
    expect(count($dates))->toEqual(0);

    $dates = $ud->getNextUsefulDates(10);
    expect(count($dates))->toEqual(0);

    $dates = $ud->getUsefulDatesByYear(2025);
    expect(count($dates))->toEqual(0);
});

it('handles date occurrences outside usefulDate range get filtered', function (): void {
    class RangeRestrictedDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Range Restricted';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2025, 1, 1, 0, 0, 0);
            $this->end_date = Carbon::create(2027, 12, 31, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 11, 11, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2024-01-01'));
    $ud->add(RangeRestrictedDate::class);

    // Should not find 2024 occurrence (before start_date)
    $dates = $ud->getUsefulDatesInYears(5);

    foreach ($dates as $date) {
        expect($date->usefulDate()->year)->toBeGreaterThanOrEqual(2025)
            ->and($date->usefulDate()->year)->toBeLessThanOrEqual(2027);
    }
});
