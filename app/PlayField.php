<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class PlayField extends Model
{
	protected $table = 'PlayField';
	protected $fillable = ['match_id','mog_id','flipped','flipped_by','owner_id','new_owner_id'];

	public static function loadGameMogs($match_id, $players_arr) {

		$player_mogs = [];
		$mog_ids = [];
		
		$player_mogs[] = User::getBettedMogs($players_arr['player1']);
		$player_mogs[] = User::getBettedMogs($players_arr['player2']);
		
		for($i = 0; $i < count($player_mogs); $i++){
			foreach($player_mogs[$i] as $mogs) {
				DB::table('PlayField')->insert(['match_id'=>$match_id, 'mog_id'=>$mogs->active_id, 'owner_id'=>$mogs->owner_id]);
				// echo "Mog $mogs->active_id inserted! <br>";
			}
		}

		return true;
	}

	//Returns mogs tied to the match
	public static function getUsersBettedMogs($match_id,$owner_id) {

		$mogs = DB::select('
						SELECT pm.mog_id as active_id, pm.owner_id as owner_id, mm.id, mm.name, mm.src_url, mm.rating
	                    FROM MogMaster as mm
	                    RIGHT JOIN PlayField as pm
	                    ON mm.id = pm.mog_id
	                    WHERE pm.match_id = :match_id and pm.owner_id = :owner_id and flipped = 0
					',['match_id' => $match_id, 'owner_id' => $owner_id]);

		return $mogs;
	}

	public static function getUsersCapturedMogs($match_id,$owner_id) {

		$mogs = DB::select('
						SELECT pm.mog_id as active_id, pm.owner_id as owner_id, mm.id, mm.name, mm.src_url, mm.rating
	                    FROM MogMaster as mm
	                    RIGHT JOIN PlayField as pm
	                    ON mm.id = pm.mog_id
	                    WHERE pm.match_id = :match_id and flipped = 1 and pm.new_owner_id = :owner_id
					',['match_id' => $match_id, 'owner_id' => $owner_id]);

		return $mogs;
	}

	// //returns array of mog ids belonging to the given match and user ids
	// public static function getCapturedMogIDs($match_id, $user_id) {

	// 	$result = [];

	// 	$rows = DB::table('PlayField')
	// 							->where('match_id', '=', $match_id)
	// 							->where('flipped', '=', 1)
	// 							->where('flipped_by', '=', $user_id)
	// 							->get();

	// 	if(!empty($rows)) {
	// 		foreach($rows as $row) {
	// 			$result[] = $row->mog_id;
	// 		}
	// 	}
		
	// 	return $result;
	// }
}