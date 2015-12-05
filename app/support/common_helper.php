<?php
use App\Fixed;

function get_tiny_url($url)  {
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch,CURLOPT_URL,'http://tinyurl.com/api-create.php?url='.$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function tl_mail($data){
		// $data = array('route' => '1','sender_email' => '','sender_name' => '','receiver_email' => '','receiver_name' => '','subject' => '','message' => '');
		if(App::environment() == 'production'){

			$mailer  = new Mailer;
			if(isset($data['route'])){
				$mailer->route = $data['route']; // transactional/paid  or normal
			}else{
				$mailer->route = 1; // transactional/paid
			}
			$mailer->sender_email = $data['sender_email'];
			$mailer->sender_name = $data['sender_name'];
			$mailer->receiver_email = $data['receiver_email'];
			$mailer->receiver_name = $data['receiver_name'];
			$mailer->subject = $data['subject'];
			$mailer->message = $data['message'];
			$mailer->status = '0';
			$mailer->save();
		}else{

		}

}
function update_referal_signup($user_id){
	try{
		if($user_id != '-'){
			$user = User::find($user_id);
			if(isset($user->id)){
				$month = date('m');
				$year = date('Y');
				$referral = Referral::where('user_id','=',$user_id)->where(DB::raw('month(created_at)'),'=',$month)->where(DB::raw('year(created_at)'),'=',$year)->first();
				if(!isset($referral->id)){

					$referral = new Referral;
					$referral->user_id = $user_id;
				}
				$referral->total_signup = $referral->total_signup + 1;
				if($referral->proposal_gained <= 30){
					$proposal = 5;
					$referral->proposal_gained = $referral->proposal_gained + $proposal;
					$account =  Account::where('user_id','=',$user_id)->first();
					$account->monthly_proposal_limit = $account->monthly_proposal_limit + $proposal;
					$account->save();
				}
				$referral->save();

			}

		}
	}
	catch(Exception $e){

	}
}
function genRandomString() {
    $length = 6;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $string = '';
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters))];
    }
    return $string;
}

function update_useronlinelog($userid){
			$onlinelog  = new Onlinelog;
			$onlinelog->user_id = $userid;
			$onlinelog->ip = Request::getClientIp();
			$onlinelog->save();
			$userscore['id'] = $userid;
			$userscore['type'] = 'onlinescore';
			Event::fire('user.score', array($userscore));
			Session::put('user_online_lastdate',date('Y-m-d'));
}
function set_currency($user){
	// currencyrate can be from tl_fixed table
	if($user->profile->ucurrency){
		$static = Fixed::where('key','=','TL_INR_USD')->first();
		Session::put('currency', $user->profile->ucurrency);
		Session::put('currencyrate', $static['value']);
	}
}
function browser_url(){
	$url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	return $url;
}
function strip_emails($string){
	$pattern = "/[^@\s]*@[^@\s]*\.[^@\s]*/";
	$replacement = "[removed]";
	return preg_replace($pattern, $replacement, $string);
}
function strip_patterns($string){
	$email_pattern = "/[^@\s]*@[^@\s]*\.[^@\s]*/";
	$replacement = "[removed]";
	$after_email = preg_replace($email_pattern, $replacement, $string);
	$mobile_pattern = '!(\b\+?[0-9()\[\]./ -]{7,17}\b|\b\+?[0-9()\[\]./ -]{7,17}\s+(extension|x|#|-|code|ext)\s+[0-9]{1,6})!i';
	$after_mobile = preg_replace($mobile_pattern, $replacement, $after_email);
	return $after_mobile;
}
function short_string($string,$length = 25){
	return Str::limit(strip_tags($string), $length);
}
function short_name($string,$length = 15){
	return str_limit($string, $length, '.');
}
function in_session_currency($currency,$amount,$type = '1', $inrToUsdRate = 0){
	$s_currency = Session::get('currency');
	$s_currencyrate = Session::get('currencyrate');

	if ($inrToUsdRate != 0) {
		$s_currencyrate = $inrToUsdRate;
	}

	if($currency != $s_currency){
		switch($s_currency) {
			case 'INR':
				$cov_rate = $s_currencyrate;
			break;
			case 'USD':
				$cov_rate = $s_currencyrate;
			break;
			case 'EUR':
				$cov_rate = $s_currencyrate;
			break;
			case 'GBP':
				$cov_rate = $s_currencyrate;
			break;
		}
		$conv_amount = $amount * $cov_rate;
		switch($type){
			case '3':
				return round($conv_amount,2);
				break;
			case '2':
				return floor(round($conv_amount,1));
			break;
			default:
				return ceil(round($conv_amount,1));
			break;
		}
	}else{
		return ceil(round($amount,1));
	}
}
function usdtoinr_amount($amount,$type = '1'){
	$cov_rate = Session::get('currencyrate');
	switch($type){
		case '2':
			return ceil(round($amount / $cov_rate,2));
		break;
	}
	$conv_amount = ceil($amount / $cov_rate);
	return $conv_amount;
}

