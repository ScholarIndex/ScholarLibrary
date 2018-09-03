<hr />
<span class="subtitle">External links</span>
<br /><br />
<table class="metadatas externallinks">
	@if($bibliodb->sbn_link)
		<tr><td><a href="{{$bibliodb->sbn_link}}">Italian National Catalogue</a></td></tr>
	@endif
	<tr><td><a href="http://{{env('VS_HOST')}}/results#details={{$bibliodb->_id}}&rT=monographies&type=references&refcat=&refid=">Venice Scholar</a></td></tr>
</table>
