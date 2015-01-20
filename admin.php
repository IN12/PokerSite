<!DOCTYPE html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="css/global.css">
<title>Our title here</title>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="js/admin.js"></script>
</head>
<body  onLoad="jsInit()">
<div style="position:relative;">
	<div style="background-color:#999; height:22px;">
	<input type="button" value="Handbrake: 0" onClick="jsHandbrake(this)" style="width:94px;">
	<input type="button" value="Abort: 0" onClick="jsAbort(this)" style="width:62px;">
	</div>
	<div id="test" style="background-color:#AAA; height:100%; overflow: scroll;">
	</div>
</div>
</body>