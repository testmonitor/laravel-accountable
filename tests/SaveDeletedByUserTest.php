<?php

namespace ByTestGear\Accountable\Test;

use ByTestGear\Accountable\Test\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use ByTestGear\Accountable\Test\Models\Record;
use ByTestGear\Accountable\Traits\Accountable;
use ByTestGear\Accountable\Test\Models\SoftDeletableUser;

class SaveDeletedByUserTest extends TestCase
{
    /**
     * @var \ByTestGear\Accountable\Test\Models\Record
     */
    protected $record;

    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabaseWithSoftDeletes();

        $this->record = new class() extends Record {
            use Accountable, SoftDeletes;
        };
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
    public function it_will_not_save_the_anonymous_user_that_deleted_a_record()
    {
        $record = new $this->record();
        $record->save();

        $record->delete();

        $this->assertNull($record->deleted_by_user_id);
        $this->assertNull($record->deletedBy);
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
