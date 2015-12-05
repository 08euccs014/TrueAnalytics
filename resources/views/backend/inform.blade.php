@extends('layouts/main')
@section('content')
<fieldset class="m-t-md">
	<legend>Inform the Job User</legend>
		<div class="row p-b-md">
			<form action="#" method="get">
				
				<div class="col-md-6 col-md-offset-3">
					<p>To : {{$job->user->fname}} &lt;{{$job->user->email}}&gt;</p>
					<textarea class="form-control" name="inform[content']" id="textbox" cols="30" rows="10">Hi {{$job->user->fname}},

					</textarea>
				</div>
				<div class="col-md-12 m-t-md">
					<button class="btn btn-primary center-block">Send</button>
				</div>
			</form>
		</div>
</fieldset>
@stop