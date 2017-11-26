@extends('layout')

@section('sidemenu')
	<p class="status"><i class="fa fa-info-circle"></i>Status</p>
@endsection


@section('content')

@include('document.article.menu')

<div class="contentWrapper">
	<h1 style="height:70px;">{{$article->journal_short_title}} {{explode(':',$article->internal_id)[1]}}<br />
	
		@if($prev)
			<a style="padding-right:20px" href="/article/overview/{{$prev->_id}}"><i class="fa fa-caret-left"></i> Previous article (same issue)</a>
		@endif
		@if($next)
			<a href="/article/overview/{{$next->_id}}">Next article (same issue) <i class="fa fa-caret-right"></i></a>
		@endif
		
	</h1>
	<table class="metadatas bibliodb">
		<thead><tr><td><span class="subtitle">Metadata</span></td><td>From Biblio DB</td></tr></thead>
		<tbody>
			<tr><th>BID</th><td>{{$article->journal_bid}}</td></tr>
			<tr><th>Author</th><td>{!! implode('<br />',$article->authors) !!}</td></tr>
			<tr><th>Title</th><td>{{$article->title}}</td></tr>
			<tr><th>Page span</th><td>{{$article->start_img_number}} - {{$article->end_img_number}}</td></tr>
			<tr><th>Volume</th><td>{{$article->volume}}</td></tr>
			<tr><th>Year</th><td>{{$article->year}}</td></tr>
			<tr><th>Digitization provenance</th><td>{{$article->digitization_provenance}}</td></tr>
			<tr><th>Issue n°</th><td>{{explode(':',$article->internal_id)[1]}}</td></tr>			
		</tbody>
	</table>
	
	@include('document.partials.externalinks.article')
 

</div>

@endsection
