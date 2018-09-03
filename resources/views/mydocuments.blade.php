<!DOCTYPE html>
<html>
    <head>
        <title>My Documnets</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%; background: #0A3B4C; color: white;
            }

            body {
                margin: 0;
                padding: 0;
                width: 60%;
                font-weight: 100;
                font-family: Arial;
                line-height: 1.5em;
				padding: 30px;
            }


            .title {
                font-size: 96px;
            }
            
            a { color: white; }
            a:hover { color: #ddd; }
        </style>
    </head>
    <body>
            	<h1>My Documents</h1>

				<h3>Bookmarks</h3>
		        <div class="ui bottom attached segment">
		                <ul>
		                  @if(count($bookmarks)>0)
		                        @foreach($bookmarks as $b)
		                       		<li><a href="/document/viewer/{{$b->thedoc->bid}}/{{$b->thedoc->number}}#{{$b->thepagenumber}}">{{$b->thedoc->bid}} / {{$b->thedoc->number}} {{ $b->thepagenumber!="" ? '/ page '.$b->thepagenumber : ''}}</a></li>
		                        @endforeach
		                  @else
		                        <li>No bookmarks.</li>
		                  @endif
		                </ul>
		        </div>


				<h3>See later</h3>
		        <div class="ui bottom attached segment">
		                <ul>
		                  @if(count($seelaters)>0)
		                        @foreach($seelaters as $b)
		                        	<li><a href="/document/viewer/{{$b->thedoc->bid}}/{{$b->thedoc->number}}#{{$b->thepagenumber}}">{{$b->thedoc->bid}} / {{$b->thedoc->number}} {{ $b->thepagenumber!="" ? '/ page '.$b->thepagenumber : ''}}</a></li>
		                        @endforeach
		                  @else
		                        <li>No See later.</li>
		                  @endif
		                </ul>
		        </div>
				
 

    </body>
</html>