function convert_currency($source,$target,$amount, $type = '1',$inrToUsdRate = 0){

		switch ($target) {
			case 'INR':
				if($source=='USD'){

					$static = Fixed::where('key','=','TL_INR_USD')->first();
					$cov_rate = 1 / $static['value'];

					if( $inrToUsdRate != 0) {
						$cov_rate = 1 / $inrToUsdRate;
					}
				}
				if($source=='INR'){
					$cov_rate = 1;
				}
				break;
			case 'USD':
				if($source=='INR'){
					$static = Fixed::where('key','=','TL_INR_USD')->first();
					$cov_rate = $static['value'];

					if( $inrToUsdRate != 0) {
						$cov_rate = $inrToUsdRate;
					}
				}
				if($source=='USD'){
					$cov_rate = 1;
				}
				break;
		}
		$conv_amount = $amount * $cov_rate;
		switch($type){
			case '3':
				return round($conv_amount,2);
				break;
			case '2':
				return ceil(round($conv_amount,1));
			break;
			default:
				return round($conv_amount);
			break;
		}
}
function raw_filename($filename)
{
	$filename = explode('/', $filename);
	array_shift($filename);
	if(is_array($filename)){
		$filename = implode('', $filename);
	}
	return $filename;
}
function sedn_fb_notification($receiver,$message,$link){
		  $app_id = Config::get('app.fb_app_id');
		  $app_secret = Config::get('app.fb_app_secret');

		  $token_url = "https://graph.facebook.com/oauth/access_token?" .
		    "client_id=" . $app_id .
		    "&client_secret=" . $app_secret .
		    "&grant_type=client_credentials";
		  $app_access_token = file_get_contents($token_url);
		  $user_id = $receiver;

		  $apprequest_url ="https://graph.facebook.com/" .
		    $user_id .
		    "/apprequests?message='Stand made a pledge'" .
		    "&". $app_access_token . "&method=post";

		 $app_access_token = explode('=', $app_access_token);
		 $app_access_token = $app_access_token[1];
		 $postdata = http_build_query(
		    array(
		        'template' => $message,
		        'href' => $link,
		        'access_token' => $app_access_token
		    )
		);
		$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => 'Content-type: application/x-www-form-urlencoded',
		        'content' => $postdata
		    )
		);
		$context  = stream_context_create($opts);
		$result = file_get_contents('https://graph.facebook.com/'.$user_id.'/notifications', false, $context);
}
function currency_symbol($currency){
		switch ($currency) {
			case 'USD':
					//dollar sign
					//return '&#36;';
					return '<div class="fa fa-usd"></div>';
				break;
			case 'INR':
					//rupee sign
					// return '&#8377;';
					return '<div class="fa fa-inr"></div>';
			break;
			case 'GBP':
					//pound sign
					 return '&#163;';
					 return '<div class="fa fa-gbp"></div>';
			break;
			case 'EUR':
					 //euro sign
					 return '&#8364;';
					 return '<div class="fa fa-eur"></div>';
			break;
			default:
				 //INR;
				return '<div class="fa fa-inr"></div>';
				break;
		}
}
function cdn_static($name){
	return Config::get('app.cdn_static').$name;
}
function job_url($title_slug,$job_id){
	return url('freelance-project/'.$title_slug.'-'.$job_id);
}
function contest_url($title_slug,$contest_id){
	return url('contest/'.$title_slug.'-'.$contest_id);
}
function servicejob_url($title_slug,$job_id){
	return url('freelance-service/'.$title_slug.'-'.$job_id);
}
function user_url($username,$id){
	return url('freelancer/'.$username);
}
function get_client_ip() {
    $ipaddress = '';

    if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'])
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']) && $_SERVER['HTTP_X_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']) && $_SERVER['HTTP_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']) && $_SERVER['HTTP_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'])
       $ipaddress = $_SERVER['REMOTE_ADDR'];

    return $ipaddress;
}
function get_local_time($timezone){

}
function get_timezone(){
	$timezone=array('-12.0'=>'(GMT -12:00) Eniwetok, Kwajalein','-11.0'=>'(GMT -10:00) Hawaii',
		'-9.0'=>'Alaska','-8.0'=>'(GMT -8:00) Pacific Time (US &amp; Canada)','-7.0'=>'(GMT -7:00) Mountain Time (US &amp; Canada)',
		'-6.0'=>'(GMT -6:00) Central Time (US &amp; Canada), Mexico City','-5.0'=>'(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima',
		'-4.0'=>'(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz','-3.5'=>'(GMT -3:30) Newfoundland','-3.0'=>'(GMT -3:00) Brazil, Buenos Aires, Georgetown',
		'-2.0'=>'(GMT -2:00) Mid-Atlantic','-1.0'=>'(GMT -1:00 hour) Azores, Cape Verde Islands','0.0'=>'(GMT) Western Europe Time, London, Lisbon, Casablanca',
		'1.0'=>'(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris','2.0'=>'(GMT +2:00) Kaliningrad, South Africa','3.0'=>'(GMT +3:00) Baghdad, Riyadh, Moscow, Nairobi',
		'3.5'=>'(GMT +3:30) Tehran','4.0'=>'(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi','4.5'=>'(GMT +4:30) Kabul',
		'5.0'=>'(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent','5.5'=>'(GMT +5:30) Mumbai, Kolkatta, Chennai, New Delhi','5.75'=>'(GMT +5:45) Kathmandu',
		'6.0'=>'(GMT +6:00) Almaty, Dhaka, Colombo','7.0'=>'(GMT +7:00) Bangkok, Hanoi, Jakarta','8.0'=>'(GMT +8:00) Beijing, Perth, Singapore, Hong Kong',
		'9.0'=>'(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk','9.5'=>'(GMT +9:30) Adelaide, Darwin','10.0'=>'(GMT +10:00) Eastern Australia, Guam, Vladivostok',
		'11.0'=>'(GMT +11:00) Magadan, Solomon Islands, New Caledonia','12.0'=>'(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka'
		);
	return $timezone;
}

