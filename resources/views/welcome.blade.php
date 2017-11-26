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
                <div class="title">LB Catalogue</div>
				<p style="font-size:2.4em;">v2.1_beta</p>
		<p><a href="/about">The Project</a> | <?php echo ( (Auth::check()) ? "<a href='/search'>HomeSearch</a> | " : ""); ?><a href="/auth/login">Login</a> | <a href="/auth/logout">Logout</a> | <?php echo ( (Auth::check()) ? Auth::user()->login." (".Auth::user()->email.")" : "Not logged in"); ?><!-- | <a href="/auth/register">Register</a>-->


	
            </div>
        </div>
    </body>
</html>
