<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

session_start();
$session_id=session_id();
session_destroy(); //otherwise renewSession.php fails

function sendMessage($id , $message)
{
	echo "id: $id" . PHP_EOL;
	echo "data:".$message."\n";
	echo PHP_EOL;
	ob_flush();
	flush();
}

include "../modules/dbaccess.php";
$dbObj = new Database("pokerdb",'localhost',"root","");
$params = new Entities();

$lastupdate = 0;//$params->getParam('lastupdate')[0]->value;
		
while(true)
{
    //if script's session not in db then kill script
	$sqlCommand = 'SELECT sid FROM session WHERE sid=:sid';
	$data = array (":sid" => $session_id);
	$session_alive = $dbObj->parameterizedSelect($sqlCommand, $data);
	
	if (empty($session_alive[0]->sid)) {die();}

	/*if ((time() % 10) % 5 == 0) //cleanup old sessions
	{
		$sqlCommand = 'DELETE FROM session
						WHERE TIMESTAMPDIFF(second,session.lastupdate,:time) > :seconds';
		$ddate = new DateTime();
		$data = array (":seconds" => $maxlifetime, ":time" => $ddate->format('Y-m-d H:i:s'));
		$dbObj->executePreparedStatement($sqlCommand, $data);
	}*/ //moved to main.php for efficiency
	
	/*test database for updates and push them to client*/
	$thisupdate = $params->getParam('lastupdate')[0]->value;
	
	//is player? test
	$pdata = array (":sid" => $session_id);
	$sqlCommand = "SELECT id FROM player WHERE sid = :sid";
	
	if ($thisupdate > $lastupdate)
	{
		if (!empty($dbObj->parameterizedSelect($sqlCommand, $pdata)))
		{
			$lastupdate = $thisupdate;
			$stage = intval($params->getParam('stage')[0]->value);
			
			switch($stage)
			{
				case 0:
					$content = '';
					break;
				case 1:
					$players = $dbObj->select("SELECT id FROM player WHERE sid <> ''");
					$hdata = array (":sid" => $session_id);

					$sqlCommand = "SELECT id, hand FROM player WHERE sid = :sid";
					$hand = $dbObj->parameterizedSelect($sqlCommand, $hdata);
					if (!empty($hand))
					$pid = intval($hand[0]->id);
					$hand = $hand[0]->hand;
					
					$content = array( "stage" => $stage, "hand" => json_decode($hand), "owner" => $pid, "players" => $players );
					break;
				case 2:
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
			sendMessage($lastupdate, json_encode($message));
		}
	}
	
	if ((time()+1 % 10) % 5 == 0)
	{
		$message = array( "type" => 1, "message" => "" );
		sendMessage($lastupdate, json_encode($message));
	}
	
	
		//echo "id: ".$startedAt."\n";
		$date = new DateTime();
		$fdate = $date->format('Y-m-d H:i:s');

		$sessions = $dbObj->select("SELECT * FROM session");
		
		$message = array( "type" => 0, "message" => $sessions );
		sendMessage($lastupdate, json_encode($message));
	
	sleep(1);
}
?>