function image_array(){
	return array('jpg','png','gif','jpeg','svg');
}

function file_array(){
	return array(
		//image
		'jpg','png','gif','jpeg','psd','svg','ai',
		//documents
		'pdf','doc','docx','xls','xlsx','txt','log', 'msg', 'odt',	'rtf','wps','csv','dat','pps', 'ppt', 'pptx', 'vcf', 'xml','dwg', 'dxf',
		//compression
		'zip', '7z', 'rar',
		//audio
		'mp3', 'mid', '	wav', 'wma',
		//video
		'3gp', 'asf', 'asx', 'avi',	'flv', 'm4v',	'mov', 'mp4',	'mpg', 'rm', 'srt', 'swf', 'vob', 'wmv'
	);
}

function upload_type($filename){
	$filename = raw_filename($filename);
	$ext = explode('.', $filename);
	$c = count($ext)-1;
	$ext = strtolower($ext[$c]);
	if(in_array($ext ,image_array())){
		return 1;
	}else{
		return 2;
	}
}

function upload_icon($filename){
	$ext = explode('.', $filename);
	$c = count($ext)-1;
	$ext = $ext[$c];
	if(in_array($ext ,image_array())){
		$icon='<i class="fa fa-file-image-o"></i>';
	}else{
		switch ($ext) {
		case 'pdf':
			$icon='<i class="fa fa-file-pdf-o"></i>';
			break;
		case 'doc':
			$icon='<i class="fa fa-file-word-o"></i>';
			break;
		case 'docx':
			$icon='<i class="fa fa-file-word-o"></i>';
			break;
		case 'xls':
			$icon='<i class="fa fa-file-excel-o"></i>';
			break;
		case 'xlsx':
			$icon='<i class="fa fa-file-excel-o"></i>';
			break;
		case 'txt':
			$icon='<i class="fa fa-file-text"></i>';
			break;
		case 'zip':
			$icon='<i class="fa fa-file-zip-o"></i>';
			break;
		default:
			$icon='<i class="fa fa-file"></i>';
			break;
		}
	}
	return $icon;
}

