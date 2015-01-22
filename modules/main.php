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
include "hand_evaluation.php";
include "card_deck.php";
$deck = new CardDeck(range(0,51), "backImage");
$deck->shuffleDeck(1000);

include "dbaccess.php";
$params = new Entities();
$dbObj = new Database("pokerdb",'localhost',"root","");

function renewLU()
{
	global $params;// = new Entities();
	$date = new DateTime();
	$fdate = $date->format('Y-m-d H:i:s');
	$params->setParam("lastupdate",$fdate);
}

function mergeCards($d_cards,$p_cards)
{
	$d_cards[5]=$p_cards[0];
	$d_cards[6]=$p_cards[1];
	return $d_cards;
}

function subtractFunds($player_id, $sub_funds)
{
	global $dbObj;
	$playerFunds = $dbObj->select("SELECT funds FROM player WHERE id='$player_id'");
	
	if ($playerFunds[0]->funds - $sub_funds < 0) //jei nepakanka pinigu zaidejo saskaitoje, pridedam naujus 5000
	{
		$newFunds = $playerFunds[0]->funds + 5000 - $sub_funds;
		$dbObj->executeSqlCommand("UPDATE player SET funds='$newFunds' WHERE id='$player_id'");
	}
	else //jei saskaitoje pinigu uztektinai, atimame is jos tiek, kiek nurodyta, viska irasome duombazeje
	{
		$newFunds = $playerFunds[0]->funds - $sub_funds;
		$dbObj->executeSqlCommand("UPDATE player SET funds='$newFunds' WHERE id='$player_id'");
	}
}

function raiseBet($player_id, $raise)
{
	$pot = intval($params->getParam('pot')[0]->value);
	$bet = $dbObj->select("SELECT bet FROM player WHERE id='$player_id'")[0]->bet;
	
	$date = new DateTime();
	$lastupdate = $date->format('Y-m-d H:i:s');
	$id_data = array (":id" => $player->id, ":bet" => ($bet+$raise), ":lastupdate" => $lastupdate);
	$sqlCommand = "UPDATE player SET bet = :bet WHERE id = :id";
	$dbObj->executePreparedStatement($sqlCommand, $id_data);
	
	$params->setParam("pot",($pot + $raise));
}

$hand = [];
$dealercards = [];
$playerlist = [];
$pot = 0;
$deckcounter = 0;
$playercount = 0;
$resetdata = json_encode(array( "action" => 0, "confirmed" => 0, "raise" => 0));
$dbObj->executeSqlCommand("UPDATE player SET sid = '', hand = '', eval = '', bet = 0, data = '".$resetdata."', quit = 0"); //justincase reset player sids in db
$params ->setParam("dealercards","");
$params->setParam("stage","0");
$params->setParam("handbrake","0");
$params->setParam("abort","0");
$params->setParam("currentbet","0");
$params->setParam("pot","0");

$maxlifetime = 15; //max session age, seconds

