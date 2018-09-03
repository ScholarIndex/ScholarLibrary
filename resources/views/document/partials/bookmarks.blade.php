<ul>
	<li>Bookmark doc 		<i class="fa {{$b ? 'fa-star' : 'fa-star-o'}}" data-type="bookmark" data-action="{{$b?'del':'add'}}" data-documentid="{{$documentId}}" data-pageid=""></i></li>
	<li>See later doc 		<i class="fa {{$s ? 'fa-star' : 'fa-star-o'}}" data-type="seelater" data-action="{{$s?'del':'add'}}" data-documentid="{{$documentId}}" data-pageid=""></i></li>
	@if($hasPage)
		<li>Bookmark page 	<i class="fa {{$bp ? 'fa-star' : 'fa-star-o'}}" data-type="bookmark" data-action="{{$bp?'del':'add'}}" data-documentid="{{$documentId}}" data-pageid="{{$pageId}}"></i></li>
		<li>See later page 	<i class="fa {{$sp ? 'fa-star' : 'fa-star-o'}}" data-type="seelater" data-action="{{$sp?'del':'add'}}" data-documentid="{{$documentId}}" data-pageid="{{$pageId}}"></i></li>
	@endif
</ul>