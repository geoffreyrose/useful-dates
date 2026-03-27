<?php

namespace UsefulDates\Facades;

use Illuminate\Support\Facades\Facade;

class UsefulDates extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \UsefulDates\UsefulDates::class;
    }
}
