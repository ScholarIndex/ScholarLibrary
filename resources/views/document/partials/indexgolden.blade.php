<ul>
	<li>In index 		<i class="fa {{$ip ? 'fa-folder-open' : 'fa-folder-open-o'}}" data-type="index"  data-action="{{$ip?'del':'add'}}" data-documentid="{{$documentId}}" data-pageid="{{$pageId}}"></i></li>
	<li>In golden set 	<i class="fa {{$gp ? 'fa-folder-open' : 'fa-folder-open-o'}}" data-type="golden" data-action="{{$gp?'del':'add'}}" data-documentid="{{$documentId}}" data-pageid="{{$pageId}}"></i></li>
</ul>