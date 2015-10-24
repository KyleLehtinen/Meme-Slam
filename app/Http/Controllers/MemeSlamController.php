<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Routing\Controller;
use App\User;
use App\ActivatedMogs;
use Crypt;

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
}