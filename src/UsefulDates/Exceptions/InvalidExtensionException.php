<?php

namespace UsefulDates\Exceptions;

class InvalidExtensionException extends \RuntimeException
{
    /**
     * Create a new InvalidExtensionException.
     *
     * Thrown when a provided extension does not extend UsefulDatesExtensionAbstract.
     */
    public function __construct()
    {
        parent::__construct("Extension must extend '\UsefulDates\Abstracts\ExtensionAbstract'");
    }
}
