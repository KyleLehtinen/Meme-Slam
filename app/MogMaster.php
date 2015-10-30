<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use DB;

class MogMaster extends Model 
{
	protected $table = 'MogMaster';
	protected $fillable = ['id'];

	public static function getBackgroundMogs() {

		$mogs = DB::table('MogMaster')->where('active', '=', 1)->get();

		$result = [];

		foreach($mogs as $mog) {
			$result[] = $mog->id;
		}

		return $result;
	}
}