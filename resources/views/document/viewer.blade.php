@extends('layout')

@section('sidemenu')
	<p class="status"><i class="fa fa-info-circle"></i>Status</p>
@endsection


@section('content')

@include('document.menu')

<div class="contentWrapper">
	<h1>{{$metadata->title['surface']}}</h1>


	<div id="pageview" data-baseuri="{{IIIFHelpers::pageUri($bid,$issue)}}"></div>
	<div id="textview"></div>


	<div class="filmstrip">
	@foreach($pages as $n => $pa)
			
		<div class="thumb {{$pa->in_index ? 'index' : ''}}" data-oid="{{$pa->_id}}" data-n="{{$n}}">
			<div>
				<img class="lazyload" src="{{$fakeimage}}" data-src="{{IIIFHelpers::pageUri($bid,$issue,$n)}}/full/80,100/0/default.jpg" />
				<a href="#{{$n}}" class="viewerlnk"></a>
			</div>
			<p>{{$n}}</p>
		</div>	
		
	@endforeach
	</div>







</div>

@endsection
