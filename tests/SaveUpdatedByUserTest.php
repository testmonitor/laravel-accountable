<?php

namespace TestMonitor\Accountable\Test;

use PHPUnit\Framework\Attributes\Test;
use TestMonitor\Accountable\Test\Models\User;
use TestMonitor\Accountable\Test\Models\Record;
use TestMonitor\Accountable\Traits\Accountable;
use TestMonitor\Accountable\AccountableSettings;
use TestMonitor\Accountable\Test\Models\SoftDeletableUser;

class SaveUpdatedByUserTest extends TestCase
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

    #[Test]
    public function it_will_save_the_user_that_last_updated_a_record()
    {
        $this->actingAs(User::all()->last());

        $record = new $this->record();
        $record->save();

        $this->actingAs(User::first());

        $record->name = 'modification';
        $record->save();

        $this->assertEquals($record->updated_by_user_id, User::first()->id);
        $this->assertEquals($record->editor->name, User::first()->name);
        $this->assertEquals($record->updatedBy->name, User::first()->name);
        $this->assertInstanceOf(get_class(User::first()), $record->editor);
    }

    #[Test]
    public function it_will_save_the_impersonator_that_last_updated_a_record()
    {
        $this->actingAs(User::all()->last());

        $record = new $this->record();
        $record->save();

        $impersonator = User::create(['name' => 'Impersonator']);
        accountable()->actingAs($impersonator);

        $record->name = 'modification';
        $record->save();

        $this->assertEquals($record->updated_by_user_id, $impersonator->id);
        $this->assertEquals($record->editor->name, $impersonator->name);
        $this->assertInstanceOf(get_class($impersonator), $record->editor);
    }

    #[Test]
    public function it_will_save_the_impersonated_user_that_last_updated_a_record_and_reset_it_while_running_callback()
    {
        $this->actingAs(User::all()->last());

        $record = new $this->record();
        $record->save();

        $impersonator = User::create(['name' => 'Impersonator']);

        accountable()->whileActingAs($impersonator, function () use ($record) {
            $record->name = 'modification';
            $record->save();
        });

        $this->assertEquals($record->updated_by_user_id, $impersonator->id);
        $this->assertEquals($record->editor->name, $impersonator->name);
        $this->assertInstanceOf(get_class($impersonator), $record->editor);
    }

    #[Test]
    public function it_will_not_save_the_anonymous_user_that_updated_a_record()
    {
        $record = new $this->record();
        $record->save();

        $record->name = 'modification';
        $record->save();

        $this->assertNull($record->updated_by_user_id);
        $this->assertNull($record->editor);
    }

    #[Test]
    public function it_will_return_a_fall_back_user_when_someone_anonymous_updated_a_record()
    {
        $record = new $this->record();
        $record->save();

        $record->name = 'modification';
        $record->save();

        $anonymous = ['name' => 'Mrs Miggins'];

        $this->config->setAnonymousUser($anonymous);

        $this->assertNull($record->updated_by_user_id);
        $this->assertInstanceOf(User::class, $record->editor);
        $this->assertEquals($anonymous['name'], $record->editor->name);
    }

    #[Test]
    public function it_will_save_a_specified_user_as_updater_when_disabling_accountable()
    {
        $this->config->disable();

        $user = User::first();
        $anotherUser = User::all()->last();

        $this->actingAs($user);

        $record = new $this->record();
        $record->save();

        $this->actingAs($anotherUser);

        $record->name = 'modification';
        $record->updated_by_user_id = $user->id;
        $record->save();

        $this->assertNotEquals($record->updated_by_user_id, $anotherUser->id);
        $this->assertEquals($record->updated_by_user_id, $user->id);
    }

    #[Test]
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

    #[Test]
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
        $this->assertEquals($record->editor->name, $user->name);
    }
}
