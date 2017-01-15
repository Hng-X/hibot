<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('home');
});

//---- bot events route
Route::post('/events', 'BotController@receive');

/* Slack Authorization Routers */

Route::get('/auth/add', 'AuthController@authorizeSlack');
Route::get('/auth/signin', 'AuthController@redirectUsertoTeamLinks');
