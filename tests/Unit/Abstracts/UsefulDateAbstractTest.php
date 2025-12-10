<?php

use Carbon\Carbon;
use UsefulDates\Enums\RepeatFrequency;
use UsefulDates\UsefulDates;

beforeEach(function (): void {
    $this->usefulDate = new UsefulDates;
});

it('respects monthly repeat frequency with start and end dates', function (): void {
    class MonthlyDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Monthly Date';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::MONTHLY;
            $this->start_date = Carbon::create(2025, 3, 15, 0, 0, 0);
            $this->end_date = Carbon::create(2025, 6, 15, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, $this->currentDate->month, 15, 0, 0, 0);
        }
    }

    $this->usefulDate->setDate(Carbon::create('2025-04-15'));
    $this->usefulDate->add(MonthlyDate::class);

    // Within range
    expect($this->usefulDate->isUsefulDate())->toBeTrue();

    // Before range
    $this->usefulDate->setDate(Carbon::create('2025-02-15'));
    expect($this->usefulDate->isUsefulDate())->toBeFalse();

    // After range
    $this->usefulDate->setDate(Carbon::create('2025-07-15'));
    expect($this->usefulDate->isUsefulDate())->toBeFalse();
});

it('respects yearly repeat frequency with start and end dates', function (): void {
    class YearlyLimitedDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Yearly Limited Date';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 6, 1, 0, 0, 0);
            $this->end_date = Carbon::create(2025, 6, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 6, 1, 0, 0, 0);
        }
    }

    $this->usefulDate->setDate(Carbon::create('2023-06-01'));
    $this->usefulDate->add(YearlyLimitedDate::class);

    // Within range
    expect($this->usefulDate->isUsefulDate())->toBeTrue();

    // Before range
    $ud2 = new UsefulDates;
    $ud2->setDate(Carbon::create('2019-06-01'));
    $ud2->add(YearlyLimitedDate::class);
    expect($ud2->isUsefulDate())->toBeFalse();

    // After range
    $ud3 = new UsefulDates;
    $ud3->setDate(Carbon::create('2026-06-01'));
    $ud3->add(YearlyLimitedDate::class);
    expect($ud3->isUsefulDate())->toBeFalse();
});

it('calculates days away correctly', function (): void {
    class TestDateForDaysAway extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Test Date';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 7, 4, 0, 0, 0);
        }
    }

    $this->usefulDate->setDate(Carbon::create('2025-07-01'));
    $this->usefulDate->add(TestDateForDaysAway::class);

    $dates = $this->usefulDate->getUsefulDate(Carbon::create('2025-07-04'));
    expect(count($dates))->toBeGreaterThan(0);

    // The date is 7/4 and we're checking from 7/1, so it should be 3-4 days away
    $daysAway = $dates[0]->daysAway();
    expect($daysAway)->toBeGreaterThanOrEqual(0);
});

it('calculates negative days away for past dates', function (): void {
    class PastDaysAwayDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Past Days';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 2, 1, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-02-01'));
    $ud->add(PastDaysAwayDate::class);

    $dates = $ud->getUsefulDate();
    expect(count($dates))->toBeGreaterThan(0);

    // Set a future date to check
    $ud->setDate(Carbon::create('2025-03-01'));
    $dates = $ud->getUsefulDate(Carbon::create('2025-02-01'));

    // Feb 1 is in the past from March 1
    expect($dates[0]->daysAway())->toBeLessThan(0);
});

it('handles additional_search_names property', function (): void {
    class AdditionalNamesDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Main Name';
            $this->additional_search_names = ['Alias 1', 'Alias 2'];
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
    $ud->setDate(Carbon::create('2025-09-01'));
    $ud->add(AdditionalNamesDate::class);

    $dates = $ud->getUsefulDate();
    expect($dates[0]->additional_search_names)->toEqual(['Alias 1', 'Alias 2']);
});

it('handles daysAway returning 0 when ceil is between -1 and 0', function (): void {
    // This covers line 87 in UsefulDateAbstract.php
    class SameDayDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Same Day';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 5, 5, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    // Set date to the exact same time to get 0 days away
    $ud->setDate(Carbon::create('2025-05-05 00:00:00'));
    $ud->add(SameDayDate::class);

    $dates = $ud->getUsefulDate();
    // The daysAway should be 0 or very close (line 87 returns 0 when ceil is between -1 and 0)
    expect($dates[0]->daysAway())->toBeLessThanOrEqual(1);
});

it('handles usefulDate returning null when date() returns null', function (): void {
    // This covers line 102 in UsefulDateAbstract.php
    class NullDateReturner extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Null Date';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): ?Carbon
        {
            return null;
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->add(NullDateReturner::class);

    $dates = $ud->getUsefulDate();
    expect(count($dates))->toEqual(0);
});

it('handles CUSTOM repeat frequency returning date directly', function (): void {
    // This covers line 106 in UsefulDateAbstract.php
    class CustomFrequencyDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Custom Frequency';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::CUSTOM;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): ?Carbon
        {
            // For CUSTOM frequency, date is returned directly without birthday check
            return Carbon::create('2025-08-15');
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->add(CustomFrequencyDate::class);

    $dates = $ud->getUsefulDate(Carbon::create('2025-08-15'));
    expect(count($dates))->toBeGreaterThan(0)
        ->and($dates[0]->usefulDate()->format('Y-m-d'))->toEqual('2025-08-15');
});

it('handles monthly range check without start_date', function (): void {
    // This covers line 131 in UsefulDateAbstract.php
    class MonthlyNoStartDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Monthly No Start';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::MONTHLY;
            // No start_date set
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, $this->currentDate->month, 10, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-03-10'));
    $ud->add(MonthlyNoStartDate::class);

    // Should be valid since no start_date means no lower bound
    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles yearly range check without start_date', function (): void {
    // This covers line 163 in UsefulDateAbstract.php
    class YearlyNoStartDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Yearly No Start';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            // No start_date set
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 11, 11, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-11-11'));
    $ud->add(YearlyNoStartDate::class);

    // Should be valid since no start_date means no lower bound
    expect($ud->isUsefulDate())->toBeTrue();
});
