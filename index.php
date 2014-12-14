<?php
if (session_status() == PHP_SESSION_NONE)
{
session_start();
echo session_id();

}



?>

<!DOCTYPE html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="css/global.css">
<title>Our title here</title>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="js/global.js"></script>
</head>
<body  onLoad="jsInit()">
<div>
Hello World!
	<div id="table">
	</div>
</div>
</body>
