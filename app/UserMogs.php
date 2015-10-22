<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class UserMogs extends Model 
{
	protected $table = 'UserMogs';
	protected $fillable = ['mog_ID', 'user_ID', 'on_bet'];
	// protected $hidden = [];

	
}