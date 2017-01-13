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

Route::get('/links/{teamSlug}', 'WebController@viewLinks');

//---- bot events route
Route::post('/links', 'BotController@receive');

//test route to test bot response
Route::get('/test', 'BotController@test');

/* Slack Authorization Routers */

Route::get('/Auth/add', 'AuthController@authorizeSlack');
Route::get('/Auth/signin', 'AuthController@redirectUsertoTeamLinks');
