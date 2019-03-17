<?php

namespace App\Repositories\Contracts;

use App\Game;
use App\Ship;
use App\Board;

interface BoardInterface
{
    public function setAnEmptyBoard(Game $game);

    public function assignAPlayer(Board $board);

    public function placeShip(Board $board, string $ship);

    public function isPlaced(Board $board, $params, Ship $ship) : bool;

    public function findNextPosition(int $row, int $column, int $direction) : array;

    public function setPositionAsOccupied(array &$layout, array $params, Ship $ship);

    public function generateEmptyLayout() : array;
}
