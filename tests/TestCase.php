<?php

namespace TestMonitor\Accountable\Test;

use TestMonitor\Accountable\Accountable;
use Illuminate\Database\Schema\Blueprint;
use TestMonitor\Accountable\Test\Models\User;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use TestMonitor\Accountable\AccountableServiceProvider;

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
            'model' => 'TestMonitor\Accountable\Test\Models\User',
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

        $builder->create('blogs', function (Blueprint $table) use ($withSoftDeletes) {
            $table->increments('id');
            $table->string('name')->default('');

            Accountable::columns($table, $withSoftDeletes); // without SoftDeletes

            if ($withSoftDeletes) {
                $table->softDeletes();
            }
        });

        $builder->create('posts', function (Blueprint $table) use ($withSoftDeletes) {
            $table->increments('id');
            $table->string('name')->default('');
            $table->integer('blog_id')->default(null);

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
