<?php

namespace TestMonitor\Accountable\Test;

use TestMonitor\Accountable\Test\Models\User;
use TestMonitor\Accountable\Test\Models\Record;
use TestMonitor\Accountable\Traits\Accountable;
use TestMonitor\Accountable\AccountableSettings;
use TestMonitor\Accountable\Test\Models\SoftDeletableUser;

class SaveCreatedByUserTest extends TestCase
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

        $this->setUpDatabase();

        $this->record = new class() extends Record {
            use Accountable;
        };

        $this->config = app()->make(AccountableSettings::class);
    }

    /** @test */
    public function it_will_save_the_user_that_created_a_record()
    {
        $user = User::first();
        $this->actingAs($user);

        $record = new $this->record();
        $record->save();

        $this->assertEquals($record->created_by_user_id, $user->id);
        $this->assertEquals($record->updated_by_user_id, User::first()->id);
        $this->assertEquals($record->createdBy->name, $user->name);
        $this->assertEquals($record->updatedBy->name, User::first()->name);
        $this->assertInstanceOf(get_class($user), $record->createdBy);
        $this->assertInstanceOf(get_class($user), $record->updatedBy);
    }

    /** @test */
    public function it_will_not_save_the_anonymous_user_that_created_a_record()
    {
        $record = new $this->record();
        $record->save();

        $this->assertNull($record->created_by_user_id);
        $this->assertNull($record->updated_by_user_id);
        $this->assertNull($record->createdBy);
        $this->assertNull($record->updatedBy);
    }

    /** @test */
    public function it_will_return_a_fall_back_user_when_someone_anonymous_created_a_record()
    {
        $record = new $this->record();
        $record->save();

        $anonymous = ['name' => 'Birmingham Bertie'];

        $this->config->setAnonymousUser($anonymous);

        $this->assertNull($record->created_by_user_id);
        $this->assertNull($record->updated_by_user_id);
        $this->assertInstanceOf(User::class, $record->createdBy);
        $this->assertInstanceOf(User::class, $record->updatedBy);
        $this->assertEquals($anonymous['name'], $record->createdBy->name);
        $this->assertEquals($anonymous['name'], $record->updatedBy->name);
    }

    /** @test */
    public function it_will_save_a_specified_user_as_creator_when_disabling_accountable()
    {
        $this->config->disable();

        $user = User::first();
        $anotherUser = User::all()->last();

        $this->actingAs($user);

        $record = new $this->record();

        $record->created_by_user_id = $anotherUser->id;
        $record->save();

        $this->assertNotEquals($record->created_by_user_id, $user->id);
        $this->assertEquals($record->created_by_user_id, $anotherUser->id);
    }

    /** @test */
    public function it_will_retrieve_the_created_records_from_a_specific_user()
    {
        collect(range(1, 5))->each(function () {
            (new $this->record())->save();
        });

        $user = User::first();
        $this->actingAs($user);

        $record = new $this->record();
        $record->save();

        $results = (new $this->record())->onlyCreatedBy($user)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($record->id, $results->first()->id);
    }

    /** @test */
    public function it_will_retrieve_the_soft_deleted_user_that_created_a_record()
    {
        collect(range(1, 5))->each(function () {
            (new $this->record())->save();
        });

        $user = SoftDeletableUser::first();
        $this->actingAs($user);

        $record = new $this->record();
        $record->save();

        $user->delete();

        $this->assertTrue($user->trashed());
        $this->assertEquals($record->createdBy->name, $user->name);
    }

    /** @test */
    public function it_will_retrieve_the_created_records_from_the_currently_authenticated_user()
    {
        $this->actingAs(User::first());

        $record = new $this->record();
        $record->save();

        $results = (new $this->record())->mine()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($record->id, $results->first()->id);
        $this->assertEquals($record->createdBy, auth()->user());
        $this->assertEquals($record->updatedBy, auth()->user());
    }
}
