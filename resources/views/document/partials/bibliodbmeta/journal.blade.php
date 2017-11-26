@if($bibliodb->bid)
	<tr><th>BID</th><td>{{$bibliodb->bid}}</td></tr>
@endif

@if($issue)
	<tr><th>Issue n°</th><td>{{$issue}}</td></tr>
@endif

@if($bibliodb->full_title)
	<tr><th>Title</th><td>{{$bibliodb->full_title}}</td></tr>
@endif

@if($bibliodb->provenance)
	<tr><th>Provenance</th><td>{{$bibliodb->provenance}}</td></tr>
@endif
