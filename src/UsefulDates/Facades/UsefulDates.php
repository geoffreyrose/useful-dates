<?php

namespace UsefulDates\Facades;

class UsefulDates extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return \UsefulDates\UsefulDates::class;
    }
}
