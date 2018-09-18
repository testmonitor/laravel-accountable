<?php

namespace ByTestGear\Accountable\Test;

use ByTestGear\Accountable\Test\Models\User;
use ByTestGear\Accountable\Test\Models\Record;
use ByTestGear\Accountable\Traits\Accountable;
use ByTestGear\Accountable\Test\Models\SoftDeletableUser;

class SaveUpdatedByUserTest extends TestCase
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
    public function it_will_save_the_user_that_last_updated_a_record()
    {
        $this->actingAs(User::all()->last());

        $record = new $this->record();
        $record->save();

        $this->actingAs(User::first());

        $record->name = 'modification';
        $record->save();

        $this->assertEquals($record->updated_by_user_id, User::first()->id);
        $this->assertEquals($record->updatedBy->name, User::first()->name);
        $this->assertInstanceOf(get_class(User::first()), $record->updatedBy);
    }

    /** @test */
    public function it_will_not_save_the_anonymous_user_that_updated_a_record()
    {
        $record = new $this->record();
        $record->save();

        $record->name = 'modification';
        $record->save();

        $this->assertNull($record->updated_by_user_id);
        $this->assertNull($record->updatedBy);
    }

    /** @test */
    public function it_will_retrieve_the_updated_records_for_a_specific_user()
    {
        $this->actingAs(User::all()->last());

        collect(range(1, 5))->each(function () {
            $record = new $this->record();
            $record->save();
            $record->name = 'modification';
            $record->save();
        });

        $this->actingAs(User::first());

        $record = new $this->record();
        $record->save();
        $record->name = 'modification';
        $record->save();

        $results = (new $this->record())->onlyUpdatedBy(User::first())->get();

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
        $record->name = 'modification';
        $record->save();

        $user->delete();

        $this->assertTrue($user->trashed());
        $this->assertEquals($record->updatedBy->name, $user->name);
    }
}
