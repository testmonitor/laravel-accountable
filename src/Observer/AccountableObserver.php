<?php

namespace TestMonitor\Accountable\Observer;

use Illuminate\Database\Eloquent\Model;
use TestMonitor\Accountable\Accountable;
use TestMonitor\Accountable\Support\Models;

class AccountableObserver
{
    /**
     * Store the user creating a record.
     */
    public function creating(Model $model): void
    {
        if (! Accountable::enabled()) {
            return;
        }

        if (empty($model->getAttribute($model->getCreatedByColumn()))) {
            $model->setCreatedBy(Accountable::authenticatedUser());
        }

        if (empty($model->getAttribute($model->getUpdatedByColumn()))) {
            $model->setUpdatedBy(Accountable::authenticatedUser());
        }
    }

    /**
     * Store the user updating a record.
     */
    public function updating(Model $model): void
    {
        if (! Accountable::enabled()) {
            return;
        }

        if ($model->isClean($model->getUpdatedByColumn())) {
            $model->setUpdatedBy(Accountable::authenticatedUser());
        }
    }

    /**
     * Store the user deleting a record.
     */
    public function deleting(Model $model): void
    {
        if (! Accountable::enabled() || ! Models::usesSoftDeletes($model)) {
            return;
        }

        if (empty($model->getAttribute($model->getDeletedByColumn()))) {
            $model->setDeletedBy(Accountable::authenticatedUser());
        }

        $model->saveQuietly();
    }
}
