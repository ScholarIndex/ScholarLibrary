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
			@include('document.partials.bibliodbmeta.'.$metadata->type_document)
		</tbody>
	</table>
	<table class="metadatas digitization">
		<thead><tr><td></td><td>From digitization</td></tr></thead>		
		<tbody>
			<tr><th>BID</th><td>{{$metadata->bid or '-'}}</td></tr>
			<tr><th>Title</th><td>{{$metadata->title['surface'] or '-'}}</td></tr>
			<tr><th>ID</th><td>{{$metadata->sbn_id or '-'}}</td></tr>
			<tr><th>Foldername</th><td>{{$metadata->foldername or '-'}}</td></tr>
			<tr><th>Language</th><td>{{$metadata->language or '-'}}</td></tr>
			<tr><th>Operator</th><td>{{$metadata->operator or '-'}}</td></tr>
			<tr><th>Subject</th>
				<td>
					@if(count($metadata->subjects)>0)
						{!! join("<br />",$metadata->subjects) !!}
					@else
						-
					@endif
				</td></tr>
			<tr><th>Responsible</th><td>{{$metadata->title['responsible'] or '-'}}</td></tr>
			<tr><th>Publisher</th><td>{{$metadata->title['publisher'] or '-'}}</td></tr>
			<tr><th>Materiality</th><td>{{$metadata->title['materiality'] or '-'}}</td></tr>
			<tr><th>Specifications</th><td>{{$metadata->title['specifications'] or '-'}}</td></tr>
			<tr><th>Date</th><td>{{$metadata->date or '-'}}</td></tr>
			<tr><th>Provenance</th><td>{{$metadata->provenance or '-'}}</td></tr>
			<tr><th>Type document</th><td>{{$metadata->type_document or '-'}}</td></tr>
			<tr><th>Type catalogue</th><td>{{$metadata->type_catalogue or '-'}}</td></tr>
		</tbody>

	</table>
	@include('document.partials.tableofcontent.'.$metadata->type_document)
	@include('document.partials.externalinks.'.$metadata->type_document)


</div>

@endsection
