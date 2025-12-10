<?php

use Carbon\Carbon;
use UsefulDates\Enums\RepeatFrequency;
use UsefulDates\UsefulDates;

it('handles multiple filters with AND logic', function (): void {
    class MultiFilterDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public int $priority = 5;

        public bool $active = true;

        public function __construct()
        {
            $this->name = 'Multi Filter';
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
    $ud->setDate(Carbon::create('2025-03-15'));
    $ud->add(MultiFilterDate::class);

    // Both filters must match
    $dates = $ud->getUsefulDate(null, [
        ['property' => 'priority', 'operator' => '>=', 'value' => 5],
        ['property' => 'active', 'operator' => '=', 'value' => true],
    ]);

    expect(count($dates))->toEqual(1);
});

it('filters out dates when one filter fails', function (): void {
    class FilterFailDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public int $priority = 3;

        public bool $active = true;

        public function __construct()
        {
            $this->name = 'Filter Fail';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 4, 20, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-04-20'));
    $ud->add(FilterFailDate::class);

    // Second filter will fail
    $dates = $ud->getUsefulDate(null, [
        ['property' => 'priority', 'operator' => '>=', 'value' => 3],
        ['property' => 'active', 'operator' => '=', 'value' => false],
    ]);

    expect(count($dates))->toEqual(0);
});

it('handles filter with non-existent property', function (): void {
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
            return Carbon::create($this->currentDate->year, 5, 25, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-05-25'));
    $ud->add(NoPropertyDate::class);

    // Filter references a property that doesn't exist
    $dates = $ud->getUsefulDate(null, [
        ['property' => 'nonexistent', 'operator' => '=', 'value' => true],
    ]);

    expect(count($dates))->toEqual(0);
});

it('handles empty filters array returns all dates', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-06-01'));
    $ud->addDate('Test 1', Carbon::create('2025-06-01'));
    $ud->addDate('Test 2', Carbon::create('2025-06-01'));

    $dates = $ud->getUsefulDate(null, []);

    expect(count($dates))->toEqual(2);
});

it('handles null filters returns all dates', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-06-01'));
    $ud->addDate('Test 1', Carbon::create('2025-06-01'));
    $ud->addDate('Test 2', Carbon::create('2025-06-01'));

    $dates = $ud->getUsefulDate(null, null);

    expect(count($dates))->toEqual(2);
});

it('handles filter with string comparison', function (): void {
    class StringPropertyDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public string $category = 'holiday';

        public function __construct()
        {
            $this->name = 'String Property';
            $this->category = 'holiday';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 7, 4, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-07-04'));
    $ud->add(StringPropertyDate::class);

    $dates = $ud->getUsefulDate(null, [
        ['property' => 'category', 'operator' => '=', 'value' => 'holiday'],
    ]);

    expect(count($dates))->toEqual(1);

    $dates = $ud->getUsefulDate(null, [
        ['property' => 'category', 'operator' => '!=', 'value' => 'workday'],
    ]);

    expect(count($dates))->toEqual(1);
});

it('handles filter with numeric boundaries', function (): void {
    class NumericBoundaryDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public float $importance = 7.5;

        public function __construct()
        {
            $this->name = 'Numeric Boundary';
            $this->importance = 7.5;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 8, 15, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-08-15'));
    $ud->add(NumericBoundaryDate::class);

    // Test >
    $dates = $ud->getUsefulDate(null, [
        ['property' => 'importance', 'operator' => '>', 'value' => 7.0],
    ]);
    expect(count($dates))->toEqual(1);

    $dates = $ud->getUsefulDate(null, [
        ['property' => 'importance', 'operator' => '>', 'value' => 7.5],
    ]);
    expect(count($dates))->toEqual(0);

    // Test <
    $dates = $ud->getUsefulDate(null, [
        ['property' => 'importance', 'operator' => '<', 'value' => 8.0],
    ]);
    expect(count($dates))->toEqual(1);

    $dates = $ud->getUsefulDate(null, [
        ['property' => 'importance', 'operator' => '<', 'value' => 7.5],
    ]);
    expect(count($dates))->toEqual(0);

    // Test >=
    $dates = $ud->getUsefulDate(null, [
        ['property' => 'importance', 'operator' => '>=', 'value' => 7.5],
    ]);
    expect(count($dates))->toEqual(1);

    // Test <=
    $dates = $ud->getUsefulDate(null, [
        ['property' => 'importance', 'operator' => '<=', 'value' => 7.5],
    ]);
    expect(count($dates))->toEqual(1);
});

it('handles filter with boolean values', function (): void {
    class BooleanFilterDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public bool $enabled = true;

        public bool $deprecated = false;

        public function __construct()
        {
            $this->name = 'Boolean Filter';
            $this->enabled = true;
            $this->deprecated = false;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 9, 10, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-09-10'));
    $ud->add(BooleanFilterDate::class);

    $dates = $ud->getUsefulDate(null, [
        ['property' => 'enabled', 'operator' => '=', 'value' => true],
    ]);
    expect(count($dates))->toEqual(1);

    $dates = $ud->getUsefulDate(null, [
        ['property' => 'deprecated', 'operator' => '=', 'value' => false],
    ]);
    expect(count($dates))->toEqual(1);

    $dates = $ud->getUsefulDate(null, [
        ['property' => 'enabled', 'operator' => '!=', 'value' => false],
    ]);
    expect(count($dates))->toEqual(1);
});

it('handles filter with unknown operator falls through', function (): void {
    class UnknownOperatorDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public int $value = 10;

        public function __construct()
        {
            $this->name = 'Unknown Operator';
            $this->value = 10;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 10, 20, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-10-20'));
    $ud->add(UnknownOperatorDate::class);

    // Unknown operator should fall through to default case (break)
    $dates = $ud->getUsefulDate(null, [
        ['property' => 'value', 'operator' => '~=', 'value' => 10],
    ]);

    // Should still include the date since default case breaks
    expect(count($dates))->toEqual(1);
});

it('handles filter comparing zero values', function (): void {
    class ZeroValueDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public int $count = 0;

        public function __construct()
        {
            $this->name = 'Zero Value';
            $this->count = 0;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 11, 25, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-11-25'));
    $ud->add(ZeroValueDate::class);

    $dates = $ud->getUsefulDate(null, [
        ['property' => 'count', 'operator' => '=', 'value' => 0],
    ]);
    expect(count($dates))->toEqual(1);

    $dates = $ud->getUsefulDate(null, [
        ['property' => 'count', 'operator' => '>', 'value' => 0],
    ]);
    expect(count($dates))->toEqual(0);

    $dates = $ud->getUsefulDate(null, [
        ['property' => 'count', 'operator' => '>=', 'value' => 0],
    ]);
    expect(count($dates))->toEqual(1);
});

it('handles filter comparing negative numbers', function (): void {
    class NegativeValueDate extends \UsefulDates\Abstracts\UsefulDateAbstract
    {
        public int $offset = -5;

        public function __construct()
        {
            $this->name = 'Negative Value';
            $this->offset = -5;
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 12, 15, 0, 0, 0);
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-12-15'));
    $ud->add(NegativeValueDate::class);

    $dates = $ud->getUsefulDate(null, [
        ['property' => 'offset', 'operator' => '<', 'value' => 0],
    ]);
    expect(count($dates))->toEqual(1);

    $dates = $ud->getUsefulDate(null, [
        ['property' => 'offset', 'operator' => '>', 'value' => -10],
    ]);
    expect(count($dates))->toEqual(1);
});
