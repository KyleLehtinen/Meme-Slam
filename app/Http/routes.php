<?php

ini_set('display_errors', 1); 
error_reporting(E_ALL);

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('/auth/login');
});

//routes authenticated users to dashboard
Route::get('/', ['middleware' => 'auth', 'uses' => 'UserController@getUser']);
Route::get('home', ['middleware' => 'auth', 'uses' => 'UserController@getUser']);

//routes to MemeSlam page
Route::get('/meme_slam/{user_id}', ['middleware' => 'auth', 'uses' => 'MemeSlamController@preInitialize']);

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// Registration routes...
Route::post('auth/register', 'Auth\AuthController@postRegister');
// Route::get('auth/register', 'Auth\AuthController@getRegister'); <Login page handles registration


//APIs for Dashboard
Route::post('/api/update_bet_status', ['middleware' => 'auth', 'uses' => 'ActivatedMogsController@updateBetStatus']);
Route::post('/api/search_for_match', ['middleware' => 'auth', 'uses' => 'MemeSlamController@searchForMatch']);
Route::post('/api/player_accepts', ['middleware' => 'auth', 'uses' => 'MemeSlamController@playerAcceptMatch']);

Route::get('/api/check_opponent_joined/{match_id}', ['middleware' => 'auth', 'uses' => 'MemeSlamController@checkP2Joined']);