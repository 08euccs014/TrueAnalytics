<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Workstreaminteraction extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'workstream_interactions';
	
	protected $guarded = array();

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	
	protected $hidden = array();

	public function workstream()
    {
        return $this->belongsTo('App\Workstream', 'workstream_id', 'id');
	}
	public function proposal()
    {
        return $this->hasOne('App\Proposal', 'id', 'interaction_id');
	}
	public function message()
    {
        return $this->hasOne('App\Message', 'id', 'interaction_id');
	}
	public function jobcompletion()
    {
        return $this->hasOne('App\Jobcompletionrequest', 'id', 'interaction_id');
	}
	public function jobfeedback()
    {
        return $this->hasOne('App\Jobfeedback', 'id', 'interaction_id');
	}
	public function requestrefund()
    {
        return $this->hasOne('App\Requestrefund', 'id', 'interaction_id');
	}
	public function cancelorder()
    {
        return $this->hasOne('App\Cancelorder', 'id', 'interaction_id');
    }
	public function jobmilestone()
    {
        return $this->hasOne('App\Jobmilestone', 'id', 'interaction_id');
	}
	public function depositrequest()
    {
        return $this->hasOne('App\Userpayments', 'id', 'interaction_id');
	}
	public function partialpayment()
	{
		return $this->hasOne('App\Partialpayment', 'id', 'interaction_id');
	}
	public function invoice()
	{
		return $this->hasOne('App\Jobinvoice', 'id', 'interaction_id');
	}
	public function is_seen(){
		if(!$this->seen_at){
			$seen='unread';
		}
		else{
			$seen='';
		}
		return $seen;
	}
	
	/*
	*convert raw sql records to modal object
	*/
	public static function sqlToElaquent($records)
	{
		if (!isset( $records )) {
			return null;
		}
		if (!is_array($records) || count($records) < 1) {
			return null;
		}

		$objects = array();

		foreach ( $records as $record )
		{
			$recordArray = ( array ) $record;
			$object = new self( $recordArray, true );
			$objects[ ] = $object;
		}

		return $objects;
	}
	public function seen_ago(){
		$ago = 'Seen few seconds ago';
		if($this->seen_at){
			$ago = 'Seen '.timeAgo($this->seen_at);
		}
		return $ago;
	}
}