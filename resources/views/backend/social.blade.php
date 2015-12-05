@extends('layouts/main')
@section('content')
<div class="row">
	
	<div class="m-t">
	<div class="col-sm-3 col-xs-6">
		<div class="card text-center">
		  <div class="card-block">
		    <h4 class="card-title">{{$facebook}}</h4>
		   	<div>Facebook</div>
		  </div>
		</div>
	</div>
	
	<div class="col-sm-3 col-xs-6">
		<div class="card text-center">
		  <div class="card-block">
		    <h4 class="card-title">{{$twitter}}</h4>
		   	<div>Twitter</div>
		  </div>
		</div>
	</div>
	
	<div class="col-sm-3 col-xs-6">
		<div class="card text-center">
		  <div class="card-block">
		    <h4 class="card-title">{{$googlePlus}}</h4>
		   	<div>Google +</div>
		  </div>
		</div>
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