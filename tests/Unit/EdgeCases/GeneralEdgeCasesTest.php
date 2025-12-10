<?php

use Carbon\Carbon;
use UsefulDates\Abstracts\UsefulDateAbstract;
use UsefulDates\Enums\RepeatFrequency;
use UsefulDates\Exceptions\InvalidUsefulDateException;
use UsefulDates\UsefulDates;

it('throws InvalidUsefulDateException for class that does not extend UsefulDateAbstract', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    expect(fn () => $ud->add('stdClass'))
        ->toThrow(InvalidUsefulDateException::class);
});

it('throws InvalidUsefulDateException for non-existent class', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    expect(fn () => $ud->add('NonExistent\\ClassName'))
        ->toThrow(InvalidUsefulDateException::class);
});

it('handles addDate with non-repeated date', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-06-15'));

    $ud->addDate('One Time Event', Carbon::create('2025-06-15'), false);

    expect($ud->isUsefulDate())->toBeTrue();

    // Different year should not match
    $ud->setDate(Carbon::create('2026-06-15'));
    expect($ud->isUsefulDate())->toBeFalse();
});

it('handles addDate with NONE repeat frequency', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-07-04'));

    $ud->addDate('Single Occurrence', Carbon::create('2025-07-04'), true, RepeatFrequency::NONE, 2025);

    expect($ud->isUsefulDate())->toBeTrue();

    // Same day different year should not match with NONE frequency
    $ud->setDate(Carbon::create('2026-07-04'));
    expect($ud->isUsefulDate())->toBeFalse();
});

it('handles addDate with MONTHLY repeat frequency', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-15'));

    $ud->addDate('Monthly Event', Carbon::create('2025-01-15'), true, RepeatFrequency::MONTHLY, 2025);

    expect($ud->isUsefulDate())->toBeTrue();

    // Next month
    $ud->setDate(Carbon::create('2025-02-15'));
    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles addDate with custom start year', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2024-08-20'));

    $ud->addDate('Future Start', Carbon::create('2025-08-20'), true, RepeatFrequency::YEARLY, 2025);

    // Should not be useful in 2024 (before start year)
    expect($ud->isUsefulDate())->toBeFalse();

    // Should be useful in 2025 (start year)
    $ud->setDate(Carbon::create('2025-08-20'));
    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles addDate with leap day', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2024-02-29')); // Leap year

    $ud->addDate('Leap Day', Carbon::create('2024-02-29'));

    expect($ud->isUsefulDate())->toBeTrue();

    // Non-leap year - Feb 29 doesn't exist
    // Carbon will handle this by creating Feb 28 or March 1 depending on version
    $ud->setDate(Carbon::create('2025-02-28'));
    // The date() method will try to create 2025-02-29 which may adjust
});

it('handles addDate with year 1 as default start year', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-09-01'));

    $ud->addDate('Ancient Date', Carbon::create('2025-09-01')); // Default startYear = 1

    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles multiple dates added to same instance', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-10-01'));

    $ud->addDate('Date 1', Carbon::create('2025-10-01'));
    $ud->addDate('Date 2', Carbon::create('2025-10-15'));
    $ud->addDate('Date 3', Carbon::create('2025-10-30'));

    $ud->setDate(Carbon::create('2025-10-01'));
    expect($ud->isUsefulDate())->toBeTrue();

    $ud->setDate(Carbon::create('2025-10-15'));
    expect($ud->isUsefulDate())->toBeTrue();

    $ud->setDate(Carbon::create('2025-10-30'));
    expect($ud->isUsefulDate())->toBeTrue();

    $ud->setDate(Carbon::create('2025-10-10'));
    expect($ud->isUsefulDate())->toBeFalse();
});

it('handles mixing add() and addDate() methods', function (): void {
    class CustomDate extends UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Custom';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 11, 1, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-11-01'));

    $ud->add(CustomDate::class);
    $ud->addDate('Simple Date', Carbon::create('2025-11-15'));

    $ud->setDate(Carbon::create('2025-11-01'));
    expect($ud->isUsefulDate())->toBeTrue();

    $ud->setDate(Carbon::create('2025-11-15'));
    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles isUsefulDate with null parameter uses context date', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-12-25'));
    $ud->addDate('Christmas', Carbon::create('2025-12-25'));

    expect($ud->isUsefulDate(null))->toBeTrue();
});

