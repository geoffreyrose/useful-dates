<?php

use Carbon\Carbon;
use UsefulDates\Enums\RepeatFrequency;
use UsefulDates\UsefulDates;

beforeEach(function (): void {
    $this->usefulDate = new UsefulDates;
});

it('filters with greater than operator', function (): void {
    class FilterGtDate1 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public int $priority = 3;

        public function __construct()
        {
            $this->name = 'Priority 3';
            $this->priority = 3;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 4, 1, 0, 0, 0);
        }
    }

    class FilterGtDate2 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public int $priority = 8;

        public function __construct()
        {
            $this->name = 'Priority 8';
            $this->priority = 8;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 4, 1, 0, 0, 0);
        }
    }

    $this->usefulDate->setDate(Carbon::create('2025-04-01'));
    $this->usefulDate->add(FilterGtDate1::class);
    $this->usefulDate->add(FilterGtDate2::class);

    $dates = $this->usefulDate->getUsefulDate(null, [['property' => 'priority', 'operator' => '>', 'value' => 5]]);

    expect(count($dates))->toEqual(1)
        ->and($dates[0]->priority)->toEqual(8);
});

it('filters with less than operator', function (): void {
    class FilterLtDate1 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public int $level = 2;

        public function __construct()
        {
            $this->name = 'Level 2';
            $this->level = 2;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 5, 1, 0, 0, 0);
        }
    }

    class FilterLtDate2 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public int $level = 9;

        public function __construct()
        {
            $this->name = 'Level 9';
            $this->level = 9;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 5, 1, 0, 0, 0);
        }
    }

    $this->usefulDate->setDate(Carbon::create('2025-05-01'));
    $this->usefulDate->add(FilterLtDate1::class);
    $this->usefulDate->add(FilterLtDate2::class);

    $dates = $this->usefulDate->getUsefulDate(null, [['property' => 'level', 'operator' => '<', 'value' => 5]]);

    expect(count($dates))->toEqual(1)
        ->and($dates[0]->level)->toEqual(2);
});

it('filters with greater than or equal operator', function (): void {
    class FilterGteDate1 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public int $score = 5;

        public function __construct()
        {
            $this->name = 'Score 5';
            $this->score = 5;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 6, 1, 0, 0, 0);
        }
    }

    class FilterGteDate2 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public int $score = 3;

        public function __construct()
        {
            $this->name = 'Score 3';
            $this->score = 3;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 6, 1, 0, 0, 0);
        }
    }

    $this->usefulDate->setDate(Carbon::create('2025-06-01'));
    $this->usefulDate->add(FilterGteDate1::class);
    $this->usefulDate->add(FilterGteDate2::class);

    $dates = $this->usefulDate->getUsefulDate(null, [['property' => 'score', 'operator' => '>=', 'value' => 5]]);

    expect(count($dates))->toEqual(1)
        ->and($dates[0]->score)->toEqual(5);
});

it('filters with less than or equal operator', function (): void {
    class FilterLteDate1 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public int $rank = 4;

        public function __construct()
        {
            $this->name = 'Rank 4';
            $this->rank = 4;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 7, 1, 0, 0, 0);
        }
    }

    class FilterLteDate2 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public int $rank = 7;

        public function __construct()
        {
            $this->name = 'Rank 7';
            $this->rank = 7;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 7, 1, 0, 0, 0);
        }
    }

    $this->usefulDate->setDate(Carbon::create('2025-07-01'));
    $this->usefulDate->add(FilterLteDate1::class);
    $this->usefulDate->add(FilterLteDate2::class);

    $dates = $this->usefulDate->getUsefulDate(null, [['property' => 'rank', 'operator' => '<=', 'value' => 5]]);

    expect(count($dates))->toEqual(1)
        ->and($dates[0]->rank)->toEqual(4);
});

it('filters with equals operator', function (): void {
    class FilterEqDate1 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public string $category = 'A';

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
            return Carbon::create($this->currentDate->year, 8, 1, 0, 0, 0);
        }
    }

    class FilterEqDate2 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public string $category = 'B';

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
            return Carbon::create($this->currentDate->year, 8, 1, 0, 0, 0);
        }
    }

    $this->usefulDate->setDate(Carbon::create('2025-08-01'));
    $this->usefulDate->add(FilterEqDate1::class);
    $this->usefulDate->add(FilterEqDate2::class);

    $dates = $this->usefulDate->getUsefulDate(null, [['property' => 'category', 'operator' => '=', 'value' => 'B']]);

    expect(count($dates))->toEqual(1)
        ->and($dates[0]->category)->toEqual('B');
});

it('filters with not equals operator', function (): void {
    class FilterNeDate1 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public string $type = 'special';

        public function __construct()
        {
            $this->name = 'Special';
            $this->type = 'special';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 9, 1, 0, 0, 0);
        }
    }

    class FilterNeDate2 extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public string $type = 'normal';

        public function __construct()
        {
            $this->name = 'Normal';
            $this->type = 'normal';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 9, 1, 0, 0, 0);
        }
    }

    $this->usefulDate->setDate(Carbon::create('2025-09-01'));
    $this->usefulDate->add(FilterNeDate1::class);
    $this->usefulDate->add(FilterNeDate2::class);

    $dates = $this->usefulDate->getUsefulDate(null, [['property' => 'type', 'operator' => '!=', 'value' => 'normal']]);

    expect(count($dates))->toEqual(1)
        ->and($dates[0]->type)->toEqual('special');
});

it('handles invalid filter format gracefully', function (): void {
    class InvalidFilterDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public int $value = 5;

        public function __construct()
        {
            $this->name = 'Test';
            $this->value = 5;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 10, 1, 0, 0, 0);
        }
    }

    $this->usefulDate->setDate(Carbon::create('2025-10-01'));
    $this->usefulDate->add(InvalidFilterDate::class);

    // Invalid filter - missing operator (will continue and add the date anyway because filter is skipped)
    $dates = $this->usefulDate->getUsefulDate(null, [['property' => 'value', 'value' => 5]]);
    expect(count($dates))->toEqual(1); // Date is still returned, just filter is skipped

    // Invalid filter - not an array  (will continue and add the date anyway)
    $dates = $this->usefulDate->getUsefulDate(null, ['invalid']);
    expect(count($dates))->toEqual(1); // Date is still returned, just filter is skipped
});

it('handles non-existent property gracefully', function (): void {
    class NoPropertyDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'No Property';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 11, 1, 0, 0, 0);
        }
    }

    $this->usefulDate->setDate(Carbon::create('2025-11-01'));
    $this->usefulDate->add(NoPropertyDate::class);

    // Filter by property that doesn't exist
    $dates = $this->usefulDate->getUsefulDate(null, [['property' => 'nonexistent', 'operator' => '=', 'value' => 5]]);
    expect(count($dates))->toEqual(0);
});

it('handles default operator case', function (): void {
    class DefaultOperatorDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public int $val = 10;

        public function __construct()
        {
            $this->name = 'Default';
            $this->val = 10;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 12, 1, 0, 0, 0);
        }
    }

    $this->usefulDate->setDate(Carbon::create('2025-12-01'));
    $this->usefulDate->add(DefaultOperatorDate::class);

    // Unknown operator - should still return date (default case)
    $dates = $this->usefulDate->getUsefulDate(null, [['property' => 'val', 'operator' => 'unknown', 'value' => 5]]);
    expect(count($dates))->toEqual(1);
});
