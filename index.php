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

if (session_status() == PHP_SESSION_NONE)
{
	echo "new session";
	$_SESSION['test']="true";
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
}
else
echo "session exists";


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
	<div id="test" style="background-color:#AAA; height:500px;">
	</div>
</div>
</body>
