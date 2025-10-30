<?php

namespace UsefulDates;

use Carbon\Carbon;
use DateTime;
use Throwable;
use UsefulDates\Abstracts\UsefulDateAbstract;
use UsefulDates\Exceptions\InvalidDateException;
use UsefulDates\Exceptions\InvalidUsefulDateException;
use UsefulDates\Traits\BusinessDays;
use UsefulDates\Traits\Dates;
use UsefulDates\Traits\Info;
use UsefulDates\Traits\Intervals;

class UsefulDates
{
    use BusinessDays;
    use Dates;
    use Info;
    use Intervals;

    private array $usefulDates = [];

    public Carbon $date {
        get => $this->date;
    }

    /**
     * @throws InvalidDateException
     */
    public function setDate(Carbon|DateTime|string $date): self
    {
        if ($date instanceof Carbon) {
            $this->date = $date;
        } elseif ($date instanceof DateTime) {
            $this->date = Carbon::create($date);
        } else {
            try {
                $this->date = Carbon::create($date);
            } catch (Throwable) {
                throw new InvalidDateException($date);
            }
        }

        return $this;
    }

    /**
     * @throws InvalidUsefulDateException
     */
    public function add($date): self
    {
        if (get_parent_class($date) !== UsefulDateAbstract::class) {
            throw new InvalidUsefulDateException;
        }

        $date->setCurrentDate($this->date);
        $this->usefulDates[] = $date;

        return $this;
    }

    public function usefulDates($year = null): array
    {
        if (!$year) {
            $year = $this->date->year;
        }

        $usefulDates = [];
        foreach ($this->usefulDates as $usefulDate) {
            $usefulDate->setCurrentDate($this->date->copy()->year($year)->startOfYear());
            $usefulDates[] = $usefulDate;
        }

        return array_values($usefulDates);
    }
}
