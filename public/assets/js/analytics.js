$(document).ready(function(){
	
	//Regular stacked chart example
	//   nv.addGraph(function() {
	//     var chart = nv.models.multiBarChart().reduceXTicks(true).showControls(true);

	//     chart.xAxis
	//         .tickFormat(function(d) { return d3.time.format('%d-%b-%y')(new Date(d*1000)) });

	//     chart.yAxis
	//         .tickFormat(d3.format('d'));

	//     d3.select('#statschart svg')
	//         .datum(statsData())
	//         .transition().duration(500)
	//         .call(chart);

	//     nv.utils.windowResize(chart.update);

	//     return chart;
	// });





	
	//revbue vs refund line chart
	nv.addGraph(function() {
		  var chart = nv.models.lineChart()
		                .margin({left: 100})  //Adjust chart margins to give the x-axis some breathing room.
		                .useInteractiveGuideline(true)  //We want nice looking tooltips and a guideline!
		                .showLegend(true)       //Show the legend, allowing users to turn on/off line series.
		                .showYAxis(true)        //Show the y-axis
		                .showXAxis(true)        //Show the x-axis
		  ;

		  chart.xAxis     //Chart x-axis settings
		      .axisLabel('Date')
		      .tickFormat(d3.format(',r'));

		  chart.yAxis     //Chart y-axis settings
		      .axisLabel('Money (in INR)')
		      .tickFormat(d3.format('.02f'));

		  d3.select('#revenueChart svg')    //Select the <svg> element you want to render the chart in.   
		      .datum(revenueData())         //Populate the <svg> element with chart data...
		      .call(chart);          //Finally, render the chart!

		  //Update the chart when window resizes.
		  nv.utils.windowResize(function() { chart.update() });
		  return chart;
		});
	
	$(document).on('keydown', '#textbox', function(e) { 
		  var keyCode = e.keyCode || e.which; 

		  if (keyCode == 9) { 
		    e.preventDefault(); 
		  }
	});	
});