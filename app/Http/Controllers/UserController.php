<?php 
namespace App\Http\Controllers;

use Auth;
use Illuminate\Routing\Controller;
use App\User;
use App\ActivatedMogs;
use App\Matches;
use Crypt;


class UserController extends Controller 
{
	
	public function getUser(){

		//Check user authenticated
		if(Auth::user()) {

			//Set authenticated user instance
			$user = Auth::user();

			if(Matches::checkForActiveMatch($user->id)) {
				return redirect('/meme_slam/' . $user->id);
			} else {
				//If user instance has no mogs give it mogs
				if($user->collection_rating == 0) {
					ActivatedMogs::newAccountDrop($user->id);
				}

				//recalc user's collection rating and update
				$collection_rating = $user->recalcCollectionRating();

				//Get all the authenticated user's mogs
				$mogs = User::getUserMogs($user->id);

				//Get user's bet rating
				$bet_rating = ActivatedMogs::getBetRating($user->id);

				//Get count of users betted mogs
				$bet_count = count(User::getBettedMogs($user->id));

				//get top collection rating
				$top_collections = User::getTopCollections();

				return view('home',['user'=>$user, 'mogs'=>$mogs, 'bet_rating'=> $bet_rating, 
						 			'collection_rating'=>$collection_rating, 'bet_count' => $bet_count,
						 			'top_collections' => $top_collections])->withEncryptedCsrfToken(Crypt::encrypt(csrf_token()));
			}
		} else {
			return "You are not authorized to view this page...";
		}
	}
};