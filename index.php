<?php
include "modules/sessionHandler.php";
$handler = new PokerSessionHandler();

session_set_save_handler(
    array($handler, 'open'),
    array($handler, 'close'),
    array($handler, 'read'),
    array($handler, 'write'),
    array($handler, 'destroy'),
    array($handler, 'gc')
    );

session_start();

include "modules/dbaccess.php";

$dbObj = new Database("pokerdb",'localhost',"root","");

$sqlCommand = 'DELETE FROM session
				WHERE sid=:sid';
$data = array (":sid" => session_id());
$dbObj->executePreparedStatement($sqlCommand, $data);


$sqlCommand = 'INSERT INTO session
				VALUES (:sid, :time, :ip )';
				
$date = new DateTime();

$data = array(":sid" => session_id(), ":time" => $date->format('Y-m-d H:i:s'), ":ip" => $_SERVER['REMOTE_ADDR']);
$dbObj->executePreparedStatement($sqlCommand, $data);

?>

<!DOCTYPE html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="css/global.css">
<title>Our title here</title>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="js/client.js"></script>
</head>
<body  onLoad="jsInit()">
<div>
	<div id="test2" style="background-color:#DDD;">
	</div>
	<div id="test" style="background-color:#999;">
	</div>
	<div id="table">
	</div>
	<div class="footbar-margin">
	</div>
	<div class="footbar">
		<div class="footbar-center">
			<div class="col">
				<input type="radio" name="action" value="0" checked> Call <br>
				<input type="radio" name="action" value="1">  Raise
				<input type="number" style="width:80px;" id="input-raise"> <br>
				<input type="radio" name="action" value="2"> Fold <br>
				<input type="button" value="Confirm" id="confirm">
				<input type="button" value="Quit: 0" id="quit">
			</div>
			<div class="col">
				Status, on your turn: <br><span id="status"></span>
			</div>
			<div class="col">
				<div id="info">
				</div>
			</div>
		</div>
	</div>
</div>
</body>
