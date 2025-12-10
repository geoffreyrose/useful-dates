<?php

use Carbon\Carbon;
use UsefulDates\Enums\RepeatFrequency;
use UsefulDates\UsefulDates;

it('handles changing date context multiple times', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->addDate('New Year', Carbon::create('2025-01-01'));

    expect($ud->isUsefulDate())->toBeTrue();

    $ud->setDate(Carbon::create('2025-06-15'));
    expect($ud->isUsefulDate())->toBeFalse();

    $ud->setDate(Carbon::create('2025-01-01'));
    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles adding dates after initial setup', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    expect($ud->isUsefulDate())->toBeFalse();

    $ud->addDate('New Year', Carbon::create('2025-01-01'));

    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles modifying business days after checking', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-04')); // Saturday
    $ud->setBusinessDays([1, 2, 3, 4, 5]); // Mon-Fri

    expect($ud->isBusinessDay())->toBeFalse();

    // Change to include Saturday
    $ud->setBusinessDays([1, 2, 3, 4, 5, 6]);

    expect($ud->isBusinessDay())->toBeTrue();
});

it('handles multiple instances with independent state', function (): void {
    $ud1 = new UsefulDates;
    $ud1->setDate(Carbon::create('2025-01-01'));
    $ud1->addDate('UD1 Date', Carbon::create('2025-01-01'));

    $ud2 = new UsefulDates;
    $ud2->setDate(Carbon::create('2025-06-15'));
    $ud2->addDate('UD2 Date', Carbon::create('2025-06-15'));

    // Each instance should have independent state
    expect($ud1->isUsefulDate())->toBeTrue();
    expect($ud2->isUsefulDate())->toBeTrue();

    $ud1->setDate(Carbon::create('2025-06-15'));
    expect($ud1->isUsefulDate())->toBeFalse(); // UD1 doesn't have June 15
    expect($ud2->isUsefulDate())->toBeTrue(); // UD2 still has June 15
});

it('handles resetting date context updates all useful dates', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->addDate('Date 1', Carbon::create('2025-01-01'));
    $ud->addDate('Date 2', Carbon::create('2025-06-15'));

    $dates = $ud->getUsefulDate();
    expect(count($dates))->toEqual(1);

    $ud->setDate(Carbon::create('2025-06-15'));
    $dates = $ud->getUsefulDate();
    expect(count($dates))->toEqual(1);
});

it('handles getting dates without changing context', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->addDate('Different Date', Carbon::create('2025-06-15'));

    // Check a different date without changing context
    $dates = $ud->getUsefulDate(Carbon::create('2025-06-15'));
    expect(count($dates))->toEqual(1);

    // Context should still be Jan 1
    expect($ud->date->format('Y-m-d'))->toEqual('2025-01-01');
    expect($ud->isUsefulDate())->toBeFalse();
});

it('handles immutability of returned dates', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-07-04'));
    $ud->addDate('Independence Day', Carbon::create('2025-07-04'));

    $dates1 = $ud->getUsefulDate();
    $dates1[0]->name = 'Modified Name';

    $dates2 = $ud->getUsefulDate();

    // Original should not be modified (cloned)
    expect($dates2[0]->name)->toEqual('Independence Day');
});

it('handles setDate returns self for chaining', function (): void {
    $ud = new UsefulDates;

    $result = $ud->setDate(Carbon::create('2025-01-01'));

    expect($result)->toBe($ud);
});

it('handles add returns self for chaining', function (): void {
    class ChainTestDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Chain Test';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 8, 1, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    $result = $ud->add(ChainTestDate::class);

    expect($result)->toBe($ud);
});

it('handles addDate returns self for chaining', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    $result = $ud->addDate('Test', Carbon::create('2025-01-01'));

    expect($result)->toBe($ud);
});

it('handles setBusinessDays returns self for chaining', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    $result = $ud->setBusinessDays([1, 2, 3, 4, 5]);

    expect($result)->toBe($ud);
});

it('handles adding same date class multiple times creates separate instances', function (): void {
    class MultiInstanceDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Multi Instance';
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

    $ud->add(MultiInstanceDate::class);
    $ud->add(MultiInstanceDate::class);

    $dates = $ud->getUsefulDate();

    // Should have 2 instances of the same date
    expect(count($dates))->toEqual(2);
});

it('handles date context preserved after exception', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    try {
        $ud->setDate('invalid');
    } catch (\Exception $e) {
        // Date should still be Jan 1
        expect($ud->date->format('Y-m-d'))->toEqual('2025-01-01');
    }
});

it('handles business days preserved after exception', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->setBusinessDays([1, 2, 3]);

    try {
        $ud->setBusinessDays([99]);
    } catch (\Exception $e) {
        // Business days should still be [1,2,3]
        expect($ud->getBusinessDays())->toEqual([1, 2, 3]);
    }
});

it('handles useful dates list preserved after exception', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->addDate('Valid Date', Carbon::create('2025-01-01'));

    $countBefore = count($ud->getUsefulDate());

    try {
        $ud->add('InvalidClass');
    } catch (\Exception $e) {
        // Should still have the same dates
        expect(count($ud->getUsefulDate()))->toEqual($countBefore);
    }
});

it('handles date reference not shared between calls', function (): void {
    $ud = new UsefulDates;
    $originalDate = Carbon::create('2025-01-01');
    $ud->setDate($originalDate);

    // Modify the original date after setting
    $originalDate->addDays(10);

    // UsefulDates should have its own copy
    expect($ud->date->format('Y-m-d'))->toEqual('2025-01-01');
});

it('handles property access via property hooks', function (): void {
    class PropertyHooksDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Property Hooks';
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
    $ud->setDate(Carbon::create('2025-10-01'));
    $ud->add(PropertyHooksDate::class);

    $dates = $ud->getUsefulDate();

    // Test property hooks work correctly
    expect($dates[0]->name)->toEqual('Property Hooks');
    expect($dates[0]->is_repeated)->toBeTrue();
    expect($dates[0]->repeat_frequency)->toEqual(RepeatFrequency::YEARLY);
});

it('handles setting properties via hooks', function (): void {
    class SettablePropsDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Original Name';
            $this->is_repeated = false;
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
    $ud->add(SettablePropsDate::class);

    $dates = $ud->getUsefulDatesByYear(2025);

    // Modify via property hooks
    $dates[0]->name = 'New Name';
    $dates[0]->is_repeated = true;
    $dates[0]->repeat_frequency = RepeatFrequency::YEARLY;

    expect($dates[0]->name)->toEqual('New Name')
        ->and($dates[0]->is_repeated)->toBeTrue()
        ->and($dates[0]->repeat_frequency)->toEqual(RepeatFrequency::YEARLY);
});
