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
use UsefulDates\Traits\Extensions;
use UsefulDates\Traits\Info;
use UsefulDates\Traits\Intervals;

class UsefulDates
{
    use BusinessDays;
    use Dates;
    use Extensions;
    use Info;
    use Intervals;

    private array $usefulDates = [];

    private array $customMethods = [];

    public Carbon $date {
        get => $this->date;
    }

    public function __construct()
    {
        //
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

        $date = new $date;
        $date->setCurrentDate($this->date);
        $this->usefulDates[] = $date;

        return $this;
    }
}
