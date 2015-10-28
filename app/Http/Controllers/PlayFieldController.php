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

class PlayFieldController extends Controller
{
	public function loadGameMogs($match_id) {

		$match_players = Matches::getMatchPlayerIDs($match_id);

		PlayField::loadGameMogs($match_id, $match_players);

		// return "success";
	}
}