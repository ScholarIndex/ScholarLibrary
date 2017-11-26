@extends('layout')

@section('sidemenu')
	<p class="status"><i class="fa fa-info-circle"></i>Status</p>
@endsection


@section('content')

@include('document.menu')

<div class="contentWrapper">
	<h1>{{$metadata->title['surface']}}</h1>
	
	
	<table class="metadatas bibliodb">
		<thead><tr><td><span class="subtitle">Metadata</span></td><td>From Biblio DB</td></tr></thead>
		<tbody>
			<tr><th>BID</th><td>{{$bid}}</td></tr>
			<tr><th>Title</th><td>{{$metadata->title['surface']}}</td></tr>
			<tr><th>Publisher</th><td>{{$metadata->title['publisher']}}</td></tr>
			<tr><th>Years</th><td>{{$yearMin}} - {{$yearMax}} </td></tr>	
		</tbody>
	</table>
	
	<hr />
	<table class="metadatas issues">
		<thead><tr><td><span class="subtitle">Issues</span></td><td></td></tr></thead>
		<tbody>
			<tr><th>Select issue</th><td>
				<select>
					@foreach($metadata->issues as $i)
						@if($i['marked_as_removed'] == false)
							<option>{{$i['foldername']}}</option>
						@endif
					@endforeach
				</select>
				<br /><br />
				<a class="button">Go to issue</a>
			</td></tr>
		</tbody>
	</table>
	
	<hr />
	<table class="metadatas links">
		<thead><tr><td><span class="subtitle">Links</span></td><td></td></tr></thead>
		<tbody>
			@if($bibliodb->sbn_link)
				<tr><th>SBN catalogue</th><td><a href="{{$bibliodb->sbn_link}}">{{$bibliodb->sbn_link}}</a></td></tr>
			@endif
		</tbody>
	</table>
	
	
		

	
	

</div>

@endsection
