<div style="text-align: center;"> 

[![Latest Stable Version](https://img.shields.io/packagist/v/geoffreyrose/useful-dates?style=flat-square)](https://packagist.org/packages/geoffreyrose/useful-dates)
[![License](https://img.shields.io/github/license/geoffreyrose/useful-dates?style=flat-square)](https://github.com/geoffreyrose/useful-dates/blob/main/LICENSE)
</div>

# Useful Dates - PHP + Laravel Facade

**Work in progress, not ready for production. It may never be, just something I'm experimenting with.**

A simple and elegant PHP library for working with commonly used or desired dates in your applications. UsefulDates helps you quickly identify and retrieve important dates like holidays, birthdays, paydays, and other significant calendar events.

This is an evolution of my other package [US Holidays](https://github.com/geoffreyrose/us-holidays).

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
        $this->start_date = Carbon::create(1582, 4, 1, 0, 0, 0);;
        $this->is_repeated = true;
        $this->repeat_frequency = RepeatFrequency::YEARLY;
    }

    public function date(): Carbon
    {
        return Carbon::create($this->currentDate->year, 4, 1, 0, 0, 0);
    }
}
```

2. Use the `add` method to add the new class to the `UsefulDates` instance.

```php
$usefulDates = new UsefulDates\UsefulDates;
$usefulDates->setDate(\Carbon\Carbon::now());
$usefulDates->add(App\Dates\AprilFools::class);

$usefulDates = $usefulDates->getNextUsefulDates(5);
// this will return the next 5 Carbon dates of April Fools' Day since that is the only UsefulDate added.
```

Methods on `UsefulDaresAbstract``
* daysAway() // a positive or negative integer from the current date set



## Extensions

### Add an Extension

Extensions can be as simple as a group of UseFulDates. They can also add new methods that UsefulDates can use.

```php
$usefulDates = new UsefulDates\UsefulDates;
$usefulDates->setDate(\Carbon\Carbon::now());
$usefulDates->addExtension(\App\Extensions\UsHolidays:class);
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

use App\UsHolidays\UsHolidaysExtension
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
if($dates->isDecember()) echo "It's December!";
```

### Linting

```
./vendor/bin/pint   
```

### Testing

```
./vendor/bin/pest 
 
./vendor/bin/pest --coverage
 
herd coverage ./vendor/bin/pest --coverage

```
