@extends('layouts/main')
@section('content')
<div class="row">
	<div class="col-sm-12 col-xs-12">
		<div class="alert alert-info">
			Data showing for startDate to EndDate
		</div>
	</div>
	
	<div class="col-sm-12 col-xs-12">
		<div class="card p-y">
		<form class="form-inline">
			<div class="form-group">
		    <label class="p-x">Calculare date within</label>
		  </div>
		  <div class="form-group">
		    <label class="sr-only" for="startDate">Start Date</label>
		    <input type="date" class="form-control" id="startDate" placeholder="Start Date">
		  </div>
		  <div class="form-group">
		    <label class="sr-only" for="endDate">End Date</label>
		    <input type="date" class="form-control" id="endDate" placeholder="End Date">
		  </div>

		  <button type="submit" class="btn btn-primary">Calculate</button>
		</form>
		</div>
	</div>
	

	
	<div class="col-sm-3 col-xs-6">
		<div class="card text-center stat-card">
		  <div class="card-block">
		    <h4 class="card-title">{{$totalTurnover}}</h4>
		    <p class="card-text">(In INR)</p>
		   	<div>Total Turnvoer <br /><small>(including gateway transaction fee)</small></div>
		  </div>
		</div>
	</div>
	
	<div class="col-sm-3 col-xs-6">
		<div class="card text-center stat-card">
		  <div class="card-block">
		    <h4 class="card-title">{{$totalTxnFee}}</h4>
		    <p class="card-text">(In INR)</p>
		   	<div>Total Transaction Fee</div>
		  </div>
		</div>
	</div>
	
	<div class="col-sm-3 col-xs-6">
		<div class="card text-center stat-card">
		  <div class="card-block">
		    <h4 class="card-title">{{$totalCredit}}</h4>
		    <p class="card-text">(In INR)</p>
		   	<div>Total Earnings</div>
		  </div>
		</div>
	</div>
	
	<div class="col-sm-3 col-xs-6">
		<div class="card text-center stat-card">
		  <div class="card-block">
		    <h4 class="card-title">{{$totalDebit}}</h4>
		    <p class="card-text">(In INR)</p>
		   	<div>Total Refunds</div>
		  </div>
		</div>
	</div>
	<div class="col-sm-6 col-xs-12">
		<table class="table table-inverse">
			<thead>
				<tr>
					<th>Total Revenue <br /><small>(In INR)</small></th>
					<th class="text-right">{{$totalRevenue}}</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Job Revenue</td>
					<td class="text-right">{{$totalCreditJob}}</td>
				</tr>
				<tr>
					<td>External Invoice</td>
					<td class="text-right">{{$totalCreditExtInv}}</td>
				</tr>
				<tr>
					<td>Membership</td>
					<td class="text-right">{{$totalCreditMembership}}</td>
				</tr>
				<tr>
					<td>Featured Revenue</td>
					<td class="text-right">{{$totalCreditFeatured}}</td>
				</tr>
				<tr>
					<td>Proposal Purchased</td>
					<td class="text-right">{{$totalCreditProposalBuy}}</td>
				</tr>
				<tr>
					<td>Partial Payments</td>
					<td class="text-right">{{$totalCreditPartialPay}}</td>
				</tr>
			</tbody>
		</table>
	</div>
	
	<!--  graph or revenue vs refunds-->
	<div class="col-sm-6 col-xs-12">
		<div id="revenueStatChart"  class="center-block"><svg></svg></div>
	</div>
	
</div>
<script type="text/javascript">
var  maxValue = <?php echo $maxValue ?>;
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
	var data = '<?php echo json_encode($revenueData); ?>';
	return JSON.parse(data);
}
$(document).ready(function(){
	nv.addGraph(function() {
		  var chart = nv.models.lineChart()
		    .useInteractiveGuideline(true);

		  chart.xAxis
		    .axisLabel('Date')
		    .tickFormat(function(d) { return d3.time.format('%d-%b-%y')(new Date(d*1000)); });

		  chart.yAxis.axisLabel('Money (in INR)')
		    .tickFormat(d3.format('d'));
		    chart.forceY([0, maxValue]);

		  d3.select('#revenueStatChart svg')
		    .datum(revenueData())
		    .transition().duration(500)
		    .call(chart);

		  nv.utils.windowResize(chart.update);

		  return chart;
	});
});
</script>
@stop