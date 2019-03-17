<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $guarded = [];
    
    protected $cast = [
        'layout' => 'array'
    ];

    public function getLayout()
    {
        return is_array($this->layout) ? $this->layout : json_decode($this->layout, true);
    }

    public function player()
    {
        return $this->hasOne(Player::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
