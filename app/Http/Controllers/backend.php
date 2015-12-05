<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Job;
use App\Proposal;
use App\User;
use App\Transaction;
use App\Workstream;
use App\Workstreaminteraction;
use App\Order;
use App\Fund;
use DB;
use Illuminate\Support\Facades\Config;
class backend extends Controller
{
	public function display(Request $request)
	{
		
		$jobType = $request->input('jobtype', 'all');
		switch ($jobType) {
			case 'fixed' : $jobType = 2; break;
			case 'hourly' : $jobType = 3; break;
			case 'services' : $jobType = 1; break;
			default : $jobType = 0;
		}
		
		$startDate = date('Y-m-d', strtotime('-12 month'));
		$endDate =date('Y-m-d');

		$jobModel = new Job();
		
		$jobCounts 	= $jobModel->select('jobtype', DB::raw('count(id) as counts'))->where('updated_at', '>=', $startDate)->where('updated_at', '<=', $endDate)->groupBy('jobtype')->get()->toArray();

		$chartData = array();
		foreach ($jobCounts as $counts) {
			
			switch ($counts['jobtype']){
				case 1 : $label = 'Services'; break;
				case 2 : $label = 'Fixed'; break;
				case 3 : $label = 'Hourly'; break;
				default : $label = 'All';
			}
			$chartData[] = array('label' => $label, 'value' => (int)$counts['counts']);
		}

		$jobStats 	= $jobModel->select(DB::raw('count(id) as counts'), DB::raw('date(updated_at) as date'))->whereIn('jobtype', array(2,3))->where('status', 6)->where('updated_at', '>=', $startDate)->where('updated_at', '<=', $endDate)->groupBy('status')->groupBy('updated_at')->get()->toArray();

		$statsData = array();
		$maxValue  = 3;
		foreach ($jobStats as $stats) {
			$statsData[strtotime($stats['date'])] = (int)$stats['counts'];
			$maxValue = ($stats['counts'] >$maxValue) ? (int)$stats['counts'] : $maxValue;
		}

		$xtmp = array();
		for($i=365; $i>=0; $i--){
			$date= strtotime($endDate.'-'.$i.' day');

			if(isset($statsData[$date])) {
				$xtmp[] = array('x'=>$date, 'y'=> $statsData[$date]);
			}else{
				$xtmp[] = array('x'=>$date, 'y'=> 0);
			}

		}

		$tmp[] = array('key'=> 'Project completed', 'values' => $xtmp);
		
	

		$totalProjects 	= $jobModel->where('status', '>' ,0)->whereIn('jobtype', array(2,3))->where('updated_at', '>=', $startDate)->where('updated_at', '<=', $endDate)->count();
		$totalPostedProjects =  $jobModel->whereIn('jobtype', array(2,3))->where('updated_at', '>=', $startDate)->where('updated_at', '<=', $endDate)->count();		
		$projectStats 	= $jobModel->select('status', DB::raw('count(id) as counts'))->whereIn('jobtype', array(2,3))->where('status', '>' ,0)->where('updated_at', '>=', $startDate)->where('updated_at', '<=', $endDate)->groupBy('status')->lists('counts','status');

		$serviceStats 	= $jobModel->select('status', DB::raw('count(id) as counts'))->where('jobtype', 1)->where('updated_at', '>=', $startDate)->where('updated_at', '<=', $endDate)->groupBy('status')->lists('counts','status');
		$totalPostedServices = array_sum($serviceStats->all());

		$services = $jobModel->select('id')->where('jobtype', 1)->lists('id');
		$serviceWorkstream  = Workstream::select('type_id', DB::raw('count(id) as counts'))->where('type', 0)->whereIn('type_id', $services)->where('status',11)->where('updated_at', '>=', $startDate)->where('updated_at', '<=', $endDate)->groupBy('type_id')->lists('counts','type_id');

		$serviceWorkstream = $serviceWorkstream->all();
		$totalSold = array_sum($serviceWorkstream);
		$totalSellingServices = count($serviceWorkstream);
		

		if ($jobType != 0) {
			$jobModel = $jobModel->where('jobtype', $jobType);
		}
		$jobs 		= $jobModel->where('updated_at', '>=', $startDate)->where('updated_at', '<=', $endDate)->orderBy('id', 'desc')->paginate(10);

		$data = array();
		$data['page'] = 'job';
		$data['totalPostedProjects'] = $totalPostedProjects;
		$data['totalProjects'] = $totalProjects;
		$data['jobs'] = $jobs;
		$data['chartData'] = $chartData;
		$data['statsData'] = $tmp;
		$data['totalCompletedProject'] = (isset($projectStats[6])) ? $projectStats[6] : 0;
		$data['totalPostedServices'] = $totalPostedServices;
		$data['totalActiveServices'] = (isset($serviceStats[2])) ? $serviceStats[2] : 0;
		$data['totalSellingServices'] = $totalSellingServices;
		$data['totalSold'] = $totalSold;
		$data['maxValue'] = $maxValue;
	
		//how many jobs are posted and how many are  completed
		//calculate project convertion ratio
		
		return view('backend.display')->with($data);
	}
	
