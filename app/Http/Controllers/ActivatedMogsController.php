<?php
namespace App\Http\Controllers;

use Auth;
use Illuminate\Routing\Controller;
use App\ActivatedMogs;

class ActivatedMogsController extends Controller
{
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