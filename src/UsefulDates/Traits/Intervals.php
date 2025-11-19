<?php

namespace UsefulDates\Traits;

use Carbon\Carbon;

trait Intervals
{
    /**
     * Returns Useful Dates in the next number of days
     *
     * @param  int  $days  The number of days to look ahead to find Useful Dates in
     */
    public function getUsefulDatesInDays(int $days, ?Carbon $startDate = null): array
    {
        $usefulDates = [];
        $currentDate = $startDate?->copy() ?: $this->date->copy();
        for ($i = 0; $i <= $days; $i++) {
            foreach ($this->getUsefulDate($currentDate) as $usefulDate) {
                $usefulDates[] = $usefulDate;
            }

            $currentDate->addDay();
        }

        return array_values($usefulDates);
    }

    /**
     * Returns Useful Dates in the next amount of years
     *
     * @param  int  $years  The number of years to look ahead to find Useful Dates in
     */
    public function getUsefulDatesInYears(int $years): array
    {
        return $this->getUsefulDatesInDays(ceil($this->date->diffInDays($this->date->copy()->addYears($years))));
    }

    /**
     * Return next Useful Date(s)
     *
     * @param  int  $number  the number of Useful Dates to get. Default is 1
     */
    public function getNextUsefulDates(int $number = 1): array
    {
        $usefulDates = [];
        $currentDate = $this->date->copy();
        while (count($usefulDates) < $number) {
            foreach ($this->getUsefulDate($currentDate) as $usefulDate) {
                $usefulDates[] = $usefulDate;
            }
            $currentDate->addDay();
        }

        return array_values($usefulDates);
    }

    /**
     * Return previous Useful Date(s)
     *
     * @param  int  $number  the number of Useful Dates to get. Default is 1
     */
    public function getPreviousUsefulDates(int $number = 1): array
    {
        $usefulDates = [];
        $currentDate = $this->date->copy();
        while (count($usefulDates) < $number) {
            foreach ($this->getUsefulDate($currentDate) as $usefulDate) {
                $usefulDates[] = $usefulDate;
            }
            $currentDate->subDay();
        }

        return array_values($usefulDates);
    }

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
