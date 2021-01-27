<?php

namespace TestMonitor\Accountable\Test;

use Illuminate\Database\Eloquent\SoftDeletes;
use TestMonitor\Accountable\Test\Models\User;
use TestMonitor\Accountable\Test\Models\Record;
use TestMonitor\Accountable\Traits\Accountable;
use TestMonitor\Accountable\AccountableSettings;
use TestMonitor\Accountable\Test\Models\SoftDeletableUser;

class SaveDeletedByUserTest extends TestCase
{
    /**
     * @var \TestMonitor\Accountable\Test\Models\Record
     */
    protected $record;

    /**
     * @var AccountableSettings
     */
    protected $config;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabaseWithSoftDeletes();

        $this->record = new class() extends Record {
            use Accountable, SoftDeletes;
        };

        $this->config = app()->make(AccountableSettings::class);
    }

    /** @test */
    public function it_will_save_the_user_that_deleted_a_record()
    {
        $this->actingAs(User::all()->first());

        $record = new $this->record();
        $record->save();

        $record->delete();

        $this->assertEquals($record->deleted_by_user_id, User::first()->id);
        $this->assertEquals($record->deletedBy->name, User::first()->name);
        $this->assertInstanceOf(get_class(User::first()), $record->deletedBy);
    }

    /** @test */
    public function it_will_save_the_impersonator_that_deleted_a_record()
    {
        $impersonator = User::create(['name' => 'Impersonator']);
        accountable()->actingAs($impersonator);

        $record = new $this->record();
        $record->save();

        $record->delete();

        $this->assertEquals($record->deleted_by_user_id, $impersonator->id);
        $this->assertEquals($record->deletedBy->name, $impersonator->name);
        $this->assertInstanceOf(get_class($impersonator), $record->deletedBy);
    }

    /** @test */
    public function it_will_not_save_the_anonymous_user_that_deleted_a_record()
    {
        $record = new $this->record();
        $record->save();

        $record->delete();

        $this->assertNull($record->deleted_by_user_id);
        $this->assertNull($record->deletedBy);
    }

    /** @test */
    public function it_will_return_a_fall_back_user_when_someone_anonymous_deleted_a_record()
    {
        $record = new $this->record();
        $record->save();

        $record->delete();

        $anonymous = ['name' => 'Neville the Fat Hamster'];

        $this->config->setAnonymousUser($anonymous);

        $this->assertNull($record->deleted_by_user_id);
        $this->assertInstanceOf(User::class, $record->deletedBy);
        $this->assertEquals($anonymous['name'], $record->deletedBy->name);
    }

    /** @test */
    public function it_will_save_a_specified_user_as_deleter_when_disabling_accountable()
    {
        $this->config->disable();

        $this->actingAs(User::all()->first());

        $record = new $this->record();
        $record->save();

        $record->deleted_by_user_id = User::all()->last()->id;

        $this->assertNotEquals($record->deleted_by_user_id, User::all()->first()->id);
        $this->assertEquals($record->deleted_by_user_id, User::all()->last()->id);
    }

    /** @test */
    public function it_will_retrieve_the_deleted_records_for_a_specific_user()
    {
        $this->actingAs(User::all()->last());

        collect(range(1, 5))->each(function () {
            $record = new $this->record();
            $record->save();
            $record->delete();
        });

        $this->actingAs(User::first());

        $record = new $this->record();
        $record->save();
        $record->delete();

        $results = (new $this->record())->onlyDeletedBy(User::first())->withTrashed()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($record->id, $results->first()->id);
    }

    /** @test */
    public function it_will_retrieve_the_soft_deleted_user_that_deleted_a_record()
    {
        collect(range(1, 5))->each(function () {
            (new $this->record())->save();
        });

        $user = SoftDeletableUser::first();
        $this->actingAs($user);

        $record = new $this->record();
        $record->save();
        $record->delete();

        $user->delete();

        $this->assertTrue($user->trashed());
        $this->assertTrue($record->trashed());
        $this->assertEquals($record->deletedBy->name, $user->name);
    }
}
