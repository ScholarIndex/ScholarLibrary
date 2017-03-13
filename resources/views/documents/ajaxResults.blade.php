

<div class="ui cards docs">
@foreach($docs as $d)

  	<div class="card" data-bid="{{$d->bid}}" data-issue="_">

    <div class="content">
      <div class="header" title="{{$d->title['surface']}}">{{ str_limit($d->title['surface'], $limit = 25, $end = '...') }}</div>
      <div class="meta">
        <a>{{$d->bid}}</a>
      </div>
      <div class="description">
      	@if( count($d->issues) > 0)
      		{{count($d->issues)}} issues
			<div class="ui selection search fluid mini dropdown searchIssue" data-bid="{{$d->bid}}">
				<input type="hidden" name="">
				<i class="dropdown icon"></i>
				<div class="default text">Search issue...</div>
				<div class="menu"></div>
			</div>
		@else

      	@endif
      </div>
    </div>
   
		<button class="ui bottom attached button openDoc">
	      <i class="eye icon"></i>
	      Open
	    </button>
   
  </div>
@endforeach
</div>




