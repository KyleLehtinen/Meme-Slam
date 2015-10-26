<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Matches extends Model
{
	protected $table = 'Matches';
	protected $fillable = ['players_matched','p1_turn_roll','p2_turn_roll','in_progress',
							'p1_id','p1_bet_rating','p1_accept',
							'p2_id','p2_bet_rating','p2_accept',
							'p1_turn','match_complete','p1_new_mogs','p2_new_mogs'];

	//logic that searches for a match, if none found calls createMatch
	//This is a flawed approach to match making and will need to be updated should meme slam
	//be deployed publicly
	public static function searchMatch($player_id, $player_bet_rating) {

		$response = [];

		$br_upper = $player_bet_rating + 300;
		$br_lower = $player_bet_rating - 300;

		//query for available matches within the bet range of the player
		$matches = DB::select('
							SELECT id
							FROM Matches
							WHERE players_matched = 0 and
								  p1_id != :player_id and
								  p1_bet_rating >= :br_lower and
								  p1_bet_rating <= :br_upper
						',['player_id' => $player_id,'br_lower' => $br_lower, 'br_upper' => $br_upper]);

		// print_r($matches);

		//if no match is found call create, else add player to an existing match as player2
		if(empty($matches)) {
			$result = Matches::createMatch($player_id, $player_bet_rating);

			if(!$result) {
				die("Error: Could not create a new match record");
			} else {
				$response['matchFound'] = false;
				$response['matchID'] = $result;
			}
			
		} else {
			$match_id = $matches[rand(0,count($matches) - 1)]->id;

			$result = Matches::joinMatch($player_id, $player_bet_rating, $match_id);

			if(!$result) {
				die("Error: Could not join the match...");
			} else {
				$response['matchFound'] = true;
				$response['matchID'] = $match_id;
			}
		}

		return $response;
	}

	//Creates a new match
	public static function createMatch($player_id, $player_bet_rating) {

		DB::insert('
				INSERT 
				INTO Matches
					(p1_id, p1_bet_rating)
				VALUES 
					(:player_id, :player_bet_rating)
			', ['player_id'=>$player_id, 'player_bet_rating'=>$player_bet_rating]);
		
		$result = DB::getPdo()->lastInsertId();

		return $result;
	}

	//Joins player to an existing match
	public static function joinMatch($player_id, $player_bet_rating, $match_id) {
		
		DB::update('
				UPDATE Matches
				SET p2_id = :player_id,
					p2_bet_rating = :player_bet_rating
				WHERE id = :match_id
			',['player_id' => $player_id, 'player_bet_rating' => $player_bet_rating, 'match_id' => $match_id]);
		
		$result = $match_id;

		return $result;
	}
}