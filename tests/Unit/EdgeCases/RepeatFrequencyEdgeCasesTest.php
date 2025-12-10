<?php

use Carbon\Carbon;
use UsefulDates\Enums\RepeatFrequency;
use UsefulDates\UsefulDates;

it('handles RepeatFrequency NONE with is_repeated false', function (): void {
    class NoneNotRepeated extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'None Not Repeated';
            $this->is_repeated = false;
            $this->repeat_frequency = RepeatFrequency::NONE;
            $this->start_date = Carbon::create(2025, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create(2025, 1, 1, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->add(NoneNotRepeated::class);

    expect($ud->isUsefulDate())->toBeTrue();

    // Should not appear in future years
    $ud->setDate(Carbon::create('2026-01-01'));
    expect($ud->isUsefulDate())->toBeFalse();
});

it('handles RepeatFrequency NONE with is_repeated true', function (): void {
    class NoneRepeated extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'None Repeated';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::NONE;
            $this->start_date = Carbon::create(2025, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 1, 1, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->add(NoneRepeated::class);

    expect($ud->isUsefulDate())->toBeTrue();

    // Even with is_repeated true, NONE means only in start year
    $ud->setDate(Carbon::create('2026-01-01'));
    expect($ud->isUsefulDate())->toBeFalse();
});

it('handles RepeatFrequency MONTHLY across multiple years', function (): void {
    class MonthlyMultiYear extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Monthly Multi Year';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::MONTHLY;
            $this->start_date = Carbon::create(2025, 1, 15, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, $this->currentDate->month, 15, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->add(MonthlyMultiYear::class);

    // Test across multiple years
    foreach ([2025, 2026, 2027] as $year) {
        foreach ([1, 6, 12] as $month) {
            $ud->setDate(Carbon::create($year, $month, 15));
            expect($ud->isUsefulDate())->toBeTrue();
        }
    }
});

it('handles RepeatFrequency YEARLY with specific start and end years', function (): void {
    class YearlyRanged extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Yearly Ranged';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2025, 6, 1, 0, 0, 0);
            $this->end_date = Carbon::create(2027, 6, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 6, 1, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->add(YearlyRanged::class);

    // Before range
    $ud->setDate(Carbon::create('2024-06-01'));
    expect($ud->isUsefulDate())->toBeFalse();

    // Within range
    $ud->setDate(Carbon::create('2025-06-01'));
    expect($ud->isUsefulDate())->toBeTrue();

    $ud->setDate(Carbon::create('2026-06-01'));
    expect($ud->isUsefulDate())->toBeTrue();

    $ud->setDate(Carbon::create('2027-06-01'));
    expect($ud->isUsefulDate())->toBeTrue();

    // After range
    $ud->setDate(Carbon::create('2028-06-01'));
    expect($ud->isUsefulDate())->toBeFalse();
});

it('handles RepeatFrequency CUSTOM with complex logic', function (): void {
    class CustomComplexLogic extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Custom Complex';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::CUSTOM;
            $this->start_date = Carbon::create(2025, 1, 1, 0, 0, 0);
        }

        public function date(): ?Carbon
        {
            // Only on prime-numbered days
            $primes = [2, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31];
            if (in_array($this->currentDate->day, $primes)) {
                return $this->currentDate->copy();
            }

            return null;
        }
    }

    $ud = new UsefulDates;
    $ud->add(CustomComplexLogic::class);

    // Prime days
    $ud->setDate(Carbon::create('2025-01-07'));
    expect($ud->isUsefulDate())->toBeTrue();

    // Non-prime days
    $ud->setDate(Carbon::create('2025-01-08'));
    expect($ud->isUsefulDate())->toBeFalse();
});

it('handles RepeatFrequency CUSTOM with null returns', function (): void {
    class CustomNullReturns extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Custom Null';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::CUSTOM;
            $this->start_date = Carbon::create(2025, 1, 1, 0, 0, 0);
        }

        public function date(): ?Carbon
        {
            // Always return null
            return null;
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2020-01-01'));
    $ud->add(CustomNullReturns::class);

    $ud->setDate(Carbon::create('2025-01-01'));
    expect($ud->isUsefulDate())->toBeFalse();
});

it('handles RepeatFrequency enum comparison', function (): void {
    expect(RepeatFrequency::NONE)->not->toBe(RepeatFrequency::MONTHLY);
    expect(RepeatFrequency::MONTHLY)->not->toBe(RepeatFrequency::YEARLY);
    expect(RepeatFrequency::YEARLY)->not->toBe(RepeatFrequency::CUSTOM);
    expect(RepeatFrequency::NONE)->toBe(RepeatFrequency::NONE);
});

it('handles RepeatFrequency MONTHLY with end_date in same year', function (): void {
    class MonthlyShortRange extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Monthly Short Range';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::MONTHLY;
            $this->start_date = Carbon::create(2025, 3, 10, 0, 0, 0);
            $this->end_date = Carbon::create(2025, 6, 10, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, $this->currentDate->month, 10, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2020-01-01'));
    $ud->add(MonthlyShortRange::class);

    // Before range
    $ud->setDate(Carbon::create('2025-02-10'));
    expect($ud->isUsefulDate())->toBeFalse();

    // Within range
    $ud->setDate(Carbon::create('2025-04-10'));
    expect($ud->isUsefulDate())->toBeTrue();

    // After range
    $ud->setDate(Carbon::create('2025-07-10'));
    expect($ud->isUsefulDate())->toBeFalse();
});

it('handles RepeatFrequency MONTHLY spanning year boundary', function (): void {
    class MonthlyYearSpan extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Monthly Year Span';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::MONTHLY;
            $this->start_date = Carbon::create(2024, 11, 20, 0, 0, 0);
            $this->end_date = Carbon::create(2025, 2, 20, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, $this->currentDate->month, 20, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2020-01-01'));
    $ud->add(MonthlyYearSpan::class);

    // Before range
    $ud->setDate(Carbon::create('2024-10-20'));
    expect($ud->isUsefulDate())->toBeFalse();

    // Within range - Nov 2024
    $ud->setDate(Carbon::create('2024-11-20'));
    expect($ud->isUsefulDate())->toBeTrue();

    // Within range - Dec 2024
    $ud->setDate(Carbon::create('2024-12-20'));
    expect($ud->isUsefulDate())->toBeTrue();

    // Within range - Jan 2025
    $ud->setDate(Carbon::create('2025-01-20'));
    expect($ud->isUsefulDate())->toBeTrue();

    // Within range - Feb 2025
    $ud->setDate(Carbon::create('2025-02-20'));
    expect($ud->isUsefulDate())->toBeTrue();

    // After range
    $ud->setDate(Carbon::create('2025-03-20'));
    expect($ud->isUsefulDate())->toBeFalse();
});

it('handles RepeatFrequency with date() returning different months/days', function (): void {
    class VariableDateReturn extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Variable Date';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            // Return different day based on current year
            $day = $this->currentDate->year % 28 + 1; // Varies by year, stays in valid range

            return Carbon::create($this->currentDate->year, 12, $day, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2020-12-06'));
    $ud->add(VariableDateReturn::class);

    // Different years should produce different days
    $ud->setDate(Carbon::create('2025-12-06'));
    $dates = $ud->getNextUsefulDates(2);
    if (count($dates) > 0) {
        $day2025 = $dates[0]->usefulDate()->day;
        $day2026 = $dates[1]->usefulDate()->day;
        expect($day2025)->not->toEqual($day2026)
            ->and($day2025)->toEqual(10)
            ->and($day2026)->toEqual(11);
    }
});

it('handles all RepeatFrequency enum values', function (): void {
    $frequencies = [
        RepeatFrequency::NONE,
        RepeatFrequency::MONTHLY,
        RepeatFrequency::YEARLY,
        RepeatFrequency::CUSTOM,
    ];

    foreach ($frequencies as $freq) {
        expect($freq)->toBeInstanceOf(RepeatFrequency::class);
    }

    expect(count($frequencies))->toEqual(4);
});

it('handles RepeatFrequency default value', function (): void {
    class DefaultFrequency extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Default Frequency';
            // Not setting repeat_frequency, should use default
            $this->is_repeated = true;
            $this->start_date = Carbon::create(2025, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 1, 1, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->add(DefaultFrequency::class);

    $dates = $ud->getUsefulDate();
    expect($dates[0]->repeat_frequency)->toBeInstanceOf(RepeatFrequency::class);
});
