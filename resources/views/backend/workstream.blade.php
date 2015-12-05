<?php
use App\Workstream;
?>
@extends('layouts/main')
@section('content')
<style>
.stat-card {
	min-height : 200px;
}
</style>
<div class="row">

	<div class="m-t">
	<div class="col-sm-3 col-xs-6">
		<div class="card text-center stat-card">
		  <div class="card-block">
		    <h4 class="card-title">{{$totalWorkstreams}}</h4>
		   	 <p class="card-text">Total workstreams</p>
		   	<div>{{$totalJobs}} <small>(over Jobs)</small></div>
		  </div>
		</div>
	</div>
	
	<div class="col-sm-3 col-xs-6">
		<div class="card text-center stat-card">
		  <div class="card-block">
		    <h4 class="card-title">{{$totalWorkstreamsActive}}</h4>
		    <p class="card-text">Total workstreams discussed</p>
		   	<div>{{$workstreamsActivevsAll}}% <small>(Active vs All)</small></div>
		  </div>
		</div>
	</div>
	
	<div class="col-sm-3 col-xs-6">
		<div class="card text-center stat-card">
		  <div class="card-block">
		    <h4 class="card-title">{{$totalWorkstreamsAwarded}}</h4>
		    <p class="card-text">Total workstreams awarded</p>
		    <div>{{$workstreamsAwardedvsActive}}% <small>(Awarded vs Active)</small> <br /> {{$workstreamsAwardedvsAll}}% <small>(Awarded vs All)</small></div>
		  </div>
		</div>
	</div>
	
	<div class="col-sm-3 col-xs-6">
		<div class="card text-center stat-card">
		  <div class="card-block">
		    <h4 class="card-title">{{$totalWorkstreamsCompleted}}</h4>
		    <p class="card-text">Total workstreams completed</p>
		    <div>{{$workstreamsCompletedvsAwarded}}% <small>(Completed vs Awarded)</small> <br /> {{$workstreamsCompletedvsActive}}% <small>(Completed vs Active)</small> <br /> {{$workstreamsCompletedvsAll}}% <small>(Completed vs All)</small></div>
		  </div>
		</div>
	</div>
	</div>
	<div class="m-t-md col-md-12 col-sm-12 col-xs-12">
		<table class="table card">
			<thead>
				<tr>
					<th>Job Id</th>
					<th class="text-center">Timeline</th>
					<th>Title</th>
					<th><span title="Discussed" data-toggle="tooltip" data-placement="top">D</span> | <span title="Awarded" data-toggle="tooltip" data-placement="top">A</span> | <span title="Completed" data-toggle="tooltip" data-placement="top">C</span></th>
					<th>
						<div class="dropdown">
						  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						    JobType
						  </button>
						  <div class="dropdown-menu" aria-labelledby="dropdownMenu1">
						    <a class="dropdown-item" href="?jobtype=fixed">Fixed Job</a>
						     <a class="dropdown-item" href="?jobtype=hourly">Hourly action</a>
						     <a class="dropdown-item" href="?jobtype=services">Services</a>
						    <a class="dropdown-item" href="?jobtype=all">All</a>
						  </div>
						</div>
					</th>
				</tr>
			</thead>
			<tbody>
				@foreach($workstreams as $workstream)
				
				<?php  $job = $workstream->job;	
				$workstreamsTmp = Workstream::select(array('status' , DB::raw('count(id) as counts')))->where('type_id', $workstream->type_id)->where('type', 0)->groupBy('status')->lists('counts', 'status');

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

				switch($workstream->status) {
					case 1 : $status = "Active";break;
					case 2 : $status = "Working";break;
					case 3 : $status = "Work Complete";break;
					case 4 : $status = "Payment Approved";break;
					case 5 : $status = "Employer Feedback";break;
					case 6 : $status = "Freelancer Feedback";break;

					case 7 : $status = "Refund";break;
					case 8 : $status = "Conflict";break;
					case 9 : $status = "Closed";break;
					default : $status = "NA";break;
				}
				?>
				<tr>
					<td>{{ $job->id }}</td>
					<td class="text-center" title="{{$status}}">
						@if(!in_array($workstream->status, array(7,8,9)))
						<?php 
						$color = 'warning';
						if(in_array($workstream->status, array(3,4))) {
							$color = 'info';
						}
						if(in_array($workstream->status, array(5,6,11))) {
							$color = 'success';
						}
						?>
						<progress class="progress progress-{{$color}}" value="{{$workstream->status}}" max="11"></progress>
						
						@else
						<div class="text-center"><small>{{$status}}</small></div>
						@endif

					</td>
					<td>{{ $job->title }}</td>
					<td>{{ $workstreamsTmp[1]  }} | {{ $workstreamsTmp[2] }} | {{ $workstreamsTmp[11] }}</td>
					<td>
						{{ ($job->jobtype == 1) ? 'Service' : ( ($job->jobtype == 2) ? 'Fixed Job' : 'Hourly Job') }}  
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		<div class="text-center">
			<?php echo $workstreams->render(); ?>
		</div>
	</div>
</div>
<script type="text/javascript">
//Pie chart example data. Note how there is only a single array of key-value pairs.
function chartData() {
	return {};
}

function statsData()
{
	return {};
}
function revenueData()
{
	return {};
}
</script>
@stop

