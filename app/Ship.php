<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ship extends Model
{
    public function getName()
    {
        return $this->name;
    }

    public function getLength()
    {
        return $this->length;
    }
}
