  @if(count($articles)>0)
  	@foreach($articles as $p => $t)
     	<li><a href="/document/{{$bid}}/{{$issue}}/{{$t['start_page']}}" target="_viewer">{{$t['title']}}, <em>{{$t['author']}}</em></a></li>  	
  	@endforeach
  @else
  	<li>No TOC entry.</li> 
  @endif