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
}