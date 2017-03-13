@extends('layout')


@section('content')

	<div class="ui bottom attached segment">
		  
		<div id="filters">
			@include('documents.filters')
		</div>
		
		<div id="pagination"></div>
		

		
		<div id="documentsResults" class="ui basic">
			<div class="ui loader active"></div>
		</div>
	</div>

@stop