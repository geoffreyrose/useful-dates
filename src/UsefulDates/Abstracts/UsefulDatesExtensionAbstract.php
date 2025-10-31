<?php

namespace UsefulDates\Abstracts;

use UsefulDates\UsefulDates;

abstract class UsefulDatesExtensionAbstract
{
    public static string $name = 'DefaultName';

    public static string $description = 'DefaultDescription';

    public static bool $hasMethods = false;

    public function __construct(public UsefulDates $usefulDates) {}

    public static function usefulDates(): array
    {
        return [];
    }

    /**
     * Return an array of custom methods as callables.
     * Key is the method name, value is the callable.
     */
    public function methods(): array
    {
        return [];
    }
}
