<?php

namespace TestMonitor\Accountable\Test;

use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\Attributes\Test;
use TestMonitor\Accountable\Test\Models\Record;
use TestMonitor\Accountable\Test\Models\SoftDeletableUser;
use TestMonitor\Accountable\Test\Models\User;
use TestMonitor\Accountable\Traits\Accountable;

final class SaveDeletedByUserTest extends TestCase
{
    /**
     * @var Record
     */
    protected $record;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabaseWithSoftDeletes();

        $this->record = new class extends Record
        {
            use Accountable, SoftDeletes;
        };
    }

    #[Test]
    public function it_will_save_the_user_that_deleted_a_record()
    {
        // Given
        $this->actingAs(User::all()->first());

        $record = new $this->record;
        $record->save();

        // When
        $record->delete();

        // Then
        $this->assertEquals($record->deleted_by_user_id, User::first()->id);
        $this->assertEquals($record->deleter->name, User::first()->name);
        $this->assertInstanceOf(User::class, $record->deleter);
    }

    #[Test]
    public function it_will_save_the_impersonator_that_deleted_a_record()
    {
        // Given
        $impersonator = User::create(['name' => 'Impersonator']);
        accountable()->actingAs($impersonator);

        $record = new $this->record;
        $record->save();

        // When
        $record->delete();

        // Then
        $this->assertEquals($record->deleted_by_user_id, $impersonator->id);
        $this->assertEquals($record->deleter->name, $impersonator->name);
        $this->assertInstanceOf($impersonator::class, $record->deleter);
    }

    #[Test]
    public function it_will_save_the_impersonator_while_running_a_callback_that_deleted_a_record()
    {
        // Given
        $impersonator = User::create(['name' => 'Impersonator']);
        $record = new $this->record;
        $record->save();

        // When
        accountable()->whileActingAs($impersonator, function () use ($record) {
            $record->delete();
        });

        // Then
        $this->assertEquals($record->deleted_by_user_id, $impersonator->id);
        $this->assertEquals($record->deleter->name, $impersonator->name);
        $this->assertInstanceOf($impersonator::class, $record->deleter);
    }

    #[Test]
    public function it_will_not_save_the_anonymous_user_that_deleted_a_record()
    {
        // Given
        $record = new $this->record;
        $record->save();

        // When
        $record->delete();

        // Then
        $this->assertNull($record->deleted_by_user_id);
        $this->assertNull($record->deleter);
    }

    #[Test]
    public function it_will_return_a_fall_back_user_when_someone_anonymous_deleted_a_record()
    {
        // Given
        $record = new $this->record;
        $record->save();

        $record->delete();

        $anonymous = ['name' => 'Neville the Fat Hamster'];

        // When
        accountable()->setAnonymousUser($anonymous);

        // Then
        $this->assertNull($record->deleted_by_user_id);
        $this->assertInstanceOf(User::class, $record->deleter);
        $this->assertEquals($anonymous['name'], $record->deleter->name);
    }

    #[Test]
    public function it_will_not_save_the_user_that_deleted_a_record_when_model_doesnt_use_softdeletes()
    {
        // Given
        $record = new class extends Record
        {
            use Accountable;
        };

        $this->actingAs(User::all()->first());

        $record = new $record;
        $record->save();

        // When
        $record->delete();

        // Then
        $this->assertNotEquals($record->deleted_by_user_id, User::all()->first());
        $this->assertNull($record->deleted_by_user_id);
    }

    #[Test]
    public function it_will_save_a_specified_user_as_deleter_when_disabling_accountable()
    {
        // Given
        accountable()->disable();

        $this->actingAs(User::all()->first());

        $record = new $this->record;
        $record->save();

        // When
        $record->deleted_by_user_id = User::all()->last()->id;

        // Then
        $this->assertNotEquals($record->deleted_by_user_id, User::all()->first()->id);
        $this->assertEquals($record->deleted_by_user_id, User::all()->last()->id);
    }

    #[Test]
    public function it_will_retrieve_the_deleted_records_for_a_specific_user()
    {
        // Given
        $this->actingAs(User::all()->last());

        collect(range(1, 5))->each(function () {
            $record = new $this->record;
            $record->save();
            $record->delete();
        });

        $this->actingAs(User::first());

        $record = new $this->record;
        $record->save();
        $record->delete();

        // When
        $results = (new $this->record)->onlyDeletedBy(User::first())->withTrashed()->get();

        // Then
        $this->assertCount(1, $results);
        $this->assertEquals($record->id, $results->first()->id);
    }

    #[Test]
    public function it_will_retrieve_the_soft_deleted_user_that_deleted_a_record()
    {
        // Given
        collect(range(1, 5))->each(function () {
            (new $this->record)->save();
        });

        $user = SoftDeletableUser::first();
        $this->actingAs($user);

        $record = new $this->record;
        $record->save();
        $record->delete();

        // When
        $user->delete();

        // Then
        $this->assertTrue($user->trashed());
        $this->assertTrue($record->trashed());
        $this->assertEquals($record->deleter->name, $user->name);
    }
}
