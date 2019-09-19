<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Scholar Index</title>
	<link href="https://fonts.googleapis.com/css?family=Handlee" rel="stylesheet">
	
	<link rel="stylesheet" href="{{ asset('/css/font-awesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('/css/nouislider.min.css') }}">
	<link rel="stylesheet" href="{{ asset('/css/jquery-ui.min.css') }}">
	<link rel="stylesheet" href="{{ asset('/css/alertify.min.css') }}">
	<link rel="stylesheet" href="{{ asset('/semantic-ui/semantic.min.css') }}">
	<link rel="stylesheet" href="{{ asset('/css/themes/default.min.css') }}">

	<link rel="stylesheet" href="{{ asset('/css/app.css') }}">
	

	
	<script>var _SOLR_ROOT_ = '{{env('SOLR_ROOT')}}';</script>
	<script>var _IIIF_ROOT_ = '{{env('IIIF_ROOT')}}';</script>
	<script src="{{ asset('/js/jquery-3.2.0.min.js') }}"></script>
	<script src="{{ asset('/js/jquery-ui.min.js') }}"></script>
	<script src="{{ asset('/js/lazysizes.min.js') }}"></script>
	<script src="{{ asset('/js/openseadragon.min.js') }}"></script>
	<script src="{{ asset('/js/jquery.hashchange.min.js') }}"></script>
	<script src="{{ asset('/js/jquery.mark.min.js') }}"></script>
	<script src="{{ asset('/js/jquery.scrollTo.min.js') }}"></script>
	<script src="{{ asset('/js/d3.min.js') }}"></script>
	<script src="{{ asset('/js/d3.tip.min.js') }}"></script>
	<script src="{{ asset('/js/nouislider.min.js') }}"></script>
	<script src="{{ asset('/js/alertify.min.js') }}"></script>
	<script src="{{ asset('/semantic-ui/semantic.min.js') }}"></script>
	<script src="{{ asset('/js/all.js') }}"></script>
	







</head>
<body class="{{$hasTopMenu}}" data-cred="{{implode(',',Auth::user()->roles)}}" data-js="{{$dataJs or ''}}" data-pagecount="{{$pagecount or ''}}" data-bid="{{$bid or ''}}" data-issue="{{$issue or ''}}" data-documentid="{{$documentId or ''}}" data-pageid="{{$pageId or ''}}" data-type="{{$type or ''}}" data-provenance="{{$provenance or ''}}" data-bidwithprov="{{$bidwithprov or ''}}">
	<i class="fa fa-question-circle"></i>
	<div id="searchmenu">@yield('searchmenu')</div>

	<div id="content">@yield('content')</div>
	<div id="sidemenu">
		<!--<a class="title" href="/search">Linked<br />Books</a>-->
		@yield('sidemenu')

		<div class="progress"></div>
		<div class="indexgolden"></div>
		<div class="bookmarks"></div>
	</div>
	<div id="screenwrapper">
		Please use this app on a screen with 1430px minimal width
	</div>
	
<div id="helpwrapper">
	<i class="fa fa-question-circle"></i>
	
	<p class="nohelp">No help message to display</p>
	
	<p class="SEARCH c1">Filters on results<br />by language<br />and library provenance</p>
	<p class="SEARCH c2">Search box</p>
	<p class="SEARCH c3">Login and out and About page</p>
	<p class="SEARCH c4">Categories of results with number of results each</p>
	<p class="SEARCH c5">Search filters : activate to search over the specific field</p>
	<p class="SEARCH c6">Number of results</p>
	<p class="SEARCH c7">Results are distributed over pages</p>
	<p class="SEARCH c8">Results are distinguished by typology/color</p>

	
	<p class="OVERVIEW c1">Back to home search</p>
	<p class="OVERVIEW c2">Status of object: number of<br />extracted references and of<br />manually verified references,<br />with proportion bar.</p>
	<p class="OVERVIEW c3">Back to previous search results</p>
	<p class="OVERVIEW c4">This tab displays the metadata related to the selected object</p>
	<p class="OVERVIEW c5">This tab displays a gallery of the scans</p>
	<p class="OVERVIEW c6">This tab displays the images and text side by side</p>
	<p class="OVERVIEW c7">This tab displays the full text and extracted references</p>
	<p class="OVERVIEW c8">This area displays the general<br />metadata related to the selected<br /> object. Metadata comes from the<br />library catalog (from digitization<br />metadata gives project internal<br />information)</p>
	<!--<p class="OVERVIEW c9">Table of contents (usually for journal issues)</p>
	<p class="OVERVIEW c10">Links to external resources</p>-->
	
	<p class="SCANS c1">Gallery of images. On click, it<br />moves to the Viewer</p>
	
	<p class="VIEWER c1">Image and OCR text, side by side</p>
	<p class="VIEWER c2">Thumbnail gallery</p>
	
	<p class="JOURNAL c1">List of available issues</p>
	
	<p class="ARTICLEOVERVIEW c1">Move to previous or next article in the same issue</p>
</div>	
	
</body>
</html>
