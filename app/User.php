<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use DB;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
 	use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'collection_rating'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public function recalcCollectionRating() {
        
        $mogs = $this->getUserMogs($this->id);
        $rating = 0;

        foreach($mogs as $mog) {
            $rating += $mog->rating;
        }

        DB::update('
                UPDATE User
                SET collection_rating = :rating
                WHERE id = :userID
            ', 
            ['rating' => $rating, 'userID' => $this->id]);
    }

    // public static function save($userID) {
    //     DB::update('
    //             UPDATE User
    //             SET id = :userID, 
    //         ')
    // }

    public static function getUserMogs($userID) {
        $mogs = DB::select("
                    SELECT mm.id, mm.name, mm.src_url, mm.rating
                    FROM MogMaster as mm
                    RIGHT JOIN UserMogs as um
                    ON mm.id = um.mog_id
                    WHERE mm.active = true and um.user_id = :userID
                    ORDER BY mm.rating desc
                ",
                ['userID' => $userID]);

        return $mogs;
    }
}














// class User {
	
// 	public $id;
// 	public $username;
// 	public $password;
// 	public $salt;
// 	public $email;
// 	public $collectionRating;
// 	public $casualWins;
// 	public $keepsWins;
// 	public $totalWins;
// 	public $gameCount;
// 	public $inventory;
// 	public static $userCount;


// 	public function __construct($id = null, $username = null, 
// 								$password = null, $email = null){
// 		//If ID is given instantiate object with given ID
// 		if ($id) {
// 			$instance = new self();
// 			$instance->getByID($id);
// 			return $instance;
// 		} else { //If no ID create and save
// 			$this->username = $username;
// 			$this->salt = User::generateSalt();
// 			$this->password = Hash::make($password + $this->salt);
// 			$this->email = $email;
// 			$this->save();
// 		}
// 	}



// 	public static function getByID($id) {

// 		//SQL
// 		$sql = "
// 			SELECT *
// 			FROM User
// 			WHERE id = :id";

// 		//Execute and Return
// 		$row = DB::selectOne($sql, ["id" => $id]);

// 		$user = new User();
// 		$user->id = $row->id;
// 		$user->username = $row->username;
// 		$user->email = $row->email;
// 		$user->collectionRating = $row->collectionRating;
// 		$user->casualWins = $row->casualWins;
// 		$user->keepsWins = $row->keepsWins
// 	}

// 	//Save (Call Insert or Update)
// 	public function save() {
// 		if(empty($this->id)) {
// 			$this->insert();
// 		} else {
// 			$this->update();
// 		}
// 	}

// 	private function insert() {

// 		//sql
// 		$sql = "
// 			INSERT INTO User (username";
// 	}

// 	//Function that generates unique salt for password encryption
// 	public function generateSalt() {

// 		$salt = '';

// 		for($i = 0; $i < 64; $i++) {
// 			$salt .= substr(
// 				"./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz01234567890",
// 				mt_rand(0, 63),
// 				1);
// 		}

// 		return $salt;
// 	}

// }