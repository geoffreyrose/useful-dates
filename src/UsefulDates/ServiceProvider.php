<?php

namespace UsefulDates;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton(UsefulDates::class, function ($app) {
            return new UsefulDates;
        });

        $this->app->alias(UsefulDates::class, 'usefuldates');
    }
}
