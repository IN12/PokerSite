<?php
session_start();
include "../modules/dbaccess.php";

$dbObj = new Database("pokerdb",'localhost',"root","");
$params = new Entities();
$sessions = $dbObj->select("SELECT * FROM session");

/*$activeSessions = "";
var_dump($sessions);
echo $sessions[0]->sid;
echo '<br><br>';
echo $sessions[0]->lastupdate;
echo '<br><br>';
echo $sessions[0]->ip;*/

/*foreach ($sessions as $session)
	{
		echo $session->sid;
		echo '<br><br>';
		echo $session->lastupdate;
		echo '<br><br>';
		echo $session->ip;
	}*/
		/*$maxlifetime=300;
		$sqlCommand = 'DELETE FROM session
						WHERE TIMESTAMPDIFF(second,session.lastupdate,:time) > :seconds';
		$date = new DateTime();
		$data = array (":seconds" => $maxlifetime, ":time" => $date->format('Y-m-d H:i:s'));
		$dbObj->executePreparedStatement($sqlCommand, $data);*/
		
	/*$sqlCommand = 'SELECT sid FROM session
				WHERE sid=:sid';
	$data = array (":sid" => session_id());
	$test = $dbObj->parameterizedSelect($sqlCommand, $data);
	
	var_dump($test);
	echo '<br><br>';
	echo "But is it empty???".var_dump(empty($test[0]->sid));
	echo '<br><br>';
	echo $test[0]->sid;*/
	
/*var_dump($params->getParam('lastupdate'));
echo $params->getParam('lastupdate')[0]->value;*/
/*$sqlCommand = 'SELECT * FROM session
						WHERE TIMESTAMPDIFF(second,session.lastupdate,:time) > :seconds';
		$ddate = new DateTime();
		$data = array (":seconds" => 15, ":time" => $ddate->format('Y-m-d H:i:s'));
		$removedsessions = $dbObj->parameterizedSelect($sqlCommand, $data);
$sessions = $dbObj->select("SELECT * FROM session");
if (empty($sessions))
{
echo 'yes';
}
else
echo 'no';
foreach ($removedsessions as $session)
{
	echo $session->sid;
	echo '<br>';
}
*/
//echo intval($dbObj->select("SELECT COUNT(*) AS Num FROM player WHERE sid <> ''")[0]->Num);
/*function test($stage)
{
$session_id = session_id();
$dbObj = new Database("pokerdb",'localhost',"root","");
$params = new Entities();
switch($stage)
		{
			case 0:
				$content = '';
				break;
			case 1:
				$players = $dbObj->select("SELECT id FROM player WHERE sid <> ''");
				$hdata = array (":sid" => $session_id);

				$sqlCommand = "SELECT id, hand FROM player WHERE sid = :sid";
				//var_dump($dbObj->parameterizedSelect($sqlCommand, $hdata));
				$hand = $dbObj->parameterizedSelect($sqlCommand, $hdata);
				if (!empty($hand))
				$pid = intval($hand[0]->id);
				$hand = $hand[0]->hand;
				
				$content = array( "stage" => $stage, "hand" => json_decode($hand), "owner" => $pid, "players" => $players );
				break;
			case 3:
			case 5:
				$dealercards = $params->getParam('dealercards')[0]->value;
				
				$content = array( "stage" => $stage, "dealercards" => json_decode($dealercards) );
			case 7:
				$dealercards = $params->getParam('dealercards')[0]->value;
				$players = $dbObj->select("SELECT id,hand FROM player WHERE sid <> ''");
				$hands = [];
				foreach ($players as $pair)
				{
					array_push($hands, array( "id" => intval($pair->id), "hand" => json_decode($pair->hand)));
				}
				$content = array( "stage" => $stage, "dealercards" => json_decode($dealercards), "hands" => $hands);
				break;
			case 8:
				$content = '';
				break;
		}
		
		
		$message = array( "type" => 2, "message" => $content );
		echo json_encode($message);
}

for ($i=0; $i<9; $i++)
{
	test($i);
	echo '<br>-='.$i.'=-<br>';
}*/
$hdata = array (":sid" => session_id());
$sqlCommand = "SELECT id, hand FROM player WHERE sid = :sid";
				//var_dump($dbObj->parameterizedSelect($sqlCommand, $hdata));
				$hand = $dbObj->parameterizedSelect($sqlCommand, $hdata);
				var_dump($hand);
?>