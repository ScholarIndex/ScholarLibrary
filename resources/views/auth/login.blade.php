<!DOCTYPE html>
<html>
    <head>
        <title>Laravel</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%; background: #0A3B4C; color: white;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 96px;
            }
            input {padding:10px; }
            
            a { color: white; }
            a:hover { color: #ddd; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">

@if (count($errors) > 0)
						<div class="alert alert-danger">
							<strong>Whoops!</strong> There were some problems with your input.<br><br>
							<ul>
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif

					<form class="form-horizontal" role="form" method="POST" action="{{ url('/auth/login') }}">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">

						<div class="form-group">
							<div class="col-md-6">
								<input type="text" class="form-control" name="login" value="{{ old('login') }}" placeholder="Login">
							</div>
						</div>
<br /><br />
						<div class="form-group">
							<div class="col-md-6">
								<input type="password" class="form-control" name="password" placeholder="Password">
							</div>
						</div>
<br /><br />
					<!--	<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<div class="checkbox">
									<label>
										<input type="checkbox" name="remember"> Remember Me
									</label>
								</div>
							</div>
					</div>-->

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">Login</button>

								<!--<a class="btn btn-link" href="{{ url('/password/email') }}">Forgot Your Password?</a>-->
							</div>
						</div>
					</form>
	
            </div>
        </div>
    </body>
</html>
