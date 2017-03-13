@extends('layout')


@section('content')

	<div class="ui bottom attached segment">
	
	
	
	<form class="ui form" method="post" action="/newpassword">
		<input name="_token" type="hidden" value="{{csrf_token()}}">
		<h1>Forgot password ?</h1>

  <div class="field">
    <label>Email</label>
    <input type="text" name="email" placeholder="Email">
  </div>
  
  <button class="ui button" type="submit">Submit</button>
</form>

	</div>

@stop