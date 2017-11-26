<hr />
<span class="subtitle">External links</span>
<br /><br />
<table class="metadatas externallinks">
	@if($bibliodb->sbn_link)
		<tr><th>SBN link</th><td><a href="{{$bibliodb->sbn_link}}">{{$bibliodb->sbn_link}}</a></td></tr>
	@endif
	<tr><th>Venice Scholar</th><td><a href="http://{{\Config::get('lbc.VS_HOST')}}/results#details={{$bibliodb->_id}}&rT=monographies&type=references&refcat=&refid=">http://{{\Config::get('lbc.VS_HOST')}}/results#details={{$bibliodb->_id}}&rT=monographies&type=references&refcat=&refid=</a></td></tr>
</table>
