<?php

namespace UsefulDates;

use Carbon\Carbon;
use DateTime;
use Throwable;
use UsefulDates\Abstracts\UsefulDateAbstract;
use UsefulDates\Enums\RepeatFrequency;
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
        if ($this->getTopParentClass($date::class) !== UsefulDateAbstract::class) {
            throw new InvalidUsefulDateException;
        }

        $date = new $date;
        $date->setCurrentDate($this->date);
        $this->usefulDates[] = $date;

        return $this;
    }

    public function addDate(string $name, Carbon $date, RepeatFrequency $repeatFrequency = RepeatFrequency::YEARLY, int $startYear = 1): self
    {
        $class = new class($name, $date, $repeatFrequency, $startYear) extends \UsefulDates\Abstracts\UsefulDateAbstract
        {
            public function __construct($name, $date, $repeatFrequency, $startYear)
            {
                $this->name = $name;
                $this->is_repeated = true;
                $this->repeat_frequency = $repeatFrequency;
                $this->start_date = Carbon::createFromFormat('Y-m-d', "{$startYear}-{$date->month}-{$date->day}");
            }

            public function date(): Carbon
            {
                return Carbon::createFromFormat('Y-m-d', "{$this->currentDate->year}-{$this->start_date->month}-{$this->start_date->day}");
            }
        };

        $class->setCurrentDate($this->date);
        $this->usefulDates[] = $class;

        return $this;
    }

    private function getTopParentClass(string $className): string
    {
        $currentClass = $className;
        $topParent = null;

        while ($parent = get_parent_class($currentClass)) {
            $topParent = $parent;
            $currentClass = $parent;
        }

        return $topParent;
    }
}
