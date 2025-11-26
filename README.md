<div style="text-align: center;"> 

[![Latest Stable Version](https://img.shields.io/packagist/v/geoffreyrose/useful-dates?style=flat-square)](https://packagist.org/packages/geoffreyrose/useful-dates)
[![License](https://img.shields.io/github/license/geoffreyrose/useful-dates?style=flat-square)](https://github.com/geoffreyrose/useful-dates/blob/main/LICENSE)
</div>

# Useful Dates - PHP + Laravel Facade

**Work in progress, not ready for production. It may never be, just something I'm experimenting with.**

A simple and elegant PHP library for working with commonly used or desired dates in your applications. UsefulDates helps you quickly identify and retrieve important dates like holidays, birthdays, paydays, and other significant calendar events.

This is an evolution of my other package [geoffreyrose/us-holidays](https://github.com/geoffreyrose/us-holidays).

You can add your own dates that can have as simple or complex logic as you want.

With useful days you can also create extensions that contain your country-specific holidays, family and friends birthdays, or anything else you want.

On its own UsefulDates comes with no dates.  It is a clean base that you can extend with your own dates and methods to calculate dates on demand to show on a page or to save in a database.

### Requirements
* PHP 8.4+
* [Carbon](http://carbon.nesbot.com/)

### Usage

#### Installation
```
composer require geoffreyrose/useful-dates
```

### Basic Usage
```php
use UsefulDates\UsefulDates;

...

$usefulDates = new UsefulDates;
$usefulDates = $usefulDates->setDate(\Carbon\Carbon::now());

$usefulDates = $usefulDates->getNextUsefulDates(5);
```

### With Laravel Facade
Laravel uses Package Auto-Discovery, which doesn't require you to manually add the ServiceProvider and Facade.
```php
$usefulDates = UsefulDates::setDate(\Carbon\Carbon::now());
```


## Add a Useful Date

**Important**: UsefulDates `date()` should always use UTC time.

1. Create a new class that extends [`\UsefulDates\Abstracts\UsefulDateAbstract`](https://github.com/geoffreyrose/useful-dates/blob/main/src/UsefulDates/Abstracts/UsefulDateAbstract.php)

An example for April Fools' Day:
```php
<?php

namespace App;

use Carbon\Carbon;
use UsefulDates\Enums\RepeatFrequency;

class AprilFools extends \UsefulDates\Abstracts\UsefulDateAbstract
{
    public function __construct()
    {
        $this->name = "April Fools' Day";
        $this->start_date = Carbon::createFromFormat('Y-m-d', '1582-04-01');
        $this->is_repeated = true;
        $this->repeat_frequency = RepeatFrequency::YEARLY;
    }

    public function date(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', "{$this->currentDate->year}-04-01");
    }
}
```

Note: Carbon::createFromFormat() in my testing is faster than Carbon:create().

2. Use the `add` method to add the new class to the `UsefulDates` instance.

```php
$usefulDates = new UsefulDates\UsefulDates;
$usefulDates->setDate(\Carbon\Carbon::now());
$usefulDates->add(App\Dates\AprilFools::class);

$usefulDates = $usefulDates->getNextUsefulDates(5);
// this will return the next 5 Carbon dates of April Fools' Day since that is the only UsefulDate added.
```

## Methods

## Core

#### setDate(Carbon|DateTime|string $date): self
Set the working date/time context (normalized to UTC). Accepts Carbon, DateTime, or a string parsable by Carbon.

```php
use UsefulDates\UsefulDates;
use Carbon\Carbon;

$ud = new UsefulDates();
$usefulDates->setDate(Carbon::now());

// Strings and DateTime also work
$usefulDates->setDate('2025-02-01');
$usefulDates->setDate(new \DateTime('2025-03-01 08:00'));
```

#### add(string $dateClass): self
Register a UsefulDate by class name. The class must extend UsefulDateAbstract.

```php
$usefulDates->add(\App\Dates\AprilFools::class);
```


#### addDate(string $name, Carbon $date, bool $isRepeated = true, RepeatFrequency $repeatFrequency = RepeatFrequency::YEARLY, int $startYear = 1): self
Add a simple UsefulDate without creating a class. Creates an internal definition that repeats according to the provided options.

```php
$usefulDates->addDate(name: "Patrick Star's Birthday", date: \Carbon\Carbon::createFromFormat('Y-m-d', '1999-08-17'), startYear: 1999);
```

#### addExtension(string $extensionClass, mixed $options = null): self
Register an extension (must extend UsefulDatesExtensionAbstract). Adds its useful dates and any custom methods exposed by the extension.

```php
$usefulDates->addExtension(\UsefulDatesUsHolidays\UsHolidaysExtension::class);
```

#### isUsefulDate(?Carbon $date = null): bool
Returns true if any registered UsefulDate matches the given date (defaults to the current context date).

```php
$usefulDates->setDate(Carbon::now());
if ($usefulDates->isUsefulDate()) {
    // today matches a useful date
}

$someDate = Carbon::create(2025, 12, 25);
$isUseful = $usefulDates->isUsefulDate($someDate);
```

#### getUsefulDate(?Carbon $date = null, ?array $filters = null): array
Returns a list of matching UsefulDate objects for the given date (defaults to the current context date). See the Filtering section for filter format.

```php
$dates = $usefulDates->getUsefulDate(Carbon::create(2025, 1, 1));
foreach ($dates as $d) {
    echo $d->name . ' occurs on ' . $d->usefulDate();
}
```

Note: See the Filtering section below for the filter array shape and operators.

## Ranges and Navigation

#### getUsefulDatesInDays(int $days, ?Carbon $startDate = null, ?array $filters = null): array
All matching UsefulDates occurring from $startDate (default: current context) through startDate + $days

```php
// Next 30 days from current context
$usefulDates->setDate(Carbon::now());
$next30 = $usefulDates->getUsefulDatesInDays(30);

// From a specific start date with filters
$start = Carbon::create(2025, 1, 1);
$federal = $usefulDates->getUsefulDatesInDays(60, $start);
```

#### getUsefulDatesInYears(int $years, ?array $filters = null): array
Convenience wrapper around getUsefulDatesInDays() for a number of years ahead from the current context date.

```php
$usefulDates->setDate(Carbon::now());
$twoYears = $usefulDates->getUsefulDatesInYears(2);
```

#### getNextUsefulDates(int $number = 1, ?array $filters = null): array
The next N useful dates after the current context date.

```php
$usefulDates->setDate(Carbon::now());
$next5 = $usefulDates->getNextUsefulDates(5);
```

#### getPreviousUsefulDates(int $number = 1, ?array $filters = null): array
The previous N useful dates before the current context date.

```php
$prev3 = $usefulDates->getPreviousUsefulDates(3);
```

#### getUsefulDatesByYear(?int $year = null, ?array $filters = null): array
All useful dates within a given calendar year (defaults to current context year).

```php
$byYear = $usefulDates->getUsefulDatesByYear(2025);
```

## Business Day Helpers

Default Business days are Monday–Friday (1,2,3,4,5)

Uses Carbon's day constants. ie `CarbonInterface::SUNDAY` (which is 0)

#### isStandardBusinessDays(): bool
True if the configured business days are Monday–Friday.

```php
$usefulDates->setBusinessDays([1,2,3,4,5]);
$standardBusinessDays = $usefulDates->isStandardBusinessDays(); // true
```

#### setBusinessDays(array $days): self
Define which days of week are business days (0=Sun..6=Sat). Throws InvalidDayException for invalid values.

```php
$usefulDates->setBusinessDays([1,2,3,4,6]); // Saturdays instead of Fridays
```

#### getBusinessDays(): array<int,int>
Get the configured business days array.

```php
$days = $usefulDates->getBusinessDays(); // [1,2,3,4,6]
```

#### isBusinessDay(?Carbon $date = null): bool
Whether the given date (or current context date) is a business day.

```php
$usefulDates->setDate(Carbon::create(2025, 5, 10)); // a Saturday
$usefulDates->setBusinessDays([1,2,3,4,5]);
$isBiz = $usefulDates->isBusinessDay(); // false
```

#### nextBusinessDay(): Carbon
The next date that is a business day after the current context date.

```php
$usefulDates->setDate(Carbon::create(2025, 5, 10)); // Saturday
echo $usefulDates->nextBusinessDay()->toDateString(); // 2025-05-12 if Mon–Fri
```

#### prevBusinessDay(): Carbon
The previous date that is a business day before the current context date.

```php
$usefulDates->setDate(Carbon::create(2025, 5, 10)); // Saturday
echo $usefulDates->prevBusinessDay()->toDateString(); // 2025-05-09 if Mon–Fri
```

#### todayOrPreviousBusinessDay(): Carbon
Today if it is a business day, otherwise the previous business day.

```php
echo $usefulDates->todayOrPreviousBusinessDay()->toDateString();
```

#### todayOrNextBusinessDay(): Carbon
Today if it is a business day, otherwise the next business day.

```php
echo $usefulDates->todayOrNextBusinessDay()->toDateString();
```

### Methods on UsefulDate definitions (UsefulDateAbstract)

These apply to each UsefulDate definition (your classes extending UsefulDateAbstract).

#### usefulDate(): ?Carbon
Return the date if it should be considered for the current context (applies repeat frequency and start/end rules), otherwise null.

#### daysAway(): int
Number of days from the current useful-date context to the `date` set on the base `Usefuldate::setDate()`. Positive=future, negative=past, 0=today.

### Example

```php
$usefulDate = new UsefulDates\UsefulDates;
$usefulDate->setDate(\Carbon\Carbon::create('2025-01-01'));
$usefulDate->addDate("Patrick Star's Birthday", \Carbon\Carbon::createFromFormat('Y-m-d', '1999-08-17'), startYear: 1999);
$usefulDate->addDate('April Fools Day', \Carbon\Carbon::createFromFormat('Y-m-d', '1582-04-01'), startYear: 1582);

$dates = $usefulDate->getUsefulDatesInYears(10);
foreach ($dates as $date) {
    echo $date->name . ' ' . $date->usefulDate() . ' ' . $date->daysAway() . '<br>';
}
```

## Extensions

### First Party Extensions

* Useful Dates US Holidays - [geoffreyrose/useful-dates-us-holidays](https://github.com/geoffreyrose/useful-dates-us-holidays)

### Add an Extension

Extensions can be as simple as a group of UseFulDates. They can also add new methods that UsefulDates can use.

```php
$usefulDates = new UsefulDates\UsefulDates;
$usefulDates->setDate(\Carbon\Carbon::now());
$usefulDates->addExtension(\UsefulDatesUsHolidays\UsHolidaysExtension::class);
```

### Create a new Extension

1. Create a new class that extends [`\UsefulDates\Abstracts\UsefulDatesExtensionAbstract`](https://github.com/geoffreyrose/useful-dates/blob/main/src/UsefulDates/Abstracts/UsefulDatesExtensionAbstract.php)

```php
<?php

namespace App\UsHolidays;

use App\UsHolidays\Holidays\AprilFools;
use UsefulDates\Abstracts\UsefulDatesExtensionAbstract;

class UsHolidays extends UsefulDatesExtensionAbstract
{
    public static string $name = 'US Holidays';

    public static string $description = 'US Holidays';
    
    public static bool $hasMethods = true;

    /*
     * Every Useful date in the returned array must extend \UsefulDates\Abstracts\UsefulDateAbstract
    */
    public static function usefulDates(): array
    {
        return [
            AprilFools::class
        ];
    }
    
    public function methods(): array
    {
        return [
            'isDecember' => [$this, 'isDecember']
        ];
    }

    public function isDecember(): bool
    {
         return $this->usefulDates->date->month === 12;
    }
}
```

```php
$date = new UsefulDates\UsefulDates; 
$date->setDate(\Carbon\Carbon::now()->addMonths(2));
$date->addExtension(\App\UsHolidays\UsHolidays::class);
if($date->isDecember()) echo "It's December!";
```

### For Better IDE Support

For better IDE support when using extension, create your own class that extends `UsefulDates`. This lets you add your IDE see the methods and properties of the extension. But other than that, using UsefulDates is the same.

This would also give you an easy way to add your own methods to UsefulDates too, without needing to create an extension.

Extension would be useful when you want to add dates and methods that someone else might have created or when you need to use the same dates/methods in multiple configurations.

```php
<?php

namespace App;

use UsefulDatesUsHolidays\UsHolidaysExtension;
use Carbon\Carbon;

/**
 * @mixin UsHolidaysExtension
 */
class MyUsefulDates extends \UsefulDates\UsefulDates
{
    public function init(Carbon $date): self
    {
        $this->setDate($date);
        $this->addExtension(UsHolidaysExtension::class);

        return $this;
    }
}
```

```php
$dates = new \App\MyUsefulDates;
$dates->init(\Carbon\Carbon::now()->addMonths(2));
```


## Filtering


```php
$usefulDates = new UsefulDates\UsefulDates;
$usefulDates->setDate(\Carbon\Carbon::now());
$usefulDates->addExtension(\UsefulDatesUsHolidays\UsHolidaysExtension::class);

$federalHolidays = $usefulDate->getUsefulDatesInDays(100, filters: [
    [
        'property' =>'is_federal_holiday',
        'operator' => '=',
        'value' => true
    ],
    [
        'property' =>'federal_holiday_start_year',
        'operator' => '<=',
        'value' => 1900
    ],
]);
```

Filter arrays must contain the following properties:
* property
* operator
  * valid operators: `>`, `<`, `>=`, `<=`, `=`, `!=`
* value

### Linting

```
./vendor/bin/pint   
```

### Testing

```
./vendor/bin/pest 
 
./vendor/bin/pest --coverage-html coverage
 
herd coverage ./vendor/bin/pest --coverage-html coverage
```
