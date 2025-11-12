<?php

use Carbon\Carbon;
use Tests\ValidAprilFools;
use UsefulDates\Abstracts\UsefulDatesExtensionAbstract;
use UsefulDates\UsefulDates;

beforeEach(function (): void {
    $this->usefulDate = new UsefulDates;
    $this->usefulDate->setDate(Carbon::create('2025-01-01'));
});

it('throws when adding an invalid extension', function (): void {
    class BadExtension {}

    $this->usefulDate->addExtension(BadExtension::class);
})->throws(\UsefulDates\Exceptions\InvalidExtensionException::class);

it('throws when extension returns invalid useful date classes', function (): void {
    class NotAUsefulDate {}

    class BadUsefulDateExtension extends UsefulDatesExtensionAbstract
    {
        public static function usefulDates(): array
        {
            return [NotAUsefulDate::class];
        }
    }

    $this->usefulDate->addExtension(BadUsefulDateExtension::class);
})->throws(\UsefulDates\Exceptions\InvalidUsefulDateException::class);

it('adds custom methods from a valid extension and invokes them dynamically', function (): void {
    class MethodOnlyExtension extends UsefulDatesExtensionAbstract
    {
        public static bool $hasMethods = true;

        public static function usefulDates(): array
        {
            return [];
        }

        public function methods(): array
        {
            return [
                'greet' => [$this, 'greet'],
            ];

        }

        public function greet(string $name): string
        {
            return 'hello ' . $name;
        }
    }

    $this->usefulDate->addExtension(MethodOnlyExtension::class);

    expect($this->usefulDate->greet('world'))->toEqual('hello world');
});

it('throws BadMethodCallException when calling unknown dynamic method', function (): void {
    class EmptyExtension extends UsefulDatesExtensionAbstract
    {
        public static function usefulDates(): array
        {
            return [];
        }
    }

    $this->usefulDate->addExtension(EmptyExtension::class);

    // No method registered
    $this->usefulDate->superSpecialMethod();
})->throws(BadMethodCallException::class);

it('add date in extension', function (): void {
    class MyExtension extends UsefulDatesExtensionAbstract
    {
        public static function usefulDates(): array
        {
            return [
                ValidAprilFools::class
            ];
        }
    }

    $this->usefulDate->addExtension(MyExtension::class);
    $dates = $this->usefulDate->getNextUsefulDates();

    expect(count($dates))->toEqual(1)
        ->and($dates[0]->usefulDate())->toEqual(Carbon::create('2025-04-01'))
        ->and($dates[0]->name)->toEqual('April Fools\' Day');

});
