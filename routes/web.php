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

Route::get('/links/{teamSlug}', 'WebController@viewLinks');


//test route to test bot response
Route::get('/test', 'BotController@test');

/* Slack Authorization Routers */

Route::get('/auth/add', 'AuthController@authorizeSlack');
Route::get('/auth/signin', 'AuthController@redirectUsertoTeamLinks');
