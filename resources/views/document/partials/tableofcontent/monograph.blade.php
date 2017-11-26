<hr />
<span class="subtitle">Table of content</span>
<br /><br />
No contributions found on this document.<br /><br />
<!-- LIST OF CONTRIBUTIONS -->

<!--<ol>
	@foreach($articles as $a)
		<a><li>{{$a->title}}, <span class="author">{{$a->authors[0]}}</span></li></a>
	@endforeach
</ol>
<br /><br />-->
@if($first_index_page)
	<a href="/document/viewer/{{$bid}}#{{$first_index_page->single_page_file_number}}" class="button">see index scans</a>
@endif
<br /><br />
