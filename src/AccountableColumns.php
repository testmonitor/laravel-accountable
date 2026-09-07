<?php

namespace TestMonitor\Accountable;

use Illuminate\Database\Schema\Blueprint;

class AccountableColumns
{
    /**
     * The name of the "created by" column.
     */
    public static function createdByColumn(): string
    {
        return config('accountable.column_names.created_by');
    }

    /**
     * The name of the "updated by" column.
     */
    public static function updatedByColumn(): string
    {
        return config('accountable.column_names.updated_by');
    }

    /**
     * The name of the "deleted by" column.
     */
    public static function deletedByColumn(): string
    {
        return config('accountable.column_names.deleted_by');
    }

    /**
     * Add accountable.column_names to the table, including indexes.
     */
    public static function add(Blueprint $table, bool $usesSoftDeletes = true): void
    {
        static::addColumn($table, static::createdByColumn());
        static::addColumn($table, static::updatedByColumn());

        if ($usesSoftDeletes) {
            static::addColumn($table, static::deletedByColumn());
        }
    }

    /**
     * Add a single Accountable column to the table. Also creates an index.
     */
    public static function addColumn(Blueprint $table, string $name): void
    {
        $table->foreignId($name)->nullable();
        $table->index($name);
    }
}
