<?php

use Carbon\Carbon;
use UsefulDates\Abstracts\UsefulDateAbstract;
use UsefulDates\Abstracts\UsefulDatesExtensionAbstract;
use UsefulDates\Enums\RepeatFrequency;
use UsefulDates\Exceptions\InvalidExtensionException;
use UsefulDates\Exceptions\InvalidUsefulDateException;
use UsefulDates\UsefulDates;

it('throws InvalidExtensionException for class that does not extend UsefulDatesExtensionAbstract', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    expect(fn () => $ud->addExtension('stdClass'))
        ->toThrow(InvalidExtensionException::class);
});
//
it('throws InvalidUsefulDateException when extension provides invalid useful date', function (): void {
    class BadUsefulDatesExtension extends UsefulDatesExtensionAbstract
    {
        public static string $name = 'No Methods';

        public static bool $hasMethods = false;

        public static function usefulDates(): array
        {
            return [
                'MyDate',
            ];
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    expect(fn () => $ud->addExtension(BadUsefulDatesExtension::class))
        ->toThrow(InvalidUsefulDateException::class);
});
//
it('handles extension with no methods', function (): void {
    class NoMethodsExtension extends UsefulDatesExtensionAbstract
    {
        public static string $name = 'No Methods';

        public static bool $hasMethods = false;

        public static function usefulDates(): array
        {
            return [];
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    $ud->addExtension(NoMethodsExtension::class);

    // Should not throw, just adds no methods
    expect(true)->toBeTrue();
});

it('handles extension with custom methods', function (): void {
    class TestDateForExtension extends UsefulDateAbstract
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
            return Carbon::create($this->currentDate->year, 6, 1, 0, 0, 0);
        }
    }

    class MethodExtension extends UsefulDatesExtensionAbstract
    {
        public static string $name = 'Method Extension';

        public static bool $hasMethods = true;

        public static function usefulDates(): array
        {
            return [TestDateForExtension::class];
        }

        public function methods(): array
        {
            return [
                'customMethod' => fn () => 'custom result',
                'doubleValue' => fn ($x) => $x * 2,
            ];
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-06-01'));
    $ud->addExtension(MethodExtension::class);

    // Call custom methods via __call
    expect($ud->customMethod())->toEqual('custom result');
    expect($ud->doubleValue(5))->toEqual(10);
});
//
it('throws BadMethodCallException for non-existent dynamic method', function (): void {
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    expect(fn () => $ud->nonExistentMethod())
        ->toThrow(BadMethodCallException::class);
});
//
it('handles extension with options parameter', function (): void {
    class MyDate extends UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Options Date';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 7, 1, 0, 0, 0);
        }
    }

    class OptionsExtension extends UsefulDatesExtensionAbstract
    {
        public static string $name = 'Options Extension';

        public static function usefulDates($options = null): array
        {
            if ($options === 'skip') {
                return [];
            }

            return [
                MyDate::class,
            ];
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-07-01'));
    $ud->addExtension(OptionsExtension::class, 'include');

    expect($ud->isUsefulDate())->toBeTrue();

    $ud2 = new UsefulDates;
    $ud2->setDate(Carbon::create('2025-07-01'));
    $ud2->addExtension(OptionsExtension::class, 'skip');

    expect($ud2->isUsefulDate())->toBeFalse();
});

it('handles multiple extensions with overlapping method names', function (): void {
    class FirstExtension extends UsefulDatesExtensionAbstract
    {
        public static string $name = 'First';

        public static bool $hasMethods = true;

        public function methods(): array
        {
            return [
                'sharedMethod' => fn () => 'first',
            ];
        }
    }

    class SecondExtension extends UsefulDatesExtensionAbstract
    {
        public static string $name = 'Second';

        public static bool $hasMethods = true;

        public function methods(): array
        {
            return [
                'sharedMethod' => fn () => 'second',
            ];
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->addExtension(FirstExtension::class);
    $ud->addExtension(SecondExtension::class);

    // Last extension should win
    expect($ud->sharedMethod())->toEqual('second');
});

it('handles extension method with multiple parameters', function (): void {
    class MultiParamExtension extends UsefulDatesExtensionAbstract
    {
        public static string $name = 'Multi Param';

        public static bool $hasMethods = true;

        public function methods(): array
        {
            return [
                'sum' => fn ($a, $b, $c = 0) => $a + $b + $c,
            ];
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->addExtension(MultiParamExtension::class);

    expect($ud->sum(1, 2))->toEqual(3);
    expect($ud->sum(1, 2, 3))->toEqual(6);
});

it('handles extension method that accesses UsefulDates instance', function (): void {
    class ContextAwareExtension extends UsefulDatesExtensionAbstract
    {
        public static string $name = 'Context Aware';

        public static bool $hasMethods = true;

        public function methods(): array
        {
            return [
                'getCurrentYear' => fn () => $this->usefulDates->date->year,
                'isWeekend' => fn () => !$this->usefulDates->isBusinessDay(),
            ];
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-06-15'));
    $ud->addExtension(ContextAwareExtension::class);

    expect($ud->getCurrentYear())->toEqual(2025);
});

it('handles extension providing multiple useful dates', function (): void {
    class Holiday1 extends UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Holiday 1';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 8, 1, 0, 0, 0);
        }
    }

    class Holiday2 extends UsefulDateAbstract
    {
        public function __construct()
        {
            $this->name = 'Holiday 2';
            $this->is_repeated = true;
            $this->repeat_frequency = RepeatFrequency::YEARLY;
            $this->start_date = Carbon::create(2020, 1, 1, 0, 0, 0);
        }

        public function date(): Carbon
        {
            return Carbon::create($this->currentDate->year, 8, 15, 0, 0, 0);
        }
    }

    class MultiDateExtension extends UsefulDatesExtensionAbstract
    {
        public static string $name = 'Multi Date';

        public static function usefulDates(): array
        {
            return [Holiday1::class, Holiday2::class];
        }
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-08-01'));
    $ud->addExtension(MultiDateExtension::class);

    expect($ud->isUsefulDate())->toBeTrue();

    $ud->setDate(Carbon::create('2025-08-15'));
    expect($ud->isUsefulDate())->toBeTrue();

    $ud->setDate(Carbon::create('2025-08-10'));
    expect($ud->isUsefulDate())->toBeFalse();
});

it('handles empty extension', function (): void {
    class EmptyExtension extends UsefulDatesExtensionAbstract
    {
        public static string $name = 'Empty';
    }

    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));
    $ud->addExtension(EmptyExtension::class);

    // Should not throw, just does nothing
    expect(true)->toBeTrue();
});
