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
	public $inventory;
	public static $userCount;


	public function __construct($id = null, $username = null, 
								$password = null, $email = null){
		//If ID is given instantiate object with given ID
		if ($id) {
			$instance = new self();
			$instance->getByID($id);
			return $instance;
		} else { //If no ID create and save
			$this->username = $username;
			$this->salt = User::generateSalt();
			$this->password = Hash::make($password + $this->salt);
			$this->email = $email;
			$this->save();
		}
	}



	public static function getByID($id) {

		//SQL
		$sql = "
			SELECT *
			FROM User
			WHERE id = :id";

		//Execute and Return
		$row = DB::selectOne($sql, ["id" => $id]);

		$user = new User();
		$user->id = $row->id;
		$user->username = $row->username;
		$user->email = $row->email;
		$user->collectionRating = $row->collectionRating;
		$user->casualWins = $row->casualWins;
		$user->keepsWins = $row->keepsWins
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