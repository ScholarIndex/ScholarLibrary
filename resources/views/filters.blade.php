<span class="unselect allfilters">Unselect all</span> / <span class="select allfilters">Select all</span>
{{--*/ $n = 0 /*--}}
{{--*/ $nbMore = 7 /*--}}
@foreach($response as $val => $cnt)
	@if($cnt>0)
		
		<li class="chk {{isset($filtrs[$field]) && in_array($val, $filtrs[$field]) ? 'checked' : ''}}" style="{{($n++ >= $nbMore) ? 'display:none' : ''}}" data-key="{{$val}}">{{Config::get($field.'.'.$val, 'Undetermined')}} ({{$cnt}})</li>
	@endif
@endforeach
@if($n > $nbMore)
	<span class="viewmore allfilters">View more...</span>
@endif