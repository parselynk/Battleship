<?php

namespace App;

use App\Ship;
use Illuminate\Database\Eloquent\Model;

class Submarine extends Ship
{
    protected $name = 'Submarine';
    protected $length = 3;
}
