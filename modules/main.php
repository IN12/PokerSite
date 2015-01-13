<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

$lock = fopen('lock', 'r+');
if (!flock($lock, LOCK_EX | LOCK_NB))
{
	sendMessage(time(),"Instance already running");
    die("Instance already running");
}
sendMessage(time(),"Script started");

function sendMessage($id , $message)
{
	echo "id: $id" . PHP_EOL;
	echo "data:".$message."\n";
	echo PHP_EOL;
	ob_flush();
	flush();
}

include "card_deck.php";
$deck = new CardDeck(range(0,51), "backImage");
$deck->shuffleDeck(1000);

include "dbaccess.php";
$params = new Entities();
$dbObj = new Database("pokerdb",'localhost',"root","");

function renewLU()
{
	$params = new Entities();
	$date = new DateTime();
	$fdate = $date->format('Y-m-d H:i:s');
	$params->setParam("lastupdate",$fdate);
}


$hand = [];
$dealercards = [];
$deckcounter = 0;
$playercount = 0;
$dbObj->executeSqlCommand("UPDATE player SET sid = '', hand = ''"); //justincase reset player sids in db
$params ->setParam("dealercards","");
$params->setParam("stage","0");

$maxlifetime = 15; //max session age, seconds

$sessions = $dbObj->select("SELECT * FROM session");
//$players = $dbObj->select("SELECT * FROM player");

$timer = 0; //for timing game stages
$stage = 0;
$nextstage = 0;

