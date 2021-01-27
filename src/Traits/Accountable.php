<?php

namespace TestMonitor\Accountable\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use TestMonitor\Accountable\AccountableServiceProvider;
use TestMonitor\Accountable\Observer\AccountableObserver;

trait Accountable
{
    /**
     * Boot the accountable trait for a model.
     *
     * @return void
     */
    public static function bootAccountable()
    {
        static::observe(new AccountableObserver);
    }

    /**
     * Determines if the user model is soft deletable.
     *
     * @return bool
     */
    protected function userModelUsesSoftDeletes()
    {
        return in_array(SoftDeletes::class, class_uses_recursive(AccountableServiceProvider::userModel()));
    }

    /**
     * Define the created by relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        $relation = $this->belongsTo(AccountableServiceProvider::userModel(), accountable()->createdByColumn())
                         ->withDefault(accountable()->anonymousUser());

        return $this->userModelUsesSoftDeletes() ? $relation->withTrashed() : $relation;
    }

    /**
     * Define the updated by relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy()
    {
        $relation = $this->belongsTo(AccountableServiceProvider::userModel(), accountable()->updatedByColumn())
                         ->withDefault(accountable()->anonymousUser());

        return $this->userModelUsesSoftDeletes() ? $relation->withTrashed() : $relation;
    }

    /**
     * Define the deleted by relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deletedBy()
    {
        $relation = $this->belongsTo(AccountableServiceProvider::userModel(), accountable()->deletedByColumn())
                         ->withDefault(accountable()->anonymousUser());

        return $this->userModelUsesSoftDeletes() ? $relation->withTrashed() : $relation;
    }

    /**
     * Scope a query to only include records created by a given user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyCreatedBy(Builder $query, Model $user)
    {
        return $query->where(accountable()->createdByColumn(), $user->getKey());
    }

    /**
     * Scope a query to only include records created by the current logged in user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMine(Builder $query)
    {
        $user = accountable()->impersonatedUser() ?? AccountableServiceProvider::accountableUser();

        return $query->where(accountable()->createdByColumn(), ! is_null($user) ? $user->getKey() : null);
    }

    /**
     * Scope a query to only include records updated by a given user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyUpdatedBy(Builder $query, Model $user)
    {
        return $query->where(accountable()->updatedByColumn(), $user->getKey());
    }

    /**
     * Scope a query to only include records deleted by a given user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyDeletedBy(Builder $query, Model $user)
    {
        return $query->where(accountable()->deletedByColumn(), $user->getKey());
    }
}
