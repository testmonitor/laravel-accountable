<?php

namespace TestMonitor\Accountable\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Models
{
    /**
     * Determines whether the given model (or model class) uses soft deletes.
     */
    public static function usesSoftDeletes(Model|string $model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model), true);
    }
}
