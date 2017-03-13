@extends('layout')


@section('content')

	<div class="ui bottom attached segment">
		<ul>
		  @if(count($bookmarks)>0)
		  	@foreach($bookmarks as $b)
		     	<li><a href="/document/{{$b->bid}}/{{$b->issue}}/{{$b->page}}" {{ $b->page ? 'target="_viewer"' : ''}}>{{$b->bid}} / {{$b->issue}} {{ $b->page ? '/ page '.$b->page : ''}}</a></li>  	
		  	@endforeach
		  @else
		  	<li>No bookmarks.</li> 
		  @endif
		</ul>
	</div>

@stop