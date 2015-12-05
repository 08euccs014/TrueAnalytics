@extends('layouts/main')
@section('content')

<div class="m-t-md">
	
	<div class="row">
		<div class="col-sm-3 col-xs-6">
			<div class="card text-center stat-card">
			  <div class="card-block">
			    <h4 class="card-title">{{$totalProjects}}</h4>
			   	 <p class="card-text" title="Including expired and closed">Total Projects(approved)</p>
			   	<div>{{$totalPostedProjects}} <small>(over posted project)</small></div>
			   	<div>{{ number_format(($totalProjects/$totalPostedProjects)*100, 2) }}% <small>(approval rate)</small></div>
			  </div>
			</div>
		</div>
		<div class="col-sm-3 col-xs-6">
			<div class="card text-center stat-card">
			  <div class="card-block">
			    <h4 class="card-title">{{$totalCompletedProject}}</h4>
			   	 <p class="card-text">Total Completed</p>
			   	<div>{{ number_format(($totalCompletedProject/$totalProjects)*100, 2) }}% <small>(conversion rate)</small></div>
			  </div>
			</div>
		</div>
		<div class="col-sm-3 col-xs-6">
			<div class="card text-center stat-card">
			  <div class="card-block">
			    <h4 class="card-title">{{$totalActiveServices}}</h4>
			   	 <p class="card-text">Active Services</p>
			   	 <div title="Including expired and closed">{{$totalPostedServices}} <small>(over posted services)</small></div>
			  </div>
			</div>
		</div>
		<div class="col-sm-3 col-xs-6">
			<div class="card text-center stat-card">
			  <div class="card-block">
			    <h4 class="card-title">{{$totalSold}}</h4>
			   	 <p class="card-text">Services Sold</p>
			   	 <div title="Including expired and closed">{{$totalSellingServices}} <small>(over distinct services)</small></div>
			  </div>
			</div>
		</div>
	</div>
	
		<div class="row p-b-md m-t">
			<div class="col-md-6 col-sm-6 col-xs-12">
				<div id="piechart"  class="center-block"><svg></svg></div>
				<div class="row p-b-lg">
					@foreach($chartData as $data)
					<div class="col-md-4 col-sm-4 col-xs-4">
						<strong> {{	$data['label'] }}</strong>
						<br />
						{{ $data['value'] }}
					</div>
					@endforeach
				</div>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-12 row">
				<div id="statschart"  class="center-block"><svg></svg></div>
			</div>
		</div>

		<table class="col-md-12 col-sm-12 col-xs-12 table card">
			<thead>
				<tr>
					<th>Id</th>
					<th>Title</th>
					<th  class="text-center">Proposals</th>
					<th  class="text-center">Convertion Rate</th>
					<th>Last Access</th>
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
					<th>Lifetime</th>
					<th class="text-center">Actions</th>
				</tr>
			</thead>
			<tbody>
				@foreach($jobs as $job)
				
				<?php
					if ($job->approved_at  > '2012-01-01') {

					$today 				= date('Y-m-d');
					$approveAt 		= date("Y-m-d", strtotime($job->approved_at));
					$date1 				= strtotime($approveAt);
					$date2 				= strtotime($today);
					$datediff 			= abs($date2 - $date1);
					$interval 			= floor($datediff/(60*60*24));
					
					$width = ($interval < 30) ? floor(($interval/30)*100) : 100;
					$color  = ($interval >= 30)? 'danger' : (($interval >= 20 && $interval < 30) ? 'warning' :  (($interval >= 10 && $interval < 20) ? 'info' :  (($interval >= 5 && $interval < 10) ? 'success' : 'success' )));
					}
					else {
						$width = 0;
						$color = 'primary';
						$interval = 0;
					} 
				?>
				<tr>
					<td>{{ $job->id }}</td>
					<td>{{ $job->title }}</td>
					<td class="text-center">{{$job->proposals->where('workstream_id','!=', 0)->count()}}/{{$job->proposals->count()}}</td>
					<td class="text-center">{{($job->proposals->count() > 0) ? ($job->proposals->where('workstream_id','!=', 0)->count()/$job->proposals->count())*100 : 0}}</td>
					<td>{{ date('h:m a  dS M Y', strtotime($job->user->last_access))}}</td>
					<td>
						{{ ($job->jobtype == 1) ? 'Service' : ( ($job->jobtype == 2) ? 'Fixed Job' : 'Hourly Job') }}  
					</td>
					<td>{{$interval}} Days
						<progress class="progress progress-{{$color}}" value="{{$width}}" max="100">{{$width}}%</progress>
					</td>
					<td class="text-center">
						<a class="btn btn-primary" href="{{ url('job/inform?jobId='.$job->id) }}" target="_blank">Send mail</a>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		<div class="text-center">
			<?php echo $jobs->render(); ?>
		</div>
</div>
<script type="text/javascript">
var  maxValue = <?php echo $maxValue ?>;
//Pie chart example data. Note how there is only a single array of key-value pairs.
function chartData() {
	var data = '<?php echo json_encode($chartData); ?>';
	return JSON.parse(data);
}

function statsData()
{
	var data = '<?php echo json_encode($statsData); ?>';
	return JSON.parse(data);
}
$(document).ready(function(){
	nv.addGraph(function() {
		  var chart = nv.models.lineChart()
		    .useInteractiveGuideline(true)
		    ;

		  chart.xAxis
		    .axisLabel('Time')
		    .tickFormat(function(d) { return d3.time.format('%d-%b-%y')(new Date(d*1000)); })
		    ;

		  chart.yAxis
		    .tickFormat(d3.format('d'))
		    ;
		    chart.forceY([0, maxValue]);

		  d3.select('#statschart svg')
		    .datum(statsData())
		    .transition().duration(500)
		    .call(chart)
		    ;

		  nv.utils.windowResize(chart.update);

		  return chart;
	});

	//Donut chart example
	nv.addGraph(function() {
	  var chart = nv.models.pieChart()
	      .x(function(d) { return d.label })
	      .y(function(d) { return d.value })
	      .showLabels(true)     //Display pie labels
	      .labelThreshold(.05)  //Configure the minimum slice size for labels to show up
	      .labelType("percent") //Configure what type of data to show in the label. Can be "key", "value" or "percent"
	      .donut(true)          //Turn on Donut mode. Makes pie chart look tasty!
	      .donutRatio(0.35)     //Configure how big you want the donut hole size to be.
	      ;

	    d3.select("#piechart svg")
	        .datum(chartData())
	        .transition().duration(350)
	        .call(chart);

	  return chart;
	});
});
</script>
@stop