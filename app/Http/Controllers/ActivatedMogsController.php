<?php
namespace App\Http\Controllers;

use Auth;
use Request;
use Illuminate\Routing\Controller;
use App\ActivatedMogs;

class ActivatedMogsController extends Controller
{
	public function updateBetStatus() {
		$mogs = [];
		$owner_id = Request::input('ownerID');
		$stringMogs = Request::input('mogs');

		//convert string data to int for sql query in next method
		if(!empty($stringMogs)) {
			foreach($stringMogs as $sMog) {
				$mogs[] = (int) $sMog;
			}	
		}
		
		ActivatedMogs::updateBetStatus($owner_id,$mogs);

		return "success";
	}
}