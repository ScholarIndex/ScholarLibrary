@extends('layout')

@section('sidemenu')

<ul>
	<li class="sub" data-field="publication_language">Language <span class='cnt'></span>
		<ul>
		</ul>	
	</li>
	<li class="sub" data-field="digitization_provenance">Provenance <span class='cnt'></span>
		<ul></ul>
	</li>
</ul>

@endsection


@section('searchmenu')
<div class="searchbar">
	<a class="title" href="/search">Linked<br />Books</a>
	
	<div class="searchform">
		<div id="search"><input type="text" value="{{$data['q'] or ''}}" /></div>
		<ul class="filters">
			<li class="chk {{isset($data['in']['authors']) && $data['in']['authors']=='false' ? '' : 'checked'}} authors">Authors</li>
			<li class="chk {{isset($data['in']['titles']) && $data['in']['titles']=='false' ? '' : 'checked'}} titles">Titles</li>
			<li class="chk {{isset($data['in']['publishers']) && $data['in']['publishers']=='false' ? '' : 'checked'}} publishers">Publishers</li>
<!--			<li class="chk">Journal Issue</li>-->
		</ul>
	</div>
	<a href="/auth/logout" class="loginname">{{Auth::user()->login}}</a>
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
