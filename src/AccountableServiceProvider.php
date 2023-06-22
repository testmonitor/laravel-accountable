<?php

namespace TestMonitor\Accountable;

use Illuminate\Support\ServiceProvider;

class AccountableServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $this->publishes([
            dirname(__DIR__) . '/config/accountable.php' => config_path('accountable.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../config/accountable.php', 'accountable');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton(AccountableSettings::class);
    }
}
