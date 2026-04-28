<?php

namespace UsefulDates\Exceptions;

class BusinessDaysNotConfiguredException extends \RuntimeException
{
    /**
     * Create a new BusinessDaysNotConfiguredException.
     */
    public function __construct()
    {
        parent::__construct('No business days configured');
    }
}
