<?php

use TestMonitor\Accountable\AccountableSettings;

if (! function_exists('accountable')) {
    function accountable(): AccountableSettings
    {
        return app()->make(AccountableSettings::class);
    }
}
