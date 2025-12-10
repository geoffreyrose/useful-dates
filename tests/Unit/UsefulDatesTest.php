<?php

use Carbon\Carbon;
use UsefulDates\UsefulDates;

it('handles getTopParentClass with non-existent class gracefully', function (): void {
    // This tests the error handling path in getTopParentClass (lines 88-89)
    // by using reflection to test the private method with an invalid class name
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    // Use reflection to test the private method with an invalid class name
    $reflection = new ReflectionClass($ud);
    $method = $reflection->getMethod('getTopParentClass');
    $method->setAccessible(true);

    // Test with a non-existent class name that could cause get_parent_class to throw
    $result = $method->invoke($ud, 'NonExistentClass\\DoesNotExist');

    // Should return null when error occurs (catch block on line 88-89)
    expect($result)->toBeNull();
});

it('handles getTopParentClass with class that has no parent', function (): void {
    // Test with a class that exists but has no parent
    $ud = new UsefulDates;
    $ud->setDate(Carbon::create('2025-01-01'));

    $reflection = new ReflectionClass($ud);
    $method = $reflection->getMethod('getTopParentClass');
    $method->setAccessible(true);

    // stdClass has no parent
    $result = $method->invoke($ud, 'stdClass');

    // Should return null when no parent exists
    expect($result)->toBeNull();
});
