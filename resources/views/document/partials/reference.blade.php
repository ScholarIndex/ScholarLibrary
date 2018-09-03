<table data-ref="{{$ref->id}}" data-dis="{{isset($dis) ? $dis->id : ''}}">
	
	
	@if($ref->ref_type)
		@if($ref->ref_type == 'primary' || $ref->ref_type == 'secondary')
			<tr>
				<td colspan=3>
					<div class="action edit"><span>Edit mode</span></div>&nbsp;
					@if(isset($dis))
						<div class="action valid"><span>Validate</span></div>&nbsp;
					@endif
					<div class="action cancel"><span>Cancel</span></div>
				</td>
			</tr>

			<tr>
				<th>Reference checked</th>
				<td>
					@if($ref->checked)
						<i class="fa fa-check refer" data-field="checked"></i>
					@else
						<i class="fa fa-times refer" data-field="checked"></i>
					@endif
				</td>
			</tr>
			<tr>
				<th>Reference correct</th>
				<td>
					@if($ref->correct)
						<i class="fa fa-check refer" data-field="correct"></i>
					@else
						<i class="fa fa-times refer" data-field="correct"></i>
					@endif
	
				</td>
			</tr>

		@endif
		
		@if(isset($dis))
		<tr>
			<th>Disambiguation checked</th>
			<td>
				@if($dis->checked)
					<i class="fa fa-check disamb" data-field="checked"></i>
				@else
					<i class="fa fa-times disamb" data-field="checked"></i>
				@endif
			</td>
		</tr>
		<tr>
			<th>Disambiguation correct</th>
			<td>
				@if($dis->correct)
					<i class="fa fa-check disamb" data-field="correct"></i>
				@else
					<i class="fa fa-times disamb" data-field="correct"></i>
				@endif

			</td>
		</tr>
		@endif
		<tr class="type">
			<th>Type</th>
			<td>
				<span class="val">{{ucfirst($ref->ref_type)}}</span>
				<div class="ui fluid selection dropdown typeselect">
				  <input type="hidden" name="typeselect" value="{{$ref->ref_type}}">
				  <i class="dropdown icon"></i>
				  <div class="default text">Type</div>
				  <div class="menu">
				    <div class="item" data-value="primary">Primary</div>
				    <div class="item" data-value="secondary">Secondary</div>
				  </div>
				</div>
			</td>
			<td>
				<div class="action validate"><span>Validate</span></div>
			</td>
		</tr>
	@endif

	@if(isset($dis))
		@if(isset($asve))
		
			@if($asve->label)
				<tr class="title">
					<th>Title</th>
					<td>
						<span class="val">{{$asve->label}}</span>
						<div class="ui fluid search selection dropdown asve title">
						  <input type="hidden" name="asvetitleselect" value="{{$asve->id}}">
						  <i class="dropdown icon"></i>
						  <div class="default text">Label</div>
						  <div class="menu">
						    <div class="item" data-value="{{$asve->id}}">{{$asve->label}}</div>
						  </div>
						</div>			
						
						<div class="ui fluid search selection dropdown book title">
						  <input type="hidden" name="booktitleselect">
						  <i class="dropdown icon"></i>
						  <div class="default text">Title</div>
						  <div class="menu"></div>
						</div>							
					</td>
					<td>
						<div class="action validate"><span>Validate</span></div>
					</td>
				</tr>
			@endif
			@if($asve->archive) <tr class="data"><th>Archive</th>		<td>{{$asve->archive}}</td><td></td></tr> @endif
			@if($asve->document_type) <tr class="data"><th>Document type</th>		<td>{{$asve->document_type}}</td><td></td></tr> @endif
			@if($asve->internal_id) <tr class="data"><th>Internal ID</th>		<td>{{$asve->internal_id}}</td><td></td></tr> @endif
			@if($asve->url) <tr class="data"><th>Links</th><td><a href="{{$asve->url}}">Archivio di Stato di Venezia</a></td><td></td></tr> @endif
		@endif
		
		@if(isset($book))
			@if($book->title)
				<tr class="title">
					<th>Title</th>
					<td>
						<span class="val">{{$book->title}}</span>
						<div class="ui fluid search selection dropdown book title">
						  <input type="hidden" name="booktitleselect" value="{{$book->id}}">
						  <i class="dropdown icon"></i>
						  <div class="default text">Title</div>
						  <div class="menu">
						    <div class="item" data-value="{{$book->id}}">{{$book->title}}</div>
						  </div>
						</div>	
						
						<div class="ui fluid search selection dropdown asve title">
						  <input type="hidden" name="asvetitleselect">
						  <i class="dropdown icon"></i>
						  <div class="default text">Label</div>
						  <div class="menu"></div>
						</div>							
						
					</td>
					<td>
						<div class="action validate"><span>Validate</span></div>
					</td>
				</tr>
			@endif
			
			@if($book->names[0]) <tr class="data"><th>Author</th>		<td>{{$book->names[0]}}</td><td></td></tr> @endif
			@if($book->bid) <tr class="data"><th>BID</th>		<td>{{$book->bid}}</td><td></td></tr> @endif
			@if($book->publication_year) <tr class="data"><th>Publication Year</th>		<td>{{$book->publication_year}}</td><td></td></tr> @endif
			@if($book->publisher) <tr class="data"><th>Publisher</th>		<td>{{$book->publisher}}</td><td></td></tr> @endif
			
		@endif
		
		
								
	 
						 
						 
		@if(isset($dis->archival_document) || isset($dis->book))
			<tr>
				<th>Links</th>
				<td>
					@if(isset($dis->archival_document)) <a href="http://{{env('VS_HOST')}}/results#details={{$dis->archival_document}}&rT=primary_sources&type=citing&refcat=&refid=">Venice Scholar</a><br /> @endif
					@if(isset($dis->book)) <a href="http://{{env('VS_HOST')}}/results#details={{$dis->book}}&rT=monographies&type=references&refcat=&refid=">Venice Scholar</a><br /> @endif
				</td>
			</tr>
		@else
			<tr class="title notreferenced">
				<th>Title</th>
				<td>
					<span class="val"></span>
					<div class="ui fluid search selection dropdown book title">
					  <input type="hidden" name="booktitleselect">
					  <i class="dropdown icon"></i>
					  <div class="default text">Title</div>
					  <div class="menu"></div>
					</div>	
					
					<div class="ui fluid search selection dropdown asve title">
					  <input type="hidden" name="asvetitleselect">
					  <i class="dropdown icon"></i>
					  <div class="default text">Label</div>
					  <div class="menu"></div>
					</div>							
					
				</td>
				<td>
					<div class="action validate"><span>Validate</span></div>
				</td>
			</tr>		
		@endif
		
	@else
		<tr class="title notreferenced">
			<th>Title</th>
			<td>
				<span class="val"></span>
				<div class="ui fluid search selection dropdown book title">
				  <input type="hidden" name="booktitleselect">
				  <i class="dropdown icon"></i>
				  <div class="default text">Title</div>
				  <div class="menu"></div>
				</div>	
				
				<div class="ui fluid search selection dropdown asve title">
				  <input type="hidden" name="asvetitleselect">
				  <i class="dropdown icon"></i>
				  <div class="default text">Label</div>
				  <div class="menu"></div>
				</div>							
				
			</td>
			<td>
				<div class="action validate"><span>Validate</span></div>
			</td>
		</tr>
	
		<tr><th colspan=3 style="font-style:italic">This reference is not yet registered for disambiguation.</th></tr>
	@endif
	
	
</table>
