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
    public function getUsefulDatesInDays(int $days, ?Carbon $startDate = null, ?array $filters = null): array
    {
        $usefulDates = [];
        $currentDate = $startDate?->copy() ?: $this->date->copy();
        for ($i = 0; $i <= $days; $i++) {
            foreach ($this->getUsefulDate($currentDate, $filters) as $usefulDate) {
                $usefulDates[] = $usefulDate;
            }

            $currentDate->addDay();
        }

        return $usefulDates;
    }

    /**
     * Returns an array of Useful Dates in for next number of years
     *
     * @param  int  $years  The number of years to look ahead to find Useful Dates in
     */
    public function getUsefulDatesInYears(int $years, ?array $filters = null): array
    {
        return $this->getUsefulDatesInDays(ceil($this->date->diffInDays($this->date->copy()->addYears($years))), filters: $filters);
    }

    /**
     * Return next Useful Date(s)
     *
     * @param  int  $number  the number of Useful Dates to get. Default is 1
     */
    public function getNextUsefulDates(int $number = 1, ?array $filters = null): array
    {
        $usefulDates = [];
        $currentDate = $this->date->copy();
        while (count($usefulDates) < $number) {
            foreach ($this->getUsefulDate($currentDate, $filters) as $usefulDate) {
                $usefulDates[] = $usefulDate;
            }
            $currentDate->addDay();
        }

        return $usefulDates;
    }

    /**
     * Return previous Useful Date(s)
     *
     * @param  int  $number  the number of Useful Dates to get. Default is 1
     */
    public function getPreviousUsefulDates(int $number = 1, ?array $filters = null): array
    {
        $usefulDates = [];
        $currentDate = $this->date->copy();
        while (count($usefulDates) < $number) {
            foreach ($this->getUsefulDate($currentDate, $filters) as $usefulDate) {
                $usefulDates[] = $usefulDate;
            }
            $currentDate->subDay();
        }

        return $usefulDates;
    }

    /**
     *  Returns Useful Dates in the specified years
     *
     * @param  int|null  $year  The year to get the Useful Dates in, Default is the current year of $date
     */
    public function getUsefulDatesByYear(?int $year = null, ?array $filters = null): array
    {
        if (!$year) {
            $year = $this->date->year;
        }

        $startOfYear = Carbon::createFromFormat('Y-m-d', "{$year}-01-01");

        return $this->getUsefulDatesInDays($startOfYear->daysInYear() - 1, startDate: $startOfYear, filters: $filters);
    }
}
