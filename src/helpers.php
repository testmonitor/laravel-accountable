<?php

use TestMonitor\Accountable\Accountable;

if (! function_exists('accountable')) {
    function accountable(): Accountable
    {
        return app(Accountable::class);
    }
}
