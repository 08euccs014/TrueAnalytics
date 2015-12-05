<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'order';
	

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

    public function proposal()
    {
    	return $this->belongsTo('App\Proposal', 'source_id', 'id');
    }

    public function invoice()
    {
        return $this->belongsTo('App\Invoice', 'source_id', 'id');
    }

    public function userdeposit()
    {
        return $this->belongsTo('App\Userdeposit', 'source_id', 'id');
    }

    public function membership()
    {
        return $this->belongsTo('App\Membership', 'source_id', 'id');
    }

    public function jobcompletion()
    {
        return $this->belongsTo('App\Jobcompletionrequest', 'source_id', 'id');
    }

    public function featured()
    {
        return $this->belongsTo('App\Featureditems', 'source_id', 'id');
    }

    public function propurchased()
    {
        return $this->belongsTo('App\Proposalpurchased', 'source_id', 'id');
    }

    public function userpayment()
    {
        return $this->belongsTo('App\Userpayments', 'source_id', 'id');
    }

    public function payment_status(){
    	$paystatus='';
		switch($this->payment_status){
			CASE '0' : $paystatus = 'Pending Payment';
				break;
			CASE '1' : $paystatus = 'Sent For Payment';
				break;
			CASE '2' : $paystatus = 'Payment Received';
				break;
		}
		return $paystatus;
	}

	public function payment_label(){
    	$label='';
		switch($this->payment_status){
			CASE '0' : $label = 'label-danger';
				break;
			CASE '1' : $label = 'label-info';
				break;
			CASE '2' : $label = 'label-success';
				break;
		}
		return $label;
	}

    public function order_detail(){
    	$order_detail='';
    	switch($this->source_type){
			CASE '1' : $order_detail = $this->proposal->item;
				break;
		}
		return $order_detail;
    }
    public function original_amount(){
    	return $this->amount - $this->txnfee;
    }

    public function amount(){
		return $this->currency.' '.round(($this->amount-$this->txnfee),2);
	}

    public function processed_amount()
    {
    	$amount = $this->amount - $this->txnfee;
    	$currency = $this->currency;
    	switch($this->source_type){
    		case '1':
    			$amount = $this->proposal->deposit;
    			$currency = $this->proposal->currency;
    			break;
    		case '2':
    			$amount = $this->invoice->amount;
    			$currency = $this->invoice->currency;
    			break;
    		case '3':
    			$amount = $this->userdeposit->amount_requested;
    			$currency = $this->userdeposit->currency;
    			break;
    		case '4':
    			$amount = $this->membership->paid_amount;
    			$currency = $this->membership->currency;
    			break;
    		case '5':
    		    // todo need to change in future 24-march-2015
    			$currency = $currency;
    			$amount = $amount;
    			break;
    		case '6':
                $transaction = Transaction::where('source_type','=','11')->where('source_id','=',$this->source_id)->first();
    			$amount = $transaction->amount;
    			$currency = $transaction->currency;
    			break;
    		case '7':
    			$amount = $this->propurchased->amount;
    			$currency = $this->propurchased->currency;
    			break;
    		case '10':
    			$amount = $this->userpayment->amount;
    			$currency = $this->userpayment->currency;
    			break;
            case '13':
                $amount   = $this->proposal->deposit;
                $currency = $this->proposal->currency;
                break;            
    		default:
    			$currency = $currency;
    			$amount = $amount;
    			break;    		
    	}

    	return in_session_currency($currency,$amount);
    }
    
    public function gatewayName()
    {
    	switch ($this->gateway) {
    		case 0 : $gateway = 'Paypal'; break;
    		case 2 : $gateway = ($this->amount == 0) ? 'Wallet' : 'Emvantage'; break;
    		case 3 : $gateway = 'PayU'; break;
    		case 4 : $gateway = 'Paytm'; break;
    		case 5 : $gateway = 'Stripe'; break;
    		case 11 : $gateway = 'Credit Points'; break;    		
    		
    	}
    	
    	return $gateway;
    }
    
    public function transactionFee(){
    	return currencyFormat($this->currency, $this->txnfee);
    }

    public function orderAmount(){
    	return currencyFormat($this->currency, $this->amount);
    }
}
