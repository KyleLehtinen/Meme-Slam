<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class PlayField extends Model
{
	protected $table = 'PlayField';
	protected $fillable = ['match_id','mog_id','flipped','flipped_by','owner_id','new_owner_id','show_animation'];

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

	//get count of active mogs for a given match_id
	public static function getActiveMogs($match_id) {

		$rows = DB::table('PlayField')
						->where('match_id', '=', $match_id)
						->where('flipped', '=', '0')->get();

		$result = $rows;

		return $result;
	}

	//Returns mogs tied to the match
	public static function getUsersBettedMogs($match_id,$owner_id) {

		$mogs = DB::select('
						SELECT pm.mog_id as active_id, pm.owner_id as owner_id, mm.id, mm.name, mm.rating, mm.src_url, mm.rating
	                    FROM MogMaster as mm
	                    RIGHT JOIN ActivatedMogs as am
	                    ON mm.id = am.mog_id
	                    RIGHT JOIN PlayField as pm
	                    ON am.id = pm.mog_id
	                    WHERE pm.match_id = :match_id and pm.owner_id = :owner_id and flipped = 0
					',['match_id' => $match_id, 'owner_id' => $owner_id]);

		return $mogs;
	}

	//Returns captured mogs tied to the match
	public static function getUsersCapturedMogs($match_id,$owner_id) {

		$mogs = DB::select('
						SELECT pm.mog_id as active_id, pm.owner_id as owner_id, mm.id, mm.name, mm.src_url, mm.rating
	                    FROM MogMaster as mm
	                    RIGHT JOIN ActivatedMogs as am
	                    ON mm.id = am.mog_id
	                    RIGHT JOIN PlayField as pm
	                    ON am.id = pm.mog_id
	                    WHERE pm.match_id = :match_id and pm.flipped = 1 and pm.new_owner_id = :owner_id
					',['match_id' => $match_id, 'owner_id' => $owner_id]);

		return $mogs;
	}

	//Get mogs flipped from the last round
	public static function getResultsAnimationMogs($match_id) {

		$mogs = DB::select('
						SELECT pm.mog_id as active_id, pm.owner_id as owner_id, mm.id, mm.name, mm.src_url, mm.rating
	                    FROM MogMaster as mm
	                    RIGHT JOIN ActivatedMogs as am
	                    ON mm.id = am.mog_id
	                    RIGHT JOIN PlayField as pm
	                    ON am.id = pm.mog_id
	                    WHERE pm.match_id = :match_id and pm.flipped = 1 and pm.show_animation = 1
					',['match_id' => $match_id]);

		return $mogs;
	}

	//Logic that flips mogs on the table
	public static function flipMogs($match_id, $active_player_id, $count) {

		//arrays for id's to be flipped and active ids for owner update
		$flip_ids = [];
		$active_ids = [];

		//Get the mogs
		$mogs = PlayField::getActiveMogs($match_id);

		//Build array of random indexes for flip
		for($i = 0; $i < $count; $i++) {
			$flipIdx = rand(0, count((array) $mogs) - 1);
			$flip_ids[] = $mogs[$flipIdx]->id;
			array_splice($mogs, $flipIdx, 1);
		}

		//flip them
		DB::table('PlayField')->where('match_id', '=', $match_id)
							  ->where('flipped', '=', 0)
						      ->whereIn('id', $flip_ids)->update(['flipped' => 1,
						      									  'new_owner_id' => $active_player_id,
						      									  'show_animation' => 1]);
		//Get flipped Mogs active id for update
		$flipped_mogs = DB::table('PlayField')->whereIn('id', $flip_ids)->get();

		//build array of active ids from flipped mogs
		foreach($flipped_mogs as $mog) {
			echo "<br>PlayField ID: " .$mog->id.", Active ID: ".$mog->mog_id;
			$active_ids[] = $mog->mog_id;
		}

		//update Activated Mog's owner
		ActivatedMogs::updateOwner($active_ids, $active_player_id);
	}
}