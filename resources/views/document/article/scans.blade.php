@extends('layout')

@section('sidemenu')
	<p class="status"><i class="fa fa-info-circle"></i>Status</p>
@endsection


@section('content')

@include('document.article.menu')

<div class="contentWrapper">
	<h1>{{$article->title}}</h1>
	<br />
	@foreach($pages as $n => $pa)
			
		<div class="thumb {{$pa->in_index ? 'index' : ''}}">
			<div>
				<img class="lazyload" src="{{$fakeimage}}" data-src="{{IIIFHelpers::pageUri($document->bid,$document->number,$n)}}/full/120,140/0/default.jpg" />
				<a href="/article/viewer/{{$oid}}#{{$n}}" class="viewerlnk"></a>
			</div>
			<p>{{$n}}</p>
		</div>	
	@endforeach
</div>

@endsection
