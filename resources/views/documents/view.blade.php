@extends('layout')


@section('content')

	<div class="ui bottom attached segment issue" data-bid="{{$bid}}" data-issue="{{$issue}}">

		<div class="ui text menu">

		<div class="item ch" style="{{ ! $isChecked ? 'display:none' : ''}}">
			<button class="ui labeled icon green button" id="unchecker" data-txt-normal="Checked" data-txt-hover="Not check" data-action="uncheck">
			  <i class="check icon"></i>
			  <span>Checked</span>
			</button>
		</div>
		<div class="item ch" style="{{$isChecked ? 'display:none' : ''}}">
			<button class="ui labeled icon red button" id="checker" data-txt-normal="Not yet checked" data-txt-hover="Mark as check" data-action="check">
			  <i class="remove icon"></i>
			  <span>Not yet checked</span>
			</button>
		</div>			
		
		<div class="bm item" style="{{ ! $isFavorite ? 'display:none' : ''}}" data-type="doc_favorite" data-action="remove">
			<button class="ui labeled icon button">
			  <i class="star yellow icon"></i>
			  Remove from favorites
			</button>
		</div>
		<div class="bm item" style="{{$isFavorite ? 'display:none' : ''}}" data-type="doc_favorite" data-action="add">
			<button class="ui labeled icon button">
			  <i class="star yellow icon"></i>
			  Add to favorites
			</button>
		</div>		
		
		
		
		<div class="bm item" style="{{ ! $isSeeLater ? 'display:none' : ''}}" data-type="doc_seelater" data-action="remove">
			<button class="ui labeled icon button">
			  <i class="star blue icon"></i>
			  Don't see later
			</button>
		</div>
		<div class="bm item" style="{{$isSeeLater ? 'display:none' : ''}}" data-type="doc_seelater" data-action="add">
			<button class="ui labeled icon button">
			  <i class="star blue icon"></i>
			  See later
			</button>
		</div>
			

		</div>


<div class="ui top attached tabular menu">
	<a class="item active" data-tab="overview">Overview</a>
	<a class="item " data-tab="pages">Pages</a>
	<a class="item " data-tab="tocmanager">TOC Manager</a>
</div>

<div class="ui bottom attached tab segment overview active" data-tab="overview">
<div class="ui three column stackable grid">

    <div class="column">
     <h3>Metadata</h3><hr />
     <p><i class="icon info small blue circle"></i>Click on metadata to edit</p>
     <table class="ui very basic table metadata">
     	<tbody>
     	@foreach($metadataList as $label => $val)
     		<tr>
     			<th>{{$label}}</th>
     			<td>{!!$val!!}</td>
     		</tr>
     	@endforeach
     	</tbody>
     </table>
     
    </div>
    <div class="column">
      <h3>Index</h3><hr />
      <ul class='ui indexOverview' style='min-height:200px;position:relative;'>
      </ul>
      <h3>Table of Contents</h3><hr />
      <ul class='ui tocOverview' style='min-height:200px;position:relative;'>
      </ul>
    </div>
    <div class="column">
      <h3>Related Documents</h3><hr />
    </div>
</div>


</div>

<div class="ui bottom attached tab segment pages" style="min-height:300px;" data-tab="pages">
	<div class="ui cards pages centered">
		<div class="ui loader active"></div>
	</div>
</div>	
	
	
	
<div class="ui bottom attached tab segment tocmanager" data-tab="tocmanager">	
	<div class="ui centered grid">
	    <div class="center aligned column">
			<div class="ui compact text menu">
				<div class="ui labeled icon button prevTocPage"><i class="icon double angle left"></i>Previous</div>
				<div class="ui floating dropdown labeled search icon button pageSelector">
				  <i class="file text outline icon"></i>
				  <span class="text">Select Image</span>
				  <div class="menu">
				    @foreach($document['pages'] as $i => $p)
				    	<div class="item">{{$i+1}}</div>
				    @endforeach
				  </div>
				</div>
				<div class="ui right labeled icon button nextTocPage">Next<i class="icon double angle right"></i></div>
			</div>
		</div>
	</div>
	
	<div class="ui cards tocpages relative centered" style="min-height:300px;"></div>
	<div class="ui stackable relative toctable" style="min-height:300px;margin-top:30px;"></div>		
</div>
	
	
</div>

@stop