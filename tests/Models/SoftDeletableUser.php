<?php

namespace TestMonitor\Accountable\Test\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletableUser extends User
{
    use SoftDeletes;
}
