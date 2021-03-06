<?php

namespace App\Http\Controllers;

use Auth;
use Crypt;
use Request;
use Illuminate\Routing\Controller;
use App\User;
use App\ActivatedMogs;
use App\Matches;
use App\PlayField;
use App\GameState;
use App\Player;
use App\MogMaster;

class MemeSlamController extends Controller
{
	//serves up MemeSlam game page
	public function initialize($user_id) {

		$required_bet_count = 20;

		$in_active_match = Matches::checkForActiveMatch($user_id);

		if(count(User::getBettedMogs($user_id)) == $required_bet_count || $in_active_match){

			//get the user
			$user = Auth::user();

			//Get the user's bet rating
			$bet_rating = ActivatedMogs::getBetRating($user_id);

			//Check if the user is already in a match
			$active_match_id = $user->getActiveMatch();

			if($active_match_id) {//user is in a match


				//get the match record to know the current state of the match
				$match_detail = Matches::find($active_match_id);
				
				//get the mogs that are in the play field
				$bet_mogs = PlayField::getUsersBettedMogs($active_match_id, $user->id);

				$captured_mogs = PlayField::getUsersCapturedMogs($active_match_id, $user->id);

			} else {//user is not in a match

				$match_detail = null;

				//Get user's betted mogs
				$bet_mogs = User::getBettedMogs($user_id);
				
				$captured_mogs = [];
				
			}

			return view('memeslam', ['user' => $user, 'bet_rating' => $bet_rating, 'match_detail', 
									 'bet_mogs' => $bet_mogs, 'captured_mogs' => $captured_mogs])
					->withEncryptedCsrfToken(Crypt::encrypt(csrf_token()));
			
		} else {
			return redirect('/');
		}
		
	}

	//finds a valid match
	public function searchForMatch() {

		$player_id = Request::input('userID');
		$player_bet_rating = Request::input('betRating');

		$response = Matches::searchMatch($player_id, $player_bet_rating);

		return $response;
	}

	//called when players accept match
	public function playerAcceptsMatch() {

		$response = [];

		$match_id = Request::input('matchID');
		$player_roll = Request::input('playerRoll');

		$response['playersMatched'] = Matches::playerAcceptsMatch($match_id, $player_roll);

		return $response;
	}

	//checks if player 2 joined the match
	public function checkP2Joined($match_id) {

		$response = [];
		$response['p2Joined'] = Matches::p2JoinedMatch($match_id);

		return json_encode($response);
	}

	//used to check if both players have accepted a match
	public function checkPlayersAcceptedMatch($match_id) {
		
		$response = [];
		$response['playersAcceptedMatch'] = Matches::checkPlayersAcceptedMatch($match_id);
		
		return $response;
	}

	//used to drop a failed match
	public function dropMatch() {

		$match_id = Request::input('matchID');

		Matches::dropMatch($match_id);

		return "success";
	}

	//creates match when two players join together
	public function initializeMatch($match_id) {
		
		$match = Matches::find($match_id);
		$match->initializeGame();

		return "success";
	}

	//returns opponent
	public function getOpponentDetails($match_id, $requestor) {

		$match = Matches::find($match_id);

		$players = $match->getMatchPlayers();

		if($requestor == 1){
			$response['opponent'] = Matches::getOpponentDetail($players['player2']);
		} else {
			$response['opponent'] = Matches::getOpponentDetail($players['player1']);
		}

		return $response;
	}

	public function checkForUpdate($match_id, $last_update) {
	
		$response = Matches::checkForUpdate($match_id, $last_update);

		return $response;
	}

	public function getGameState($match_id,$user_id) {

		$response = [];
		$response[] = GameState::getGameState($match_id, $user_id);

		return $response;
	}

	public static function getBackgroundMogs() {

		$result = MogMaster::getBackgroundMogs();

		return $result;
	}

	public static function checkForActiveMatch($user_id) {

		$match_id = Matches::checkForActiveMatch($user_id);

		return $match_id;
	}

	public static function updateMatchState() {
		
		$match_id = Request::input('matchID');

		$match = Matches::find($match_id);

		$state_detail = array(
				"current_state" => Request::input('currentState'),
				"state_data" => Request::input('stateData')
			);

		$response = $match->updateMatchState($state_detail);

		return $response;
	}

	public function checkPlayersViewedRoundResults($match_id) {

		$match = Matches::find($match_id);

		return $match->checkPlayersViewedResultsScreen();
	}

	public function getGameOverDetail($match_id) {

		$response = [];

		$response['match_detail'] = Matches::find($match_id);

		$response['p1_name'] = User::getUsername($response['match_detail']->p1_id);
		$response['p2_name'] = User::getUsername($response['match_detail']->p2_id);

		return $response;
	}
}