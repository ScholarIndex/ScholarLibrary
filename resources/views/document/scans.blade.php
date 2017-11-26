@extends('layout')

@section('sidemenu')
	<p class="status"><i class="fa fa-info-circle"></i>Status</p>
@endsection


@section('content')

@include('document.menu')

<div class="contentWrapper">
	<h1>{{$metadata->title['surface']}}</h1>
	<br />
	@foreach($pages as $n => $pa)
			
		<div class="thumb {{$pa->in_index ? 'index' : ''}}">
			<div>
				<img class="lazyload" src="{{$fakeimage}}" data-src="{{IIIFHelpers::pageUri($document->bid,$document->number,$n)}}/full/120,140/0/default.jpg" />
				<a href="/document/viewer/{{$bid}}/{{$issue or ''}}#{{$n}}" class="viewerlnk"></a>
				@if($metadata->type_document == 'journal_issue')
					<a href="/document/toc/{{$bid}}/{{$issue or ''}}#{{$n}}" class="toclnk">TOC Manager</a>
				@endif
			</div>
			<p>{{$n}}</p>
		</div>	
		
	@endforeach
	







</div>

@endsection
