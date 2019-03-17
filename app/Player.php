<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $guarded = [];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }
}
