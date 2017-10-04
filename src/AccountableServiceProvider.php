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
     * @return string
     */
    public static function authDriver()
    {
        return config('accountable.auth_driver') ?? auth()->getDefaultDriver();
    }

    /**
     * Returns the current used, based on the configured authentication driver.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public static function accountableUser()
    {
        return auth()->guard(self::authDriver())->user();
    }

    /**
     * Returns the user model, based on the configured authentication driver.
     *
     * @return string
     */
    public static function userModel()
    {
        $guard = self::authDriver();

        return collect(config('auth.guards'))
            ->map(function ($guard) {
                return config("auth.providers.{$guard['provider']}.model");
            })->get($guard);
    }
}
