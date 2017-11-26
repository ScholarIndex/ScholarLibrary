@extends('layout')

@section('sidemenu')
	<p class="status"><i class="fa fa-info-circle"></i>Status</p>
@endsection


@section('content')

@include('document.article.menu')

<div class="contentWrapper">
	<h1>{{$article->title}}</h1>
	<br />

	<div id="textSearch"><input type="text" /></div>
	
	<div class="fulltext">
		@foreach($pages as $n => $oidp)
			<div data-n="{{$n}}" data-oid="{{$oidp}}" class="notLoaded page" ><p class="number">Page {{$n}} <i class="fa fa-hand-o-up"></i></p></div>
		@endforeach
	</div>
	
	<div class="refDetails"></div>




</div>

@endsection
