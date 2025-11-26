<?php

namespace UsefulDates\Traits;

use Carbon\Carbon;
use UsefulDates\Abstracts\UsefulDateAbstract;
use UsefulDates\Enums\RepeatFrequency;
use UsefulDates\Exceptions\InvalidUsefulDateException;

trait Dates
{
    /**
     * @throws InvalidUsefulDateException
     */
    public function add($date): self
    {
        if ($this->getTopParentClass($date) !== UsefulDateAbstract::class) {
            throw new InvalidUsefulDateException;
        }

        $date = new $date;
        $date->setCurrentDate($this->date);
        $date->setCurrentUsefulDate($this->date);
        $this->usefulDates[] = $date;

        return $this;
    }

    public function addDate(string $name, Carbon $date, bool $isRepeated = true, RepeatFrequency $repeatFrequency = RepeatFrequency::YEARLY, int $startYear = 1): self
    {
        $class = new class($name, $date, $isRepeated, $repeatFrequency, $startYear) extends \UsefulDates\Abstracts\UsefulDateAbstract
        {
            public function __construct($name, $date, $isRepeated, $repeatFrequency, $startYear)
            {
                $this->name = $name;
                $this->is_repeated = $isRepeated;
                $this->repeat_frequency = $repeatFrequency;
                $this->start_date = Carbon::createFromFormat('Y-m-d H:i:s', "{$startYear}-{$date->month}-{$date->day} 00:00:00");
            }

            public function date(): Carbon
            {
                return Carbon::createFromFormat('Y-m-d H:i:s', "{$this->currentDate->year}-{$this->start_date->month}-{$this->start_date->day} 00:00:00");
            }
        };

        $class->setCurrentDate($this->date);
        $class->setCurrentUsefulDate($this->date);
        $this->usefulDates[] = $class;

        return $this;
    }
}
