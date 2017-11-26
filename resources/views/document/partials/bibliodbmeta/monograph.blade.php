<tr><th>BID</th><td>{{$bibliodb->bid or '-'}}</td></tr>
<tr><th>Title</th><td>{{$bibliodb->title or '-'}}</td></tr>
<tr><th></th><td>&nbsp;</td></tr>
<tr><th>Authors</th>
	<td>
	@if(count($bibliodb->names) > 0)	
		{!!implode("<br />",$bibliodb->names)!!}
	@else
		-
	@endif
	</td>
</tr>
<tr><th>Provenance</th><td>{{$bibliodb->provenance or '-'}}</td></tr>
<tr><th>Digitization Provenance</th><td>{{$bibliodb->digitization_provenance or '-'}}</td></tr>
<tr><th>Publication year</th><td>{{$bibliodb->publication_year or '-'}}</td></tr>
<tr><th>Publication place</th><td>{{$bibliodb->publication_place or '-'}}</td></tr>
<tr><th>Publication country</th><td>{{$bibliodb->publication_country or '-'}}</td></tr>
<tr><th>Publication language</th><td>{{$bibliodb->publication_language or '-'}}</td></tr>