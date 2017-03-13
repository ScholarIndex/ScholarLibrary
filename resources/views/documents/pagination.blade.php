<form>
	<span class="count">
		@if($docsCount == 0)
			No document found
		@else
			{{$docsCount}} document(s) found
		@endif
	</span>

	<span class="pages">
		<button id="prevPage" class="ui labeled  small icon button">
			<i class="left chevron icon"></i>
			Prev
		</button>
	
		<div class="ui compact right labeled input">
			<input style="width:50px;text-align:center;" type="text" name="page" id="page" value="{{$page}}">
			<div class="ui label" id="pageCount">{{$pageCount}}</div>
		</div>
	
		<button id="nextPage" class="ui right labeled icon small button">
			Next
			<i class="right chevron icon"></i>
		</button>
	</span>
	
	<span class="params">

		<div class="ui selection dropdown" >
			<input type="hidden" name="docsPerPage" value="{{$docsPerPage}}"/>
			<i class="dropdown icon"></i>
			<div class="default text"></div>
			<div class="menu">
  				@foreach($paginationOptions as $opt)
  					<div class="item" data-value="{{$opt}}">{{$opt}} per page</div>
				@endforeach
  			</div>
		</div>
	</span>
</form>
<hr />