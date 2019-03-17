<?php

namespace App\Repositories;

use App\Game;
use App\Board;
use App\Player;
use App\Repositories\Contracts\PlayerInterface;

class PlayerRepository implements PlayerInterface
{
    protected $player;

    const STATE_WAITING = 'waiting';
    const STATE_PLAYING = 'playing';
    const STATE_WINNER = 'winner';
    const STATE_LOOSER = 'looser';


    const COLUMNS_LABELS = ['A'=>0,'B'=>1,'C'=>2,'D'=>3,'E'=>4,'F'=>5,'G'=>6,'H'=>7,'I'=>8,'J'=>9];
    const ROWS_LABELS = [1=>0,2=>1,3=>2,4=>3,5=>4,6=>5,7=>6,8=>7,9=>8,10=>9];


    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function processPlayerAttack(string $hit): array
    {
        $hitArray = str_split($hit);
        $y = $hitArray[0];
        $x = count($hitArray) > 2 ? $hitArray[1].$hitArray[2] : $hitArray[1];
        $this->throwExceptionIfKeyIsNotValid([$x,$y]);
        return ['y'=> self::COLUMNS_LABELS[$y], 'x'=>self::ROWS_LABELS[$x]];
    }

    public function updateState(Player $player, array $data): bool
    {
        return $player->update($data);
    }

    public function getState(Player $player, Board $opponetBoard) : string
    {
        return $this->isWinner($opponetBoard) ? self::STATE_WINNER : self::STATE_WAITING;
    }

    public function isWinner(Board $board) : bool
    {
        return $board->ship_spots <= $board->hit;
    }

    public function updateOponentPlayerState(Player $opponentPlayer, string $currentPlayerState) : Player
    {
        if ($currentPlayerState == self::STATE_WINNER) {
            $this->updateState($opponentPlayer, ['state' => self::STATE_LOOSER]);
        } elseif ($currentPlayerState == self::STATE_WAITING) {
            $this->updateState($opponentPlayer, ['state' => self::STATE_PLAYING]);
        }

        return $opponentPlayer;
    }

    public function getCurrentPlayer(Game $game, int $currentPlayerId) : Player
    {
        return $game->boards[$currentPlayerId - 1 ]->player;
    }

    public function isAllowedToPlay(Game $game, int $currentPlayerId) : bool
    {
        return $this->getCurrentPlayer($game, $currentPlayerId)->state == self::STATE_PLAYING;
    }

    public function updateStateAfterAttack(Player $player, array $playerData)
    {
        if ($this->updateState($player, $playerData)) {
            return $player;
        };
    }

    public function getShips(string $boardLayout):array
    {
        $layoutArray = json_decode($boardLayout, true);
        return $this->getShipSpots($layoutArray);
    }

    public function getShipSpots(array $layout) : array
    {
        $ships= [];
        for ($i = 0; $i < count($layout); $i++) {
            for ($j = 0; $j < count($layout[$i]); $j++) {
                if (isset($layout[$i][$j]['data'])) {
                    $direction = $layout[$i][$j]['data']['direction'];
                    $shipName = $layout[$i][$j]['data']['ship_name'];
                    $ships[$shipName]['direction'] = $this->getDirectionName($direction);
                    $praparedPoint = $this->prepareSpotsForPlayerOutput([$j,$i]);
                    $ships[$shipName]['spots'][] = $praparedPoint;
                }
            }
        }

        return $ships;
    }

    protected function prepareSpotsForPlayerOutput(array $points)
    {
        $columns = array_flip(self::COLUMNS_LABELS);
        return "{$columns[$points[0]]}" . (int)($points[1] + 1);
    }

    protected function getDirectionName(int $directionNumber)
    {
        return $directionNumber == 0 || $directionNumber == 3 ? 'vertical' : 'horizontal';
    }

    protected function throwExceptionIfKeyIsNotValid(array $keys)
    {
        foreach ($keys as $key) {
            if (!isset(self::COLUMNS_LABELS[$key]) && !isset(self::ROWS_LABELS[$key])) {
                throw new \RangeException("This key does not exist");
            }
        }
    }
}
