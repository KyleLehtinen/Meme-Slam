<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class PlayField extends Model
{
	protected $table = 'PlayField';
	protected $fillable = ['match_id', 'mog_id', 'owner_id', 'flipped', 'p1_flipped'];

	public static function loadGameMogs($match_id, $players_arr) {

		$player_mogs = [];
		$mog_ids = [];
		
		$player_mogs[] = User::getBettedMogs($players_arr['player1']);
		$player_mogs[] = User::getBettedMogs($players_arr['player2']);
		
		for($i = 0; $i < count($player_mogs); $i++){
			foreach($player_mogs[$i] as $mogs) {
				DB::table('PlayField')->insert(['match_id'=>$match_id, 'mog_id'=>$mogs->active_id]);
				// echo "Mog $mogs->active_id inserted! <br>";
			}
		}

		return true;
	}
}