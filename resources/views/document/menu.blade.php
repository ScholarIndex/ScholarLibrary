<ul id="navHeaders" class="{{$document->type}}">
	<li><a href="/lastSearch" class="small"><i class="fa fa-caret-left"></i> Back to search results</a></li>
	
	<li class="{{$overviewActive or ''}}">		<a href="/document/overview/{{$bid}}/{{$issue or ''}}">Overview</a>			</li>
	@if($document->type != 'journal')
		<li class="{{$scansActive or ''}}">			<a href="/document/scans/{{$bid}}/{{$issue or ''}}">Scans</a>			</li>
		<li class="{{$viewerActive or ''}}">		<a href="/document/viewer/{{$bid}}/{{$issue or ''}}">Viewer</a>			</li>
		<li class="{{$referencesActive or ''}}">	<a href="/document/references/{{$bid}}/{{$issue or ''}}">Text</a>				</li>
	@endif
	@if($metadata->type_document == 'journal' && isset($issue))
		<li class="{{$tocActive or ''}}">			<a href="/document/toc/{{$bid}}/{{$issue or ''}}">TOC  Manager</a>		</li>
	@endif
</ul>