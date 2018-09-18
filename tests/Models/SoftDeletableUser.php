<?php

namespace ByTestGear\Accountable\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletableUser extends User
{
    use SoftDeletes;
}
