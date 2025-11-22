<?php

namespace UsefulDates;

use Carbon\Carbon;
use DateTime;
use Throwable;
use UsefulDates\Exceptions\InvalidDateException;
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

    private function getTopParentClass(string $className): ?string
    {
        try {
            $currentClass = $className;
            $topParent = null;

            while ($parent = get_parent_class($currentClass)) {
                $topParent = $parent;
                $currentClass = $parent;
            }

            return $topParent;
        } catch (Throwable) {
            return null;
        }
    }
}
