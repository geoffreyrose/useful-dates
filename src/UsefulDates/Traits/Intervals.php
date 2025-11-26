<?php

namespace UsefulDates\Traits;

use Carbon\Carbon;
use UsefulDates\Enums\RepeatFrequency;

trait Intervals
{
    /**
     * Return useful dates that occur within the next N days from a start date.
     *
     * @param  int  $days  The number of days to look ahead from $startDate (inclusive of the end day).
     * @param  Carbon|null  $startDate  Optional starting date; defaults to the instance context date if null.
     * @param  array<int, array{property:string, operator:string, value:mixed}>|null  $filters  Optional property filters.
     * @return array<int, object> A sorted list of matching useful-date objects (cloned instances).
     */
    public function getUsefulDatesInDays(int $days, ?Carbon $startDate = null, ?array $filters = null): array
    {
        $start = $startDate?->copy() ?: $this->date->copy();
        $end = $start->copy()->addDays($days);

        $filteredDates = $this->filterUsefulDates($filters);

        $usefulDates = [];

        foreach ($filteredDates as $filteredDate) {
            $frequency = $filteredDate->repeat_frequency ?? RepeatFrequency::NONE;

            if ($frequency === RepeatFrequency::YEARLY || $frequency === RepeatFrequency::NONE) {
                $startYear = $start->year;
                $endYear = $end->year;

                // +1 for years to pick up dates that might fall on the last day of the year but cannot be calculated until the next year
                // such as NewYearsDayObserved, which is on 2021-12-31, but cannot really calculate that date unless you look at New Years for 2022
                for ($year = $startYear; $year <= $endYear + 1; $year++) {
                    $filteredDate->setCurrentDate(Carbon::createFromFormat('Y-m-d H:i:s', "{$year}-01-01 00:00:00"));
                    $occurrenceDate = $filteredDate->date();
                    if (!$occurrenceDate) {
                        continue;
                    }

                    if ($occurrenceDate->gte($start) && $occurrenceDate->lte($end)) {
                        $filteredDate->setCurrentDate($occurrenceDate->copy());
                        if ($filteredDate->usefulDate()) {
                            $usefulDates[] = clone $filteredDate;
                        }
                    }
                }
            } elseif ($frequency === RepeatFrequency::MONTHLY) {
                $cursorDate = Carbon::createFromFormat('Y-m-d H:i:s', "{$start->year}-{$start->month}-01 00:00:00");
                while ($cursorDate->lte($end)) {
                    $filteredDate->setCurrentDate($cursorDate);
                    $occurrenceDate = $filteredDate->date();
                    if ($occurrenceDate) {
                        if ($occurrenceDate->gte($start) && $occurrenceDate->lte($end)) {
                            $filteredDate->setCurrentDate($occurrenceDate->copy());
                            if ($filteredDate->usefulDate()) {
                                $usefulDates[] = clone $filteredDate;
                            }
                        }
                    }
                    $cursorDate->addMonthNoOverflow();
                }
            } else {
                $cursorDate = $start->copy();
                while ($cursorDate->lte($end)) {
                    $filteredDate->setCurrentDate($cursorDate->copy());
                    if ($filteredDate->usefulDate()) {
                        $usefulDates[] = clone $filteredDate;
                    }
                    $cursorDate->addDay();
                }
            }
        }

        usort($usefulDates, function ($a, $b) {
            $aDate = $a->usefulDate();
            $bDate = $b->usefulDate();
            if ($aDate == $bDate) {
                return 0;
            }

            return $aDate <=> $bDate;
        });

        return $usefulDates;
    }

    /**
     * Return useful dates that occur within the next N years from the current context date.
     *
     * @param  int  $years  The number of years to look ahead.
     * @param  array<int, array{property:string, operator:string, value:mixed}>|null  $filters  Optional property filters.
     * @return array<int, object> A sorted list of matching useful-date objects (cloned instances).
     */
    public function getUsefulDatesInYears(int $years, ?array $filters = null): array
    {
        $days = (int) ceil($this->date->diffInDays($this->date->copy()->addYears($years)));

        return $this->getUsefulDatesInDays($days, filters: $filters);
    }

    /**
     * Get the next N useful dates going forward from the current context date.
     *
     * @param  int  $number  The number of useful dates to return (default 1).
     * @param  array<int, array{property:string, operator:string, value:mixed}>|null  $filters  Optional property filters.
     * @return array<int, object> A list of the next matching useful-date objects (cloned instances).
     */
    public function getNextUsefulDates(int $number = 1, ?array $filters = null): array
    {
        $usefulDates = [];
        $currentDate = $this->date->copy()->addDay();
        while (count($usefulDates) < $number) {
            foreach ($this->getUsefulDate($currentDate, $filters) as $usefulDate) {
                $usefulDates[] = $usefulDate;
            }
            $currentDate->addDay();
        }

        return $usefulDates;
    }

    /**
     * Get the previous N useful dates going backward from the current context date.
     *
     * @param  int  $number  The number of useful dates to return (default 1).
     * @param  array<int, array{property:string, operator:string, value:mixed}>|null  $filters  Optional property filters.
     * @return array<int, object> A list of the previous matching useful-date objects (cloned instances), ordered nearest-first.
     */
    public function getPreviousUsefulDates(int $number = 1, ?array $filters = null): array
    {
        $usefulDates = [];
        $currentDate = $this->date->copy()->subDay();
        while (count($usefulDates) < $number) {
            foreach ($this->getUsefulDate($currentDate, $filters) as $usefulDate) {
                $usefulDates[] = $usefulDate;
            }
            $currentDate->subDay();
        }

        return $usefulDates;
    }

    /**
     * Return useful dates that occur within a specific calendar year.
     *
     * If $year is null, the year from the current context date is used.
     *
     * @param  int|null  $year  The calendar year to search (defaults to current context year).
     * @param  array<int, array{property:string, operator:string, value:mixed}>|null  $filters  Optional property filters.
     * @return array<int, object> A sorted list of matching useful-date objects (cloned instances) for that year.
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
