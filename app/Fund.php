<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fund extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'funds';
	protected $guarded = array();
	

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	
	protected $hidden = array();
}