function uploaded_icon($filename){
	$ext = explode('.', $filename);
	$c = count($ext)-1;
	$ext = $ext[$c];
	if(in_array($ext ,image_array())){
		$icon='fa-file-image-o';
	}else{
		switch ($ext) {
		case 'pdf':
			$icon='fa-file-pdf-o';
			break;
		case 'doc':
			$icon='fa-file-word-o';
			break;
		case 'docx':
			$icon='fa-file-word-o';
			break;
		case 'xls':
			$icon='fa-file-excel-o';
			break;
		case 'xlsx':
			$icon='fa-file-excel-o';
			break;
		case 'txt':
			$icon='fa-file-text';
			break;
		case 'zip':
			$icon='fa-file-zip-o';
			break;
		default:
			$icon='fa-file';
			break;
		}
	}
	return $icon;
}

	function jobrejectarray(){
		$comments = array(
			array("id"=>"0","name"=>"Job : Add More Details","detail"=>"Kindly add more details about the job. Jobs with clear descriptions and requirements attract talented freelancers."),
			array("id"=>"1","name"=>"Service : Add Proper Desc ","detail"=>"Kindly add comprehensive and specific details about the service you intend to sell. Services with proper descriptions attract more buyers and results in more sales."),
			array("id"=>"2","name"=>"Service : Guidelines Issue ","detail"=>'This service does not meets our Terms of Service. Services are fixed priced offerings with CLEAR deliverables.')

		);
		return $comments;
	}

	function check_pcomplete($user){
		if($user->profile->headline == '' || $user->profile->bio =='' || $user->profile->rate ==''){
			return 0;
		}else{
			return 1;
		}
	}

	function generate_couponcode(){
		$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$code = "";
		for ($i = 0; $i < 10; $i++) {
			$code .= $chars[mt_rand(0, strlen($chars)-1)];
		}
		return $code;
	}

function globalXssClean()
{
    // Recursive cleaning for array [] inputs, not just strings.
    $sanitized = static::arrayStripTags(Input::get());
    Input::merge($sanitized);
}

function arrayStripTags($array)
{
    $result = array();

    foreach ($array as $key => $value) {
        // Don't allow tags on key either, maybe useful for dynamic forms.
        $key = strip_tags($key);

        // If the value is an array, we will just recurse back into the
        // function to keep stripping the tags out of the array,
        // otherwise we will set the stripped value.
        if (is_array($value)) {
            $result[$key] = static::arrayStripTags($value);
        } else {
            // I am using strip_tags(), you may use htmlentities(),
            // also I am doing trim() here, you may remove it, if you wish.
            $result[$key] = trim(strip_tags($value));
        }
    }

    return $result;
}
function display_projects(){
	$projects = array('2','3','4'); //active,active and freezed,multiple proposal
	return $projects;
}
function display_services(){
	$services = array('2','4'); //active,freezed
	return $services;
}
function services_purchased(){
	$purchased = array('1','2','3','4','5','6','11');
	return $purchased;
}
function featuredproject(){
	$fproject = array("price" => "500" , "currency" => "INR", "days"=>"15");
	return $fproject;
}
function featuredservice(){
	$fservice = array("price" => "500" , "currency" => "INR", "days"=>"15");
	return $fservice;
}
function paypal_amount($inr_amount){
	$paypal_rate = Fixed::where('key','=','paypal_fee')->first();
	$usdconv = $paypal_rate['value'];
	$usdamount = $inr_amount * $usdconv;
	$usdamount = $usdamount + 0.50;
	return $usdamount;
}
function stripe_amount($inr_amount){
	$exachange_rate 	= Fixed::where('key','=','TL_INR_USD')->first();
	$usdconv 			= $exachange_rate['value'];
	$usdamount 			= $inr_amount * $usdconv;
	$usdamount 			= round(($usdamount + $usdamount * Config::get('app.STRIPE_TXN')), 2);
	$usdamount 			= $usdamount + Config::get('app.STRIPE_TXN_EXTRA');
	return $usdamount;
}

