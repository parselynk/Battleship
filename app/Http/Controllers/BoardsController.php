<?php

namespace App\Http\Controllers;

use App\Game;
use App\Board;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Repositories\Contracts\GameInterface;
use App\Repositories\Contracts\BoardInterface;
use App\Repositories\Contracts\PlayerInterface;

class BoardsController extends BaseController
{
    
    const BOARD_1 = 0;
    const BOARD_2 = 1;
    
    protected $game;
    protected $board;

    public function __construct(GameInterface $game, BoardInterface $board)
    {
        $this->game = $game;
        $this->board = $board;
    }

    public function store(Game $game)
    {
        $rules = [ 'game_id' => 'required|integer'];
        $messages = ['game_id.required' => 'game_id is not provided' ];

        if ($validate = $this->returnErrorMessageIfNotValid($this->validateParams($rules, $messages))) {
            return $validate;
        }

        if ($game->find(request('game_id'))->boards->count() > 1) {
            return response()->json([
                'message' => 'game has 2 boards already',
                'success' => false
            ], 403);
        }

        
        $game = $game->find(request('game_id'));
        $board = $this->board->setAnEmptyBoard($game);
        return response()->json([
            'game_id' => $game->id,
            'board_id' => $board->id
        ], 200);
    }

    public function update(Board $board)
    {
        $rules = [ 'ship' => 'required|in:battleship,submarine,carrier,patrol,cruiser'];
        $messages = ['ship.required' => 'no ship is provided',
        'ship.in' , 'provided ship is invalid'];


        if ($validate = $this->returnErrorMessageIfNotValid($this->validateParams($rules, $messages))) {
            return $validate;
        }

        if ($board->ships == 5) {
             return response()->json(['message' => "This board is fully set, no more ships are allowed." , 'success'=> false], 403);
        }

        if ($this->board->shipExist($board->layout, request('ship'))) {
            return response()->json(['message' => "{request('ship')} already placed on this board" , 'success'=> false], 403);
        }

        $this->board->placeShip($board, request('ship'));
       
        $responseData = [
            'game_id' => $board->game_id,
            'board_id' => $board->id,
            'layout' => $board->layout
        ];
         
        if ($board->game->isPlayable() && $this->game->boardsAreSet($board->game->boards)) {
            $this->game->setGameStateAsPlaying($board->game);
            $this->game->setPlayerStateAsPlaying($board->player);
            $responseData['game_status'] = $board->game->status;
            $responseData['game_url'] = $board->game->path();
        }

        return response()->json($responseData, 200);
    }
}
