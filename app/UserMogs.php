<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class UserMogs extends Model 
{
	protected $table = 'UserMogs';
	protected $fillable = ['mog_ID', 'user_ID', 'on_bet'];
	// protected $hidden = [];

	public static function insertToUser($mogID, $userID) {
		DB::insert('
				INSERT 
				into UserMogs (mog_ID, user_ID)
				VALUES (:mogID, :userID)
				', 
				['mogID' => $mogID, 'userID' => $userID]
			);
	}
}