function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
    $output = NULL;
    if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($deep_detect) {
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    }
    $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
    $support    = array("country", "countrycode", "state", "region", "city", "location", "address");
    $continents = array(
        "AF" => "Africa",
        "AN" => "Antarctica",
        "AS" => "Asia",
        "EU" => "Europe",
        "OC" => "Australia (Oceania)",
        "NA" => "North America",
        "SA" => "South America"
    );
    if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
        $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
        if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
            switch ($purpose) {
                case "location":
                    $output = array(
                        "city"           => @$ipdat->geoplugin_city,
                        "state"          => @$ipdat->geoplugin_regionName,
                        "country"        => @$ipdat->geoplugin_countryName,
                        "country_code"   => @$ipdat->geoplugin_countryCode,
                        "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                        "continent_code" => @$ipdat->geoplugin_continentCode
                    );
                    break;
                case "address":
                    $address = array($ipdat->geoplugin_countryName);
                    if (@strlen($ipdat->geoplugin_regionName) >= 1)
                        $address[] = $ipdat->geoplugin_regionName;
                    if (@strlen($ipdat->geoplugin_city) >= 1)
                        $address[] = $ipdat->geoplugin_city;
                    $output = implode(", ", array_reverse($address));
                    break;
                case "city":
                    $output = @$ipdat->geoplugin_city;
                    break;
                case "state":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "region":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "country":
                    $output = @$ipdat->geoplugin_countryName;
                    break;
                case "countrycode":
                    $output = @$ipdat->geoplugin_countryCode;
                    break;
            }
        }
    }
    return $output;
}


