<?php

namespace UsefulDates\Exceptions;

class InvalidUsefulDateException extends \RuntimeException
{
    /**
     * Create a new InvalidUsefulDateException.
     *
     * Thrown when a provided class does not extend UsefulDateAbstract.
     */
    public function __construct()
    {
        parent::__construct("Useful Dates must extend \UsefulDates\Abstracts\UsefulDateAbstract");
    }
}