it('handles isUsefulDate with explicit date parameter', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01')); // Context date
    $ud->addDate('Birthday', Carbon::create('2025-06-15'));

    $checkDate = Carbon::create('2025-06-15');
    expect($ud->isUsefulDate($checkDate))->toBeTrue();

    $checkDate = Carbon::create('2025-06-16');
    expect($ud->isUsefulDate($checkDate))->toBeFalse();
});

it('handles getUsefulDate returns cloned instances', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-07-20'));
    $ud->addDate('Test', Carbon::create('2025-07-20'));

    $dates1 = $ud->getUsefulDate();
    $dates2 = $ud->getUsefulDate();

    // Should be different instances (cloned)
    expect($dates1[0])->not->toBe($dates2[0]);
    // But same values
    expect($dates1[0]->name)->toEqual($dates2[0]->name);
});

it('handles date context changes update all useful dates', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->addDate('New Year', Carbon::create('2025-01-01'));

    expect($ud->isUsefulDate())->toBeTrue();

    // Change context date
    $ud->setDate(Carbon::create('2025-06-15'));
    expect($ud->isUsefulDate())->toBeFalse();
});

it('handles fluent interface for chaining methods', function (): void {
    $result = (new UsefulDates)
        ->setDate(Carbon::create('2025-08-01'))
        ->addDate('Date 1', Carbon::create('2025-08-01'))
        ->addDate('Date 2', Carbon::create('2025-08-15'))
        ->setBusinessDays([1, 2, 3, 4, 5]);

    expect($result)->toBeInstanceOf(UsefulDates::class);
    expect($result->isUsefulDate())->toBeTrue();
});

it('handles useful date with properties set via property hooks', function (): void {
    class PropertyHookDate extends UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Property Hook Test';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
            $this->end_date = Carbon::create(2030, 12, 31, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 9, 20, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-09-20'));
    $ud->add(PropertyHookDate::class);

    $dates = $ud->getUsefulDate();
    expect($dates[0]->name)->toEqual('Property Hook Test');
    expect($dates[0]->is_repeated)->toBeTrue();
    expect($dates[0]->repeat_frequency)->toEqual(RepeatFrequency::YEARLY);
});

it('handles date with additional_search_names array property', function (): void {
    class SearchNamesDate extends UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Primary Name';
            $this->additional_search_names = ['Alias 1', 'Alias 2', 'Alias 3'];
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 10, 10, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-10-10'));
    $ud->add(SearchNamesDate::class);

    $dates = $ud->getUsefulDate();
    expect($dates[0]->additional_search_names)->toHaveCount(3);
    expect($dates[0]->additional_search_names[0])->toEqual('Alias 1');
});

it('handles date with start_date and end_date range', function (): void {
    class RangedDate extends UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Ranged Date';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2024, 11, 1, 0, 0, 0);
            $this->end_date = Carbon::create(2026, 11, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 11, 1, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;

    // Before range
    $ud->setDate(Carbon::create('2023-11-01'));
    $ud->add(RangedDate::class);
    expect($ud->isUsefulDate())->toBeFalse();

    // Within range
    $ud2 = new UsefulDates;
    $ud2->setDate(Carbon::create('2025-11-01'));
    $ud2->add(RangedDate::class);
    expect($ud2->isUsefulDate())->toBeTrue();

    // After range
    $ud3 = new UsefulDates;
    $ud3->setDate(Carbon::create('2027-11-01'));
    $ud3->add(RangedDate::class);
    expect($ud3->isUsefulDate())->toBeFalse();
});

it('handles midnight time for dates', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-11-30 00:00:00'));
    $ud->addDate('Midnight', Carbon::create('2025-11-30 00:00:00'));

    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles end of day time for dates', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-11-30 23:59:59'));
    $ud->addDate('End of Day', Carbon::create('2025-11-30 23:59:59'));

    expect($ud->isUsefulDate())->toBeTrue();
});