function update_score($user){
	$score = 0;
	if($user->cscore > $user->fscore && $user->fscore > 50){
		$ms = $user->fscore + (100 / ($user->cscore - $user->fscore));
		if($ms){
			$score = $ms;
		}else{
			$score = 0;
		}
	}
	elseif($user->fscore > $user->cscore && $user->fscore > 50 && $user->cscore > 50){
		$ms = $user->fscore + (100 / ($user->fscore - $user->cscore));
		if($ms){
			$score = $ms;
		}else{
			$score = 0;
		}
	}
	else{
		$score = $user->fscore;
	}
	return $score;
}
function autocorrect_email($email){
	$domain = explode('@', $email);
	$domain = explode('.', $domain[1]);
	$email = str_replace('.coom', '.com', $email);

	if(strlen($domain[0]) > 3 && ((strlen($domain[0]) < 7) || (strlen($domain[0]) < 8))){
		if(substr($domain[0],0,1) == 'g'){
			$patterns = array('/'.$domain[0].'/','/'.$domain[1].'/');
			$replacements = array('gmail','com');
			$emailid = preg_replace($patterns, $replacements, $email);
		}elseif(substr($domain[0],0,1) == 'y'){
			if(isset($domain[2])){
				$patterns = array('/'.$domain[0].'/','/'.$domain[1].'/','/'.$domain[2].'/');
				$replacements = array('yahoo','co',$domain[2]);
				$emailid = preg_replace($patterns, $replacements, $email);
			}else{
				$patterns = array('/'.$domain[0].'/','/'.$domain[1].'/');
				$replacements = array('yahoo','com');
				$emailid = preg_replace($patterns, $replacements, $email);
			}
		}elseif(substr($domain[0],0,1) == 'r'){
			$patterns = array('/'.$domain[0].'/','/'.$domain[1].'/');
			$replacements = array('rediff','com');
			$emailid =  preg_replace($patterns, $replacements, $email);
		}else{
			$emailid = $email;
		}
	}else{
		$emailid = $email;
	}
	return $emailid;
}
function discount_percent(){
	$percent = array('10','15','20','25','30','40','50','60');
	return $percent;
}
function discount_duration(){
	$duration = array("1"=>"1 Day","2"=>"2 Day","7"=>"7 Days");
	return $duration;
}
function proposal_credit($credit = 0){
	$credits = array(
		array("credit"=>"5","amount"=>"200"),
		array("credit"=>"10","amount"=>"300"),
		array("credit"=>"25","amount"=>"600"),
		array("credit"=>"50","amount"=>"1000")
	);
	if($credit == 0){
		return $credits;
	}else{
		$amount = 0;
		foreach ($credits as $c) {
			if($c['credit'] == $credit){
				$amount = $c['amount'];
			}
		}
		return $amount;
	}

}
function availability_status(){
	$availability = array(
		array("type"=>"1","status"=>"Full-time : 30+ hrs/week"),
		array("type"=>"2","status"=>"Part-time : 10-30 hrs/week"),
		array("type"=>"3","status"=>"As needed : Less than 10 hrs/week"),
		array("type"=>"4","status"=>"Not sure at this time"),
		array("type"=>"5","status"=>"Currently on Leave"),
		array("type"=>"6","status"=>"Not Available to hire")
	);
	return $availability;
}
function experience_years(){
	$years = array('0','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20');
	return $years;
}
function experience_months(){
	$months = array('00','01','02','03','04','05','06','07','08','09','10','11');
	return $months;
}
function posting_currency(){
	$currencys  = (object) array(
			array("currency" => "INR"),
			array("currency" => "USD")
		);
	return $currencys;
}
function adminmsgarray(){
	$messages = array(
		array("title"=>"Pool Invite","subject"=>"POOL-INVITE","detail"=>"POOL-INVITE Template"),
		array("title"=>"Update Contact Details","subject"=>"Action Required : Truelancer Profile","detail"=>"Kindly update your Contact details like Skype ID and Contact number in your profile. So that we can Contact you to get your problems resolved swiftly and efficiently."),
		array("title"=>"Update Bank Details ","subject"=>"Truelancer Action Required : Update Bank Details","detail"=>"Kindly add your Bank account details so that we can process your payments."),
		array("title"=>"Warning: Contact Shared","subject"=>"Truelancer : WARNING Notification","detail"=>"We have noticed that you shared your Contact details with another User directly. Sharing Contact details like Phone Number, Email ID and Skype is not allowed as per Truelancer's Terms and Conditions. Your account may be suspended permanently if our automated filters or review team spots you repeating this violation. <br>Please be advised, that by driving clients away from Truelancer platform, you will not only risk your funds but also your ratings on Truelancer platform. Higher Ratings and good Feedbacks on Truelancer will help you get more and better proposals on our platform in future. We look forward for a long term relationship with our Users and expect the same from users of our platform."),
		array("title"=>"Verify Card Details","subject"=>"Truelancer Action Required : Payment Verification","detail"=>"Kindly verify your recent payment to Truelancer for Hiring the Freelancer as per process below. http://www.truelancer.com/blog/credit-card-verification"),
		array("title"=>"Skype Employer","subject"=>"Connect with Truelancer Support ","detail"=>"Please add truelancer.support on Skype so that our Team can contact you directly and help you in getting your Project done quickly.")
	);
	return $messages;
}
function adminworkstreammsgarray($buyer,$seller){
	$messages = array(
		array("receiverid" => $buyer->id , "usertype" => "Buyer" ,"receivername" => $buyer->fname),
		array("receiverid" => $seller->id, "usertype" => "Seller" ,"receivername" => $seller->fname),
	);
	return $messages;
}
function adminbuyermsg($buyer){
	$buyermessages = array(
		array("title" => "Payment Approve" , "detail" => "Dear ".$buyer->fname." ,\nKindly review the work and suggest changes or approve the Work Completion Request and write feedback for the freelancer."),
		array("title" => "Feedback"        , "detail" => "Dear ".$buyer->fname." ,\nKindly write a feedback for the Freelancer to complete the process."),
		array("title" => "Refund Request"  , "detail" => "Dear ".$buyer->fname." ,\nKindly create a Refund Request from the bottom left of the workstream."),
		array("title" => "Work Feedback"  , "detail" => "Dear ".$buyer->fname." ,\nKindly share your feedback regarding the Project Progress till now."),
		array("title" => "Update Urgently", "detail" => "Dear ".$buyer->fname." ,\nIt has been quite a long time since you last took an update about this project in this Workstream. Please take regular updates from your Freelancer in Truelancer Workstream so that it is in our records and we can help you in case of any conflicts."),
	);
	return $buyermessages;
}
function adminsellermsg($seller){
	$sellermessages = array(
		array("title" => "Respond to Buyer" , "detail" => "Dear ".$seller->fname." ,\nWe have noticed that you haven't responded to your Employer's message. Please respond to Employer and address his question/concerns. Replying promptly to Employer's questions/concerns is important as it reflects your professionalism."),
		array("title" => "Work Completion"  , "detail" => "Dear ".$seller->fname." ,\nCreate Work Completion if you have completed the work."),
		array("title" => "Feedback"         , "detail" => "Dear ".$seller->fname." ,\nKindly write a feedback for the Client."),
		array("title" => "Progress Update"  , "detail" => "Dear ".$seller->fname." ,\nKindly share the work progress and updates regularly in the workstream so that Truelancer team and Employer is updated. Keeping Workstream updated also reflects your professionalism."),
		array("title" => "Update Urgently"  , "detail" => "Dear ".$seller->fname." ,\nIt has been quite a long time since you last updated the work progress in this Workstream. Please update the Employer in Truelancer Workstream regularly so that it is in our records and we can help you in case of any conflicts."),
	);
	return $sellermessages;
}
function unsubscribe_link($uid,$email,$campaign){
	$base_64 = base64_encode($uid.','.$email);
	$unsubscribe_link = url('mail/unsubscribe/'.rtrim($base_64, '=').'/'.$campaign);
	return	$unsubscribe_link;
}
function string_sanitize($string){
	$string =  strip_tags($string);
	$string = str_replace('(', '', $string);
	$string = str_replace(')', '', $string);
	$string = str_replace('"', '', $string);
	return $string;
}
function update_skillrating($jobid,$userid,$rating){
	try{
		$jobskills  = Jobskill::select('skill_id')->where('job_id','=',$jobid)->lists('skill_id');
		$userskills = Userskill::where('user_id','=',$userid)->whereIN('skill_id',$jobskills)->get();
		foreach($userskills as $userskill){
			$userskill->rating = $userskill->rating + $rating;
			$userskill->skill_score_c15days = $userskill->skill_score_c15days + $userskill->skill_score_l15days + $rating;
			$userskill->save();
		}
	}
	catch(Exception $e){

	}
}
function isAjaxUrl($url = '') {
	if ($url == '') {
		return false;
	}
	$matched = stristr($url, '/ajax/');
	if (!$matched) {
		return false;
	}
	return true;
}

