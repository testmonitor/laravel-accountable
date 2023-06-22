<?php

namespace TestMonitor\Accountable;

use Illuminate\Database\Schema\Blueprint;

class Accountable
{
    /**
     * Returns the configured authentication driver.
     *
     * @return string
     */
    public static function authDriver()
    {
        return config('accountable.auth_driver') ?? auth()->getDefaultDriver();
    }

    /**
     * Returns the current user, based on the configured authentication driver.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Model|null
     */
    public static function authenticatedUser()
    {
        return accountable()->impersonatedUser() ?? auth()->guard(self::authDriver())->user();
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
            ->map(fn ($guard) => config("auth.providers.{$guard['provider']}.model"))
            ->get($guard);
    }

    /**
     * Add accountable.column_names to the table, including indexes.
     *
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param bool $usesSoftDeletes
     */
    public static function columns(Blueprint $table, bool $usesSoftDeletes = true): void
    {
        self::addColumn($table, config('accountable.column_names.created_by'));
        self::addColumn($table, config('accountable.column_names.updated_by'));

        if ($usesSoftDeletes) {
            self::addColumn($table, config('accountable.column_names.deleted_by'));
        }
    }

    /**
     * Add a single Accountable column to the table. Also creates an index.
     *
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param string $name
     */
    public static function addColumn(Blueprint $table, string $name): void
    {
        $table->unsignedInteger($name)->nullable();
        $table->index($name);
    }
}
