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
    public function getUsefulDate(?Carbon $date = null, ?array $filters = null): array
    {
        if (!$date) {
            $date = $this->date;
        }
        $usefulDates = [];
        $copy = $date->copy();

        foreach ($this->usefulDates as $usefulDate) {
            if (is_array($filters)) {
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
            }

            $usefulDate->setCurrentDate($copy);
            if ($usefulDate->usefulDate()) {
                $usefulDates[] = clone $usefulDate;
            }
        }

        return $usefulDates;
    }
}
