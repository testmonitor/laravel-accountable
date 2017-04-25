<?php

namespace ByTestGear\Accountable\Test;

use ByTestGear\Accountable\Test\Models\User;
use ByTestGear\Accountable\Test\Models\Record;
use ByTestGear\Accountable\Traits\Accountable;

class SaveCreatedByUserTest extends TestCase
{
    /**
     * @var \ByTestGear\Accountable\Test\Models\Record
     */
    protected $record;

    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();

        $this->record = new class() extends Record {
            use Accountable;
        };
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
        $this->assertEquals($record->createdByUser->name, $user->name);
        $this->assertEquals($record->updatedByUser->name, User::first()->name);
        $this->assertInstanceOf(get_class($user), $record->createdByUser);
        $this->assertInstanceOf(get_class($user), $record->updatedByUser);
    }

    /** @test */
    public function it_will_not_save_the_anonymous_user_that_created_a_record()
    {
        $record = new $this->record();
        $record->save();

        $this->assertNull($record->created_by_user_id);
        $this->assertNull($record->updated_by_user_id);
        $this->assertNull($record->createdByUser);
        $this->assertNull($record->updatedByUser);
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

        $results = (new $this->record())->createdBy($user)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($record->id, $results->first()->id);
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
        $this->assertEquals($record->createdByUser, auth()->user());
        $this->assertEquals($record->updatedByUser, auth()->user());
    }
}
