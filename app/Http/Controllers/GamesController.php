<?php

namespace App\Http\Controllers;

use App\Game;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Repositories\Contracts\GameInterface;

class GamesController extends BaseController
{
    const BOARD_1 = 0;
    const BOARD_2 = 1;

    protected $game;

    public function __construct(GameInterface $game)
    {
        $this->game = $game;
    }

    public function store()
    {
        $game = $this->game->init();
        return response()->json([
                'id' => $game->id,
        ]);
    }

    public function update(Game $game)
    {
        $rules = [ 'hit_spot' => ['regex:/^[A-Z]{1}([1-9]|10){1}$/' , 'required'],
                    'player_id' => 'required|integer|in:1,2'];
        $messages = ['player_id.required' => 'player_id is not provided', 'hit_spot.regex' => 'hit should be matching [A-J][1-10] eg A2' ];

        if ($validate = $this->returnErrorMessageIfNotValid($this->validateParams($rules, $messages))) {
            return $validate;
        }

        if (!$this->game->isAllowedToPlay($game, request('player_id'))) {
            return response()->json($this->game->getState($game, request('player_id')));
        }

        $attackData = $this->game->getAttackData($game, request('player_id'));

        $data = $this->game->performAttack(request('hit_spot'), $attackData);

        $data['game_id'] = $game->id;
        $data['player_id'] = request('player_id');

        return response()->json($data);
    }

    public function show(Game $game)
    {
        if (!$game->isPlayable() && !$this->game->boardsAreSet($game->boards)) {
            return response()->json([
                'game_id' => $game->id,
                'game_status' => $game->status
            ]);
        }
        return response()->json($this->getGameData($game));
    }

    protected function getGameData(Game $game)
    {
        return [
            'id' => $game->id,
            'game_status' => $game->status,
            'board_1' =>  [
                'layout' => $game->boards[self::BOARD_1]->getLayout(),
                'remaining_spots' => $game->boards[self::BOARD_1]->ship_spots,
                'hit' => $game->boards[self::BOARD_1]->hit,
                'player_id' => $game->boards[self::BOARD_1]->player->id,
                'player_state' => $game->boards[self::BOARD_1]->player->state
            ],
                'board_2' => [
                'layout' => $game->boards[self::BOARD_2]->getLayout(),
                'remaining_spots' => $game->boards[self::BOARD_2]->ship_spots,
                'hit' => $game->boards[self::BOARD_2]->hit,
                'player_id' => $game->boards[self::BOARD_2]->player->id,
                'player_state' => $game->boards[self::BOARD_2]->player->state
                ]];
    }
}
