<?php

namespace UsefulDates\Traits;

trait Dates
{
    /**
     *  Returns Useful Dates in the specified years
     *
     * @param  int|null  $year  The year to get the Useful Dates in
     */
    public function getUsefulDatesByYear(?int $year = null): array
    {
        if (!$year) {
            $year = $this->date->year;
        }

        return $this->usefulDates($year);
    }
}