	public function workstream(Request $request)
	{
		$data = array();

		$workstreamInteractionIds = Workstreaminteraction::select('workstream_id')->groupBy('workstream_id')->orderBy('updated_at', 'desc')->lists('workstream_id');
		$Ids = implode(',', $workstreamInteractionIds->all());

		$workstreamJobs = Workstream::select(array('*', DB::raw('count(type_id) as counts')))->where('type', 0)->whereIn('id', $workstreamInteractionIds)->groupBy('type_id')->orderByRaw(DB::raw("FIELD(id, $Ids)"))->paginate(10);

		$workstreamsTmp = Workstream::select(array('status' , DB::raw('count(type_id) as counts')))->where('type', 0)->groupBy('status')->lists('counts', 'status');
		
		$data['totalWorkstreams'] = $totalWorkstreams = Workstream::where('type', 0)->count();
		
		$data['totalJobs'] = Workstream::select(array(DB::raw('count(type_id) as counts')))->where('type', 0)->groupBy('type_id')->lists('counts')->count(); 

		$workstreamsTmp[1] = (isset($workstreamsTmp[1])) ? $workstreamsTmp[1] : 0;
		$workstreamsTmp[2] = (isset($workstreamsTmp[2])) ? $workstreamsTmp[2] : 0;
		$workstreamsTmp[11] = (isset($workstreamsTmp[11])) ? $workstreamsTmp[11] : 0;
		foreach ($workstreamsTmp as $status => $count) {
			if($status > 1) {
				$workstreamsTmp[1] =$workstreamsTmp[1] + $count;
			}
			if($status > 2) {
				$workstreamsTmp[2] =$workstreamsTmp[2] + $count;
			}
		}
		
		$data['totalWorkstreamsActive'] = $totalWorkstreamsActive = $workstreamsTmp[1];
		$data['totalWorkstreamsAwarded'] = $totalWorkstreamsAwarded = $workstreamsTmp[2];
		$data['totalWorkstreamsCompleted'] = $totalWorkstreamsCompleted = $workstreamsTmp[11];
		
		$data['workstreamsActivevsAll'] = number_format(($totalWorkstreamsActive/$totalWorkstreams)*100, 2);
		
		$data['workstreamsAwardedvsActive'] = number_format(($totalWorkstreamsAwarded/$totalWorkstreamsActive)*100, 2);
		$data['workstreamsAwardedvsAll'] = number_format(($totalWorkstreamsAwarded/$totalWorkstreams)*100, 2);
		
		$data['workstreamsCompletedvsAwarded'] = number_format(($totalWorkstreamsCompleted/$totalWorkstreamsAwarded)*100, 2);
		$data['workstreamsCompletedvsActive'] = number_format(($totalWorkstreamsCompleted/$totalWorkstreamsActive)*100, 2);
		$data['workstreamsCompletedvsAll'] = number_format(($totalWorkstreamsCompleted/$totalWorkstreams)*100, 2);
		
		$data['workstreams'] = $workstreamJobs;
		$data['page'] = 'workstream';
		
		return view('backend.workstream')->with($data);
	}
	
