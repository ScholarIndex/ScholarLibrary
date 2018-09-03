<!DOCTYPE html>
<html>
    <head>
        <title>Laravel</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%; background: #0A3B4C; color: white;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 100;
                font-family: Arial;
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 96px;
            }
            
            a { color: white; }
            a:hover { color: #ddd; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title"><img src="/logoVSL.png" /></div>
				<p style="font-style:">The Scholar Library gives you access to thousands of digitized publications about the history of Venice</p>
				<p style="font-size:2.4em;">v2.5</p>
		<p><a href="/about">The Project</a> | <?php echo ( (Auth::check()) ? "<a href='/search'>HomeSearch</a> | " : ""); ?><a href="/auth/login">Login</a> | <a href="/auth/logout">Logout</a> | <?php echo ( (Auth::check()) ? Auth::user()->login." (".Auth::user()->email.")" : "Not logged in"); ?><!-- | <a href="/auth/register">Register</a>-->
		
		<p style="font-style:italic;margin-top:50px;font-size:0.7em;">The access to the Scholar Library is currently restricted to users with login (due to the current license agreements with the content providers).<br />If you are interested in trying out this tool, write to Giovanni Colavizza or Matteo Romanello (firstName.lastName[at] epfl [dot] ch) with a one-line description of your research interests</p>


	
            </div>
        </div>
    </body>
</html>
