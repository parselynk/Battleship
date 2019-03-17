<?php

use Faker\Generator as Faker;

$tempBoard = array_map(function ($row) {
            return [$row] = array_map(function ($column) {
                    return [$index] = -1;
            }, range(1, 10));
}, range(1, 10));

$factory->define(App\Board::class, function (Faker $faker) use ($tempBoard) {
    return [
        'layout' => json_encode($tempBoard),
        'game_id' => 0
    ];
});