	public function revenue(Request $request)
	{
		$data = array();
		$data['page'] = 'revenue';
		
		//total turnoveUserr
		$totalTurnoverINR = Order::where('payment_status', 2)->where('currency', 'INR')->sum('amount');
		$totalTurnoverUSD = Order::where('payment_status', 2)->where('currency', 'USD')->sum('amount');

		if($totalTurnoverUSD > 0) {
			$totalTurnoverINR = $totalTurnoverINR + convert_currency('USD', 'INR', $totalTurnoverUSD);
		}
		$data['totalTurnover'] =  number_format($totalTurnoverINR, 2, '.',',');

		$totalFeeINR = Order::where('payment_status', 2)->where('currency', 'INR')->sum('txnfee');
		$totalFeeUSD = Order::where('payment_status', 2)->where('currency', 'USD')->sum('txnfee');
		
		if($totalFeeUSD > 0) {
			$totalFeeINR = $totalFeeINR + convert_currency('USD', 'INR', $totalFeeUSD);
		}
		$data['totalTxnFee'] =  number_format($totalFeeINR, 2, '.',',');
		
		//earnings
		$totalCredit = Fund::select(array(DB::raw('sum(amount) as amount'), 'currency'))->where('txn_type', 1)->groupBy('currency')->lists('amount', 'currency');
		
		if(isset($totalCredit['USD']) && $totalCredit['USD'] > 0) {
			$totalCredit['INR'] = $totalCredit['INR'] + convert_currency('USD', 'INR', $totalCredit['USD']);
		}
		$totalCredit =  $totalCredit['INR'];
		
		//refunds
		$totalDebit = Fund::select(array(DB::raw('sum(amount) as amount'), 'currency'))->where('txn_type', 0)->groupBy('currency')->lists('amount', 'currency');
		
		if(isset($totalDebit['USD']) && $totalDebit['USD'] > 0) {
			$totalDebit['INR'] = $totalDebit['INR'] + convert_currency('USD', 'INR', $totalDebit['USD']);
		}
		$totalDebit  =  $totalDebit['INR'];
		//total income
		$data['totalRevenue'] = number_format(($totalCredit-$totalDebit), 2, '.',',');
		$data['totalCredit'] = number_format(($totalCredit), 2, '.',',');
		$data['totalDebit'] = number_format(($totalDebit), 2, '.',',');
		
		//total income from jobs / services / contest
		$totalCreditINR = Fund::select(array(DB::raw('sum(amount) as amount'), 'source_type'))->where('txn_type', 1)->where('currency', 'INR')->groupBy('source_type')->lists('amount', 'source_type');
		$totalCreditUSD = Fund::select(array(DB::raw('sum(amount) as amount'), 'source_type'))->where('txn_type', 1)->where('currency', 'USD')->groupBy('source_type')->lists('amount', 'source_type');
		
		foreach ($totalCreditINR as $key => $credits) {
			$tmpInr = (isset($totalCreditUSD[$key]))?  convert_currency('USD', 'INR', $totalCreditUSD[$key]) : 0.0;
			$totalCreditINR[$key] = $totalCreditINR[$key] + $tmpInr;
		}

		$startDate = date('Y-m-d', strtotime('-12 month'));
		$endDate =date('Y-m-d');

		$fundStatsUSD= Fund::select(DB::raw('sum(amount) as amount'), DB::raw('date(created_at) as date'))->where('txn_type', 1)->where('currency', 'USD')->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)->groupBy('source_type')->groupBy('updated_at')->lists('amount', 'date');
		$fundStatsINR = Fund::select(DB::raw('sum(amount) as amount'), DB::raw('date(created_at) as date'))->where('txn_type', 1)->where('currency', 'INR')->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)->groupBy('source_type')->groupBy('updated_at')->lists('amount', 'date');

		$debitStatsUSD= Fund::select(DB::raw('sum(amount) as amount'), DB::raw('date(created_at) as date'))->where('txn_type', 0)->where('currency', 'USD')->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)->groupBy('source_type')->groupBy('updated_at')->lists('amount', 'date');
		$debitStatsINR = Fund::select(DB::raw('sum(amount) as amount'), DB::raw('date(created_at) as date'))->where('txn_type', 0)->where('currency', 'INR')->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)->groupBy('source_type')->groupBy('updated_at')->lists('amount', 'date');
		
		$statsDataUSD = array();
		foreach ($fundStatsUSD as  $date => $amount) {
			$statsDataUSD[strtotime($date)] = convert_currency('USD', 'INR', $amount);			
		}

		$statsData = array();
		$maxValue  = 50;
		foreach ($fundStatsINR as $date => $amount) {
			$date = strtotime($date);
			$tmp = (isset($statsDataUSD[$date]))? (floatval($amount) + $statsDataUSD[$date])  : floatval($amount);
			$statsData[$date] = $tmp;
			$maxValue = ($tmp >$maxValue) ? $tmp : $maxValue;
		}

		$statsDebitDataUSD = array();
		foreach ($debitStatsUSD as  $date => $amount) {
			$statsDebitDataUSD[strtotime($date)] = convert_currency('USD', 'INR', $amount);			
		}

		$statsDebitData = array();
		foreach ($debitStatsINR as $date => $amount) {
			$date = strtotime($date);
			$tmp = (isset($statsDebitDataUSD[$date]))? (floatval($amount) + $statsDebitDataUSD[$date])  : floatval($amount);
			$statsDebitData[$date] = $tmp;
		}

		$xtmp = array();
		
		for($i=365; $i>=0; $i--){
			$date= strtotime($endDate.'-'.$i.' day');

			if(isset($statsData[$date])) {
				$xtmp[] = array('x'=>$date, 'y'=> $statsData[$date]);
			}else{
				$xtmp[] = array('x'=>$date, 'y'=> 0);
			}

			if(isset($statsDebitData[$date])) {
				$debitTmp[] = array('x'=>$date, 'y'=> $statsDebitData[$date]);
			}else{
				$debitTmp[] = array('x'=>$date, 'y'=> 0);
			}
		}

		$tmp =  array();
		$tmp[] = array('key'=> 'Earnings', 'values' => $xtmp, 'color' => '#2ca02c');
		$tmp[] = array('key'=> 'Redunds & Chargebacks', 'values' => $debitTmp, 'color' => '#ff7f0e');


		$data['totalCreditJob'] = (isset($totalCreditINR['1'])) ? number_format($totalCreditINR['1'], 2, '.',',') : 0.00;
		$data['totalCreditExtInv'] =(isset($totalCreditINR['2'])) ? number_format($totalCreditINR['2'], 2, '.',',') : 0.00;
		$data['totalCreditMembership'] = (isset($totalCreditINR['3'])) ? number_format($totalCreditINR['3'], 2, '.',',') : 0.00;
		$data['totalCreditFeatured'] = (isset($totalCreditINR['4'])) ? number_format($totalCreditINR['4'], 2, '.',',') : 0.00;
		$data['totalCreditProposalBuy'] =(isset($totalCreditINR['5'])) ? number_format($totalCreditINR['5'], 2, '.',',') : 0.00;
		$data['totalCreditTxnAdmin'] = (isset($totalCreditINR['6'])) ? number_format($totalCreditINR['6'], 2, '.',',') : 0.00;
		$data['totalCreditPartialPay'] = (isset($totalCreditINR['7'])) ? number_format($totalCreditINR['7'], 2, '.',',') : 0.00;
		$data['revenueData'] = $tmp;
		$data['maxValue'] = $maxValue;
		
		return view('backend.revenue')->with($data);
	}
	
	public function inform(Request $request)
	{
		$jobId = $request->input('jobId', 0);
		$job = Job::find($jobId);
		
		if(empty($job)) {
			$job = false;
		}
		
		if($request->has('inform')) {
			//send mail
			$content = $request->input('inform', array());
			//$job->user->sendMail($content['content']);
			//show notification
			return redirect()->action('backend@display');
		}
		
		return view('backend.inform')->with(array('job' =>$job, 'page' => 'inform'));
	}
	
	public function social(Request $request)
	{
		$data =  array();
		$data['page'] = 'social';
		$accessToken = Config::get('app.accessToken.fb', '');
// 		$jsonData = file_get_contents("https://graph.facebook.com/truelancerofficial?access_token=$accessToken&fields=likes");
// 		$jsonData = json_decode($jsonData);
//		$likes =$jsonData->likes;
		$data['facebook'] = 84712;
		$data['twitter'] = 3000;
		$data['googlePlus'] = '?';

		return view('backend.social')->with($data);
	}
}
