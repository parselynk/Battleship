<?php
namespace Tests\Traits;

use App\Game;

trait GameTrait
{
    public function initGame()
    {
        return factory('App\Game')->create();
    }

    public function setEmptyBoard($game_id)
    {
        return $this->post('/api/boards', ['game_id'=> $game->id]);
    }

    public function initAgameAndBoard()
    {
        $game = $this->initGame();
        $this->post('/api/boards', ['game_id'=> $game->id]);
        return $game;
    }

    public function toArray(string $json, $key = null)
    {
         $array = json_decode($json, true);
        if (!empty($key) && array_key_exists($key, $array)) {
            return $array[$key];
        }

         return $array;
    }

    public function assignAship(int $boardId, string $ship)
    {
        return $this->patch("/api/boards/{$boardId}", ['ship'=>$ship]);
    }

    public function updateGameQuery()
    {
        return Game::latest()->first();
    }

    public function printBoard($board)
    {
        echo nl2br("\n");
        for ($i=0; $i < count($board); $i++) {
            $row = '';
            for ($j=0; $j < 10; $j++) {
                $value = $board[$i][$j]['type'] != -1 ? " {$board[$i][$j]['type']}" : -1;
                $row .= "$value ";
            }
            echo nl2br("{$row}\n");
        }
    }

    public function printBoardData($board)
    {
        echo nl2br("\n");
        for ($i=0; $i < count($board); $i++) {
            $row = '';
            for ($j=0; $j < 10; $j++) {
                $value = isset($board[$i][$j]['data']) ? " |{$board[$i][$j]['data']['ship_name']}-L:{$board[$i][$j]['data']['length']}-D:{$board[$i][$j]['data']['direction']}" : $j+1;
                $row .= "$value ";
            }
            echo nl2br("{$row}\n");
        }
    }

    public function getSpotsData(array $boardLayout)
    {
         $data = [];
        for ($i=0; $i < count($boardLayout); $i++) {
            for ($j=0; $j < 10; $j++) {
                if (isset($boardLayout[$i][$j]['data'])) {
                    $data["$i-$j"] = $boardLayout[$i][$j]['data'];
                }
            }
        }

        return $data;
    }

    public function fakeWin(Game $game)
    {
        return $game->boards[0]->update(['ship_spots' => 5, 'hit' => 5]);
    }

    public function getSpotsType(array $boardLayout)
    {
         $type = [];
        for ($i=0; $i < count($boardLayout); $i++) {
            for ($j=0; $j < 10; $j++) {
                if (isset($boardLayout[$i][$j]['type'])) {
                    $type["$i-$j"] = $boardLayout[$i][$j]['type'];
                }
            }
        }

        return $type;
    }

    public function performAttack(int $gameId, int $palyerId, string $hitSpot)
    {
        return $this->patch("/api/games/{$gameId}", ['player_id'=> $palyerId,'hit_spot' => $hitSpot]);
    }

    public function getShipSpots(string $layout)
    {
        $layoutArray = $this->toArray($layout);
        $shipsSpot = $this->getSpotsData($layoutArray);

        return $this->getShipSpotsPositions(array_keys($shipsSpot));
    }

    protected function getShipSpotsPositions(array $shipSpots)
    {
        $spots = [];
        foreach ($shipSpots as $spot) {
            $spotArray = explode('-', $spot);
            $spots[] = $this->prapareSpotsForPlayer($spotArray);
        }
        return $spots;
    }

    protected function prapareSpotsForPlayer(array $spot)
    {
        $columns = ['A','B','C','D','E','F','G','H','I','J'];

        return "{$columns[$spot[1]]}" . (int)($spot[0] + 1);
    }

    public function makeGameWithTwoRandomBoards()
    {
        $game = $this->initGame();
        $this->post('/api/boards', ['game_id'=> $game->id]);
        $this->post('/api/boards', ['game_id'=> $game->id]);
        
        $game->boards->each(function ($board) {
            $this->assignAship($board->id, 'submarine');
            $this->assignAship($board->id, 'cruiser');
            $this->assignAship($board->id, 'battleship');
            $this->assignAship($board->id, 'patrol');
            $this->assignAship($board->id, 'carrier');
        });

        return $this->updateGameQuery();
    }
}
