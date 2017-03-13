<table class="ui celled padded table">
	 <thead>
	<tr>
		<th>Section</th>
		<th>Author</th>
		<th>Title</th>
		<th>Page range</th>
		<th>Action</th>
	</tr>
	</thead>
	<tbody>
		@foreach($articles as $i => $t)
			<tr>
				<td># {{$i+1}}</td>
				<td><p class='editable' contenteditable='true' data-field="author">{{$t['author']}}</p></td>
				<td><p class='editable' contenteditable='true' data-field="title">{{$t['title']}}</p></td>
				<td>
					<span class='editable sectionStartPage' style='display:inline-block;width:50px;text-align:center;' contenteditable='true' data-field="start_page">{{$t['start_page']}}</span>
					-
					<span class='editable sectionEndPage' style='display:inline-block;width:50px;text-align:center;' contenteditable='true' data-field="end_page">{{$t['end_page']}}</span>
				</td>
				<td>
					<i class="icon trash link circular inverted"></i>
				</td>
			</tr>
		@endforeach
	<tr class='model'>
		<td></td>
		<td><p class='editable' contenteditable='true' data-field="author"></p></td>
		<td><p class='editable' contenteditable='true' data-field="title"></p></td>
		<td><span class='editable sectionStartPage' style='display:inline-block;width:50px;text-align:center;' contenteditable='true' data-field="start_page"></span>
			-
			<span class='editable sectionEndPage' style='display:inline-block;width:50px;text-align:center;' contenteditable='true' data-field="end_page"></span></td>
		<td><i class="icon trash link circular inverted"></i></td>
	</tr>
	<tr class='new'>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td><i class="icon add link circular inverted"></i></td>
	</tr>
</tbody>
</table>