<?php 
namespace App\Http\Controllers;

use Auth;
use Illuminate\Routing\Controller;
use App\User;
use App\ActivatedMogs;


class UserController extends Controller 
{
	
	public function getUser(){
		//Check user authenticated
		if(Auth::user()) {
			//Set authenticated user instance
			$user = Auth::user();

			//If user instance has no mogs give it mogs
			if($user->collection_rating == 0) {
				ActivatedMogs::newAccountDrop($user->id);
			}

			$collection_rating = $user->recalcCollectionRating();

			//Get all the authenticated user's mogs
			$mogs = User::getUserMogs($user->id);

			return view('home',['user'=>$user, 'mogs'=>$mogs, 'collectionRating'=>$collection_rating]);
		}
	}
};