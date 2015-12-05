<?php $showSplash = (in_array($page, array('job', 'workstream', 'revenue', 'social'))) ? true : false; ?>
@include('layouts/header')
<!DOCTYPE html>
<html>
<head>
<title>True Backend</title>

<link href="//fonts.googleapis.com/css?family=Lato:100,400,700" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="{!! asset('/assets/css/bootstrap.min.css') !!}" />
<link rel="stylesheet" href="{!! asset('/assets/css/nv.d3.min.css') !!}" />
<link rel="stylesheet" href="{!! asset('/assets/css/analytics.css') !!}" />
<style>
html, body {
	height: 100%;
	font-family: 'Lato';
}
#splash-screen {
	background: #FFFFFF;
	height: 100%;
	width: 100%;
}
#splash-screen .splash-content {
	position: absolute;
	z-index: 2;
	top: 22%;
	left: 35%;
	border-radius: 53%;
	padding-top: 90px;
	height: 390px;
	width: 390px;
}
#splash-screen .splash-content h6{
	color : #dddddd;
}

.wrapper {
  position: absolute;
  margin: 40px auto;
  background: white;
  top: 16%;
	left: 35%;
}

.wrapper, .wrapper * {
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
  box-sizing: border-box;
}

.wrapper {
  width: 390px;
  height: 390px;
	z-index: 1;
}

.wrapper .pie {
  width: 50%;
  height: 100%;
  transform-origin: 100% 50%;
  position: absolute;
  background: #FFFFFF;
  border: 12px solid #F89B20;
}

.wrapper .spinner {
  border-radius: 100% 0 0 100% / 50% 0 0 50%;
  z-index: 200;
  border-right: none;
  animation: rota 2s linear;
}

.wrapper:hover .spinner,
.wrapper:hover .filler,
.wrapper:hover .mask {
  animation-play-state: running;
}

.wrapper .filler {
  border-radius: 0 100% 100% 0 / 0 50% 50% 0;
  left: 50%;
  opacity: 0;
  z-index: 100;
  animation: opa 2s steps(1, end) reverse;
  border-left: none;
}

.wrapper .mask {
  width: 50%;
  height: 100%;
  position: absolute;
  background: inherit;
  opacity: 1;
  z-index: 300;
  animation: opa 2s steps(1, end);
}

@keyframes rota {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
@keyframes opa {
  0% {
    opacity: 1;
  }
  50%, 100% {
    opacity: 0;
  }
}

</style>
<script type="text/javascript" src="{!! asset('/assets/js/jquery.min.js') !!}" ></script>
<script type="text/javascript">
	(function(jQuery){
	var $ = jQuery; 
});
</script>
</head>
<body @if($showSplash) id="main-content" style="overflow-y :hidden;" @endif>
	@if($showSplash)
	<div id="splash-screen">
		<div class="splash-content">
			<div class="text-center"><img src="{!! asset('/assets/image/analytics.jpg') !!}" height="100px"></div>
			<h1 class="text-center">True Analytics</h1>
			<h6 class="text-center">by - Er.  Mohit G agrawal</h6>
		</div>
		<div class="wrapper">
			<div class="pie spinner"></div>
			<div class="pie filler"></div>
			<div class="mask"></div>
		</div>
	</div>
	@endif
	<div class="container-fluid">
		@yield('header')
		@yield('content')    
	</div>
</body>
<script type="text/javascript" src="{!! asset('/assets/js/bootstrap.min.js') !!}" ></script>
<script type="text/javascript" src="{!! asset('/assets/js/nvd3/d3.js') !!}" ></script>
<script type="text/javascript" src="{!! asset('/assets/js/nv.d3.min.js') !!}" ></script>
<script type="text/javascript" src="{!! asset('/assets/js/analytics.js') !!}" ></script>
@if($showSplash)
<script type="text/javascript">
	$(document).ready(function(){
		setTimeout(function(){
		$('#splash-screen').hide();
		$('#main-content').css('overflow-y','auto');
		},2000);
	})
</script>
@endif
</html>
