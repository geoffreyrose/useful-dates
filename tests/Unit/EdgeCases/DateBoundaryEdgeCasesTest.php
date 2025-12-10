<?php

use Carbon\Carbon;
use UsefulDates\Enums\RepeatFrequency;
use UsefulDates\UsefulDates;

it('handles February 28 in non-leap year', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-02-28')); // Non-leap year
    $ud->addDate('Last Feb', Carbon::create('2025-02-28'));

    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles February 29 in leap year', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2024-02-29')); // Leap year
    $ud->addDate('Leap Day', Carbon::create('2024-02-29'));

    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles month boundaries - last day of month', function (string $date): void {
    $ud = new UsefulDates;
    $carbon = Carbon::create($date);
    $ud->setDate($carbon);
    $ud->addDate('Month End', $carbon);

    expect($ud->isUsefulDate())->toBeTrue();
})->with([
    '2025-01-31', // January
    '2025-03-31', // March
    '2025-04-30', // April
    '2025-05-31', // May
    '2025-06-30', // June
    '2025-07-31', // July
    '2025-08-31', // August
    '2025-09-30', // September
    '2025-10-31', // October
    '2025-11-30', // November
    '2025-12-31', // December
]);

it('handles month boundaries - first day of month', function (int $month): void {
    $ud = new UsefulDates;
    $carbon = Carbon::create(2025, $month, 1);
    $ud->setDate($carbon);
    $ud->addDate('Month Start', $carbon);

    expect($ud->isUsefulDate())->toBeTrue();
})->with([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]);

it('handles year 2000 (century leap year)', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2000-02-29'));
    $ud->addDate('Y2K Leap', Carbon::create('2000-02-29'));

    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles year 1900 (century non-leap year)', function (): void {
    $ud = new UsefulDates;
    // 1900 was not a leap year
    $ud->setDate(Carbon::create('1900-02-28'));
    $ud->addDate('1900 Feb End', Carbon::create('1900-02-28'));

    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles daylight saving time spring forward', function (): void {
    // In most US timezones, DST starts second Sunday of March (2am becomes 3am)
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-03-09 02:30:00', 'America/New_York'));
    $ud->addDate('DST Spring', Carbon::create('2025-03-09 02:30:00'));

    // Should still work despite DST
    expect($ud->date)->toBeInstanceOf(Carbon::class);
});

it('handles daylight saving time fall back', function (): void {
    // In most US timezones, DST ends first Sunday of November (2am becomes 1am)
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-11-02 01:30:00', 'America/New_York'));
    $ud->addDate('DST Fall', Carbon::create('2025-11-02 01:30:00'));

    expect($ud->date)->toBeInstanceOf(Carbon::class);
});

it('handles dates crossing international date line', function (): void {
    $ud = new UsefulDates;
    // Pacific/Kiritimati is UTC+14 (one of the earliest timezones)
    $earliestTz = Carbon::create('2025-01-01 00:00:00', 'Pacific/Kiritimati');
    $ud->setDate($earliestTz);

    // Should normalize to UTC
    expect($ud->date->timezone->getName())->toEqual('UTC');
});

it('handles Unix epoch boundary', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('1970-01-01 00:00:00'));
    $ud->addDate('Unix Epoch', Carbon::create('1970-01-01'));

    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles dates before Unix epoch', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('1969-12-31 23:59:59'));
    $ud->addDate('Pre-Epoch', Carbon::create('1969-12-31'));

    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles midnight exactly', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-06-15 00:00:00.000000'));
    $ud->addDate('Midnight', Carbon::create('2025-06-15'));

    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles one microsecond before midnight', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-06-14 23:59:59.999999'));
    $ud->addDate('Almost Midnight', Carbon::create('2025-06-14'));

    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles date at year 1 AD', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('0001-01-01'));
    $ud->addDate('Year 1', Carbon::create('0001-01-01'));

    expect($ud->date->year)->toEqual(1);
});

it('handles century boundaries', function (string $date): void {
    $ud = new UsefulDates;
    $carbon = Carbon::create($date);
    $ud->setDate($carbon);
    $ud->addDate('Century', $carbon);

    expect($ud->isUsefulDate())->toBeTrue();
})->with([
    '1900-01-01',
    '2000-01-01',
    '2100-01-01',
    '2200-01-01',
]);

it('handles week boundaries with business days', function (): void {
    $ud = new UsefulDates;
    $ud->setBusinessDays([1, 2, 3, 4, 5]);

    // Saturday (end of week)
    $ud->setDate(Carbon::create('2025-01-04')); // Saturday
    expect($ud->isBusinessDay())->toBeFalse();

    // Sunday (start of week by ISO)
    $ud->setDate(Carbon::create('2025-01-05')); // Sunday
    expect($ud->isBusinessDay())->toBeFalse();

    // Monday (start of business week)
    $ud->setDate(Carbon::create('2025-01-06')); // Monday
    expect($ud->isBusinessDay())->toBeTrue();
});

it('handles same date in different years with YEARLY frequency', function (): void {
    class YearlyBoundaryDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Yearly Boundary';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 6, 15, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 6, 15, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2000-06-15'));
    $ud->add(YearlyBoundaryDate::class);

    // Test multiple years
    foreach ([2020, 2025, 2030, 2050, 2100] as $year) {
        $ud->setDate(Carbon::create("{$year}-06-15"));
        expect($ud->isUsefulDate())->toBeTrue();
    }
});

it('handles quarter boundaries', function (string $date): void {
    $ud = new UsefulDates;
    $carbon = Carbon::create($date);
    $ud->setDate($carbon);
    $ud->addDate('Quarter End', $carbon);

    expect($ud->isUsefulDate())->toBeTrue();
})->with([
    '2025-03-31', // Q1 end
    '2025-06-30', // Q2 end
    '2025-09-30', // Q3 end
    '2025-12-31', // Q4 end
]);

it('handles dates with start_date equal to end_date', function (): void {
    class SingleDayRange extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Single Day';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::NONE;
            $this->start_date = Carbon::create(2025, 7, 4, 0, 0, 0);
            $this->end_date = Carbon::create(2025, 7, 4, 0, 0, 0); // Same as start
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 7, 4, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-07-04'));
    $ud->add(SingleDayRange::class);

    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles monthly date on 31st for months with fewer days', function (): void {
    class Day31Monthly extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Day 31 Monthly';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::MONTHLY;
            $this->start_date = Carbon::create(2025, 1, 31, 0, 0, 0);
        }

        public function date(): ?Carbon
        {
            // Try to create day 31, but some months don't have it
            if ($this->currentDate->daysInMonth >= 31) {
                return Carbon::create($this->currentDate->year, $this->currentDate->month, 31, 0, 0, 0);
            }

            return null; // No day 31 in this month
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2000-06-15'));
    $ud->add(Day31Monthly::class);

    // January has 31 days
    $ud->setDate(Carbon::create('2025-01-31'));
    expect($ud->isUsefulDate())->toBeTrue();

    // February doesn't have 31 days
    $ud->setDate(Carbon::create('2025-02-28'));
    expect($ud->isUsefulDate())->toBeFalse();

    // March has 31 days
    $ud->setDate(Carbon::create('2025-03-31'));
    expect($ud->isUsefulDate())->toBeTrue();
});

it('handles week 53 in ISO 8601', function (): void {
    // Some years have 53 weeks in ISO 8601
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2020-12-31')); // 2020 had 53 weeks

    expect($ud->date->isoWeek)->toEqual(53);
});
