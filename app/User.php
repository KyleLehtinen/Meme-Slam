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

    protected $table = 'user';
    protected $fillable = ['name', 'email', 'password', 'collection_rating'];
    protected $hidden = ['password', 'remember_token'];

    //returns any active match the user is currently in
    public function getActiveMatch() {

        $result = 0;
        
        $row = DB::table('Matches')
                    ->where('match_complete', '=', 0)
                    ->where(function($query){
                        $query->where('p1_id', '=', $this->id)
                            ->orWhere('p2_id', '=', $this->id);
                    })->get();

        if(!empty($row)){
            $result = $row[0]->id;
        }

        return $result;                                  
    }
    
    //recalculates user's collection rating
    public function recalcCollectionRating() {
   
        //get this instance's owner mogs
        $mogs = $this->getUserMogs($this->id);

        $rating = 0;

        //build rating value from retrieved mogs
        foreach($mogs as $mog) {
            $rating += $mog->rating;
        }

        //update user's collection rating to new value
        DB::update('
                UPDATE User
                SET collection_rating = :rating
                WHERE id = :userID
            ', 
            ['rating' => $rating, 'userID' => $this->id]);

        return $rating;
    }

    //returns user's mogs
    public static function getUserMogs($owner_id) {
            
        $mogs = DB::select("
                    SELECT am.id as active_id, am.on_bet as on_bet, mm.id, mm.name, mm.src_url, mm.rating
                    FROM MogMaster as mm
                    RIGHT JOIN ActivatedMogs as am
                    ON mm.id = am.mog_id
                    WHERE mm.active = true and am.owner_id = :owner_id
                    ORDER BY mm.rating desc
                ",
                ['owner_id' => $owner_id]);

        return $mogs;
    }

    //returns user's betting mogs
    public static function getBettedMogs($owner_id) {

        $mogs = DB::select('
                    SELECT am.id as active_id, am.owner_id as owner_id, mm.id, mm.name, mm.src_url, mm.rating
                    FROM MogMaster as mm
                    RIGHT JOIN ActivatedMogs as am
                    ON mm.id = am.mog_id
                    WHERE mm.active = true and am.owner_id = :owner_id and am.on_bet = true
                    ORDER BY mm.rating desc
                ',
                ['owner_id' => $owner_id]);

        return $mogs;
    }

    //returns user's name
    public static function getUsername($id) {

        $row = DB::table('User')->where('id', '=', $id)->get();
        $username = $row[0]->name;
        
        return $username;
    }
}