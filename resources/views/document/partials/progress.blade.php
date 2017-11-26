<br /><br />
References : {{$countRefs}}
<br /><br />
Checked : {{$countChecked}}
<br /><br />
@if($countRefs > 0)
<div class="bar">
	<p>{{round($countChecked/$countRefs*100)}}%</p>
	<div class="prog" style="width:{{$countChecked/$countRefs*100}}%"></div>
</div>
@endif