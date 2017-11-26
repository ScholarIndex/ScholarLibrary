<table>
	
	@if($ref->ref_type) <tr><th>Type</th>		<td>{{ucfirst($ref->ref_type)}}</td></tr> @endif

	@if(isset($dis))
		@if(isset($asve))
			@if($asve->title) <tr><th>Title</th>		<td>{{$asve->title}}</td></tr> @endif
			@if($asve->archive) <tr><th>Archive</th>		<td>{{$asve->archive}}</td></tr> @endif
			@if($asve->document_type) <tr><th>Document type</th>		<td>{{$asve->document_type}}</td></tr> @endif
			@if($asve->internal_id) <tr><th>Internal ID</th>		<td>{{$asve->internal_id}}</td></tr> @endif
			@if($asve->url) <tr><th>Link to ASVe</th><td><a href="{{$asve->url}}">{{$asve->url}}</a></td></tr> @endif
		@endif
		
		@if(isset($book))
			@if($book->title) <tr><th>Title</th>		<td>{{$book->title}}</td></tr> @endif
			@if($book->bid) <tr><th>BID</th>		<td>{{$book->bid}}</td></tr> @endif
			@if($book->publicaction_year) <tr><th>Publication Year</th>		<td>{{$book->publicaction_year}}</td></tr> @endif
			@if($book->publisher) <tr><th>Publisher</th>		<td>{{$book->publisher}}</td></tr> @endif
			@if($book->names[0]) <tr><th>Author</th>		<td>{{$book->names[0]}}</td></tr> @endif
			
		@endif
		
		@if(isset($dis->archival_document)) <tr><th>Link to VS</th><td><a href="http://{{\Config::get('lbc.VS_HOST')}}/results#details={{$dis->archival_document}}&rT=primary_sources&type=citing&refcat=&refid=">http://{{\Config::get('lbc.VS_HOST')}}/results#details={{$dis->archival_document}}&rT=primary_sources&type=citing&refcat=&refid=</a></td></tr> @endif
		@if(isset($dis->book)) <tr><th>Link to VS</th><td><a href="http://{{\Config::get('lbc.VS_HOST')}}/results#details={{$dis->book}}&rT=monographies&type=references&refcat=&refid=">http://{{\Config::get('lbc.VS_HOST')}}/results#details={{$dis->book}}&rT=monographies&type=references&refcat=&refid=</a></td></tr> @endif
		
	@else
		<tr><th colspan=2 style="font-style:italic">This reference is not yet registered for disambiguation.</th></tr>
	@endif
	
	
</table>