<?php

namespace App\Repositories;

use App\Game;
use App\Ship;
use App\Board;
use App\Player;
use App\Repositories\Contracts\GameInterface;
use App\Repositories\Contracts\BoardInterface;
use App\Repositories\Contracts\PlayerInterface;

class GameRepository implements GameInterface
{
    protected $game;
    protected $board;
    protected $player;

    const POINT_EMPTY = -1;
    const POINT_OCCUPIED = 'y';
    const PLAYING_STATUS = 'playing';
    const FINISHED_STATUS = 'finished';
    const INITIATED_STATUS = 'initiated';

    public function __construct(BoardInterface $board, PlayerInterface $player)
    {
        $this->board = $board;
        $this->player = $player;
    }

    public function init(): Game
    {
        $this->setGame();
        return $this->game;
    }

    public function initBoards()
    {
        $this->board->setEmptyBoards($this->game);
    }

    protected function updateGameState(Board $opponentBoard) : string
    {
        if ($this->player->isWinner($opponentBoard)) {
            $opponentBoard->game->update(['status' => self::FINISHED_STATUS]);
        }
         return $opponentBoard->game->status;
    }

    public function setGameStateAsPlaying(Game $game)
    {
        $game->update(['status' => self::PLAYING_STATUS]);
    }

    public function setGame()
    {
        $this->game = Game::create(['status' => self::INITIATED_STATUS]);

        return $this->game;
    }

    public function getAttackData(Game $game, int $currentPlayerId) : array
    {
        $currentPlayer = $this->player->getCurrentPlayer($game, $currentPlayerId);
        $opponentBoard = $this->board->getOpponentBoard($game, $currentPlayerId);
        return  ['currentPlayer' => $currentPlayer,'opponentBoard' => $opponentBoard];
    }

    /**
     * check if game has 2 boards and each board has 5 ships
     * @param  Collection $boards
     */
    public function boardsAreSet($boards)
    {
        if ($boards->count() < 2) {
            return false;
        }
        $boards->each(function ($board) {
            if ($board->ships < 5) {
                return false;
            }
        });
        return true;
    }

    /**
     * get Game state for wating player
     */
    public function getState(Game $game, $currentPlayerId)
    {
        $currentPlayer = $this->player->getCurrentPlayer($game, $currentPlayerId);

        return ['game_status' => $game->status,
                'player_state' => $currentPlayer->state,
                'last_hit_spot' => $currentPlayer->last_move,
                'hit' => (bool)$currentPlayer->last_result,
                'game_id' => $game->id,
                'player_id' => $currentPlayerId,
                'attack_performed' => false
            ];
    }

    public function setPlayerStateAsPlaying(Player $player)
    {
        $this->player->updateState($player, ['state' => 'playing']);
    }

    public function isAllowedToPlay(Game $game, $currentPlayerId)
    {
        return $game->status == self::PLAYING_STATUS
            && $this->player->isAllowedToPlay($game, $currentPlayerId);
    }

    public function performAttack(string $hit, array $attackData) : array
    {
        $opponentBoard = $attackData['opponentBoard'];
        $player = $attackData['currentPlayer'];
        //get attack info
        $processedHit = $this->player->processPlayerAttack($hit);
        $hitSpot = $this->board->findBoardSpot($opponentBoard->layout, $processedHit);
        $isHit = $this->board->isHit($hitSpot);
        //update board and get current state of player
        $opponentBoard = $this->board->updateSpot($opponentBoard, $processedHit, $isHit);
        $curentPlayerState = $this->player->getState($player, $opponentBoard);
        //update player, game and opponent state accordingly
        $playerData = ['last_result' => $isHit, 'last_move' => $hit, 'state' => $curentPlayerState];
        $player = $this->player->updateStateAfterAttack($player, $playerData);
        $opponentPlayer = $this->player->updateOponentPlayerState($opponentBoard->player, $curentPlayerState);
        $gameStatus = $this->updateGameState($opponentBoard);
        $playerShips = $this->player->getShips($player->board->layout);

        return [ 'hit' => (bool)$isHit,
                 'last_hit_spot' => $hit,
                 'player_state' => $player->state,
                 'attack_performed' => true,
                 'game_status' => $gameStatus,
                 'player_ships' => $playerShips,
                 'opponent_data' => [
                    'last_hit_spot' => $opponentPlayer->last_move,
                    'player_state' => $opponentPlayer->state ,
                    'hit' => (bool)$opponentPlayer->last_result
                    ]
                ];
    }
}
