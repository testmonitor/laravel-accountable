<?php

namespace TestMonitor\Accountable\Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletableUser extends User
{
    use SoftDeletes;
}
