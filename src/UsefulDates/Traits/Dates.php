<?php

namespace UsefulDates\Traits;

use Carbon\Carbon;

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

        $startOfYear = Carbon::create($year, 1, 1);

        return $this->getUsefulDatesInDays($startOfYear->daysInYear() - 1, $startOfYear);
    }
}
