@extends('layout')

@section('sidemenu')
	<p class="status"><i class="fa fa-info-circle"></i>Status</p>
	<a class="button" style="width:100px;margin:auto;position:absolute;bottom:10px;"><i class="fa fa-question-circle-o"></i> Help</a>
@endsection


@section('content')


@include('document.menu')


<div class="contentWrapper">
	<h1>{{$metadata->title['surface']}}</h1>
	<br />

	<table class="tocViewer" border=2>
		
		<tr>
			<td colspan=2><a class="prev"><i class="fa fa-caret-left"></i> Previous</a></td>
			<td colspan=2><select id="goPage">
				@foreach($pages as $p)
					<option value="{{$p->single_page_file_number}}">{{$p->single_page_file_number}}{{$p->in_index ? ' [INDEX]' : ''}}</option>
				@endforeach
			</select></td>
			<td colspan=2><a class="next">Next <i class="fa fa-caret-right"></i></a></td>
		</tr>
		<tr>
			<td colspan=3><div id="openseadragon_left" class="osd"></div></td>
			<td colspan=3><div id="openseadragon_right" class="osd"></div></td>
		</tr>
				
		<tr>
			<td colspan=3 class="leftPg"></td>
			<td colspan=3 class="rightPg"></td>
		</tr>
	</table>

	
	<table id="toc">
		<tr><th>Authors</th><th class="title">Title</th><th>Page range</th><th>Actions</th></tr>
		
		@foreach($articles as $a)
			<tr class="editOff" data-id="{{$a['id']}}">
				<td class="author">
					<span class="textual">
						{{--*/ $first = true /*--}}
						@foreach($disamb[$a['id']] as $k => $v)
							@if($first)
					    		{{$v}}
					    		{{--*/ $first = false /*--}}
					    	@else
					    		,{{$v}}
					    	@endif
					    @endforeach
					</span>
					<div class="fields">
						<div class="ui fluid multiple search selection dropdown author">
						  <input name="authors" type="hidden" value="{{join(',', array_keys($disamb[$a['id']]))}}">
						  <i class="dropdown icon"></i>
						  <div class="default text">Authors</div>
						  <div class="menu">
						  	@foreach($disamb[$a['id']] as $k => $v)
						      <div class="item" data-value="{{$k}}">{{$v}}</div>
						    @endforeach
						  </div>
						 </div>
					 </div>
				</td>
				<td class="title">
					<span class="textual">{{$a['title']}}</span>
					<div class="fields"><div class="ui fluid input"><input placeholder="Title" class="title" type="text" value="{{$a['title']}}" /></div></div>
				</td>
				<td class="pagerange">
					<span class="textual">{{$a['start_img_number']}}&nbsp;&nbsp;-&nbsp;&nbsp;{{$a['end_img_number']}}</span>
					<div class="fields">
					From : <div class="ui search selection dropdown page start">
					  <input type="hidden" name="pagestart" value="{{$a['start_img_number']}}">
					  <i class="dropdown icon"></i>
					  <div class="default text">page</div>
					  <div class="menu">
							@foreach($pages as $p)
								<div class="item" data-value="{{$p->single_page_file_number}}">{{$p->single_page_file_number}}</div>
							@endforeach
					  </div>
					</div> 
					To : <div class="ui search selection dropdown page end">
						  <input type="hidden" name="pageend" value="{{$a['end_img_number']}}">
						  <i class="dropdown icon"></i>
						  <div class="default text">page</div>
						  <div class="menu">
							@foreach($pages as $p)
								<div class="item" data-value="{{$p->single_page_file_number}}">{{$p->single_page_file_number}}</div>
							@endforeach
						  </div>
						</div>
					</div>
				</td>	
				<td class="actions">
					@if(Auth::check() && in_array('editor', Auth::user()->roles))
						<i class="fa fa-pencil-square-o"></i><!--&nbsp;<i class="fa fa-trash-o"></i>-->
					@endif
				</td>
			</tr>
		@endforeach
		
	
		<tr class="editOff" data-id="new">
			<td class="author">
				<span class="textual"></span>
				<div class="fields">
				<div class="ui fluid multiple search selection dropdown author">
				  <input name="authors" type="hidden" value="">
				  <i class="dropdown icon"></i>
				  <div class="default text">Authors</div>
				  <div class="menu"></div>
				 </div>
				 </div>
			</td>
			<td class="title">
				<span class="textual"></span>
				<div class="fields">
				<div class="ui fluid input"><input placeholder="Title" class="title" type="text" value="" /></div>
				</div>
			</td>
			<td class="pagerange">
				<span class="textual"></span>
					<div class="fields">
					From : <div class="ui search selection dropdown page start">
					  <input type="hidden" name="pagestart" value="">
					  <i class="dropdown icon"></i>
					  <div class="default text">page</div>
					  <div class="menu">
							@foreach($pages as $p)
								<div class="item" data-value="{{$p->single_page_file_number}}">{{$p->single_page_file_number}}</div>
							@endforeach
					  </div>
					</div> 
					To : <div class="ui search selection dropdown page end">
						  <input type="hidden" name="pageend" value="">
						  <i class="dropdown icon"></i>
						  <div class="default text">page</div>
						  <div class="menu">
							@foreach($pages as $p)
								<div class="item" data-value="{{$p->single_page_file_number}}">{{$p->single_page_file_number}}</div>
							@endforeach
						  </div>
						</div>
					</div>				
				
			</td>	
			<td class="actions">
				@if(Auth::check() && in_array('editor', Auth::user()->roles))
					<i class="fa fa-plus-square-o"></i>
				@endif
			</td>
		</tr>		
	</table>





</div>

@endsection
