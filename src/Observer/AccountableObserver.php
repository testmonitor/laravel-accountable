<?php

namespace TestMonitor\Accountable\Observer;

use Illuminate\Database\Eloquent\Model;
use TestMonitor\Accountable\Accountable;
use Illuminate\Database\Eloquent\SoftDeletes;
use TestMonitor\Accountable\AccountableSettings;

class AccountableObserver
{
    /**
     * @var \TestMonitor\Accountable\AccountableSettings
     */
    protected $settings;

    /**
     * AccountableObserver constructor.
     */
    public function __construct()
    {
        $this->settings = app()->make(AccountableSettings::class);
    }

    /**
     * Store the user creating a record.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function creating(Model $model)
    {
        if ($this->settings->enabled()) {
            $model->setCreatedBy(Accountable::authenticatedUser());
            $model->setUpdatedBy(Accountable::authenticatedUser());
        }
    }

    /**
     * Store the user updating a record.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function updating(Model $model)
    {
        if ($this->settings->enabled()) {
            $model->setUpdatedBy(Accountable::authenticatedUser());
        }
    }

    /**
     * Store the user deleting a record.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function deleting(Model $model)
    {
        if ($this->settings->enabled() && $this->modelUsesSoftDeletes($model)) {
            $model->setDeletedBy(Accountable::authenticatedUser());

            $model->saveQuietly();
        }
    }

    /**
     * Determines if the model uses soft deletes.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return bool
     */
    protected function modelUsesSoftDeletes(Model $model): bool
    {
        return collect(class_uses($model))->contains(SoftDeletes::class);
    }
}
