<?php

namespace UsefulDates\Traits;

use Carbon\Carbon;

trait Info
{
    /**
     * Determine whether the provided date is a "useful date" according to the registered rules.
     *
     * If no date is provided, the instance's current date context is used.
     *
     * @param  Carbon|null  $date  Optional date to check. Defaults to the current context date.
     * @return bool True if at least one registered useful date matches the given date; false otherwise.
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
     * Get the UsefulDate item(s), if any, for the provided date.
     *
     * If no date is supplied, the current context date is used.
     *
     * @param  Carbon|null  $date  Optional date to evaluate against. Defaults to current context date.
     * @param  array<int, array{property:string, operator:string, value:mixed}>|null  $filters  Optional property filters.
     * @return array<int, object> A list of matching useful-date objects (cloned instances).
     */
    public function getUsefulDate(?Carbon $date = null, ?array $filters = null): array
    {
        if (!$date) {
            $date = $this->date;
        }
        $usefulDates = [];
        $copy = $date->copy();

        $filteredDates = $this->filterUsefulDates($filters);

        foreach ($filteredDates as $usefulDate) {
            $usefulDate->setCurrentDate($copy);
            if ($usefulDate->usefulDate()) {
                $usefulDates[] = clone $usefulDate;
            }
        }

        return $usefulDates;
    }

    /**
     * Centralized filtering logic for useful dates.
     *
     * Filters are arrays of the form [property, operator, value]. Supported
     * operators are: >, <, >=, <=, =, !=
     *
     * @param  array<int, array{property:string, operator:string, value:mixed}>|null  $filters  Optional property filters.
     * @return array<int, object> The list of useful-date definitions matching the filters (original instances).
     */
    private function filterUsefulDates(?array $filters): array
    {
        if (!is_array($filters)) {
            return $this->usefulDates;
        }

        $filteredDates = [];
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

        return $filteredDates;
    }
}
