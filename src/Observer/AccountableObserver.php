<?php

namespace TestMonitor\Accountable\Observer;

use Illuminate\Database\Eloquent\SoftDeletes;
use TestMonitor\Accountable\AccountableSettings;
use TestMonitor\Accountable\AccountableServiceProvider;

class AccountableObserver
{
    /**
     * @var AccountableSettings
     */
    protected $config;

    /**
     * AccountableObserver constructor.
     */
    public function __construct()
    {
        $this->config = app()->make(AccountableSettings::class);
    }

    /**
     * @return mixed
     */
    protected function accountableUserId()
    {
        $user = AccountableServiceProvider::accountableUser();

        return ! is_null($user) ? $user->getKey() : null;
    }

    /**
     * Store the user creating a record.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function creating($model)
    {
        if ($this->config->enabled()) {
            $model->{$this->config->createdByColumn()} = $this->accountableUserId();
            $model->{$this->config->updatedByColumn()} = $this->accountableUserId();

            foreach ($model->getTouchedRelations() as $relation) {
                $model->$relation->{$this->config->updatedByColumn()} = $this->accountableUserId();
                $model->$relation->save();
            }
        }
    }

    /**
     * Store the user updating a record.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function updating($model)
    {
        if ($this->config->enabled()) {
            $model->{$this->config->updatedByColumn()} = $this->accountableUserId();

            foreach ($model->getTouchedRelations() as $relation) {
                $model->$relation->{$this->config->updatedByColumn()} = $this->accountableUserId();
                $model->$relation->save();
            }
        }
    }

    /**
     * Store the user deleting a record.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function deleting($model)
    {
        if ($this->config->enabled() &&
            collect(class_uses($model))->contains(SoftDeletes::class)) {
            $model->{$this->config->deletedByColumn()} = $this->accountableUserId();

            foreach ($model->getTouchedRelations() as $relation) {
                $model->$relation->{$this->config->updatedByColumn()} = $this->accountableUserId();
                $model->$relation->save();
            }

            $model->save();
        }
    }
}
