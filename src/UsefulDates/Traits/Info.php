<?php

namespace UsefulDates\Traits;

use Carbon\Carbon;

trait Info
{
    /**
     * Check if a date is a Useful Date. Returns boolean
     */
    public function isUsefulDate(?Carbon $date = null): bool
    {
        if (!$date) {
            $date = $this->date;
        }
        $isUsefulDate = false;

        foreach ($this->usefulDates as $usefulDate) {
            $usefulDate->setCurrentDate($date);
            if ($date->isBirthday($usefulDate->usefulDate())) {
                $isUsefulDate = true;
                break;
            }
        }

        return $isUsefulDate;
    }

    /**
     * Get the UsefulDate(s), if any, for the given date
     */
    public function getUsefulDate(?Carbon $date = null): array
    {
        if (!$date) {
            $date = $this->date;
        }
        $usefulDates = [];

        foreach ($this->usefulDates as $usefulDate) {
            $usefulDate->setCurrentDate($date->copy());
            if ($usefulDate->usefulDate()) {
                $usefulDates[] = clone $usefulDate;
            }
        }

        return $usefulDates;
    }
}