function minutes_diff($olddate,$curdate){
	$datetime1 = strtotime($olddate);
	$datetime2 = strtotime($curdate);
	$interval  = abs($datetime2 - $datetime1);
	$minutes   = round($interval / 60);
	return $minutes;
}

function validateEmail($email)
{
	$regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/';

	$res = (bool) preg_match($regex, $email);

	//verfiy the for the virtual inboxes
	if ($res) {
		list($username, $domain) = explode('@', $email);
		$virtualInboxes = Config::get('app.virtual_inboxes', array());
		if (in_array($domain, $virtualInboxes)) {
			return false;
		}
	}

	return $res;
}
function partial_work_percent(){
	$percent = array('10','25','50','75');
	return $percent;
}
function document_type($countrycode){
	$document = array("1" => 'Driving License' ,"3" => 'Passport');
	if($countrycode == 'IN'){
		$document["2"] =  'Pan Card';
	}
	return $document;
}
function document_params($id){
	switch ($id) {
		case '1':
			$params = array('name' => 'Legal Name','no' => 'Number' );
		break;
		default:
			$params = array('name' => 'Legal Name','no' => 'Number' );
		break;
	}
	return json_encode($params);
}

function currencyFormat($currency, $amount)
{
	if ( Config::get('app.currency_represent') == 'symbol') {
		return currency_symbol($currency).' '.number_format($amount,  Config::get('app.currency_decimal'), '.', '');
	}
	else {
		return $currency.' '.number_format($amount,  Config::get('app.currency_decimal'), '.', '');
	}
}
function transaction_exchangerate(){
	$exchangeRate = Fixed::where('key','=','TL_INR_USD')->first();
	return $exchangeRate['value'];
}
function order_difference($request,$conv_currency = 'INR'){
	$order_diff = in_session_currency($request['currency'],round($request['amount'],2)) - in_session_currency($request['currency'],round($request['wallet_balance'],2),'2');
	if($conv_currency == 'USD'){
		return $order_diff;
	}else{
	    $order_diff = convert_currency(Session::get('currency'),'INR',$order_diff, 3);
		return $order_diff;
	}
}

