
	@foreach($pages as $i => $obj)
		<div class="ui card" style="width: 130px;"  >
    		<div class="image">
				<img style="height: 150px;" class="b-lazy" data-src="http://dhlabsrv4.epfl.ch/iiif_lbc/{{$bid}}::{{$issue}}::{{$i+1}}/full/,130/0/default.jpg" />
    		</div>
    		<div class="content">
      			<div class="header" title="">Image {{$i+1}}
      		
      			@if(in_array($obj, $indexPages))
      				&nbsp;<i class="blue info mini inverted circular icon" style="vertical-align:text-bottom;"></i>
      			@endif	
      			</div>
    		</div>
    		
    		<a class="ui bottom attached mini button" target="_viewer" href="/document/{{$bid}}/{{$issue}}/{{$i+1}}">
	    		<i class="eye icon"></i>
	      		In viewer
	    	</a>
    		<button class="ui bottom attached mini button inTocManager" data-page="{{$i+1}}">
	    		<i class="browser icon"></i>
	      		In TOC Manager
	    	</button>	    	
  		</div>
	@endforeach
	@if($hasMore)
    		<button class="ui button loadMore">
	    		<i class="eye icon"></i>
	      		Load more...
	    	</button>
	@endif