while(true)
{
	while (empty($sessions)) //wait for clients
	{
		sleep(5);
		sendMessage(time(),"no clients, waiting");
		$sessions = $dbObj->select("SELECT * FROM session");
	}
	
	if ((time() % 10) % 5 == 0) //cleanup old sessions
	{
		//for logging purposes
		//sendMessage(time(),"testing session");
		$sqlCommand = 'SELECT * FROM session
						WHERE TIMESTAMPDIFF(second,session.lastupdate,:time) > :seconds';
		$ddate = new DateTime();
		$data = array (":seconds" => $maxlifetime, ":time" => $ddate->format('Y-m-d H:i:s'));
		$removedsessions = $dbObj->parameterizedSelect($sqlCommand, $data);
		
		if (!empty($removedsessions))
		{
			foreach ($removedsessions as $removedsession)//the actual removal
			{
				$sid_data = array (":sid" => $removedsession->sid);
				
				$sqlCommand = 'DELETE FROM session
						WHERE sid = :sid';
				$dbObj->executePreparedStatement($sqlCommand, $sid_data);
				
				$sqlCommand = "UPDATE player SET sid = ''
						WHERE sid = :sid";
				$dbObj->executePreparedStatement($sqlCommand, $sid_data);
			}
			
			sendMessage(time(),json_encode($removedsessions)." removing session");
			$sessions = $dbObj->select("SELECT * FROM session"); //incidentally that might've been the last online session
		}
	}
	
	/*if ((time() % 60) % 30 == 0)
	{
		for ($i = 0; $i < 5; $i++)
		{
			$dealercards[$i]=array("color" => $deck[$i]->getColor(), "weight" => $deck[$i]->getWeight(), "frontImage" => $deck[$i]->getFrontImage());
		}
		$deck->shuffleDeck(1000);
		$date = new DateTime();
		$fdate = $date->format('Y-m-d H:i:s');
		$params->setParam("lastupdate",$fdate);
		$params->setParam("dealercards",json_encode($dealercards));

		//json_encode($dealercards)
		sendMessage(time(),json_encode($dealercards)." <- inserting to DB");
	}*/
	
	if ($timer==0) //game logic block
	{
		if ($nextstage==1)
		{
			if ($stage < 8)	{$stage++;}
			else {$stage = 0;}
			$params->setParam("stage",$stage);
		}
		switch($stage)
		{
			case 0: //handle players joining
				$wildsessions = $dbObj->select("SELECT session.sid FROM session WHERE session.sid NOT IN ( SELECT player.sid FROM player)");
				
				foreach ($wildsessions as $wildsession)
				{
					$wsdata = array (":sid" => $wildsession->sid);
				
					$sqlCommand = "UPDATE player SET sid = :sid WHERE sid = '' ORDER BY id ASC LIMIT 1";
					$dbObj->executePreparedStatement($sqlCommand, $wsdata);
				}
				$playercount = intval($dbObj->select("SELECT COUNT(*) AS Num FROM player WHERE sid <> ''")[0]->Num);
				
				if ($playercount >= 1)
				{
					$timer = 10;//30;
					$nextstage = 1;

					renewLU();
				}
				else
				{
					$timer = 8;
				}
				break;
				
			case 1: //deal hands
				$players = $dbObj->select("SELECT * FROM player");
				
				foreach ($players as $player)
				{
					if (!empty($player->sid))
					{
						$idx = 51 - $deckcounter;
						$hand[0] = array("color" => $deck[$idx]->getColor(), "weight" => $deck[$idx]->getWeight(), "frontImage" => $deck[$idx]->getFrontImage());
						$deckcounter +=1;
						$idx = 51 - $deckcounter;
						$hand[1] = array("color" => $deck[$idx]->getColor(), "weight" => $deck[$idx]->getWeight(), "frontImage" => $deck[$idx]->getFrontImage());
						$deckcounter +=1;
						
						$phdata = array(":sid" => $player->sid, ":hand"=>json_encode($hand));
						$sqlCommand = "UPDATE player SET hand = :hand
										WHERE sid = :sid";
						$dbObj->executePreparedStatement($sqlCommand, $phdata);
					}
				}
				
				renewLU();
				
				$timer = 5;//10;
				$nextstage = 1;
				
				break;
				
			case 2: //blind rotation
			/*
				renewLU();
			
				$timer = 10;
				$stage = 3;
				$params->setParam("stage",$stage);*/
				break;
				
			case 3: //deal 3
				for ($i = 0; $i < 3; $i++)
				{
					$idx = 51 - $deckcounter;
					$dealercards[$i] = array("color" => $deck[$idx]->getColor(), "weight" => $deck[$idx]->getWeight(), "frontImage" => $deck[$idx]->getFrontImage());
					$deckcounter +=1;
				}
				$params->setParam("dealercards",json_encode($dealercards));
				
				renewLU();
				
				$timer = 5;
				$nextstage = 1;
				break;
				
			case 4: //1st rotation
				break;
				
			case 5: //deal 4th
				$idx = 51 - $deckcounter;
				$dealercards[3] = array("color" => $deck[$idx]->getColor(), "weight" => $deck[$idx]->getWeight(), "frontImage" => $deck[$idx]->getFrontImage());
				$deckcounter +=1;
				$params->setParam("dealercards",json_encode($dealercards));
				
				renewLU();
				
				$timer = 5;
				$nextstage = 1;
				break;
				
			case 6: //2nd rotation
				break;
				
			case 7: //deal 5th
				$idx = 51 - $deckcounter;
				$dealercards[4] = array("color" => $deck[$idx]->getColor(), "weight" => $deck[$idx]->getWeight(), "frontImage" => $deck[$idx]->getFrontImage());
				$deckcounter +=1;
				$params->setParam("dealercards",json_encode($dealercards));
				
				renewLU();
				
				$timer = 5;//20;
				$nextstage = 1;
				break;
				
			case 8: //evaluate
			
				$hand = [];
				$dealercards = [];
				$params ->setParam("dealercards","");
				$deckcounter = 0;
				$playercount = 0;
				$dbObj->executeSqlCommand("UPDATE player SET sid = '', hand = ''");
				$sessions = $dbObj->select("SELECT * FROM session");
				$deck->shuffleDeck(1000);
				
				renewLU();
				
				$timer = 8;
				$nextstage = 1;
				break;
		}
	}
	
	if ($timer>0)
		$timer -= 1;

	sleep(1);
}


//fclose($lock);
?>