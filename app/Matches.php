<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Matches extends Model
{
	protected $table = 'Matches';
	protected $fillable = ['p1_id','p1_accept','p1_turnroll','p2_id','p2_accept','p2_turnroll', 'p1_turn', 'in_progress','p1_new_mogs','p2_new_mogs'];

	//logic that searches for a match, if none found calls createMatch
	//This is a flawed approach to match making and will need to be updated should meme slam
	//be deployed publicly
	public static function searchMatch($player_id) {
		
		//get the player's bp rating
		$player_rating = ActivatedMogs::getBetRating($player_id);

		//query for available matches within the bet range of the player
		$matches = DB::select('
							SELECT id
							FROM Matches
							WHERE p1_bet_rating >= (:player_rating - 300) and
								  p1_bet_rating <= (:player_rating + 300)
						',['player_rating' => $player_rating]);

		//if no match is found call create, else add player to an existing match as player2
		if(empty($matches)) {
			createMatch($player_id);
		} else {
			$match_id = $matches[rand(0,count($matches) - 1)];

			joinMatch($player_id, $match_id);

		}
	}

	//Creates a new match
	public static function createMatch($player_id) {

	}

	//Joins player to an existing match
	public static function joinMatch($player_id, $match_id) {

	}
}