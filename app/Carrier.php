<?php

namespace App;

use App\Ship;
use Illuminate\Database\Eloquent\Model;

class Carrier extends Ship
{
    protected $name = 'Carrier';
    protected $length = 5;
}
