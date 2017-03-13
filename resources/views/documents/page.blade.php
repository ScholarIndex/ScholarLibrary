@extends('layout')


@section('content')
<div class="ui bottom attached segment page" data-bid="{{$bid}}" data-obj="{{$pageObject->id}}" data-issue="{{$issue}}" data-page="{{$pg}}">
	<div class="ui text menu">
		<div class="item">
			<a onclick="window.close();" class="ui labeled icon button">
			  <i class="angle double left icon"></i>
			  Back to document
			</a>
		</div>
		
		
		<div class="item">
			<a href="/document/{{$bid}}/{{$issue}}/{{$rnd}}" class="ui labeled icon button">
			  <i class="wizard icon"></i>
			  Jump to random page
			</a>
		</div>
		
		<div class="item right">
			<div class="inx item" style="{{ ! $isIndex ? 'display:none' : ''}}" data-action="remove">
				<button class="ui labeled icon button">
				  <i class="File Text Outline icon"></i>
				  Remove from index pages
				</button>
			</div>
			<div class="inx item" style="{{$isIndex ? 'display:none' : ''}}" data-action="add">
				<button class="ui labeled icon button">
				  <i class="File Text Outline icon"></i>
				  Add to index pages
				</button>
			</div>		
		</div>
		
		<div class="item">
			<div class="golden item" style="{{ ! $isGolden ? 'display:none' : ''}}" data-action="remove">
				<button class="ui labeled icon button">
				  <i class="yellow folder open icon"></i>
				  Remove from golden set
				</button>
			</div>
			<div class="golden item" style="{{$isGolden ? 'display:none' : ''}}" data-action="add">
				<button class="ui labeled icon button">
				  <i class="yellow folder open icon"></i>
				  Add to golden set
				</button>
			</div>		
		</div>		
      	
      	<div class="item">
			<button class="ui labeled icon button metadatas">
			  <i class="double angle down icon "></i>
			  Page metadatas
			</button>
			<div class="ui popup flowing metadatas" style="width:300px">

					<div class="ui labeled input">
					  <div class="ui label">Printed page number</div>
					  <input type="text" id="printedPageNumber" style="width:100px" value="{{join(',',$pageObject->printed_page_number)}}">
					</div>
					
					<br /><br />
					

					<div class="ui text menu">
						<div class="item">
	
						 	<div class="ui buttons savePrinted">
							  <button class="ui button" data-type='set'>Save only</button>
							  <div class="or"></div>
							  <button class="ui button" data-type='propagate'>Save & propagate</button>
							</div>
					   </div>
					</div>
			</div>
      	</div>
      	
      	
	</div>
	<div class="ui two column grid">
		<div class="column">
			<div class="ui text menu">
				
				<div class="bm item" style="{{ ! $isFavorite ? 'display:none' : ''}}" data-type="favorite" data-action="remove">
					<button class="ui labeled icon button">
					  <i class="star yellow icon"></i>
					  Remove from favorites
					</button>
				</div>
				<div class="bm item" style="{{$isFavorite ? 'display:none' : ''}}" data-type="favorite" data-action="add">
					<button class="ui labeled icon button">
					  <i class="star yellow icon"></i>
					  Add to favorites
					</button>
				</div>		
				
				
				
				<div class="bm item" style="{{ ! $isSeeLater ? 'display:none' : ''}}" data-type="seelater" data-action="remove">
					<button class="ui labeled icon button">
					  <i class="star blue icon"></i>
					  Don't see later
					</button>
				</div>
				<div class="bm item" style="{{$isSeeLater ? 'display:none' : ''}}" data-type="seelater" data-action="add">
					<button class="ui labeled icon button">
					  <i class="star blue icon"></i>
					  See later
					</button>
				</div>			
					
			
		      	
		      	
			</div>
			<div id="openseadragon1" class="ui centered" data-src="http://dhlabsrv4.epfl.ch/iiif_lbc/{{$bid}}::{{$issue}}::{{$pg}}/info.json" style="width:100%;"></div>
		</div>
		<div class="column">
			
			<div class="ui text menu">
				
			
				<div class="item right">
		        	Scan rating : <div class="ui rating" data-type="scan" data-rating="{{$ratingScan}}"></div>
		      	</div>
				<div class="item">
		        	OCR rating : <div class="ui rating" data-type="ocr" data-rating="{{$ratingOcr}}"></div>
		      	</div>
		    
		      	
			</div>
			<div class='fulltext {{$pageModeLBC}}'>
				@foreach($lines as $i => $t)
					<p class="{{$pageObject->lines[$i]['in_footnote'] ? 'isfootnote' : ''}} {{isset($pageObject->split_after_line) && $pageObject->split_after_line == $i ? 'issplitup' : ''}} {{isset($pageObject->split_after_line) && $pageObject->split_after_line == $i-1 ? 'issplitdown' : ''}}" data-line="{{$i}}">{{$t}}</p>
				@endforeach
			</div>
			
			<div class="ui text menu">
				<div class="item">
					<div class="ui floating labeled icon dropdown button">
					  <input name="mode" type="hidden" value="{{$pageModeLBC}}">
					  <i class="dropdown icon"></i>
					  <span class="text">Mode</span>
					  <div class="menu">
					    <div class="header">
					      <i class="tags icon"></i>
					      Select mode
					    </div>
					    <div class="divider"></div>
					    <div class="item" data-value="selection">
					      <i class="copy icon"></i>
					      Selection
					    </div>
					    <div class="item" data-value="footnote">
					      <i class="pointing down icon"></i>
					      Footnote
					    </div>
					    <div class="item" data-value="split">
					      <i class="cut icon"></i>
					      Split
					    </div>
					  </div>
					</div>
				</div>
				<div class="item action right saveFootnotes" style="display:none;">
					<div class="ui link labeled icon button">
					  <i class="pointing down icon"></i>
					  Save footnotes
					</div>
				</div>
				<div class="item action right splitHere" style="display:none;">
					<div class="ui link labeled icon button">
					  <i class="cut icon"></i>
					  Split here
					</div>
				</div>	
				<div class="item action right copySelection" style="display:none;">
					<div class="ui disabled labeled icon button">
					  <i class="copy icon"></i>
					  Use ctrl + C
					</div>
				</div>						
			</div>
		</div>
	</div>
</div>


<div class="ui inverted menu fixed footer">
	
	@foreach($document['pages'] as $i => $obj)
		<a class="ui card link {{$i==$pg-1 ? 'active' : ''}}" href="/document/{{$bid}}/{{$issue}}/{{$i+1}}">
    		<div class="image">
				<img class="b-lazy" style="width:89px;height:100px;" data-src="http://dhlabsrv4.epfl.ch/iiif_lbc/{{$bid}}::{{$issue}}::{{$i+1}}/full/89,/0/default.jpg" />
    		</div>
    		<div class="ui bottom attached button {{$i==$pg-1 ? 'primary' : ''}}">
      			{{$i+1}}
      			@if(in_array($obj,$indexPages))
      				&nbsp;<i class="mini inverted circular blue info icon" style="vertical-align:middle;font-size:0.6em;"></i>
      			@endif
    		</div>
  		</a>
	@endforeach

</div>

@stop

