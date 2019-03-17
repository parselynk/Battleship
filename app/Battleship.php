<?php

namespace App;

use App\Ship;
use Illuminate\Database\Eloquent\Model;

class Battleship extends Ship
{
    protected $name = 'Battleship';
    protected $length = 4;
}
