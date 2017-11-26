<ul class="bdb">
	@foreach($authors as $a)
		<li>{{$a->author_final_form}}</li>
	@endforeach
</ul>
<ul class="viaf">
	<li class="load">VIAF Lookup</li>
</ul>