$handbrake = intval($params->getParam('handbrake')[0]->value);
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
		sendMessage(time(),"No clients, waiting");
		$sessions = $dbObj->select("SELECT * FROM session");
		$abort = intval($params->getParam('abort')[0]->value);
			
			if ($abort)
			{
				sendMessage(time(),"=abort=");
				exit();
			}
	}
	
	if ((time() % 10) % 5 == 0) //cleanup old sessions
	{
		$sqlCommand = 'SELECT * FROM session
						WHERE TIMESTAMPDIFF(second,session.lastupdate,:time) > :seconds';
		$ddate = new DateTime();
		$data = array (":seconds" => $maxlifetime, ":time" => $ddate->format('Y-m-d H:i:s'));
		$removedsessions = $dbObj->parameterizedSelect($sqlCommand, $data);
		
		if (!empty($removedsessions))
		{
			foreach ($removedsessions as $removedsession)//the actual removal
			{
				$date = new DateTime();
				$lastupdate = $date->format('Y-m-d H:i:s');
				$fold = json_encode(array( "action" => 2, "confirmed" => 1, "raise" => 0));
				$sid_data = array (":sid" => $removedsession->sid);
				$fold_data = array (":sid" => $removedsession->sid, ":fold" => $fold,  ":lastupdate" => $lastupdate);
				
				$sqlCommand = 'DELETE FROM session
						WHERE sid = :sid';
				$dbObj->executePreparedStatement($sqlCommand, $sid_data);
				
				$sqlCommand = "UPDATE player SET data = :fold, quit = 1, lastupdate = :lastupdate WHERE sid = :sid"; //set leaver to fold and quit if he's a player
				$dbObj->executePreparedStatement($sqlCommand, $fold_data);
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
	if ($timer==0 || $timer % 5 == 0) //test handbrake more often
	{
		$handbrake = intval($params->getParam('handbrake')[0]->value);
		while ($handbrake)//handbrake
		{
			sleep(15);
			sendMessage(time(),"Paused.");
			$handbrake = intval($params->getParam('handbrake')[0]->value);
			$abort = intval($params->getParam('abort')[0]->value);
			
			if ($abort)
			{
				sendMessage(time(),"=abort=");
				exit();
			}
		}
	}
	
	if ($timer==0) //game logic block
	{
		if ($nextstage==1)		//stage advance
		{
			$nextstage = 0;
			if ($stage < 11)	{$stage++;}
			else {$stage = 0;}
			$params->setParam("stage",$stage);
		}
		switch($stage)
		{
			case 0: 			//handle players joining
				$wildsessions = $dbObj->select("SELECT session.sid FROM session WHERE session.sid NOT IN ( SELECT player.sid FROM player)");
				
				foreach ($wildsessions as $wildsession)
				{
					$wsdata = array (":sid" => $wildsession->sid);
				
					$sqlCommand = "UPDATE player SET sid = :sid WHERE sid = '' ORDER BY id ASC LIMIT 1";
					$dbObj->executePreparedStatement($sqlCommand, $wsdata);
				}
				$playercount = intval($dbObj->select("SELECT COUNT(*) AS Num FROM player WHERE sid <> ''")[0]->Num);
				
				if ($playercount >= 2)
				{
					$players = $dbObj->select("SELECT id FROM player WHERE sid <> ''");
					foreach ($players as $player)
					{
						$entrance_fee = intval($params->getParam('entrancefee')[0]->value);
					}
										
					$timer = 8;
					$nextstage = 1;

					renewLU();
				}
				else
				{
					$timer = 8;
				}
				break;
				
			case 1: //deal hands
				$dbObj->executeSqlCommand("UPDATE player SET quit = 0"); //reset quitters
				
				$players = $dbObj->select("SELECT sid FROM player WHERE sid <> ''");
				
				foreach ($players as $player)
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
				
				renewLU();
				
				$timer = 5;//10;
				$nextstage = 1;
				
				break;
				
			case 2: //preflop rotation
				/*$players = $dbObj->select("SELECT id FROM player WHERE sid <> ''");
				
				foreach ($players as $player)
				{
					
				}
				renewLU();*/
				$nextstage = 1;
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
				$nextstage = 1;
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
				$nextstage = 1;
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
				
			case 8: //3rd rotation
				$nextstage = 1;
				break;
				
			case 9://evaluate
				$players = $dbObj->select("SELECT id,sid,hand FROM player WHERE sid <> ''");
				$dcards = $params->getParam('dealercards')[0]->value;
				
				foreach ($players as $player)
				{
					$pcards = $player->hand;
					$ecards = mergeCards(json_decode($dcards),json_decode($pcards));
					$handresult = new HandEvaluation(CardDeck::parseHandJSON($ecards));
					
					$edata = array(":sid" => $player->sid, ":eval"=>json_encode(Array("score" => $handresult->Score, "note" => $handresult->Note)));
					$sqlCommand = "UPDATE player SET eval = :eval WHERE sid = :sid";
					$dbObj->executePreparedStatement($sqlCommand, $edata);
				}
				
				renewLU();
				
				$timer = 10;
				$nextstage = 1;
				break;
				
			case 10: //special quitter stage, gives time for update.php to close client if player.quit set to 1
				renewLU();
				$timer = 5;
				$nextstage = 1;
				break;
				
			case 11: //reset
				//make certain that quitter sessions are removed at this point
				$dbObj->executeSqlCommand("DELETE FROM session WHERE session.sid IN (SELECT sid FROM `player` WHERE quit = 1)");
				//reset all the things
				$hand = [];
				$dealercards = [];
				$params ->setParam("dealercards","");
				$deckcounter = 0;
				$playercount = 0;
				$dbObj->executeSqlCommand("UPDATE player SET sid = '', hand = '', bet = 0, data = '".$resetdata."', quit = 0");
				$sessions = $dbObj->select("SELECT * FROM session");
				$deck->shuffleDeck(1000);
				
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