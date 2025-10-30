<?php

namespace UsefulDates\Abstracts;

abstract class ExtensionAbstract
{
    public static string $name = 'DefaultName';

    public static string $description = 'DefaultDescription';

    /**
     * Initialize the extension.
     */
    public function init(): self
    {
        return $this;
    }
}
