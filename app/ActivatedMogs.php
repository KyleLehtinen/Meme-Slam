<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

ini_set('display_errors', 1); 
error_reporting(E_ALL);

class ActivatedMogs extends Model 
{
	protected $table = 'ActivatedMogs';
	protected $fillable = ['active_mog_ID','exchanges','recent'];

	//Retrieves a selected mog given the activated mog's id
	public static function getMog($activated_id) {
		$mog = DB::table('ActivatedMogs')->where('id', $activated_id)->first();
		return $mog;
	}

	//Used to give initial mog drop for newly registered accounts or accounts that have exhausted their mog inventory
	public static function newAccountDrop($userID) {
		ActivatedMogs::activateNew(45,5,0,$userID);
	}

	//Activates new mogs for new account drop, end-game drops, daily logins, etc
	protected static function activateNew($nCommon, $nRare, $nLegendary, $userID) {
		
		//Get Mogs
		$commonMogs = DB::select('select id from MogMaster where rating < 600 and active = 1');
		$rareMogs = DB::select('select id from MogMaster where rating < 900 and rating >= 600 and active = 1');
		$legendaryMogs = DB::select('select id from MogMaster where rating >= 900 and active = 1');

		//instantiate common Mogs
		if ($nCommon) {
			ActivatedMogs::insert($nCommon, $commonMogs, $userID);
		}

		//instantiate rare Mogs
		if ($nRare) {
			ActivatedMogs::insert($nRare, $rareMogs, $userID);
		}

		//instantiate legendary Mogs 
		if ($nLegendary) {
			ActivatedMogs::insert($nLegendary, $legendaryMogs, $userID);
		}
	}

	//Utility method for new mog instantiation and insertion to a user's account 
	protected static function insert($count, $mogType, $owner_id) {
		
		//repeat per given count
		for($i = 0; $i < $count; $i++) {
			
			//get's mog id from given list for next insert
			$mog_id = $mogType[rand(0,count($mogType) - 1)]->id; 

			//insert into ActiveMogs
			DB::insert("
					INSERT 
					into ActivatedMogs (mog_id, owner_id)
					VALUES (:mog_id, :owner_id)
				",
				['mog_id' => $mog_id, 'owner_id' => $owner_id]);
		}

		return true;
	}

	//Updates betting status of a user's mogs given user ID and list of betted mogs
	public static function updateBetStatus($owner_id, $bet_mog_ids) {

		//first set all user's mogs bet status to false
		$changes = DB::update('
				UPDATE ActivatedMogs
				SET on_bet = 0
				WHERE owner_id = :owner_id
			',
			['owner_id' => $owner_id]);

		//Now reupdate bet status for user's mogs with given id's
		if(!empty($bet_mog_ids)) {
			$updates = DB::table('ActivatedMogs')
						->whereIn('id',$bet_mog_ids)
						->update(['on_bet' => 1]);
		}
		
		return true;	
	}

	//Calcs and returns the bet pod rating of a given user id 
	public static function getBetRating($owner_id) {

		$bet_rating = 0;

		//Get the given user's mogs
		$current_bet_rating = DB::select("
                    SELECT sum(mm.rating) as bet_rating
                    FROM MogMaster as mm
                    RIGHT JOIN ActivatedMogs as am
                    ON mm.id = am.mog_id
                    WHERE mm.active = true and am.owner_id = :owner_id and am.on_bet = true
                ",
                ['owner_id' => $owner_id]);

		//check if value returned is zero
		if($current_bet_rating[0]->bet_rating > 0) {
			$bet_rating = $current_bet_rating[0]->bet_rating;	
		}

		return $bet_rating;
	}

	//Used to toggle the bet status of a given activated mog
	public static function toggleBetStatus($mog_id) {
		
		$mog = ActivatedMogs::getMog($mog_id);

		if($mog->on_bet == 0){
			$new_val = 1;
		} else {
			$new_val = 0;
		}
		 
		DB::update('
				UPDATE ActivatedMogs
				SET on_bet = :new_val
				WHERE id = :mog_id
			',
			['new_val' => $new_val, 'mog_id' => $mog_id]);

		return true;
	}

	//update the owner of the given array of activated mogs
	public static function updateOwner($arr_activated_ids, $new_owner_id) {

		//update owner of given array of mogs
		DB::table('ActivatedMogs')->whereIn('id', $arr_activated_ids)->update(['owner_id' => $new_owner_id,
																			   'recent' => 1]);

		//determine new ones for use in incrementing exchanges
		////later if I have time...
	}

	//resets the user's betting mogs at end of match
	public static function resetBetStatus($user_id) {
		DB::table('ActivatedMogs')->where('owner_id','=',$user_id)->update(['on_bet' => 0]);
	}

	//retrieves users recent mogs
	public static function getRecentMogs($owner_id) {

		//get list of recent mogs
		$row = DB::table('ActivatedMogs')->where('owner_id','=',$owner_id)
										 ->where('recent','=',1)->lists('mog_id');
		return $row;
	}

	//reset recent flag on user's mogs
	public static function resetUserRecentMogs($owner_id) {
		DB::table('ActivatedMogs')->where('owner_id','=',$owner_id)->update(['recent' => 0]);
	}
}