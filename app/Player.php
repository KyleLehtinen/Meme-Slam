<?php

namespace App;
use DB;

class Player 
{
	public $name;
	public $playing_mogs = [];
	public $captured_mogs = [];

	public function __construct($name, $playing_mogs, $captured_mogs) {
		$this->name = $name;
		$this->playing_mogs = $playing_mogs;
		$this->captured_mogs = $captured_mogs;
	}

	public static function getPlayerState($match_id, $player_id) {

		//get player username
		$name = User::getUserName($player_id);

		//get player's playing mogs
		$playing_mogs = PlayField::getUsersBettedMogs($match_id, $player_id);

		//get player's captured mogs
		$captured_mogs = PlayField::getUsersCapturedMogs($match_id, $player_id);

		$player = new Player($name, $playing_mogs, $captured_mogs);
		// echo "Got player $player_id";
		return $player;		
	}
}