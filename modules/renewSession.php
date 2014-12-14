<?php
session_start();
if (session_id()=="")
{
	echo "I dont want to live on this earth anymore";
	die();
}
include "../modules/dbaccess.php";

$dbObj = new Database("pokerdb",'localhost',"root","");

$sqlCommand = 'UPDATE session
				SET lastupdate=:time
				WHERE sid=:sid';
				
$date = new DateTime();
$data = array (":sid" => session_id(), ":time" => $date->format('Y-m-d H:i:s'));
$dbObj->executePreparedStatement($sqlCommand, $data);

?>