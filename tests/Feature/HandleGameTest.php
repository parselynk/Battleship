<?php

namespace Tests\Feature;

use App\Game;
use Tests\TestCase;
use Tests\Traits\GameTrait;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HandleGameTest extends TestCase
{
    
    use RefreshDatabase, withFaker, GameTrait;
    
    /**
     * @test
     */
    public function game_initiates()
    {
        $this->withoutExceptionHandling();
        $response = $this->post('/api/games', []);
        $content = $response->assertStatus(200)->assertJsonStructure(['id']);
    }
    
    /**
     * @test
     */
    public function game_create_an_empty_board()
    {
        $game = $this->initGame();

        $this->post('/api/boards', ['game_id'=> $game->id])->assertJsonStructure(['board_id', 'game_id'])->getContent();
        $boards = $game->boards;

        $this->assertCount(1, $boards);
        $layout = $this->toArray($boards[0]['layout']);

        $this->assertEquals(10, count($layout));
        $this->assertEquals(10, count($layout[0]));
        // //board only has -1
        $this->assertContains('-1', array_flatten($layout));
        $this->assertNotContains('y', array_flatten($layout));
        $this->assertNotContains('x', array_flatten($layout));
        $this->assertNotContains('-', array_flatten($layout));
    }

    /**
     * @test
     */
    public function a_game_cannot_have_more_than_2_boards()
    {
        $game = $this->initGame();

        $this->post('/api/boards', ['game_id'=> $game->id]);
        $this->post('/api/boards', ['game_id'=> $game->id]);
        $this->post('/api/boards', ['game_id'=> $game->id]);

        $this->assertDatabaseHas('boards', [
            'game_id' => $game->id
        ]);

        $this->assertCount(2, $game->boards);
    }

   /**
    * @test
    */
    public function game_only_can_start_when_has_2_boards()
    {
        //$this->withoutExceptionHandling();
        $game = $this->initGame();

        factory('App\Board')->create(['game_id'=>$game->id]);

        $this->assertNotTrue($game->isPlayable());

        factory('App\Board')->create(['game_id'=>$game->id]);

        $game = $this->updateGameQuery();

        $this->assertCount(2, $game->boards);
        $this->assertTrue($game->isPlayable());
    }

    /**
     * @test
     */
    public function board_can_have_a_ship()
    {
        //$this->withoutExceptionHandling();
        $game = $this->initGame();

        $response = $this->post('/api/boards', ['game_id'=> $game->id])->getContent();
        $boardId = $this->toArray($response, 'board_id');
        $board = factory('App\Board')->create(['game_id'=>$game->id]);
        

        $assignShip = $this->patch("/api/boards/{$boardId}", ['ship'=>'cruiser'])->assertJsonStructure(['game_id','board_id', 'layout'])->assertStatus(200)->getContent();

        $layoutArray = $this->toArray($this->toArray($assignShip, 'layout'));

        $this->assertContains('-1', array_flatten($layoutArray));
        $this->assertContains('y', array_flatten($layoutArray));
        $this->assertContains('Cruiser', array_flatten($layoutArray));
    }

        /**
     * @test
     */
    public function board_only_registers_valid_ships()
    {
        $this->withoutExceptionHandling();
        $game = $this->initGame();

        $response = $this->post('/api/boards', ['game_id'=> $game->id])->getContent();
        $boardId = $this->toArray($response, 'board_id');
        $board = factory('App\Board')->create(['game_id'=>$game->id]);

        $assignShip = $this->patch("/api/boards/{$boardId}", ['ship' => 'cruiserc'])->assertStatus(404)
            ->assertJsonStructure(['data','success']);
    }

    /**
     * @test
     */
    public function board_can_place_only_5_ships_on_boards()
    {
        //$this->withoutExceptionHandling();
        $game = $this->initGame();

        $this->post('/api/boards', ['game_id'=> $game->id]);
        $this->post('/api/boards', ['game_id'=> $game->id]);
        
        $game->boards->each(function ($board) {
            $this->assignAship($board->id, 'submarine');
            $this->assignAship($board->id, 'cruiser');
            $this->assignAship($board->id, 'battleship');
            $this->assignAship($board->id, 'patrol');
            $this->assignAship($board->id, 'carrier');
            $this->assignAship($board->id, 'carrier'); // ship six
        });
        $game = $this->updateGameQuery();
        $game->boards->each(function ($board) {
            $layoutArray = $this->toArray($board->layout);
            $layOutData = $this->getSpotsData($layoutArray);

            $ships = array_map(function ($data) {
                return $data['ship_name'];
            }, $layOutData);

            $this->assertEquals(15, $board->ship_spots); // total ships' length
            $this->assertEquals(5, $board->ships); // total ships cannot exceed 5

            $this->assertContains('Cruiser', $ships);
            $this->assertContains('Submarine', $ships);
            $this->assertContains('Battleship', $ships);
            $this->assertContains('Patrol', $ships);
            $this->assertContains('Carrier', $ships);
        });
    }

    /**
    * @test
    */
    public function each_board_has_a_player()
    {
        $this->withoutExceptionHandling();
        $game = $this->initAgameAndBoard();
         $game->boards->each(function ($board) {
            $this->assertInstanceOf('App\Player', $board->player);
         });
    }

    /**
     * @test
     */
    public function two_Tables_5_ships_game_can_be_started()
    {
        $this->withoutExceptionHandling();

        $game = $this->makeGameWithTwoRandomBoards();

        $gameData = $this->get($game->path())->assertStatus(200)
            ->assertJsonStructure(['id',
                'board_1' => [
                    'layout',
                    'remaining_spots',
                    'hit',
                    'player_id'
                ],
                'board_2' => [
                    'layout',
                    'remaining_spots',
                    'hit',
                    'player_id'
                ]
            ])->getContent();

        $board_1 = $this->toArray($gameData, 'board_1');
        $board_2 = $this->toArray($gameData, 'board_2');

        $this->assertNotEquals($board_1, $board_2);
    }
}
