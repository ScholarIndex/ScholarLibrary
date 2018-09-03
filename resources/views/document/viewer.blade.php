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
	@if(Auth::check() && in_array('editor', Auth::user()->roles))
		<div id="actions">
			<div class="action split"><span>Split text</span></div>
			<br />
			<div class="action metadata"><span>Page metadata <i class="fa fa-caret-down"></i></span>
				<div class="sub">
					Printed page number :
					<br />
					<input id="ppn" name="ppn" type="text" value="" />
					<br />
					<input type="checkbox" id="propagate" name="propagate" value="1" /> propagate
					<br />
					<div class="save">save</div>
				</div>
				
			</div>
		</div>
	@endif
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
