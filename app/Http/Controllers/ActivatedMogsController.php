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
		$string_mogs = Request::input('mogs');

		//convert string data to int for sql query in next method
		if(!empty($string_mogs)) {
			foreach($string_mogs as $s_mogs) {
				$mogs[] = (int) $s_mogs;
			}	
		}
		
		ActivatedMogs::updateBetStatus($owner_id,$mogs);

		return "success";
	}
}