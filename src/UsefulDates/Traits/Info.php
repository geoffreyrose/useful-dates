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

        foreach ($this->usefulDates as $usefulDate) {
            $usefulDate->setCurrentDate($date);
            if ($usefulDate->usefulDate()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the UsefulDate(s), if any, for the given date
     */
    public function getUsefulDate(?Carbon $date = null, ?array $filters = null): array
    {
        if (!$date) {
            $date = $this->date;
        }
        $usefulDates = [];
        $copy = $date->copy();

        $filteredDates = [];

        if (is_array($filters)) {
            foreach ($this->usefulDates as $usefulDate) {
                foreach ($filters as $filter) {
                    if (!is_array($filter) || !isset($filter['property'], $filter['operator'], $filter['value'])) {
                        continue;
                    }

                    if (!property_exists($usefulDate, $filter['property'])) {
                        continue 2;
                    }

                    switch ($filter['operator']) {
                        case '>':
                            if ($usefulDate->{$filter['property']} > $filter['value']) {
                                break;
                            } else {
                                continue 3;
                            }
                        case '<':
                            if ($usefulDate->{$filter['property']} < $filter['value']) {
                                break;
                            } else {
                                continue 3;
                            }
                        case '>=':
                            if ($usefulDate->{$filter['property']} >= $filter['value']) {
                                break;
                            } else {
                                continue 3;
                            }
                        case '<=':
                            if ($usefulDate->{$filter['property']} <= $filter['value']) {
                                break;
                            } else {
                                continue 3;
                            }
                        case '=':
                            if ($usefulDate->{$filter['property']} === $filter['value']) {
                                break;
                            } else {
                                continue 3;
                            }
                        case '!=':
                            if ($usefulDate->{$filter['property']} !== $filter['value']) {
                                break;
                            } else {
                                continue 3;
                            }
                        default:
                            break;
                    }
                }

                $filteredDates[] = $usefulDate;
            }
        } else {
            $filteredDates = $this->usefulDates;
        }

        foreach ($filteredDates as $usefulDate) {
            $usefulDate->setCurrentDate($copy);
            if ($usefulDate->usefulDate()) {
                $usefulDates[] = clone $usefulDate;
            }
        }

        return $usefulDates;
    }
}
