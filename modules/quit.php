<?php
session_start();
$session_id=session_id();
session_destroy();

include "../modules/dbaccess.php";
$dbObj = new Database("pokerdb",'localhost',"root","");

$session = array (":sid" => $session_id);
$sqlCommand = "SELECT quit FROM player WHERE sid = :sid";
$quit = intval($dbObj->parameterizedSelect($sqlCommand, $session)[0]->quit);

if ($quit)
{
	$sqlCommand = "UPDATE player SET quit = 0 WHERE sid = :sid";
	$dbObj->executePreparedStatement($sqlCommand, $session);
}
else
{
	$sqlCommand = "UPDATE player SET quit = 1 WHERE sid = :sid";
	$dbObj->executePreparedStatement($sqlCommand, $session);
}

$quit = ($quit) ? 0 : 1; 
echo $quit;
?>