<?php

use UsefulDates\Abstracts\UsefulDatesExtensionAbstract;
use UsefulDates\UsefulDates;

it('uses default usefulDates implementation returning empty array', function (): void {
    // Create an extension that doesn't override usefulDates()
    $extensionClass = new class(new UsefulDates) extends UsefulDatesExtensionAbstract
    {
        public static string $name = 'Test Extension';

        public static string $description = 'Test Description';
        // usefulDates() not overridden - will use default returning []
    };

    // Call the static method
    $dates = $extensionClass::usefulDates();

    expect($dates)->toEqual([]);
});

it('uses default methods implementation returning empty array', function (): void {
    // Create an extension that doesn't override methods()
    $extension = new class(new UsefulDates) extends UsefulDatesExtensionAbstract
    {
        public static string $name = 'Test Extension';

        public static string $description = 'Test Description';
        // methods() not overridden - will use default returning []
    };

    // Call the instance method
    $methods = $extension->methods();

    expect($methods)->toEqual([]);
});
