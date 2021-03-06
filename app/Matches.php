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
							'active_player_id','match_state','match_complete','p1_mog_count','p2_mog_count',
							'p1_viewed_round','p2_viewed_round','updated_at'];

	//logic that searches for a match, if none found calls createMatch
	//This is a flawed approach to match making and will need to be updated should meme slam
	//be deployed publicly
	public function initializeGame() {

		if($this->in_progress == 0) {

			DB::table('Matches')->where('id',$this->id)->update(['in_progress'=>1]);
			
			Matches::chooseFirstTurn($this->id);
			
			$match_players = $this->getMatchPlayers();
			PlayField::loadGameMogs($this->id, $match_players);
		}
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

	public function updateMatchState($arr) {

		$result = 0;

		if($arr["current_state"] == 0) {//post stack 
		
			DB::table('Matches')
					->where('id', '=', $this->id)
					->update(['match_state' => 1]);
			$result = 1;
		
		} else if ($arr["current_state"] == 1){//post slammer
		
			$this->calcRoundOutcome($arr['state_data']);
			DB::table('Matches')
					->where('id', '=', $this->id)
					->update(['match_state' => 2]);
			$result = 1;
		
		} else if ($arr["current_state"] == 2) {//mini-game results, check if game over and reset round
			
			//updating that the given player has seen the result
			if($this->match_complete == 0) {
				$this->updatePlayerViewedResult($arr['state_data']);
			}

			$result = 1;
		}

		return $result;
	}

	public function resetRound() {

		//get data for logic
		$row = DB::table('Matches')->where('id','=',$this->id)->get();
		$p1 = $row[0]->p1_id;
		$p2 = $row[0]->p2_id;

		//determine next active player
		if($row[0]->active_player_id == $p1) {
			$new_active_player = $p2;
		} else {
			$new_active_player = $p1;
		}

		//reset match
		DB::table('Matches')->where('id','=',$this->id)->update(['match_state' => 0,
																 'active_player_id' => $new_active_player,
																 'p1_viewed_round' => 0,
																 'p2_viewed_round' => 0]);
		//reset show_animation for playfield mogs
		DB::table('PlayField')->where('match_id','=',$this->id)->update(['show_animation' => 0]);
	}

	//check if a game over state is reached by seeing if there are any remaining mogs in play
	public function checkIfGameOver() {

		$result = 0;

		$row = PlayField::getActiveMogs($this->id);

		if(empty($row)) {
			$result = 1;
		}

		return $result;
	}

	//update match to indicate player has observed the round
	public function updatePlayerViewedResult($player_id) {

		$row = DB::table('Matches')->where('id','=',$this->id)->get();

		//update players viewed
		if($row[0]->p1_id == $player_id && $row[0]->p1_viewed_round == 0) {
			DB::table('Matches')->where('id','=',$this->id)->update(['p1_viewed_round' => 1]);
		} else if(($row[0]->p2_id == $player_id && $row[0]->p2_viewed_round == 0)) {
			DB::table('Matches')->where('id','=',$this->id)->update(['p2_viewed_round' => 1]);
		} 

		if($this->checkIfGameOver()) {//check if game over and update match state if so to alert clients
			$this->processGameOver();	
		} else if ($this->checkPlayersViewedResultsScreen()){//reset the round if not game over
			$this->resetRound();
		}
	}

	//logic that handles game over
		//logic that handles game over
	public function processGameOver(){
		//set match state, match complete, and in progress
		$this->in_progress = 0;
		$this->match_complete = 1;
		$this->match_state = 3;

		//update game count for players
		DB::table('User')->whereIn('id', array($this->p1_id,$this->p2_id))->increment('game_count');

		//determine who wins
		if($this->p1_mog_count > $this->p2_mog_count) {//player 1 wins
			$winner = $this->p1_id;
			$loser = $this->p2_id;
		} else if ($this->p1_mog_count < $this->p2_mog_count) {//player 2 wins
			$winner = $this->p2_id;
			$loser = $this->p1_id;
		} else {//tie
			$winner = false;
		}
 		 
		//update winners game count
		DB::table('User')->where('id','=',$winner)->increment('keeps_wins'); 
 		DB::table('User')->where('id','=',$winner)->increment('total_wins');

		//call new mog drops for winner/loser respectively
		if(!$winner) {//if tie users get low common drop
			ActivatedMogs::activateNew(5,0,0, $this->p1_id);
			ActivatedMogs::activateNew(5,0,0, $this->p2_id);
		} else {
			//values for drops
			$commonNum = 15;
			$rareNum = 0;
			$legendaryNum = 0;

			//rare roll...
			if(rand(0,10) > 8) {
				$rareNum = rand(1,4);
			}
 		 
			//legendary roll...
			if(rand(0,10) >= 9) {
				$legendaryNum = 1;
			}
 		
			$commonNum -= ($rareNum + $legendaryNum); 
			ActivatedMogs::activateNew($commonNum,$rareNum,$legendaryNum, $winner);
			ActivatedMogs::activateNew(5,0,0, $loser);
		}


		//reset players mog bet status
		ActivatedMogs::resetBetStatus($this->p1_id);
		ActivatedMogs::resetBetStatus($this->p2_id);

		$this->save();
	}

	//checks to see if both players have observe round outcome
	public function checkPlayersViewedResultsScreen() {

		$result = 0;

		$row = DB::table('Matches')->where('id', '=', $this->id)
								   ->where('p1_viewed_round', '=', 1)
								   ->where('p2_viewed_round', '=', 1)->get();
		if(!empty($row)) {
			$result = 1;
		}

		return $result;
	}

	public function calcRoundOutcome($slam_time) {

		//determine round_bias from given slam time
		if($slam_time == 0) {
			$round_bias = 0;
		} else if($slam_time > 1 && $slam_time <= 200) {
			$round_bias = 1;
		} else if ($slam_time > 200 && $slam_time <= 300) {
			$round_bias = 0.9;
		} else if ($slam_time > 300 && $slam_time <= 600) {
			$round_bias = 0.8;
		} else if ($slam_time > 600 && $slam_time <= 700) {
			$round_bias = 0.7;
		} else if ($slam_time > 700 && $slam_time <= 900) {
			$round_bias = 0.6;
		} else if ($slam_time > 900 && $slam_time <= 1200) {
			$round_bias = 0.5;
		} else if ($slam_time > 1200 && $slam_time <= 1600) {
			$round_bias = 0.4;
		} else if ($slam_time > 1600 && $slam_time <= 2100) {
			$round_bias = 0.3;
		} else if ($slam_time > 2100 && $slam_time <= 4000) {
			$round_bias = 0.2;
		} else {
			$round_bias = 0.1;
		} 

		//get count of available mogs for this match
		$available_mog_count = PlayField::getActiveMogs($this->id);
		$available_mog_count = count((array)$available_mog_count);

		//get number to be flipped
		$flip_count = 40 * $round_bias;

		//make sure number to be flipped doesn't exceed available
		if($flip_count > $available_mog_count) {
			$flip_count = $available_mog_count;
		}

		//Flip the mogs
		PlayField::flipMogs($this->id, $this->active_player_id, $flip_count);

		//update count of mogs for player
		if($this->active_player_id == $this->p1_id) {
			$this->p1_mog_count += $flip_count;
		} else {
			$this->p2_mog_count += $flip_count;
		}
		$this->save();
	}

	public static function checkForUpdate($match_id, $last_update) {

		$result = 0;

		$row = DB::table('Matches')
						->where('id', '=', $match_id)
						->get();

		if($row[0]->updated_at != $last_update) {
			$result = 1;
		}

		return $result;
	}

	public static function checkForActiveMatch($user_id) {

		$result = 0;

		//see if given user id is player 1 or 2 in an active match
		$row = DB::table('Matches')
					->where('in_progress', '=', 1)
					->where('match_complete', '=', 0)
					->where(function($query) use($user_id){
						$query->where('p1_id', '=', $user_id)
							  ->orWhere('p2_id', '=', $user_id);
					})
					->get();

		//if match exists prep to return it
		if(!empty($row)) {
			$result = $row[0]->id;
		}

		return $result;
	}

	public static function getOpponentDetail($opponent_id) {
		$opponent = DB::select('
							SELECT name
							FROM User
							WHERE id = :opponent_id
						',['opponent_id' => $opponent_id]);

		$result = $opponent[0]->name;

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

		//if no match is found call create, else add player to an existing match as player2
		if(empty($matches)) {
			
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
		
		$match = Matches::find($match_id);

		if(rand(0,1)) {
			$player = $match->p1_id;
		} else {
			$player = $match->p2_id;
		}

		DB::table('Matches')
					->where('id', $match_id)
					->update(['active_player_id' => $player]);

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

	public static function getOpponentID($match_id, $player_id) {

		$match = Matches::find($match_id);
		
		// print_r($match);
		if($match['p1_id'] == $player_id) {
			$opponent_id = $match['p2_id'];
		} else {
			$opponent_id = $match['p1_id'];
		}

		return $opponent_id;
	}
}