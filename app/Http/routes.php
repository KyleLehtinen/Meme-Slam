<?php

ini_set('display_errors', 1); 
error_reporting(E_ALL);

Route::get('/', function () {
    return view('/auth/login');
});

//routes authenticated users to dashboard
Route::get('/', ['middleware' => 'auth', 'uses' => 'UserController@getUser']);
Route::get('home', ['middleware' => 'auth', 'uses' => 'UserController@getUser']);

//routes to MemeSlam page
Route::get('/meme_slam/{user_id}', ['middleware' => 'auth', 'uses' => 'MemeSlamController@initialize']);

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// Registration routes...
Route::post('auth/register', 'Auth\AuthController@postRegister');

//APIs for Dashboard
Route::post('/api/update_bet_status', ['middleware' => 'auth', 'uses' => 'ActivatedMogsController@updateBetStatus']);
Route::post('/api/search_for_match', ['middleware' => 'auth', 'uses' => 'MemeSlamController@searchForMatch']);
Route::post('/api/player_accepts_match', ['middleware' => 'auth', 'uses' => 'MemeSlamController@playerAcceptsMatch']);
Route::post('/api/player_drop_match', ['middleware' => 'auth', 'uses' => 'MemeSlamController@playerDropMatch']);
Route::post('/api/drop_match', ['middleware' => 'auth', 'uses' => 'MemeSlamController@dropMatch']);
Route::post('/api/update_match_state', ['middleware' => 'auth', 'uses' => 'MemeSlamController@updateMatchState']);

Route::get('/api/get_background_mogs', 'MemeSlamController@getBackgroundMogs');
Route::get('/api/initialize_match/{match_id}', ['middleware' => 'auth', 'uses' => 'MemeSlamController@initializeMatch']);
Route::get('/api/check_opponent_joined/{match_id}', ['middleware' => 'auth', 'uses' => 'MemeSlamController@checkP2Joined']);
Route::get('/api/check_players_accepted/{match_id}', ['middleware' => 'auth', 'uses' => 'MemeSlamController@checkPlayersAcceptedMatch']);
Route::get('/api/get_match_players/{match_id}/{requestor}', ['middleware' => 'auth', 'uses' => 'MemeSlamController@getOpponentDetails']);
Route::get('/api/check_for_update/{match_id}/{last_update}', ['middleware' => 'auth', 'uses' => 'MemeSlamController@checkForUpdate']);
Route::get('/api/get_game_state/{match_id}/{user_id}', ['middleware' => 'auth', 'uses' => 'MemeSlamController@getGameState']);
Route::get('/api/check_for_active_match/{user_id}', ['middleware' => 'auth', 'uses' => 'MemeSlamController@checkForActiveMatch']);
Route::get('/api/check_players_viewed_round_results/{match_id}', ['middleware' => 'auth', 'uses' => 'MemeSlamController@checkPlayersViewedRoundResults']);
Route::get('/api/get_game_over_detail/{match_id}',['middleware' => 'auth', 'uses' => 'MemeSlamController@getGameOverDetail']);