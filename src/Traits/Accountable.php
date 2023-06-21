<?php

namespace TestMonitor\Accountable\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TestMonitor\Accountable\Observer\AccountableObserver;
use TestMonitor\Accountable\Accountable as AccountableService;

trait Accountable
{
    /**
     * Boot the accountable trait for a model.
     *
     * @return void
     */
    public static function bootAccountable(): void
    {
        static::observe(new AccountableObserver);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        $relation = $this->belongsTo(AccountableService::userModel(), accountable()->createdByColumn())
                         ->withDefault(accountable()->anonymousUser());

        return $this->userModelUsesSoftDeletes() ? $relation->withTrashed() : $relation;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function editor(): BelongsTo
    {
        $relation = $this->belongsTo(AccountableService::userModel(), accountable()->updatedByColumn())
                         ->withDefault(accountable()->anonymousUser());

        return $this->userModelUsesSoftDeletes() ? $relation->withTrashed() : $relation;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deleter(): BelongsTo
    {
        $relation = $this->belongsTo(AccountableService::userModel(), accountable()->deletedByColumn())
                         ->withDefault(accountable()->anonymousUser());

        return $this->userModelUsesSoftDeletes() ? $relation->withTrashed() : $relation;
    }

    /**
     * Determines if the user model support soft deleting.
     *
     * @return bool
     */
    protected function userModelUsesSoftDeletes(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive(AccountableService::userModel()));
    }

    /**
     * @deprecated
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->creator();
    }

    /**
     * @deprecated
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy(): BelongsTo
    {
        return $this->editor();
    }

    /**
     * @deprecated
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deletedBy(): BelongsTo
    {
        return $this->deleter();
    }

    /**
     * Update the model's editor.
     *
     * @return bool
     */
    public function touchEditor(): bool
    {
        $this->setUpdatedBy(AccountableService::authenticatedUser());

        return $this->save();
    }

    /**
     * Update the model's update timestamp and editor without raising any events.
     *
     * @param string|null $attribute
     *
     * @return bool
     */
    public function touchQuietlyWithEditor($attribute = null): bool
    {
        return $this->withoutEvents(function () use ($attribute) {
            return $this->touch($attribute) && $this->touchEditor();
        });
    }

    /**
     * Set the value of the "created by" attribute.
     *
     * @param mixed $value
     * @return $this
     */
    public function setCreatedBy($value): static
    {
        $this->{$this->getCreatedByColumn()} = $value instanceof Model ? $value->getKey() : $value;

        return $this;
    }

    /**
     * Set the value of the "updated by" attribute.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function setUpdatedBy($value): static
    {
        $this->{$this->getUpdatedByColumn()} = $value instanceof Model ? $value->getKey() : $value;

        return $this;
    }

    /**
     * Set the value of the "deleted by" attribute.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function setDeletedBy($value): static
    {
        $this->{$this->getDeletedByColumn()} = $value instanceof Model ? $value->getKey() : $value;

        return $this;
    }

    /**
     * Get the name of the "created by" column.
     *
     * @return string
     */
    public function getCreatedByColumn(): string
    {
        return accountable()->createdByColumn();
    }

    /**
     * Get the name of the "updated by" column.
     *
     * @return string
     */
    public function getUpdatedByColumn(): string
    {
        return accountable()->updatedByColumn();
    }

    /**
     * Get the name of the "deleted by" column.
     *
     * @return string
     */
    public function getDeletedByColumn(): string
    {
        return accountable()->deletedByColumn();
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
        return $query->where($this->getCreatedByColumn(), $user->getKey());
    }

    /**
     * Scope a query to only include records created by the current logged in user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMine(Builder $query)
    {
        return $query->where(
            $this->getCreatedByColumn(),
            AccountableService::authenticatedUser()?->getKey()
        );
    }

    /**
     * Scope a query to only include records updated by a given user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyUpdatedBy(Builder $query, Model $user)
    {
        return $query->where($this->getUpdatedByColumn(), $user->getKey());
    }

    /**
     * Scope a query to only include records deleted by a given user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyDeletedBy(Builder $query, Model $user)
    {
        return $query->where($this->getDeletedByColumn(), $user->getKey());
    }
}
