<?php

namespace App\Repositories\Contracts;

use App\Game;
use App\Board;
use App\Player;

interface PlayerInterface
{
    public function processPlayerAttack(string $hit): array;
    public function updateState(Player $player, array $data): bool;
    public function getState(Player $player, Board $opponetBoard):string;
    public function isWinner(Board $board) : bool;
    public function updateOponentPlayerState(Player $opponentPlayer, string $currentPlayerState):Player;
    public function getShips(string $boardLayout):array;
}
