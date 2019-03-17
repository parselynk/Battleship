<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GameTest extends TestCase
{
    use RefreshDatabase, withFaker;
    
    /**
     * @test
     */
    public function it_has_a_path()
    {
        $game = factory('App\Game')->create();
        $this->assertEquals("/api/games/{$game->id}", $game->path());
    }
}
