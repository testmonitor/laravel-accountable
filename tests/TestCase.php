<?php

namespace ByTestGear\Accountable\Test;

use ByTestGear\Accountable\Accountable;
use Illuminate\Database\Schema\Blueprint;
use ByTestGear\Accountable\Test\Models\User;
use ByTestGear\Accountable\AccountableServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

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
     * @param \Illuminate\Foundation\Application $app
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
            'model' => 'ByTestGear\Accountable\Test\Models\User',
        ]);
    }

    protected function setUpDatabase($withSoftDeletes = false)
    {
        $builder = $this->app['db']->connection()->getSchemaBuilder();

        $builder->create('records', function (Blueprint $table) use ($withSoftDeletes) {
            $table->increments('id');
            $table->string('name')->default('');

            Accountable::columns($table, $withSoftDeletes); // without SoftDeletes

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
