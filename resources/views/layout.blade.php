<html>
	<head>
		{!! HTML::style("semantic/semantic.min.css") !!}
		{!! HTML::style("css/app.css") !!}
		{!! HTML::style("css/alertify.min.css") !!}
		{!! HTML::style("css/default.min.css") !!}
		
		
		{!! HTML::script("js/jquery-2.1.4.min.js") !!}
		{!! HTML::script("js/jquery-ui.min.js") !!}
		{!! HTML::script("js/holder.min.js") !!}
		{!! HTML::script("js/openseadragon.min.js") !!}
		{!! HTML::script("js/blazy.min.js") !!}
		{!! HTML::script("js/jquery.scrollTo.min.js") !!}
		
		
		
		{!! HTML::script("js/app.min.js") !!}
		{!! HTML::script("semantic/semantic.min.js") !!}

		{!! HTML::script("js/alertify.min.js") !!}
	
		<meta name="_token" content="{!! csrf_token() !!}"/>


	</head>
	<body data-page="{{$page}}">
		@include('menu')
		
		<div class="ui main container">
			@include('messages')
    		@yield('content')
  		</div>
	</body>
</html>
