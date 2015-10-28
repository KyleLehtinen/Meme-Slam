<?php

namespace App\Http\Controllers;

use Auth;
use Crypt;
use Request;
use Illuminate\Routing\Controller;
use App\User;
use App\ActivatedMogs;
use App\Matches;


class MemeSlamController extends Controller
{
	public function preInitialize($user_id) {

		$required_bet_count = 20;

		if(count(User::getBettedMogs($user_id)) == $required_bet_count) {

			$user = Auth::user();
			
			$bet_mogs = User::getBettedMogs($user_id);

			$bet_rating = ActivatedMogs::getBetRating($user_id);

			return view('memeslam', ['bet_mogs' => $bet_mogs, 'bet_rating' => $bet_rating, 'user' => $user])
					->withEncryptedCsrfToken(Crypt::encrypt(csrf_token()));
		} else {
			return redirect('/');
		}
	}

	public function searchForMatch() {

		$player_id = Request::input('userID');
		$player_bet_rating = Request::input('betRating');

		$response = Matches::searchMatch($player_id, $player_bet_rating);

		return $response;
	}

	public function playerAcceptsMatch() {

		$response = [];

		$match_id = Request::input('matchID');
		$player_roll = Request::input('playerRoll');

		$response['playersMatched'] = Matches::playerAcceptsMatch($match_id, $player_roll);

		return $response;
	}

	public function checkP2Joined($match_id) {

		$response = [];
		$response['p2Joined'] = Matches::p2JoinedMatch($match_id);

		return json_encode($response);
	}

	public function checkPlayersAcceptedMatch($match_id) {
		
		$response = [];
		$response['playersAcceptedMatch'] = Matches::checkPlayersAcceptedMatch($match_id);

		if($response['playersAcceptedMatch'] == true){
			$match = Matches::find($match_id);
			$match->initializeGame();
		}
		
		return $response;
	}

	public function dropMatch() {
		$match_id = Request::input('matchID');

		Matches::dropMatch($match_id);

		return "success";
	}

	public function getOpponentDetails($match_id, $requester) {

		$match = Matches::find($match_id);

		$players = $match->getMatchPlayers();

		if($requester == 1){
			$response['opponent'] = Matches::getOpponentDetail($players['player2']);
		} else {
			$response['opponent'] = Matches::getOpponentDetail($players['player1']);
		}

		return $response;
	}
}