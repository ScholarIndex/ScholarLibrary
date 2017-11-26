@foreach($authors as $viafId => $name)
	<li data-viafid="{{$viafId}}">{{$name}}</li>
@endforeach