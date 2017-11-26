<ul id="navHeaders" class="article">
	<li><a href="/lastSearch" class="small"><i class="fa fa-caret-left"></i> Back to search results</a></li>
	
	<li class="{{$overviewActive or ''}}">		<a href="/article/overview/{{$oid}}">Overview</a>			</li>
	<li class="{{$scansActive or ''}}">			<a href="/article/scans/{{$oid}}">Scans</a>			</li>
	<li class="{{$viewerActive or ''}}">		<a href="/article/viewer/{{$oid}}#{{$article->start_img_number}}">Viewer</a>			</li>
	<li class="{{$referencesActive or ''}}">	<a href="/article/references/{{$oid}}">Text</a>				</li>
</ul>