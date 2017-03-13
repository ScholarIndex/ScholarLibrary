  @if(count($pages)>0)
  	@foreach($pages as $p => $t)
     	<li><a href="/document/{{$bid}}/{{$issue}}/{{$p+1}}" target="_viewer">Image {{$p+1}}</a></li>  	
  	@endforeach
  @else
  	<li>No index entry.</li> 
  @endif