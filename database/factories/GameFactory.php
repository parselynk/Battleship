<?php

use Faker\Generator as Faker;

$factory->define(App\Game::class, function (Faker $faker) {
    return [
        'status' => 'initiated'
    ];
});

// $factory->afterCreating(App\Game::class, function ($game, $faker) {
//     $game->boards()->save(factory(App\Board::class)->make())
//          ->player()->save(factory(App\Player::class)->make());
//     $game->boards()->save(factory(App\Board::class)->make())
//          ->player()->save(factory(App\Player::class)->make());
// });
