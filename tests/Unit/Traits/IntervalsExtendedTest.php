<?php

use Carbon\Carbon;
use UsefulDates\Enums\RepeatFrequency;
use UsefulDates\UsefulDates;

beforeEach(function (): void {
    $this->usefulDate = new UsefulDates;
});

it('handles null date from date() method in getUsefulDatesInDays', function (): void {
    class NullDateEvent extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Null Date Event';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): ?Carbon
        {
            // Return null to test the continue path
            return null;
        }
    }

    $this->usefulDate->setDate(Carbon::create('2025-01-01'));
    $this->usefulDate->add(NullDateEvent::class);

    $list = $this->usefulDate->getUsefulDatesInDays(30);

    expect(count($list))->toEqual(0);
});

it('handles MONTHLY frequency in getUsefulDatesInDays with null occurrence', function (): void {
    class MonthlyNullEvent extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Monthly Null Event';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::MONTHLY;
            $this->start_date = Carbon::create(2025, 1, 1, 0, 0, 0);
        }

        public function date(): ?Carbon
        {
            // Return null for odd months
            if ($this->currentDate->month % 2 === 1) {
                return null;
            }

            return Carbon::create($this->currentDate->year, $this->currentDate->month, 15, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->add(MonthlyNullEvent::class);

    $list = $ud->getUsefulDatesInDays(90); // ~3 months

    // Should only get February since Jan and March are odd
    expect(count($list))->toBeGreaterThanOrEqual(1);
});

it('handles CUSTOM frequency in getUsefulDatesInDays', function (): void {
    class CustomFreqEvent extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Custom Freq Event';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::CUSTOM;
            $this->start_date = Carbon::create(2025, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return $this->currentDate->copy();
        }

        public function usefulDate(): ?Carbon
        {
            // Only on Wednesdays
            if ($this->currentDate->dayOfWeek === 3) {
                return $this->currentDate->copy();
            }

            return null;
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->add(CustomFreqEvent::class);

    $list = $ud->getUsefulDatesInDays(14); // 2 weeks should have ~2 Wednesdays

    expect(count($list))->toBeGreaterThanOrEqual(2);
});

it('tests getUsefulDatesInDays with custom start date', function (): void {
    class CustomStartDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Custom Start';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 3, 15, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->add(CustomStartDate::class);

    // Use a custom start date of Feb 1
    $customStart = Carbon::create('2025-02-01');
    $list = $ud->getUsefulDatesInDays(60, $customStart); // Should include March 15

    expect(count($list))->toBeGreaterThanOrEqual(1);
    expect($list[0]->usefulDate())->toEqual(Carbon::create('2025-03-15'));
});

it('handles year boundary in getUsefulDatesInDays', function (): void {
    class YearBoundaryDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Year Boundary';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 1, 5, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2024-12-20'));
    $ud->add(YearBoundaryDate::class);

    // Should cross into 2025
    $list = $ud->getUsefulDatesInDays(30);

    expect(count($list))->toBeGreaterThanOrEqual(1);
});

it('handles monthly date with addMonthNoOverflow', function (): void {
    class MonthlyNoOverflow extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Monthly No Overflow';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::MONTHLY;
            $this->start_date = Carbon::create(2025, 1, 31, 0, 0, 0);
        }

        public function date(): Carbon
        {
            // Always returns the 31st, but some months don't have it
            if ($this->currentDate->daysInMonth >= 31) {
                return Carbon::create($this->currentDate->year, $this->currentDate->month, 31, 0, 0, 0);
            }

            return Carbon::create($this->currentDate->year, $this->currentDate->month, $this->currentDate->daysInMonth, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->add(MonthlyNoOverflow::class);

    $list = $ud->getUsefulDatesInDays(90); // ~3 months (Jan, Feb, Mar)

    expect(count($list))->toBeGreaterThanOrEqual(2); // Jan 31 and Mar 31 (Feb doesn't have 31)
});

it('sorts dates correctly by usefulDate', function (): void {
    class SortDate1 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Sort Date 1';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 3, 20, 0, 0, 0);
        }
    }

    class SortDate2 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Sort Date 2';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 3, 10, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-03-01'));
    $ud->add(SortDate1::class); // Add later date first
    $ud->add(SortDate2::class);

    $list = $ud->getUsefulDatesInDays(30);

    // Should be sorted by date
    expect(count($list))->toEqual(2);
    expect($list[0]->usefulDate()->day)->toEqual(10); // Earlier date first
    expect($list[1]->usefulDate()->day)->toEqual(20);
});

it('handles getUsefulDatesByYear with filters', function (): void {
    class YearFilterDate1 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public bool $important = true;

        public function __construct()
        {
            $this->name = 'Important';
            $this->important = true;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 4, 10, 0, 0, 0);
        }
    }

    class YearFilterDate2 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public bool $important = false;

        public function __construct()
        {
            $this->name = 'Not Important';
            $this->important = false;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 5, 10, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->add(YearFilterDate1::class);
    $ud->add(YearFilterDate2::class);

    $list = $ud->getUsefulDatesByYear(2025, [['property' => 'important', 'operator' => '=', 'value' => true]]);

    expect(count($list))->toEqual(1);
    expect($list[0]->important)->toBeTrue();
});

it('sorts equal dates correctly in getUsefulDatesInDays (line 81)', function (): void {
    // This covers line 81 in Intervals.php - the `return 0;` when dates are equal
    class SameDayEvent1 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Same Day Event 1';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 6, 15, 12, 0, 0);
        }
    }

    class SameDayEvent2 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Same Day Event 2';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            // Same date as SameDayEvent1 to trigger the equal comparison
            return Carbon::create($this->currentDate->year, 6, 15, 12, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-06-01'));
    $ud->add(SameDayEvent1::class);
    $ud->add(SameDayEvent2::class);

    $list = $ud->getUsefulDatesInDays(30);

    // Should have both events even though they're on the same date
    expect(count($list))->toEqual(2);
    // Both should be on the same date
    expect($list[0]->usefulDate()->format('Y-m-d'))->toEqual($list[1]->usefulDate()->format('Y-m-d'));
});
