<?php

namespace App\Repositories\Contracts;

use App\Game;
use App\Board;

interface GameInterface
{
    public function init() : Game;
    public function getAttackData(Game $game, int $currentPlayerId) : array;
    public function performAttack(string $hit, array $attackData) : array;
}
