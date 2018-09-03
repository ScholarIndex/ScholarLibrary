@extends('layout')

@section('sidemenu')
<p class="menuCategory">Sort results</p>
<select name="sort" id="sort">
	<option value="score">Pertinence</option>
	<option value="title">Title</option>
	<option value="year_asc">Publication date (asc)</option>
	<option value="year_desc">Publication date (desc)</option>
</select>
<p class="menuCategory">Filter results</p>
<ul>
	
	<li class="disabled yearfilter">Publication year<br /><br />
		<input type="hidden" name="mindate" />
		<input type="hidden" name="maxdate" />
		<div class="barchart"></div><br />
		<ul><li class="chk year"><span class="nm">Enable year filter</span></li></ul>	
		<div class="yearslider"></div><br /><br />
	</li>
	
		
	<li class="sub" data-field="publication_language">Language <span class='cnt'></span>
		<ul>
		</ul>	
	</li>
	<li class="sub" data-field="digitization_provenance">Provenance <span class='cnt'></span>
		<ul></ul>
	</li>
	<li class="sub" data-field="short_title_ac">Journal <span class='cnt'></span>
		<ul></ul>
	</li>
	
	<span class="reset allfilters">Reset all filters</span>
</ul>

@endsection


@section('searchmenu')
<div class="searchbar">
	<a class="title" href="/search"><img src="/logoVSL.png" /></a>
	
	<div class="searchform">
		<div id="search"><input type="text" value="{{$data['q'] or ''}}" /></div>
		<ul class="filters">
			<li class="chk {{isset($data['in']['authors']) && $data['in']['authors']=='false' ? '' : 'checked'}} authors">Authors</li>
			<li class="chk {{isset($data['in']['titles']) && $data['in']['titles']=='false' ? '' : 'checked'}} titles">Titles</li>
			<li class="chk {{isset($data['in']['publishers']) && $data['in']['publishers']=='false' ? '' : 'checked'}} publishers">Publishers</li>
<!--			<li class="chk">Journal Issue</li>-->
		</ul>
	</div>
	<a href="/mydocuments" class="mydoc" title="Logged as : {{Auth::user()->login}}">My Documents</a>
	<a href="/auth/logout" class="logout" title="Logged as : {{Auth::user()->login}}">Logout</a>
	<a href="/about" class="about">About</a>
</div>
<div class="countersbar">
	<ul>
		<li class="docs"><p><span>0</span></p><p>documents</p></li>
		<li class="cat monograph {{isset($data['ns']) && !in_array('monograph',$data['ns']) ? 'disabled' : ''}}" data-ns="monograph"><span>0</span>Books</li>
		<li class="cat contribution {{isset($data['ns']) && !in_array('contribution',$data['ns']) ? 'disabled' : ''}}" data-ns="contribution"><span>0</span>Contributions</li>
		<li class="cat journal {{isset($data['ns']) && !in_array('journal',$data['ns']) ? 'disabled' : ''}}" data-ns="journal"><span>0</span>Journals</li>
		<li class="cat article {{isset($data['ns']) && !in_array('article',$data['ns']) ? 'disabled' : ''}}" data-ns="article"><span>0</span>Articles</li>
		<li class="pagin"><p>Page</p><!--<p>Show<span class="dropd"><i class="fa fa-caret-down"></i> 20</span>per page</p>--><p><i class="fa fa-angle-left"></i> <span class="noPage">{{$data['page'] or 1}}</span> / <span class="nbPage">1</span> <i class="fa fa-angle-right"></i></p></li>
	</ul>	
</div>
@endsection



@section('content')
<div class="results"></div>
@endsection
