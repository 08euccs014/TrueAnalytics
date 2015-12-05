<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fixed extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'static';
	
	protected $guarded = array();

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	
	protected $hidden = array();

	public function standard(){
		$static = Fixed::where('key','=','TL_INR_USD')->first();
		$conversion = $static['value'];
		return $conversion;
	}

	public function high(){
		$static = Fixed::where('key','=','TL_INR_USD')->first();
		$conversion = $static['value'];
		return $conversion;
	}

	public function low(){
		$static = Fixed::where('key','=','TL_INR_USD')->first();
		$conversion = $static['value'];
		return $conversion;
	}
}
