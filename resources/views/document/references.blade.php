@extends('layout')

@section('sidemenu')
	<p class="status"><i class="fa fa-info-circle"></i>Status</p>
@endsection


@section('content')

@include('document.menu')

<div class="contentWrapper">
	<h1>{{$metadata->title['surface']}}</h1>
	<br />

	<div id="textSearch"><input type="text" /></div>
	
	<div class="fulltext">
		@foreach($pages as $n => $oid)
			<div data-n="{{$n}}" data-oid="{{$oid}}" class="notLoaded page" ><p class="number">Page {{$n}} <i class="fa fa-hand-o-up"></i></p></div>
		@endforeach
	</div>
	
	<div class="refDetails"></div>




</div>

@endsection
