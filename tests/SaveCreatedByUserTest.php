<?php

namespace TestMonitor\Accountable\Test;

use PHPUnit\Framework\Attributes\Test;
use TestMonitor\Accountable\Test\Models\Record;
use TestMonitor\Accountable\Test\Models\SoftDeletableUser;
use TestMonitor\Accountable\Test\Models\User;
use TestMonitor\Accountable\Traits\Accountable;

class SaveCreatedByUserTest extends TestCase
{
    /**
     * @var Record
     */
    protected $record;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();

        $this->record = new class extends Record
        {
            use Accountable;
        };
    }

    #[Test]
    public function it_will_save_the_user_that_created_a_record()
    {
        // Given
        $user = User::first();
        $this->actingAs($user);

        // When
        $record = new $this->record;
        $record->save();

        // Then
        $this->assertEquals($record->created_by_user_id, $user->id);
        $this->assertEquals($record->updated_by_user_id, User::first()->id);
        $this->assertEquals($record->creator->name, $user->name);
        $this->assertEquals($record->editor->name, User::first()->name);
        $this->assertEquals($record->creator->name, $user->name);
        $this->assertEquals($record->editor->name, User::first()->name);
        $this->assertInstanceOf(get_class($user), $record->creator);
        $this->assertInstanceOf(get_class($user), $record->editor);
    }

    #[Test]
    public function it_will_save_the_impersonator_that_created_a_record()
    {
        // Given
        $impersonator = User::create(['name' => 'Impersonator']);
        accountable()->actingAs($impersonator);

        // When
        $record = new $this->record;
        $record->save();

        // Then
        $this->assertEquals($record->created_by_user_id, $impersonator->id);
        $this->assertEquals($record->creator->name, $impersonator->name);
        $this->assertInstanceOf(get_class($impersonator), $record->creator);
        $this->assertInstanceOf(get_class($impersonator), $record->editor);
    }

    #[Test]
    public function it_will_save_the_user_that_created_a_record_after_resetting_the_impersonator()
    {
        // Given
        $user = User::first();
        $this->actingAs($user);

        $impersonator = User::create(['name' => 'Impersonator']);
        accountable()->actingAs($impersonator);

        accountable()->reset();

        // When
        $record = new $this->record;
        $record->save();

        // Then
        $this->assertEquals($record->created_by_user_id, $user->id);
        $this->assertEquals($record->updated_by_user_id, User::first()->id);
        $this->assertEquals($record->creator->name, $user->name);
        $this->assertEquals($record->editor->name, User::first()->name);
        $this->assertInstanceOf(get_class($user), $record->creator);
        $this->assertInstanceOf(get_class($user), $record->editor);
    }

    #[Test]
    public function it_will_save_the_impersonated_user_and_reset_it_while_running_callback()
    {
        // Given
        $user = User::first();
        $this->actingAs($user);

        $impersonator = User::create(['name' => 'Impersonator']);
        $record = new $this->record;

        // When
        accountable()->whileActingAs($impersonator, function () use ($record) {
            $record->save();
        });

        // Then
        $this->assertEquals($record->created_by_user_id, $impersonator->id);
        $this->assertEquals($record->updated_by_user_id, $impersonator->id);
        $this->assertEquals($record->creator->name, $impersonator->name);
        $this->assertEquals($record->editor->name, $impersonator->name);
        $this->assertInstanceOf(get_class($impersonator), $record->creator);
        $this->assertInstanceOf(get_class($impersonator), $record->editor);
    }

    #[Test]
    public function it_will_not_save_the_anonymous_user_that_created_a_record()
    {
        // When
        $record = new $this->record;
        $record->save();

        // Then
        $this->assertNull($record->created_by_user_id);
        $this->assertNull($record->updated_by_user_id);
        $this->assertNull($record->creator);
        $this->assertNull($record->editor);
    }

    #[Test]
    public function it_will_return_a_fall_back_user_when_someone_anonymous_created_a_record()
    {
        // Given
        $record = new $this->record;
        $record->save();

        $anonymous = ['name' => 'Birmingham Bertie'];

        // When
        accountable()->setAnonymousUser($anonymous);

        // Then
        $this->assertNull($record->created_by_user_id);
        $this->assertNull($record->updated_by_user_id);
        $this->assertInstanceOf(User::class, $record->creator);
        $this->assertInstanceOf(User::class, $record->editor);
        $this->assertEquals($anonymous['name'], $record->creator->name);
        $this->assertEquals($anonymous['name'], $record->editor->name);
    }

    #[Test]
    public function it_will_save_a_specified_user_as_creator_when_it_is_explicitly_set()
    {
        // Given
        $user = User::first();
        $anotherUser = User::all()->last();

        $this->actingAs($user);

        $record = new $this->record;

        // When
        $record->created_by_user_id = $anotherUser->id;
        $record->save();

        // Then
        $this->assertNotEquals($record->created_by_user_id, $user->id);
        $this->assertEquals($record->created_by_user_id, $anotherUser->id);
    }

    #[Test]
    public function it_will_save_a_specified_user_as_creator_when_disabling_accountable()
    {
        // Given
        accountable()->disable();

        $user = User::first();
        $anotherUser = User::all()->last();

        $this->actingAs($user);

        $record = new $this->record;

        // When
        $record->created_by_user_id = $anotherUser->id;
        $record->save();

        // Then
        $this->assertNotEquals($record->created_by_user_id, $user->id);
        $this->assertEquals($record->created_by_user_id, $anotherUser->id);
    }

    #[Test]
    public function it_will_retrieve_the_created_records_from_a_specific_user()
    {
        // Given
        collect(range(1, 5))->each(function () {
            (new $this->record)->save();
        });

        $user = User::first();
        $this->actingAs($user);

        $record = new $this->record;
        $record->save();

        // When
        $results = (new $this->record)->onlyCreatedBy($user)->get();

        // Then
        $this->assertCount(1, $results);
        $this->assertEquals($record->id, $results->first()->id);
    }

    #[Test]
    public function it_will_retrieve_the_soft_deleted_user_that_created_a_record()
    {
        // Given
        collect(range(1, 5))->each(function () {
            (new $this->record)->save();
        });

        $user = SoftDeletableUser::first();
        $this->actingAs($user);

        $record = new $this->record;
        $record->save();

        // When
        $user->delete();

        // Then
        $this->assertTrue($user->trashed());
        $this->assertEquals($record->editor->name, $user->name);
    }

    #[Test]
    public function it_will_retrieve_the_created_records_from_the_currently_authenticated_user()
    {
        // Given
        $this->actingAs(User::first());

        $record = new $this->record;
        $record->save();

        // When
        $results = (new $this->record)->mine()->get();

        // Then
        $this->assertCount(1, $results);
        $this->assertEquals($record->id, $results->first()->id);
        $this->assertEquals($record->creator, auth()->user());
        $this->assertEquals($record->editor, auth()->user());
    }

    #[Test]
    public function it_will_retrieve_anonymous_records_when_calling_mine_without_an_authenticated_user()
    {
        // Given
        $record = new $this->record;
        $record->save();

        // When
        $results = (new $this->record)->mine()->get();

        // Then
        $this->assertNull($record->created_by_user_id);
        $this->assertCount(1, $results);
        $this->assertEquals($record->id, $results->first()->id);
    }
}
