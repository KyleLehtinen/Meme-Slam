<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Matches extends Model
{
	protected $table = 'Matches';
	protected $fillable = ['players_matched','in_progress',
							'p1_id','p1_bet_rating','p1_accept',
							'p2_id','p2_bet_rating','p2_accept',
							'p1_turn','match_complete','p1_new_mogs','p2_new_mogs'];

	//logic that searches for a match, if none found calls createMatch
	//This is a flawed approach to match making and will need to be updated should meme slam
	//be deployed publicly

	public function initializeGame() {

		Matches::chooseFirstTurn($this->id);
		$match_players = $this->getMatchPlayers();
		PlayField::loadGameMogs($this->id, $match_players);

		return true;
	}

	public function getMatchPlayers() {

		$result = [];

		$row = DB::select('
						SELECT p1_id, p2_id
						FROM Matches
						WHERE id = :match_id 
					',['match_id' => $this->id]);

		$result['player1'] = $row[0]->p1_id;
		$result['player2'] = $row[0]->p2_id;

		return $result;
	}


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
						',['player_id'=>$player_id,'br_lower'=>$br_lower, 'br_upper'=>$br_upper]);

		// print_r($matches);
		// echo '<br> PlayerID: ' .$player_id;
		// echo '<br> Bet Rating: '. $player_bet_rating

		//if no match is found call create, else add player to an existing match as player2
		if(empty($matches)) {
			
			// echo "Shouldn't have come here....";
			$result = Matches::createMatch($player_id, $player_bet_rating);

			if(!$result) {
				die("Error: Could not create a new match record");
			} else {
				$response['matchFound'] = false;
				$response['matchID'] = $result;
				$response['playerRoll'] = 1;
			}
			
		} else {
			$match_id = $matches[rand(0,count($matches) - 1)]->id;

			$result = Matches::joinMatch($player_id, $player_bet_rating, $match_id);

			if(!$result) {
				die("Error: Could not join the match...");
			} else {
				$response['matchFound'] = true;
				$response['matchID'] = $match_id;
				$response['playerRoll'] = 2;
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

	public static function p2JoinedMatch($match_id) {
		
		$result = false;
		
		$row = DB::select('
						SELECT id, p2_id
						FROM Matches
						WHERE id = :match_id and
							  p2_id IS NOT NULL
					', ['match_id' => $match_id]);

		if(!empty($row)) {
			$result = true;
		}

		return $result;
	}

	public static function playerAcceptsMatch($match_id, $player_roll) {
		
		//check which player is accepting
		if($player_roll == 1) {
			$sql = '
					UPDATE Matches
					SET p1_accept = 1
					WHERE id = :match_id
				';
		} else {
			$sql = '
					UPDATE Matches
					SET p2_accept = 1
					WHERE id = :match_id
				';
		}

		//update match accordingly
		DB::update($sql,['match_id' => $match_id]);

		//check if both have accepted and update players_matched if so...
		$players_matched = DB::select('
									SELECT id
									FROM Matches
									WHERE p1_accept = 1 and
										  p2_accept = 1 and
										  id = :match_id
								', ['match_id' => $match_id]);
		if(!empty($players_matched)) {
			DB::update('
						UPDATE Matches
						SET players_matched = 1
						WHERE id = :match_id
					', ['match_id' => $match_id]);
			$players_matched = true;
		} else {
			$players_matched = false;
		}

		return $players_matched;
	}

	//checks if both players have accepted the match
	public static function checkPlayersAcceptedMatch($match_id) {

		$result = false;

		$check_accepted = DB::select('
								SELECT id
								FROM Matches
								WHERE players_matched = 1 and
									  id = :match_id
							',['match_id' => $match_id]);

		if(!empty($check_accepted)) {
			$result = true;		
		}

		return $result;
	}

	public static function chooseFirstTurn($match_id) {
		
		$player = rand(0,1);

		DB::table('Matches')
					->where('id', $match_id)
					->update(['p1_turn' => $player]);

		return true; 
	}

	public static function dropMatch($match_id) {

		DB::delete('
				DELETE
				FROM Matches
				WHERE id = :match_id and
					  match_complete = 0
			', ['match_id'=>$match_id]);

	}
}