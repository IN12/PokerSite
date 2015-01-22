<?php
if(!isset($_POST["action"])) {exit();}
	else $action = intval($_POST["action"]);

if(!isset($_POST["raise"]) or $_POST["raise"]=="") $raise=0;
	else $raise = intval($_POST["raise"]);
	
session_start();
$session_id=session_id();
session_destroy();

include "../modules/dbaccess.php";
$dbObj = new Database("pokerdb",'localhost',"root","");
$params = new Entities();

$session = array (":sid" => $session_id);
$sqlCommand = "SELECT quit FROM player WHERE sid = :sid";
if (empty($dbObj->parameterizedSelect($sqlCommand, $session)))
{
	echo "Not yet playing.";
	exit();
}

$date = new DateTime();
$lastupdate = $date->format('Y-m-d H:i:s');
$data = array( "action" => $action, "confirmed" => 1, "raise" => $raise);
$ray = array(":sid" => $session_id, ":data"=>json_encode($data), ":lastupdate" => $lastupdate);
$sqlCommand = "UPDATE player SET data = :data, lastupdate = :lastupdate WHERE sid = :sid";
$dbObj->executePreparedStatement($sqlCommand, $ray);
$currentbet = intval($params->getParam('currentbet')[0]->value);

$cdata = array (":sid" => $session_id);
$sqlCommand = "SELECT bet FROM player WHERE sid = :sid";
$playerbet = intval($dbObj->parameterizedSelect($sqlCommand, $cdata)[0]->bet);

switch($action)
{
	case 0: //Call
		echo 'Calling. Matching the bet by '.($currentbet-$playerbet);
		break;
	case 1: //Raise
		echo "Raising by ".$raise.". Bet will be ".($currentbet+$raise);
		break;
	case 2: //Fold
		echo "Folding, losing ".$playerbet;
		break;
}
?>