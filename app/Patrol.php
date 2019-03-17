<?php

namespace App;

use App\Ship;
use Illuminate\Database\Eloquent\Model;

class Patrol extends Ship
{
    protected $name = 'Patrol';
    protected $length = 1;
}
