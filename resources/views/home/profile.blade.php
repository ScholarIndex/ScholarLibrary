@extends('layout')


@section('content')

	<div class="ui bottom attached segment">
	
	
	
	<form class="ui form" method="post" action="/changepassword">
		<input name="_token" type="hidden" value="{{csrf_token()}}">
		<h1>Profile</h1>

  <div class="field">
    <label>Current password</label>
    <input type="password" name="currentpassword" placeholder="Current password">
  </div>
  <div class="field">
    <label>New password</label>
    <input type="password" name="newpassword" placeholder="New password">
  </div>
  <div class="field">
    <label>Confirm new password</label>
    <input type="password" name="confirmnewpassword" placeholder="Confirm new password">
  </div>
  
  <button class="ui button" type="submit">Submit</button>
</form>



		

	</div>

@stop