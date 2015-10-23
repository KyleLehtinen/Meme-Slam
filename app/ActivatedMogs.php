<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class ActivatedMogs extends Model 
{
	protected $table = 'ActivatedMogs';
	protected $fillable = ['active_mog_ID','exchanges','recent'];

	/**
	 *Static method that retrieves a selected mog
	 *Takes: 
	 *	activated mog ID
	 *Return: 
	 *	Instantiated mog
	 */
	public static function getMog($activated_id) {
		//select mog of given ID and return
		$mog = DB::table('ActivatedMogs')->where('id', $activated_id)->first();
		return $mog;
	}


	/**
	 *Static method for initial mog drop for newly created accounts
	 *Takes: -
	 *Return: 
	 *	array of newly activated Mog IDs for join table
	 */
	public static function newAccountDrop($userID) {
		ActivatedMogs::activateNew(45,5,0,$userID);
	}


	/**
	 *Main method to activate new mogs
	 *Takes: 
	 *	number of new mogs to create/activate
	 *	number of legendary to create 
	 *	number of rare to create
	 *Returns: 
	 *	array of newly activated Mog IDs for join table
	 */
	protected static function activateNew($nCommon, $nRare, $nLegendary, $userID) {
		
		//Get Mogs
		$commonMogs = DB::select('select id from MogMaster where rating < 1000');
		$rareMogs = DB::select('select id from MogMaster where rating < 100000 and rating >= 1000');
		$legendaryMogs = DB::select('select id from MogMaster where rating >= 100000');

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

	/**
	 *Helper method for new mog instantiation
	 *Takes: 
	 *	Number of mogs, class of Mogs, activated ID's array
	 *Return: 
	 *	Array of activated Mog IDs
	 */
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

	//given user ID and list of betted mogs...
	public static function updateBetStatus($owner_id, $bet_mog_ids) {

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

	// public function toggleBetStatus($mog_id) {
		
	// 	$mog = ActivatedMogs::getMog($mog_id);

	// 	if($mog->on_bet == 0){
	// 		$new_val = 1;
	// 	} else {
	// 		$new_val = 0;
	// 	}
		 
	// 	DB::update('
	// 			UPDATE ActivatedMogs
	// 			SET on_bet = :new_val
	// 		',
	// 		['new_val' => $new_val]);

	// 	return true
	// }
}