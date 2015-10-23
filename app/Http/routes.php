<?php

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
// Route::get('/meme_slam/{user_id}', '')
// Route::get('/', ['middleware' => 'auth', function() {$user = Auth::user; return view('home',compact('user'));}]);
// Route::get('home', ['middleware' => 'auth', function() {return view('home');}]);

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// Registration routes...
// Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');

//APIs for Dashboard

// Route::post('home/{mog_id}/toggle_bet', 'ActivatedMogsController@toggleBetStatus');