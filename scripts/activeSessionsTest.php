<?php
include "../modules/dbaccess.php";

$dbObj = new Database("pokerdb",'localhost',"root","");

$sessions = $dbObj->select("SELECT * FROM session");

/*$activeSessions = "";
var_dump($sessions);
echo $sessions[0]->sid;
echo '<br><br>';
echo $sessions[0]->lastupdate;
echo '<br><br>';
echo $sessions[0]->ip;*/

foreach ($sessions as $session)
	{
		echo $session->sid;
		echo '<br><br>';
		echo $session->lastupdate;
		echo '<br><br>';
		echo $session->ip;
	}
		/*$maxlifetime=300;
		$sqlCommand = 'DELETE FROM session
						WHERE TIMESTAMPDIFF(second,session.lastupdate,:time) > :seconds';
		$date = new DateTime();
		$data = array (":seconds" => $maxlifetime, ":time" => $date->format('Y-m-d H:i:s'));
		$dbObj->executePreparedStatement($sqlCommand, $data);*/
?>