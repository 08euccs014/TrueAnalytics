<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'transactions';
	protected $guarded = array();
	

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	
	protected $hidden = array();
	
	public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function amount(){
		return currency_symbol($this->currency).' '.round($this->amount,2);
	}

	public function session_currency_amount()
	{
		$amount = in_session_currency($this->currency,$this->amount, 3, $this->exchange_rate);
		return currency_symbol(Session::get('currency')).' '.$amount;
	}

	public function membership(){
        return $this->belongsTo('App\Membership', 'source_id', 'id'); // source_id belongs to membership
    }

    public function proposal(){
        return $this->belongsTo('App\Proposalpurchased', 'source_id', 'id'); // source_id belongs to proposalpurchased
    }

	public function txnlink(){
		switch ($this->source_type) {
			case '0':
				$link= url('pdf/txnFeeInvoice/'.$this->id);
			break;
			case '1':
				$link= url('pdf/receipt/'.$this->source_id);
			break;
			case '4':
				$link= url('pdf/externalinvoice/'.$this->source_id);
			break;
			case '5':
				$link= url('pdf/invoice/'.$this->source_id);
			break;
			case '6':
				$link= url('pdf/invoice/'.$this->source_id);
			break;
			case '9':
				$link= url('pdf/membershipInvoice/'.$this->id);
			break;
			case '12':
				$link = url('pdf/proposalCredit/'.$this->id);
			break;
			case '22':
				$link= url('pdf/tlFeeInvoice/'.$this->id);
			break;
			default:
				$link='#';
			break;
		}
		return $link;
	}
}