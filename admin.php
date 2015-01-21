<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin</title>
	<script type="text/javascript" src="js/admin.js"></script>
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	
  </head>
  <body onLoad="jsInit()">
	<p>
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12 col-md-8" id="test">
				</div>
				<div class="col-xs-6 col-md-4">
					<input class="btn btn-default" type="button" onClick="jsHandbrake(this)" value="Handbrake: 0">
					<input class="btn btn-default" type="button" onClick="jsAbort(this)" value="Abort: 0">
				</div>
			</div>
		</div>
	</p>
	
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>