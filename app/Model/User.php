<?php

class User {
	
	public $id;
	public $username;
	public $password;
	public $salt;
	public $email;
	public $collectionRating;
	public $casualWins;
	public $keepsWins;
	public $totalWins;
	public $gameCount;
	public static $userCount;


	// public function __construct($id = null){
	// 	if ($id) {
	// 		$instance = new self();
	// 		$instance->loadByID($id);
	// 		return $instance;
	// 	} else {

	// 	}
	// }



	public static function getByID($id) {

		//SQL
		$sql = "
			SELECT *
			FROM User
			WHERE id = :id";

		//Execute and Return
		return DB::selectOne($sql, ["id" => $id]);
	}

	//Save (Call Insert or Update)
	public function save() {
		if(empty($this->id)) {
			$this->insert();
		} else {
			$this->update();
		}
	}

	private function insert() {

		//sql
		$sql = "
			INSERT INTO User (username";
	}

	//Function that generates unique salt for password encryption
	public function generateSalt() {

		$salt = '';

		for($i = 0; $i < 64; $i++) {
			$salt .= substr(
				"./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz01234567890",
				mt_rand(0, 63),
				1);
		}

		return $salt;
	}

}