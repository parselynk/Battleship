<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $guarded = [];

    public function path()
    {
        return "/api/games/{$this->id}";
    }

    public function boards()
    {
        return $this->hasMany(Board::class);
    }

    public function isPlayable()
    {
        return $this->boards->count() == 2 ? true : false;
    }
}
