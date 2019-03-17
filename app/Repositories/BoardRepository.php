<?php

namespace App\Repositories;

use App\Game;
use App\Ship;
use App\Board;
use App\Repositories\Contracts\BoardInterface;
use App\Repositories\Contracts\PlayerInterface;

class BoardRepository implements BoardInterface
{
    protected $playerRepository;

    const ROWS = 10;
    const COLUMNS = 10;
    const BOARD_1 = 0;
    const BOARD_2 = 1;
    const POINT_OCCUPIED = 'y';
    const POINT_EMPTY = -1;
    const POINT_MISSED = '-';
    const POINT_HIT = 'X';
    const SPOT_TYPE = 'type';

    public function __construct(PlayerInterface $playerRepository)
    {
        $this->playerRepository = $playerRepository;
    }

    public function setEmptyBoards(Game $game)
    {

        $this->setAnEmptyBoard($game);
        $this->setAnEmptyBoard($game);

        return $this;
    }

    public function setAnEmptyBoard(Game $game)
    {
        if (!$game->isPlayable()) { // if game has less than 2 boards
            $board = $game->boards()->create(['layout' => json_encode($this->generateEmptyLayout())]);
            return $this->assignAPlayer($board);
        }
    }

    public function shipExist(string $layout, string $ship)
    {
        return isset($this->playerRepository->getShips($layout)[ucfirst($ship)]);
    }

    public function assignAPlayer(Board $board)
    {
        if (!$board->player) { // if game has less than 2 boards
            $board = $board->player()->create(['state' => 'waiting']);
        }
        return $board;
    }

    public function findBoardSpot(string $layout, array $spot)
    {
        return json_decode($layout, true)[$spot['x']][$spot['y']];
    }

    public function updateSpot(Board $board, array $spot, bool $isHit)
    {
        
        $attackedSpot = $this->findBoardSpot($board->layout, $spot);
        $layoutArray = json_decode($board->layout, true);
        if ($attackedSpot['type'] == self::POINT_EMPTY) {
            $layoutArray[$spot['x']][$spot['y']]['type'] = self::POINT_MISSED;
            $boardData = ['layout' => json_encode($layoutArray)];
            $board->update($boardData);
        } elseif ($attackedSpot['type'] == self::POINT_OCCUPIED) {
            $layoutArray[$spot['x']][$spot['y']]['type'] = self::POINT_HIT;
            $boardData = ['layout' => json_encode($layoutArray), 'hit' => $board->hit + 1];
            $board->update($boardData);
        }

        return $board;
    }

    public function getOpponentBoard(Game $game, int $currentPlayerId) : Board
    {
        $opponentBoardId = $currentPlayerId == 2 ? 0 : 1;
        return  $game->boards[$opponentBoardId];
    }

    public function isHit(array $spot)
    {
        return $spot[self::SPOT_TYPE] == self::POINT_OCCUPIED ||  $spot[self::SPOT_TYPE] == self::POINT_HIT;
    }

    /**
     * Try placing each ship on board till it makes sure place is shipd
     */
    public function placeShip(Board $board, string $ship)
    {
        $shipInstance = $this->getShipInstance($ship);

        do {
            $params = $this->setRandomParams();
            $placed = $this->isPlaced($board, $params, $shipInstance);
        } while (!$placed);
    }

    protected function getShipInstance(string $name)
    {
         $ship = 'App\\'.ucfirst($name);
         return new $ship;
    }

    public function setRandomParams()
    {
        return [
            'row' => rand(0, self::ROWS - 1),
            'column' => rand(0, self::COLUMNS - 1),
            'direction' => rand(0, 3)
        ];
    }


    /**
     * makes sure every spot of ship is placed on emoty spots of board
     */
    public function isPlaced(Board $board, $params, Ship $ship):bool
    {
        $layoutClone = $board->getLayout();
          
        // initial spot - row, col
        if ($this->positionOutOfBoard($params)) {
            return false;
        };
        if ($this->positionIsNotEmpty($layoutClone, $params)) {
            return false;
        };
        $this->setPositionAsOccupied($layoutClone, $params, $ship);

        // next spots - row, col - according to ship length
        for ($i=0; $i < $ship->getLength() - 1; $i++) {
            list($params['row'], $params['column']) = $this->findNextPosition($params['row'], $params['column'], $params['direction']);
            
             // initial spot - row, col
            if ($this->positionOutOfBoard($params)) {
                return false;
            };

            if ($this->positionIsNotEmpty($layoutClone, $params)) {
                return false;
            };
            $this->setPositionAsOccupied($layoutClone, $params, $ship);
        }
        $board->update([
            'layout' => json_encode($layoutClone),
            'ship_spots' => $board->ship_spots + $ship->getLength(),
            'ships' => $board->ships + 1
        ]);
        return true;
    }

    public function findNextPosition(int $row, int $column, int $direction) : array
    {
        if ($direction === 0) {
            $row = $row - 1; // V - vertical
        }
        if ($direction === 1) {
            $column = $column + 1; // > - horizontal
        }
        if ($direction === 2) {
            $row = $row + 1; // ^ - vertical
        }
        if ($direction === 3) {
            $column = $column - 1; // t - horizontal
        }

          return [$row, $column];
    }

    public function positionOutOfBoard(array $params)
    {
        return $params['row'] > self::ROWS - 1 || $params['column'] > self::COLUMNS - 1 ||
            0 > $params['row'] || 0 > $params['column'];
    }

    public function positionIsNotEmpty(array $layout, array $params)
    {
        return $layout[$params['row']][$params['column']]['type'] !== self::POINT_EMPTY;
    }

    public function setPositionAsOccupied(array &$layout, array $params, Ship $ship)
    {
        $layout[$params['row']][$params['column']]['type'] = self::POINT_OCCUPIED;
        $layout[$params['row']][$params['column']]['data'] = [
            'ship_name' => $ship->getName(),
            'length' => $ship->getLength(),
            'direction' => $params['direction']
        ];
    }

    public function generateEmptyLayout() : array
    {
        $tempBoard = [];
        for ($row=0; $row < self::ROWS; $row++) {
            for ($column=0; $column < self::COLUMNS; $column++) {
                $tempBoard[$row][$column]['type'] = -1;
            }
        }
        return $tempBoard;
    }
}