function contestcategory(){
	$titles = array(
		array("categoryid"=>"24","title" => "Design a Logo","detail" => "Design a Logo"),
		array("categoryid"=>"25","title" => "Design a Business Card","detail" => "Design a Business Card."),
		array("categoryid"=>"34","title" => "Design a Banner","detail" => "Design a Banner"),
		array("categoryid"=>"23","title" => "Design a Website Mockup","detail" => "Design a Website Mockup"),
		array("categoryid"=>"25","title" => "Design a Graphic","detail" => "Design a Graphic"),
		array("categoryid"=>"25","title" => "Design some Stationary","detail" => "Design some Stationary"),
		array("categoryid"=>"43","title" => "Create a Video","detail" => "Create a Video"),
		array("categoryid"=>"25","title" => "Design a T-Shirt","detail" => "Design a T-Shirt"),
		array("categoryid"=>"30","title" => "Design a Brochure","detail" => "Design a Brochure"),
		array("categoryid"=>"23","title" => "Design an App Mockup","detail" => "Design an App Mockup")
	);
	return $titles;
}

function categories(){
	$cate = array(
		array("categoryid"=>"23","title" => "Website and App Mockup","catname" => "Website and App Mockup"),
		array("categoryid"=>"24","title" => "Logo Design","catname" => "Logo Design"),
		array("categoryid"=>"25","title" => "Business Card","catname" => "Business Card"),
		array("categoryid"=>"30","title" => "Brochure","catname" => "Brochure"),
		array("categoryid"=>"34","title" => "Banner","catname" => "Banner"),
		array("categoryid"=>"43","title" => "Video","catname" => "Video")
	);
	return $cate;
}
function get_contest_category($catid){
	$categories = categories();
	foreach ($categories as $category) {
		if($category['categoryid'] == $catid){
			return $category['catname'];
		}
	}
	return '';
}
function entryrating(){
	$ratings = array("1" => "Very Poor", "2" => "Poor","3" => "Fair","4" => "Nice","5" => "Good",
		"6" => "Very Good","7" => "Superb","8" => "Awesome","9" => "Excellent","10" => "Outstanding"
	);
	return $ratings;
}

function unparse_url($parsed = array()) {
	$scheme   =& $parsed['scheme'];
	$host     =& $parsed['host'];
	$port     =& $parsed['port'];
	$user     =& $parsed['user'];
	$pass     =& $parsed['pass'];
	$path     =& $parsed['path'];
	$query    =& $parsed['query'];
	$fragment =& $parsed['fragment'];

	$userinfo  = !strlen($pass) ? $user : "$user:$pass";
	$host      = !"$port" ? $host : "$host:$port";
	$authority = !strlen($userinfo) ? $host : "$userinfo@$host";
	$hier_part = !strlen($authority) ? $path : "//$authority$path";
	$url       = !strlen($scheme) ? $hier_part : "$scheme:$hier_part";
	$url       = !strlen($query) ? $url : "$url?$query";
	$url       = !strlen($fragment) ? $url : "$url#$fragment";

	return $url;
}

function urlAppendTracking($url, $tracking)
{
	$tmpUrl = parse_url($url);
	$query = '';
	if (is_string($tracking)) {
		$query = $tracking;
	}
	elseif (is_array($tracking)) {
		$query =  http_build_query($tracking);
	}

	if ($query != '') {
		$tmpUrl['query'] = (isset($tmpUrl['query'])) ? $tmpUrl['query'].'&'.$query : $query;
	}

	return unparse_url($tmpUrl);

}
