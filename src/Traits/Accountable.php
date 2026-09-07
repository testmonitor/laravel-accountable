<?php

namespace TestMonitor\Accountable\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use TestMonitor\Accountable\Support\Models;
use TestMonitor\Accountable\AccountableColumns;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TestMonitor\Accountable\Observer\AccountableObserver;
use TestMonitor\Accountable\Accountable as AccountableService;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait Accountable
{
    public static function bootAccountable(): void
    {
        static::whenBooted(fn () => static::observe(AccountableObserver::class));
    }

    public function creator(): BelongsTo
    {
        return $this->accountableRelation($this->getCreatedByColumn());
    }

    public function editor(): BelongsTo
    {
        return $this->accountableRelation($this->getUpdatedByColumn());
    }

    public function deleter(): BelongsTo
    {
        return $this->accountableRelation($this->getDeletedByColumn());
    }

    /**
     * Build the "created/updated/deleted by" relation for the given column.
     */
    protected function accountableRelation(string $column): BelongsTo
    {
        $relation = $this->belongsTo(AccountableService::userModel(), $column)
                         ->withDefault(AccountableService::anonymousUser());

        return $this->userModelUsesSoftDeletes() ? $relation->withTrashed() : $relation;
    }

    /**
     * Determines if the user model supports soft deleting.
     */
    protected function userModelUsesSoftDeletes(): bool
    {
        return Models::usesSoftDeletes(AccountableService::userModel());
    }

    /**
     * Update the model's editor.
     */
    public function touchEditor(): bool
    {
        $this->setUpdatedBy(AccountableService::authenticatedUser());

        return $this->save();
    }

    /**
     * Update the model's update timestamp and editor without raising any events.
     */
    public function touchQuietlyWithEditor(?string $attribute = null): bool
    {
        return $this->withoutEvents(fn () => $this->touch($attribute) && $this->touchEditor());
    }

    /**
     * Set the value of the "created by" attribute.
     */
    public function setCreatedBy(mixed $value): static
    {
        $this->{$this->getCreatedByColumn()} = $value instanceof Model ? $value->getKey() : $value;

        return $this;
    }

    /**
     * Set the value of the "updated by" attribute.
     */
    public function setUpdatedBy(mixed $value): static
    {
        $this->{$this->getUpdatedByColumn()} = $value instanceof Model ? $value->getKey() : $value;

        return $this;
    }

    /**
     * Set the value of the "deleted by" attribute.
     */
    public function setDeletedBy(mixed $value): static
    {
        $this->{$this->getDeletedByColumn()} = $value instanceof Model ? $value->getKey() : $value;

        return $this;
    }

    /**
     * Get the name of the "created by" column.
     */
    public function getCreatedByColumn(): string
    {
        return AccountableColumns::createdByColumn();
    }

    /**
     * Get the name of the "updated by" column.
     */
    public function getUpdatedByColumn(): string
    {
        return AccountableColumns::updatedByColumn();
    }

    /**
     * Get the name of the "deleted by" column.
     */
    public function getDeletedByColumn(): string
    {
        return AccountableColumns::deletedByColumn();
    }

    /**
     * Scope a query to only include records created by a given user.
     */
    public function scopeOnlyCreatedBy(Builder $query, Model $user): Builder
    {
        return $query->where($this->getCreatedByColumn(), $user->getKey());
    }

    /**
     * Scope a query to records created by the current user, or anonymous records when none is authenticated.
     */
    public function scopeMine(Builder $query): Builder
    {
        return $query->where(
            $this->getCreatedByColumn(),
            AccountableService::authenticatedUser()?->getAuthIdentifier()
        );
    }

    /**
     * Scope a query to only include records updated by a given user.
     */
    public function scopeOnlyUpdatedBy(Builder $query, Model $user): Builder
    {
        return $query->where($this->getUpdatedByColumn(), $user->getKey());
    }

    /**
     * Scope a query to only include records deleted by a given user.
     */
    public function scopeOnlyDeletedBy(Builder $query, Model $user): Builder
    {
        return $query->where($this->getDeletedByColumn(), $user->getKey());
    }
}
