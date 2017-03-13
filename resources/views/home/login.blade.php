@extends('layout')


@section('content')

	<div class="ui bottom attached segment">
	
	
	
	<form class="ui form" method="post" action="/login">
		<input name="_token" type="hidden" value="{{csrf_token()}}">
		<h1>Login</h1>
  <div class="field">
    <label>Login</label>
    <input type="text" name="login" placeholder="Login">
  </div>
  <div class="field">
    <label>Password</label>
    <input type="password" name="password" placeholder="Password">
  </div>
  
  <div class="field">
    <a href="/forgot">Forgot password ?</a>
  </div>

  <button class="ui button" type="submit">Submit</button>
</form>



		

	</div>

@stop