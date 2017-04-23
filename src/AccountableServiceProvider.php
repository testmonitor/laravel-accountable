<?php

namespace ByTestGear\Accountable;

class AccountableServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $this->publishes([
            dirname(__DIR__).'/config/accountable.php' => config_path('accountable.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../config/accountable.php', 'accountable');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        //
    }

    /**
     * Returns the current used, based on the configured authentication driver.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public static function accountableUser()
    {
        $authDriver = config('accountable.auth_driver') ?? auth()->getDefaultDriver();

        return auth($authDriver)->user();
    }
}
