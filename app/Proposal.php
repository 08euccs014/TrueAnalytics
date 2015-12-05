<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'proposals';
	

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	
	protected $hidden = array();
	
	public function job()
    {
        return $this->belongsTo('Job', 'type_id', 'id');
    }
    public function contest()
    {
        return $this->belongsTo('Contest', 'type_id', 'id');
    }
	public function sender()
    {
        return $this->belongsTo('User', 'sender_id', 'id');
    }
	public function receiver()
    {
       return $this->belongsTo('User', 'receiver_id', 'id');
	}

	public function workstream()
    {
        return $this->belongsTo('Workstream', 'workstream_id', 'id');
	}
	public function uploads()
    {
        return $this->hasMany('Upload','belong_id','id')->where('belongs','=', 4); // belongs 4 is proposal
    }

	/* function to return deposit with currency symbol
	*/
	public function deposit(){
		return currency_symbol(Session::get('currency')).' '.in_session_currency($this->currency,$this->deposit,3);
	}

	/* function to return due amount with currency symbol
	*/
	public function dueamount(){
		$due = $this->amount - $this->deposit;
		return currency_symbol(Session::get('currency')).' '.in_session_currency($this->currency,$due,3);
	}

	/* function to return amount with currency symbol
	*/
	public function amount(){
		return currency_symbol(Session::get('currency')).' '.in_session_currency($this->currency,$this->amount,3);
	}

	public function amount_released(){
		return currency_symbol(Session::get('currency')).' '.in_session_currency($this->currency,$this->amount_released,3);
	}
	
	/* function to return hourly rate with currency symbol
	*/
	public function hourlyrate(){
		return currency_symbol(Session::get('currency')).' '.in_session_currency($this->currency,$this->rate_hourly,3);
	}

	/* function to return deposit with currency symbol to admin 
	*/
	public function addeposit(){
		return currency_symbol($this->currency).' '.$this->deposit;
	}

	public function adamount(){
		return currency_symbol($this->currency).' '.$this->amount;
	}

	// function used to calculate tobepaid amount in workstream Job Commpletion
	public function tobepaid(){
		$amount = $this->amount - $this->amount_released;
		return $tobepaid = in_session_currency($this->currency,$amount,3);
	}

	/*function to return status of proposals*/
	public function status_name(){
		$status = '';
			switch($this->status){
				CASE '-2' : $status = 'Withdrawn';
					break;
				CASE '-1' : $status = 'Rejected';
					break;
				CASE '0' : $status = 'Active';
					break;
				CASE '1' : $status = 'Accepted';
					break;
				CASE '2' : $status = 'Curretly Working';
					break;
				CASE '3' : $status = 'Work Completed';
					break;
				CASE '4' : $status = 'Payment Approved';
					break;
				CASE '5' : $status = 'Feedback Employer';
					break;
				CASE '6' : $status = 'Feedback Freelancer';
					break;
				CASE '7' : $status = 'Refund';
					break;
				CASE '8' : $status = 'Conflict';
					break;
				CASE '11' : $status = 'Payment Released';
					break;
			}
		return $status;
	}

	/*function to return color of label a/c to status*/
	public function status_label(){
		$label='';
			switch($this->status){
				CASE '-2' : $label = 'label-danger';
					break;
				CASE '-1' : $label = 'label-danger';
					break;
				CASE '0' : $label = 'label-info';
					break;
				CASE '1' : $label = 'label-success';
					break;
				CASE '2' : $label = 'label-info';
					break;
				CASE '3' : $label = 'label-success';
					break;
				CASE '4' : $label = 'label-success';
					break;
				CASE '5' : $label = 'label-info';
					break;
				CASE '6' : $label = 'label-info';
					break;
				CASE '7' : $label = 'label-danger';
					break;
				CASE '8' : $label = 'label-danger';
					break;	
				CASE '11' : $label = 'label-success';
					break;	
			}
		return $label;
	}
	public function public_ws_status(){
		$status = '';		
		if($this->status > 0 && $this->status < 7 ){
			$status = 'Accepted';
		}elseif($this->status == '8' || $this->status == '11'){
			$status = 'Accepted';
		}elseif($this->status == '7'){
			$status = 'Refund';
		}
		return $status;
	}
	public function public_ws_label(){
		$label = '';		
		if($this->status > 0 && $this->status < 7 ){
			$label = 'btn-success';
		}elseif($this->status == '8' || $this->status == '11'){
			$label = 'btn-success';
		}elseif($this->status == '7'){
			$label = 'btn-warning';
		}
		return $label;
	}
	/*
		send email to the employer on new proposal
	*/
	public  function email_newProposal(){
		$subject='You have received a new proposal from '.$this->sender->fname.' for your job #'.$this->type_id;
		$proposalUrl = $this->plink();
		$data = array('proposal_url'=> $proposalUrl,'from' => NOTIFICATION_EMAIL,'job_title'=>$this->job->title,'from_name' => SENDER_NAME,'to' =>$this->receiver->email,'fname' =>$this->receiver->fname,'sender_name'=> $this->sender->fname,'subject' => $subject);
		Mail::send('emails.proposals/proposal_new', $data, function($message) use ($data)
		{
		    $message->to($data['to'], $data['fname'])->subject($data['subject']);
			$message->from($data['from'],$data['from_name']);
		});
		//if mail is sent then udpate the action	
		$this->action = 1;
		$this->save();

		return TRUE;
	}
	/*
		send email to the buyer on proposal deposit
	*/
	public  function email_buyer_proposal_deposit(){
		$subject='Thank you we have received your deposit for proposal  #'.$this->id;
		$url=url('workstream/view/'.$this->workstream_id);
		$data = array('proposal'=>$this,'url'=> $url,'from' => NOTIFICATION_EMAIL,'from_name' => NOTIFICATION_NAME,'to' =>$this->receiver->email,'fname' =>$this->receiver->fname,'sender_name'=> $this->sender->fname,'subject' => $subject);
		Mail::send('emails.proposals/buyer_proposal_deposit', $data, function($message) use ($data)
		{
		    $message->to($data['to'], $data['fname'])->subject($data['subject']);
			$message->from($data['from'],$data['from_name']);
		});
		return TRUE;
	}
	/*
		send email to the seller on proposal deposit
	*/
	public  function email_seller_proposal_deposit(){
		$subject='Congratulations your proposal is accepted  for '.$this->job->title;
		$url=url('workstream/view/'.$this->workstream_id);
		$data = array('proposal'=>$this,'url'=> $url,'from' => NOTIFICATION_EMAIL,'from_name' => NOTIFICATION_NAME,'to' =>$this->sender->email,'fname' =>$this->sender->fname,'sender_name'=> $this->receiver->fname,'subject' => $subject);
		Mail::send('emails.proposals/seller_proposal_deposit', $data, function($message) use ($data)
		{
		    $message->to($data['to'], $data['fname'])->subject($data['subject']);
			$message->from($data['from'],$data['from_name']);
		});
		return TRUE;
	}
	/*
		send email to the admin for proposal deposit
	*/
	public  function email_admin_proposal_deposit(){
		$subject='New Proposal Deposit';
		$data = array('proposal' => $this,'from' => NOTIFICATION_EMAIL,'from_name' => NOTIFICATION_NAME,'to' => REVIEW_EMAIL,'fname' =>REVIEW_NAME,'subject' => $subject);
		Mail::send('emails.proposals/admin_proposal_deposit', $data, function($message) use ($data)
		{
		    $message->to($data['to'], $data['fname'])->subject($data['subject']);
			$message->from($data['from'],$data['from_name']);
		});
		return TRUE;
	}
	
	public function plink()
	{
		return $this->job->manage_proposals_plink();
	}

	public function save(array $options = array())
	{
		if ( $this->status != 0 || $this->workstream_id != 0) {
			$this->action = 2;
		}
		return parent::save($options);
	}
}