<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\GameTrait;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PerformAttackTest extends TestCase
{
    
    use RefreshDatabase, withFaker, GameTrait;

    /**
     * @test
     */
    public function player_performs_attack()
    {
        $this->withoutExceptionHandling();
        $game = $this->makeGameWithTwoRandomBoards();

        $response = $this->patch("/api/games/{$game->id}", ['player_id'=> 2,'hit_spot' => 'A1'])->assertStatus(200)->assertJsonStructure(['hit',
                    'last_hit_spot' ,
                    'player_state',
                    'game_id',
                    'player_id',
                    'game_status',
                    'attack_performed',
                    'player_ships',
                    'opponent_data' => [
                        'last_hit_spot' ,
                        'player_state'  ,
                        'hit'
                    ]
                ])->getContent();
    }

     /**
     * @test
     */
    public function players_must_play_in_turn()
    {
        $this->withoutExceptionHandling();
        $game = $this->makeGameWithTwoRandomBoards();

        $playerOneFirstAttack = $this->performAttack($game->id, 2, 'A1')->assertStatus(200)->getContent();
         $playerOneSecondAttackImmediately = $this->performAttack($game->id, 2, 'J3')->assertStatus(200)->assertJsonStructure(['game_status',
                'player_state',
                'last_hit_spot',
                'hit',
                'game_id',
                'player_id' ,
                'attack_performed'])->getContent();
         

          $playerTwoFirstAttack = $this->performAttack($game->id, 1, 'J3')->assertStatus(200)->getContent();
          $playerOneAttacksAfterPlayerTwo = $this->performAttack($game->id, 2, 'G5')->assertStatus(200)->getContent();

         $this->assertTrue($this->toArray($playerOneFirstAttack)['attack_performed']);
         $this->assertFalse($this->toArray($playerOneSecondAttackImmediately)['attack_performed']);
         $this->assertTrue($this->toArray($playerTwoFirstAttack)['attack_performed']);
        $this->assertTrue($this->toArray($playerOneAttacksAfterPlayerTwo)['attack_performed']);
    }

     /**
     * @test
     */
    public function player_wins_if_all_ship_spots_are_hit()
    {
        $this->withoutExceptionHandling();
        $game = $this->makeGameWithTwoRandomBoards();

        $this->fakeWin($game);

        $playerOneFirstAttack = $this->performAttack($game->id, 2, 'A1')->assertStatus(200)->getContent();

        $this->assertEquals('winner', $this->toArray($playerOneFirstAttack)['player_state']);
    }

    /**
     * @test
     */
    public function game_has_a_looser_at_the_end()
    {
        //$this->withoutExceptionHandling();
        $game = $this->makeGameWithTwoRandomBoards();

        $this->fakeWin($game);
        
        $winnerAttack = $this->performAttack($game->id, 2, 'A1')->assertStatus(200)->getContent();
        $playerTwoAttack = $this->performAttack($game->id, 1, 'J3')->assertStatus(200)->getContent();

        $this->assertEquals('looser', $this->toArray($playerTwoAttack)['player_state']);
    }

    /**
     * @test
     */
    public function players_cannot_play_anymore_once_game_is_finished()
    {
        //$this->withoutExceptionHandling();
        $game = $this->makeGameWithTwoRandomBoards();
        
        $this->fakeWin($game);

        $playerOneFirstAttack = $this->performAttack($game->id, 2, 'A1')->assertStatus(200)->getContent();
        $playerTwoAttack = $this->performAttack($game->id, 1, 'J3')->assertStatus(200)->getContent();
        $playerOneSecondAttack = $this->performAttack($game->id, 2, 'A1')->assertStatus(200)->getContent();

        $this->assertEquals('winner', $this->toArray($playerOneFirstAttack)['player_state']);
        $this->assertFalse($this->toArray($playerTwoAttack)['attack_performed']);
        $this->assertFalse($this->toArray($playerOneSecondAttack)['attack_performed']);
    }


    /**
     * @test
     */
    public function player_can_hit_a_ship()
    {
        //$this->withoutExceptionHandling();
        $game = $this->makeGameWithTwoRandomBoards();

        $shipSpots = $this->getShipSpots($game->boards[0]->layout);


        $playerFirstAttack = $this->performAttack($game->id, 2, $shipSpots[0])->assertStatus(200)->getContent();

        $this->assertTrue($this->toArray($playerFirstAttack)['hit']);
    }

    /**
     * @test
     */
    public function a_full_hame_can_be_palyed_by_players()
    {
        //$this->withoutExceptionHandling();
        $game = $this->makeGameWithTwoRandomBoards();

        $shipSpots = $this->getShipSpots($game->boards[0]->layout);

        foreach ($shipSpots as $shipSpot) {
            $playerAttack = $this->performAttack($game->id, 2, $shipSpot)->getContent();
            $this->performAttack($game->id, 1, $shipSpot);
        }
            $this->assertEquals('winner', $this->toArray($playerAttack)['player_state']);
            $secondPlayerAttack = $this->performAttack($game->id, 1, $shipSpot)->getContent();
            $this->assertEquals('looser', $this->toArray($secondPlayerAttack)['player_state']);
    }
}
