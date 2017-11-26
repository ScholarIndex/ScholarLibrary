<hr />
<span class="subtitle">Table of content</span>
<br /><br />
<ol>
	@foreach($articles as $a)
		<a href="/article/overview/{{$a->_id}}"><li>{{$a->title}}, <span class="author">{{$a->authors[0]}}</span></li></a>
	@endforeach
</ol>
<br /><br />
@if($first_index_page)
	<a href="/document/viewer/{{$bid}}/{{$issue}}#{{$first_index_page->single_page_file_number}}" class="button">see index scans</a>
@endif
<br /><br />
