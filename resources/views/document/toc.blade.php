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
		<tr><th>Author</th><th class="title">Title</th><th>Page range</th><th></th></tr>
		
		@foreach($articles as $a)
			<tr>
				<td class="author"><input type="text" disabled value="{{$a['authors'][0]}}"/></td>
				<td class="title"><input type="text" disabled value="{{$a['title']}}" /></td>
				<td>{{$a['start_img_number']}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$a['end_img_number']}}</td>	
				<td><!--<i class="fa fa-trash-o"></i>--></td>
			</tr>
		@endforeach
<!--
		<tr class="addnew"><td colspan=5>Add new line</td></tr>
	

		<tr>
			<td class="author"><input class="name" type="text" /><input class="viaf" type="hidden" name="viaf" /><div class="authorDropdown"></div></td>
			<td class="title"><input type="text" /></td>
			<td><input type="text" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" /></td>	
			<td><i class="fa fa-plus-square-o"></i></td>
		</tr>
-->	

	</table>





</div>

@endsection
