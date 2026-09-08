<?php

namespace TestMonitor\Accountable\Test;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use TestMonitor\Accountable\AccountableColumns;
use TestMonitor\Accountable\AccountableServiceProvider;
use TestMonitor\Accountable\Test\Models\User;

abstract class TestCase extends OrchestraTestCase
{
    protected $users;

    protected function getPackageProviders($app)
    {
        return [
            AccountableServiceProvider::class,
        ];
    }

    /**
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => User::class,
        ]);
    }

    protected function setUpDatabase($withSoftDeletes = false)
    {
        $builder = $this->app['db']->connection()->getSchemaBuilder();

        $builder->create('records', function (Blueprint $table) use ($withSoftDeletes) {
            $table->increments('id');
            $table->string('name')->default('');

            AccountableColumns::add($table, $withSoftDeletes);

            if ($withSoftDeletes) {
                $table->softDeletes();
            }
        });

        $builder->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');

            $table->softDeletes();
        });

        $this->seedUsers();
    }

    protected function setUpDatabaseWithSoftDeletes()
    {
        $this->setUpDatabase(true);
    }

    protected function seedUsers($amount = 5)
    {
        collect(range(1, $amount))->each(function ($index) {
            User::create(['name' => "User {$index}"]);
        });
    }
}
