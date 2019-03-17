# Battleship API

This is a prototype for Battleshit Game which presists the state of game and provide endpoints to initialize Games, generate boards, assign ships and players.

# About project

This API is developed using Laravel and mysql and TDD. Majority of system features and some of the important methods are tested. there are 2 database in this system, one for is an in memory sqlLite for testing and other one is mySql for actual system.


# To run Project

Enter the project folder and run following commands:

```sh
$ composer install
$ php artisan migrate
```
# Api endpoints
Endpoins are designed based on an assumed scenario which starts from initializing a game and assign boards and ships to it until palaying game and winning the game.


#### 1- Init a game

```sh
$ [POST] /api/games
```
#### 2- Init a board for the game

```sh
$ [POST] /api/boards
  params: [game_id {int}]
```
>Note that every game must have 2 boards

#### 3- Assign a ship to a board 

```sh
$ [PATCH] /api/boards/{board_id}
  params: [ship {string}]
```
> * only battleship,submarine,carrier,patrol,cruiser are accepted as ships
> * each board must have 5 ships
> * board cannot have more than 5 ships
> * one ship cannot be assigned twice to a single board


#### 4- Play game

```sh
$ [PATCH] /api/Games/{Game_id}
  params: [player_id {int}, hit_spot {string}]
```
> * hit_spot should match the format [A-J][1-10] to be accepted eg:A10
> * each player must play in turn and cannot play while the other player is playing
> * hit and miss are recorded as boolean
> * once a player wins, game considered as finshed and cannot be played anymore
> * after each attack game provides info about attack, game status, palyer ships spot and opponent info
