<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    throw new NotFoundHttpException("no");
});


Route::fallback(function () {
    return response()->json(['message' => 'this URL is incorrect' , 'success'=> false], 404);
});


Route::post('/games/', 'GamesController@store');
Route::post('/boards', 'BoardsController@store');
Route::patch('/boards/{board}', 'BoardsController@update');
Route::get('/games/{game}', 'GamesController@show');
Route::patch('/games/{game}', 'GamesController@update');
