<?php
namespace App\Http\Controllers;

use Auth;
use Illuminate\Routing\Controller;
use App\ActivatedMogs;

class ActivatedMogsController extends Controller
{
	//given user ID and list of betted mogs...
	public function updateBetStatus($owner_id, $bet_mog_ids) {

		//first set all user's mogs bet status to false
		DB::update('
				UPDATE ActivatedMogs
				SET on_bet = 0
				WHERE owner_id = :owner_id
			',
			['owner_id' => $owner_id]);

		//Now reupdate bet status for user's mogs with given id's
		DB::update('
				UPDATE ActivatedMogs
				SET on_bet = 1
				WHERE id IN (:bet_mog_ids)
			',
			['bet_mog_ids' => implode(',',$bet_mog_ids)]);

		return true;
		}
		
	}

	public function toggleBetStatus($mog_id) {
		
		$mog = ActivatedMogs::getMog($mog_id);

		if($mog->on_bet == 0){
			$new_val = 1;
		} else {
			$new_val = 0;
		}
		 
		DB::update('
				UPDATE ActivatedMogs
				SET on_bet = :new_val
			',
			['new_val' => $new_val]);

		return true
	}
}