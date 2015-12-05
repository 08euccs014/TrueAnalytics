<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Job extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'jobs';
	

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	
	protected $hidden = array();
	
	const APPROVED_AT = 'approved_at';
	const DELETED_AT = 'deleted_at';


	public function jobdate(){
		//return date_format($this->created_at, 'g:ia \o\n l jS F Y');
		return date_format($this->created_at, 'l jS F Y');
	}
	public function status_name(){
		$status = 'Inactive';
		 if($this->jobtype == 2){
			switch($this->status){
				CASE '-2' : $status = 'Deleted';
					break;
				CASE '-1' : $status = 'Closed';
						break;
				CASE '0' : $status = 'Inactive';
					break;
				CASE '1' : $status = 'Pending Approval';
					break;
				CASE '2' : $status = 'Active';
					break;			
				CASE '3' : $status = 'Active & Freezed';
					break;
				CASE '4' : $status = 'Freezed and Awarded';
					break;			
				CASE '5' : $status = 'Closed for proposal';
					break;
				CASE '6' : $status = 'Completed';
					break;
				CASE '11' : $status = 'Expired';
					break;			
				CASE '12' : $status = 'Closed by Admin';
					break;
			}
		}else{
			switch($this->status){
				CASE '-2' : $status = 'Deleted';
					break;
				CASE '-1' : $status = 'Unpublished';
						break;
				CASE '0' : $status = 'Inactive';
					break;
				CASE '1' : $status = 'Pending Approval';
					break;
				CASE '2' : $status = 'Active';
					break;			
				CASE '3' : $status = 'Active and Paused';
					break;
				CASE '4' : $status = 'Freezed';
					break;			
				CASE '5' : $status = 'Closed';
					break;
				CASE '11' : $status = 'Expired';
					break;			
				CASE '12' : $status = 'Closed by Admin';
					break;
			}
		}
		return $status;
	}

	public function status_label(){
			switch($this->status){
				CASE '-2' : $label = 'label-danger';
					break;
				CASE '-1' : $label = 'label-info';
					break;
				CASE '0' : $label = 'label-danger';
					break;
				CASE '1' : $label = 'label-info';
					break;
				CASE '2' : $label = 'label-success';
					break;		
				CASE '3' : $label = 'label-success';
					break;
				CASE '4' : $label = 'label-info';
					break;
				CASE '5' : $label = 'label-info';
					break;
				CASE '6' : $label = 'label-success';
					break;
				CASE '11' : $label = 'label-danger';
					break;
				CASE '12' : $label = 'label-info';
					break;
			}
		return $label;
	}
	public function title_slug(){
		return Str::slug($this->title);
	}
	public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
	public function proposals()
    {
        return $this->hasMany('App\Proposal','type_id','id');
    }
    public function acceptedproposals()
    {
        return $this->hasMany('App\Proposal','type_id','id')->where('status','>','0');
    }
    public function proposals_by_status($order = 'desc')
    {
        return $this->hasMany('App\Proposal')->orderBy('status',$order);
    }
	public function tags()
    {
        return $this->hasMany('Tag','type_id','id')->where('type','=', 1); // type 1 is job
    }
	public function fixedjob()
    {
        return $this->hasOne('App\FixedJob');
    }
	public function servicejob()
    {
        return $this->hasOne('App\ServiceJob');
    }
	public function hourlyjob()
    {
        return $this->hasOne('App\HourlyJob');
    }
    public function uploads()
    {
        return $this->hasMany('App\Upload','belong_id','id')->where('belongs','=', 1); // belongs 1 is job
    }
	public function activeuploads()
    {
        return $this->hasMany('App\Upload', 'belong_id', 'id')->where('belongs','=',1)->where('status','=','0'); // belongs 1 is job
    }
	public function activeimageuploads()
    {
        return $this->hasMany('App\Upload', 'belong_id', 'id')->where('belongs','=',1)->where('type','=',1)->where('status','=','0'); // belongs 1 is job
    }
    public function category()
    {
        return $this->hasOne('App\Category','id','category_id');
    }
    public function jobbadges()
    {
    	return $this->belongsToMany('App\Jobbadges','job_badge');
    }
    public function topjobbadge()
    {
    	return $this->belongsToMany('App\Jobbadges','job_badge')->orderBy('jobbadges.priority','desc')->take(1);	
    }
    public function jobinvite()
    {
        return $this->hasMany('App\JobInvite','job_id','id');
    }

    public function discount()
    {
        return $this->hasMany('App\Servicediscount','service_id','id');
    }
    public function skills()
    {
        return $this->belongsToMany('App\Skill','job_skills');
    }
	public function isfeatured(){
		return $this->featured;
	}
	public function isfavourite(){
		if(isset(Auth::user()->id)){
			$user = Auth::user();
			$userservices = $user->favourite_services();
			if(in_array($this->id, $userservices)){
				return TRUE;
			}else{
				return FALSE;
			}
		}else{
			return FALSE;
		}
	}
	public function type_name(){
		switch ($this->jobtype) {
			case 1:
				return 'Service';
			break;
			case 2:
				return 'Project';
			break;
			case 3:
				return 'Hourly Project';
			break;
		}
	}
	public function visibility(){
		switch ($this->visibility) {
			case 0:
				return 'Public';
			break;
			case 1:
				return 'Private';
			break;
		}
	}

	public function activediscount()
    {
        return $this->hasOne('App\Servicediscount','service_id','id')->where('status','=','3');
    }
    public function activefeatureditem()
    {
        return $this->hasOne('App\Featureditems','source_id','id')->where('status','=','1');
    }
    public function hasdiscount(){
    	$status = array('1','2','3');
    	return $this->hasOne('App\Servicediscount','service_id','id')->whereIn('status',$status);
    }

    public function salepercent(){
    	if(isset($this->activediscount->id)){
    		$discount = $this->activediscount->percent;
    	}else{
    		$discount = FALSE;
    	}
    	return $discount;
    }
    public function price(){
    	if($this->jobtype == '1'){
    		if(isset($this->activediscount->id)){
    			$price = $this->servicejob->price - ($this->servicejob->price * ($this->activediscount->percent/100));
	    	}else{
	    		$price = $this->servicejob->price;
	    	}
    	}else{
    		$price = $this->fixedjob->budget;
    	}    	
    	return $price;
    }
	public function plink(){
		if($this->jobtype == 1){
			return servicejob_url($this->title_slug(),$this->id);
		}else{
			return job_url($this->title_slug(),$this->id);
		}
	}
	
	public function manage_proposals_plink(){
		return job_url($this->title_slug(),$this->id).'#proposal_received';
	}
	
	/*
		send email to the admin a job has been posted
	*/
	public  function email_admin_newJob(){
		if($this->jobtype == 1){
			$subject='#'.$this->id.' Review the new Service posted.';
		}else{
			$subject='#'.$this->id.' Review the new Project posted.';
		}
		$data = array('job'=> $this,'from' => NOTIFICATION_EMAIL,'from_name' => NOTIFICATION_NAME,'to' =>REVIEW_EMAIL,'fname' =>REVIEW_NAME,'sender_name'=> NOTIFICATION_NAME,'subject' => $subject);
		Mail::send('emails.jobs/job_review', $data, function($message) use ($data)
		{
		    $message->to($data['to'], $data['fname'])->subject($data['subject']);
			$message->from($data['from'],$data['from_name']);
		});
		return TRUE;
	}
	public  function email_admin_job_updated(){
		if($this->jobtype == 1){
			$subject='#'.$this->id.' Review the Updated Service.';
		}else{
			$subject='#'.$this->id.' Review the Updated Project.';
		}
		$data = array('job'=> $this,'from' => NOTIFICATION_EMAIL,'from_name' => NOTIFICATION_NAME,'to' =>REVIEW_EMAIL,'fname' =>REVIEW_NAME,'sender_name'=> NOTIFICATION_NAME,'subject' => $subject);
		Mail::send('emails.jobs/job_review', $data, function($message) use ($data)
		{
		    $message->to($data['to'], $data['fname'])->subject($data['subject']);
			$message->from($data['from'],$data['from_name']);
		});
		return TRUE;
	}

	public  function email_creator_job_approve(){
		if($this->jobtype == 1){
			$subject=$this->user->fname.', your service has been approved and ready for selling.';
			$email_view='emails.jobs/service_approved';
		}else{
			$subject=$this->user->fname.', your job has been approved, Invite Freelancers.';
			$email_view='emails.jobs/job_approved';
		}
		$data = array('job'=>$this,'job_url'=> $this->plink(),'from' => NOTIFICATION_EMAIL,'from_name' => NOTIFICATION_NAME,'to' =>$this->user->email,'fname' =>$this->user->fname,'sender_name'=> NOTIFICATION_NAME,'subject' => $subject);
		Mail::send($email_view, $data, function($message) use ($data)
		{
		    $message->to($data['to'], $data['fname'])->subject($data['subject']);
			$message->from($data['from'],$data['from_name']);
		});
		return TRUE;
	}

	public  function email_creator_job_reject(){
		if($this->jobtype == 1){
			$subject=$this->user->fname.', your service review.';
			$email_view='emails.jobs/service_rejected';
		}else{
			$subject=$this->user->fname.', your project review.';
			$email_view='emails.jobs/job_rejected';
		}
		$data = array('job'=>$this,'job_url'=> $this->plink(),'from' => NOTIFICATION_EMAIL,'from_name' => NOTIFICATION_NAME,'to' =>$this->user->email,'fname' =>$this->user->fname,'sender_name'=> NOTIFICATION_NAME,'subject' => $subject);
		Mail::send($email_view, $data, function($message) use ($data)
		{
		    $message->to($data['to'], $data['fname'])->subject($data['subject']);
			$message->from($data['from'],$data['from_name']);
		});
		return TRUE;
	}

    public function share_facebook(){
    	$app_id = Config::get('app.fb_app_id');
		$return_url = url('share/fbshare');
		$url = 'https://www.facebook.com/dialog/share?app_id='.$app_id.'&href='.$this->plink().'&redirect_uri='.$return_url.'&display=popup';
    	return $url;
    }
    public function share_twitter(){
    	$url = 'https://twitter.com/intent/tweet?url='.$this->plink().'&text='.$this->title.'#truelancer_';
    	return $url;
    }
    public function share_google(){
		$url = 'https://plus.google.com/share?url='.$this->plink();
    	return $url;
    }
    public function share_linkedin(){
		$url = 'https://www.linkedin.com/shareArticle?mini=true&url='.$this->plink();
    	return $url;
    }
	
	// email for new job proposal invitation
	
	public  function proposal_invite($user){

		$unsubscribelink = unsubscribe_link($user->id,$user->email,'job_invite_cron');
		
		$targetlink = Autologin::to($user, $this->plink().'?utm_source=proposal_invite&utm_medium=email&utm_campaign=Notifications');
				
		$data = array('targetlink' => $targetlink, 'unsubscribelink' => $unsubscribelink, 'job'=>$this,'from' => UPDATE_EMAIL,'from_name' => SENDER_NAME,'to' =>$user->email,'fname' =>$user->fname,'subject' =>$user->fname.', you have received a job request from '.$this->user->fname);
		
		$mailer  = new Mailer;
		$mailer->sender_email = $data['from'];
		$mailer->sender_name = $data['from_name'];
		$mailer->receiver_email = $data['to'];
		$mailer->receiver_name = $data['fname'];
		$mailer->subject = $data['subject'];
		$mailer->message = View::make('emails.jobs/proposal_invite', $data)->render();
		$mailer->status = '0';
		$mailer->priority = '1';
		$mailer->save(); 
		
		return TRUE;		
	}

	// email for job expire
	public  function email_jobexpire($job){
		$proposalsUrl = $job->manage_proposals_plink();
		$data = array('job'=>$this,'from' => UPDATE_EMAIL,'from_name' => SENDER_NAME,'to' =>$this->user->email,'fname' =>$this->user->fname,'subject' =>$this->user->fname.', your '.$this->type_name().' has been expired #'.$this->id, "proposalsUrl" => $proposalsUrl);
		Mail::send('emails.jobs/jobexpire', $data, function($message) use ($data)
		{
			$message->to($data['to'], $data['fname'])->subject($data['subject']);
			$message->from($data['from'],$data['from_name']);
		});
		return TRUE;	
	}

	public  function email_creator_job_close(){
		if($this->jobtype == 1){
			$subject=$this->user->fname.', your service has been closed.';
			$email_view='emails.jobs/service_closed';
		}else{
			$subject=$this->user->fname.', your project has been closed.';
			$email_view='emails.jobs/job_closed';
		}
		$data = array('job'=>$this,'job_url'=> $this->plink(),'from' => NOTIFICATION_EMAIL,'from_name' => NOTIFICATION_NAME,'to' =>$this->user->email,'fname' =>$this->user->fname,'sender_name'=> NOTIFICATION_NAME,'subject' => $subject);
		Mail::send($email_view, $data, function($message) use ($data)
		{
		    $message->to($data['to'], $data['fname'])->subject($data['subject']);
			$message->from($data['from'],$data['from_name']);
		});
		return TRUE;
	}
	public  function email_admin_autoapprovejob(){
		if($this->jobtype == 1){
			$subject = '#'.$this->id.' Review the new autoapproved Service.';
		}else{
			$subject = '#'.$this->id.' Review the new autoapproved Project.';
		}
		$data = array('jobreview_url' => $this->plink(),'from' => NOTIFICATION_EMAIL,'from_name' => NOTIFICATION_NAME,'to' => REVIEW_EMAIL,'fname' => REVIEW_NAME,'sender_name'=> NOTIFICATION_NAME,'subject' => $subject);
		Mail::send('emails.jobs/autojob_review', $data, function($message) use ($data)
		{
		    $message->to($data['to'], $data['fname'])->subject($data['subject']);
			$message->from($data['from'],$data['from_name']);
		});
		return TRUE;
	}
	
	public  function reopen_buyer(){
		$subject = $this->user->fname.', your project has been active.';
		$email_view = 'emails.jobs/project_reopened';
		$data = array('job'=>$this,'job_url'=> $this->plink(),'from' => NOTIFICATION_EMAIL,'from_name' => NOTIFICATION_NAME,'to' => 'yajendra@truelancer.com','fname' =>$this->user->fname,'sender_name'=> NOTIFICATION_NAME,'subject' => $subject);
		Mail::send($email_view, $data, function($message) use ($data)
		{
		    $message->to($data['to'], $data['fname'])->subject($data['subject']);
			$message->from($data['from'],$data['from_name']);
		});
		return TRUE;
	}
	
	public function getDates()
	{
		$defaults = array(static::CREATED_AT, static::UPDATED_AT,  static::APPROVED_AT, static::DELETED_AT);
	
		return array_merge($this->dates, $defaults);
	}
}
