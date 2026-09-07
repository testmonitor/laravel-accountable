<?php

namespace TestMonitor\Accountable\Test;

use PHPUnit\Framework\Attributes\Test;
use TestMonitor\Accountable\Test\Models\Record;
use TestMonitor\Accountable\Test\Models\SoftDeletableUser;
use TestMonitor\Accountable\Test\Models\User;
use TestMonitor\Accountable\Traits\Accountable;

class SaveUpdatedByUserTest extends TestCase
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
    public function it_will_save_the_user_that_last_updated_a_record()
    {
        // Given
        $this->actingAs(User::all()->last());

        $record = new $this->record;
        $record->save();

        $this->actingAs(User::first());

        // When
        $record->name = 'modification';
        $record->save();

        // Then
        $this->assertEquals($record->updated_by_user_id, User::first()->id);
        $this->assertEquals($record->editor->name, User::first()->name);
        $this->assertInstanceOf(get_class(User::first()), $record->editor);
    }

    #[Test]
    public function it_will_save_the_impersonator_that_last_updated_a_record()
    {
        // Given
        $this->actingAs(User::all()->last());

        $record = new $this->record;
        $record->save();

        $impersonator = User::create(['name' => 'Impersonator']);
        accountable()->actingAs($impersonator);

        // When
        $record->name = 'modification';
        $record->save();

        // Then
        $this->assertEquals($record->updated_by_user_id, $impersonator->id);
        $this->assertEquals($record->editor->name, $impersonator->name);
        $this->assertInstanceOf(get_class($impersonator), $record->editor);
    }

    #[Test]
    public function it_will_save_the_impersonated_user_that_last_updated_a_record_and_reset_it_while_running_callback()
    {
        // Given
        $this->actingAs(User::all()->last());

        $record = new $this->record;
        $record->save();

        $impersonator = User::create(['name' => 'Impersonator']);

        // When
        accountable()->whileActingAs($impersonator, function () use ($record) {
            $record->name = 'modification';
            $record->save();
        });

        // Then
        $this->assertEquals($record->updated_by_user_id, $impersonator->id);
        $this->assertEquals($record->editor->name, $impersonator->name);
        $this->assertInstanceOf(get_class($impersonator), $record->editor);
    }

    #[Test]
    public function it_will_not_save_the_anonymous_user_that_updated_a_record()
    {
        // Given
        $record = new $this->record;
        $record->save();

        // When
        $record->name = 'modification';
        $record->save();

        // Then
        $this->assertNull($record->updated_by_user_id);
        $this->assertNull($record->editor);
    }

    #[Test]
    public function it_will_return_a_fall_back_user_when_someone_anonymous_updated_a_record()
    {
        // Given
        $record = new $this->record;
        $record->save();

        $record->name = 'modification';
        $record->save();

        $anonymous = ['name' => 'Mrs Miggins'];

        // When
        accountable()->setAnonymousUser($anonymous);

        // Then
        $this->assertNull($record->updated_by_user_id);
        $this->assertInstanceOf(User::class, $record->editor);
        $this->assertEquals($anonymous['name'], $record->editor->name);
    }

    #[Test]
    public function it_will_save_a_specified_user_as_updater_when_it_is_explicitly_set()
    {
        // Given
        $user = User::first();
        $anotherUser = User::all()->last();

        $this->actingAs($user);

        $record = new $this->record;
        $record->save();

        // When
        $record->name = 'modification';
        $record->updated_by_user_id = $anotherUser->id;
        $record->save();

        // Then
        $this->assertNotEquals($record->updated_by_user_id, $user->id);
        $this->assertEquals($record->updated_by_user_id, $anotherUser->id);
    }

    #[Test]
    public function it_will_save_a_specified_user_as_updater_when_disabling_accountable()
    {
        // Given
        accountable()->disable();

        $user = User::first();
        $anotherUser = User::all()->last();

        $this->actingAs($user);

        $record = new $this->record;
        $record->save();

        $this->actingAs($anotherUser);

        // When
        $record->name = 'modification';
        $record->updated_by_user_id = $user->id;
        $record->save();

        // Then
        $this->assertNotEquals($record->updated_by_user_id, $anotherUser->id);
        $this->assertEquals($record->updated_by_user_id, $user->id);
    }

    #[Test]
    public function it_will_retrieve_the_updated_records_for_a_specific_user()
    {
        // Given
        $this->actingAs(User::all()->last());

        collect(range(1, 5))->each(function () {
            $record = new $this->record;
            $record->save();
            $record->name = 'modification';
            $record->save();
        });

        $this->actingAs(User::first());

        $record = new $this->record;
        $record->save();
        $record->name = 'modification';
        $record->save();

        // When
        $results = (new $this->record)->onlyUpdatedBy(User::first())->get();

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
        $record->name = 'modification';
        $record->save();

        // When
        $user->delete();

        // Then
        $this->assertTrue($user->trashed());
        $this->assertEquals($record->editor->name, $user->name);
    }

    #[Test]
    public function it_will_update_the_editor_using_touch_editor()
    {
        // Given
        $this->actingAs(User::all()->last());

        $record = new $this->record;
        $record->save();

        $editor = User::first();
        $this->actingAs($editor);

        // When
        $this->assertTrue($record->touchEditor());

        // Then
        $this->assertEquals($record->updated_by_user_id, $editor->id);
        $this->assertEquals($record->editor->name, $editor->name);
    }

    #[Test]
    public function it_will_update_the_editor_quietly_without_raising_events()
    {
        // Given
        $this->actingAs(User::all()->last());

        $record = new $this->record;
        $record->save();

        $eventsFired = false;

        $record::updating(function () use (&$eventsFired) {
            $eventsFired = true;
        });

        $editor = User::first();
        $this->actingAs($editor);

        // When
        $this->assertTrue($record->touchQuietlyWithEditor('name'));

        // Then
        $this->assertFalse($eventsFired);
        $this->assertEquals($record->updated_by_user_id, $editor->id);
        $this->assertEquals($record->editor->name, $editor->name);
    }
}
