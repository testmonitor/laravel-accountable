<?php

namespace ByTestGear\Accountable;

use Illuminate\Database\Schema\Blueprint;

class Accountable
{
    /**
     * Add accountable.column_names to the table, including indexes.
     *
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param bool $usesSoftDeletes
     */
    public static function columns(Blueprint $table, $usesSoftDeletes = true)
    {
        self::addColumn($table, config('accountable.column_names.created_by'));
        self::addColumn($table, config('accountable.column_names.updated_by'));

        if ($usesSoftDeletes) {
            self::addColumn($table, config('accountable.column_names.deleted_by'));
        }
    }

    /**
     * Add a single Accountable column to the table. Also creates an index.
     *
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param string $name
     */
    public static function addColumn(Blueprint $table, string $name)
    {
        $table->unsignedInteger($name)->nullable();
        $table->index($name);
    }
}
