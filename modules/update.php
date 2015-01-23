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

$startdate = new DateTime();
$lastinfoupdate = $startdate->format('Y-m-d H:i:s');
$lastupdate = 0;//$params->getParam('lastupdate')[0]->value;
$content = [];
$type = 2;

//First run
$stage = intval($params->getParam('stage')[0]->value);
$ldate = $params->getParam('lastupdate')[0]->value;

if($stage >= 3){
	$dealercards = $params->getParam('dealercards')[0]->value;
	$content = array( "stage" => 5, "dealercards" => json_decode($dealercards) );
	$message = array( "type" => 4, "message" => $content );	//just run update stage
	sendMessage($ldate, json_encode($message));
}

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
	
	/*test db for player info updates and push to client */
	$infoquery = $dbObj->select("SELECT lastupdate FROM `player` WHERE sid <> '' ORDER BY lastupdate DESC LIMIT 1");
	if (!empty($infoquery))
	$thisinfoupdate = $infoquery[0]->lastupdate;
	{
		if ($thisinfoupdate > $lastinfoupdate) //updates in player info
		{
			$sqlCommand = "SELECT id,funds,bet,data,quit FROM player
							WHERE TIMESTAMPDIFF(second,:time,player.lastupdate) >= 0 AND sid <> ''";
			$ddate = new DateTime();
			$data = array (":time" => $lastinfoupdate);
			$playerinfo = $dbObj->parameterizedSelect($sqlCommand, $data);
			
			$lastinfoupdate = $thisinfoupdate;
			
			$info = [];
			foreach ($playerinfo as $player)
			{
				array_push($info, array( "id" => $player->id, "funds" => $player->funds, "bet" => $player->bet, "data" => json_decode($player->data), "quit" => $player->quit ));
			}
			
			//also get pot info, likely to change with some player info
			$pot = intval($params->getParam('pot')[0]->value);
			$currentbet = intval($params->getParam('currentbet')[0]->value);
			//$info = count($playerinfo);
			
			$content = array( "playerinfo" => $info, "potinfo" => array( "pot" => $pot, "currentbet" => $currentbet ) );
			$message = array( "type" => 3, "message" => $content );
			
			sendMessage($lastinfoupdate, json_encode($message));
		}
	}
	
	/*test database for updates and push them to client*/
	$thisupdate = $params->getParam('lastupdate')[0]->value;
	
	//is player? test
	$pdata = array (":sid" => $session_id);
	$sqlCommand = "SELECT id FROM player WHERE sid = :sid";
	
	if ($thisupdate > $lastupdate) //updates in game state
	{
		if (!empty($dbObj->parameterizedSelect($sqlCommand, $pdata)[0]->id))
		{
			$lastupdate = $thisupdate;
			$stage = intval($params->getParam('stage')[0]->value);
			
			switch($stage)
			{
				case 0:
					$content = array( "stage" => $stage);
					break;
				case 1:
					$players = $dbObj->select("SELECT id FROM player WHERE sid <> ''");

					$sqlCommand = "SELECT id, hand FROM player WHERE sid = :sid";
					$hand = $dbObj->parameterizedSelect($sqlCommand, $pdata);
					if (!empty($hand))
					$pid = intval($hand[0]->id);
					$hand = $hand[0]->hand;
					
					$content = array( "stage" => $stage, "hand" => json_decode($hand), "owner" => $pid, "players" => $players );
					break;
				case 2:
				case 4:
				case 6:
				case 8:
					$rotationid = intval($params->getParam('rotationid')[0]->value);
					$reactionid = intval($params->getParam('reactionid')[0]->value);
					$content = array( "stage" => $stage, "rotationid" => $rotationid,"reactionid" => $reactionid );
					break;
				case 3:
				case 5:
					$dealercards = $params->getParam('dealercards')[0]->value;
					
					$content = array( "stage" => $stage, "dealercards" => json_decode($dealercards) );
				case 7:
					$dealercards = $params->getParam('dealercards')[0]->value;
					
					$content = array( "stage" => $stage, "dealercards" => json_decode($dealercards));
					break;
				/*case 8:
					$content = array( "stage" => $stage);
					break;*/
				case 9:
					$players = $dbObj->select("SELECT id,hand,eval FROM player WHERE sid <> ''");
					
					$hands = [];
					foreach ($players as $pair)
					{
						array_push($hands, array( "id" => intval($pair->id), "hand" => json_decode($pair->hand)));
					}
					
					$results = [];
				
					foreach ($players as $result)
					{
						array_push($results, array( "id" => intval($result->id), "eval" => json_decode($result->eval)));
					}
					
					$content = array( "stage" => $stage, "results" => $results,  "hands" => $hands);
					break;
				case 10: //check for quitting
					$sqlCommand = "SELECT quit FROM player WHERE sid = :sid";
					$quit = intval($dbObj->parameterizedSelect($sqlCommand, $pdata)[0]->quit);
					if ($quit)
					{
						$type = -1;
					}
					$content = array( "stage" => $stage);
					break;
				case 11:
					$content = array( "stage" => $stage);
					break;
			}
			
			
			$message = array( "type" => $type, "message" => $content );
			sendMessage($lastupdate, json_encode($message));
			if ($type==-1)
			{
				sleep(10); //give a gratuitous window for client.js to react
				exit(); //and then quit
			}
			$type = 2;
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