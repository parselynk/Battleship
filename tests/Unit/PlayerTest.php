<?php

namespace Tests\Unit;

use App\Player;
use Tests\TestCase;
use App\Repositories\PlayerRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlayerTest extends TestCase
{

    /**
     * @test
     */
    public function it_interprets_user_input()
    {
        $result = $this->repository()->processPlayerAttack('A1');
        $this->assertEquals(['y'=>0,'x'=>0], $result);

        $result = $this->repository()->processPlayerAttack('C7');
        $this->assertEquals(['y'=>2,'x'=>6], $result);

        $result = $this->repository()->processPlayerAttack('J10');
        $this->assertEquals(['y'=>9,'x'=>9], $result);
    }
     /**
     * @test
     */
    public function palyer_input_must_be_in_range()
    {
        $this->expectException(\RangeException::class);
        $this->repository()->processPlayerAttack('X0');
    }

    public function repository()
    {
        $player = \Mockery::mock(Player::class);
        return new PlayerRepository($player);
    }
}
