@if($prev)
	
	<div class="ui card first" style="width: 400px;" data-page="{{$pg-1}}" >
		<div class="image">
			<img style="height: 450px;" class="b-lazy" data-src="http://dhlabsrv4.epfl.ch/iiif_lbc/{{$bid}}::{{$issue}}::{{$pg-1}}/full/,400/0/default.jpg" />
		</div>
		<div class="content">
  			<div class="header" title="">Image {{$pg-1}}</div>
		</div> 	
		
		
		<div class="ui two bottom attached buttons">
		    <div class="ui button labeled icon drag" style="cursor:move"><i class="icon move"></i>Drag me</div>
		    <a target="_viewer" href="/document/{{$bid}}/{{$issue}}/{{$pg-1}}" class="ui button icon In viewer"><i class="icon eye"></i>&nbsp;&nbsp;&nbsp;Open in viewer</a>
		</div>	

	</div>
@endif

@if($curr)
	<div class="ui card" style="width: 400px;" data-page="{{$pg}}"  >
		<div class="image">
			<img style="height: 450px;" class="b-lazy" data-src="http://dhlabsrv4.epfl.ch/iiif_lbc/{{$bid}}::{{$issue}}::{{$pg}}/full/,400/0/default.jpg" />
		</div>
		<div class="content">
  			<div class="header" title="">Image {{$pg}}</div>
		</div> 	
		
		<div class="ui two bottom attached buttons">
		    <div class="ui button labeled icon drag" style="cursor:move"><i class="icon move"></i>Drag me</div>
		    <a target="_viewer" href="/document/{{$bid}}/{{$issue}}/{{$pg}}" class="ui button icon In viewer"><i class="icon eye"></i>&nbsp;&nbsp;&nbsp;Open in viewer</a>
		</div>		

	</div>
@endif

@if($next)
	<div class="ui card last" style="width: 400px;" data-page="{{$pg+1}}" >
		<div class="image">
			<img style="height: 450px;" class="b-lazy" data-src="http://dhlabsrv4.epfl.ch/iiif_lbc/{{$bid}}::{{$issue}}::{{$pg+1}}/full/,400/0/default.jpg" />
		</div>
		<div class="content">
  			<div class="header" title="">Image {{$pg+1}}</div>
		</div> 	
		
		
		<div class="ui two bottom attached buttons">
		    <div class="ui button labeled icon drag" style="cursor:move"><i class="icon move"></i>Drag me</div>
		    <a target="_viewer" href="/document/{{$bid}}/{{$issue}}/{{$pg+1}}" class="ui button icon In viewer"><i class="icon eye"></i>&nbsp;&nbsp;&nbsp;Open in viewer</a>
		</div>		
	</div>
	
@endif