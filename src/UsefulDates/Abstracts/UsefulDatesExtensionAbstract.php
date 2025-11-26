<?php

namespace UsefulDates\Abstracts;

use UsefulDates\UsefulDates;

abstract class UsefulDatesExtensionAbstract
{
    public static string $name = 'DefaultName';

    public static string $description = 'DefaultDescription';

    public static bool $hasMethods = false;

    /**
     * Construct the extension with a UsefulDates instance.
     *
     * @param  UsefulDates  $usefulDates  The UsefulDates context this extension augments.
     */
    public function __construct(public UsefulDates $usefulDates) {}

    /**
     * Provide a list of useful-date class names to be registered by this extension.
     *
     * @return array<int, class-string> Fully-qualified class names of UsefulDate implementations.
     */
    public static function usefulDates(): array
    {
        return [];
    }

    /**
     * Return an array of custom methods as callables.
     *
     * Keys are method names; values are callables to be invoked when the method is called on UsefulDates.
     *
     * @return array<string, callable> Map of method names to callables.
     */
    public function methods(): array
    {
        return [];
    }
}
