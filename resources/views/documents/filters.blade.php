
  	<h3>Filter by</h3>
  	<hr />
  
	
	<div class="ui accordion">
		<form>
		@foreach($filters as $label => $params)
			<div class="title {{$params['active'] or ''}}">
				<i class="dropdown icon"></i>
				{{$label}}
			</div>
			<div class="content {{$params['active'] or ''}}">
				@if($params['type'] == 'checkbox')
					<div class="ui form">
						<div class="grouped fields">
							@foreach($params['values'] as $v)
								@if($v[0] != "")
									<div class="field">
								 		<div class="ui checkbox">
											<input type="checkbox" name="{{$label}}[]" value="{{$v[0]}}">
											<label>{{ucfirst($v[0])}}</label>
							 			</div>
									</div>
								@endif
							@endforeach
						</div>
					</div>	
				@endif
				@if($params['type'] == 'input')
					<div class="ui icon input filterSearchInput">
					  <input type="text" name="{{$label}}" value="{{$params['defaultValue'] or ''}}" placeholder="{{$label}} contains...">
					  <i class="remove link icon"></i>
					</div>
				@endif

			</div>		
		@endforeach
		</form>
	</div>

  