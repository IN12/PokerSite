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
	global $params;
	$date = new DateTime();
	$fdate = $date->format('Y-m-d H:i:s');
	$params->setParam("lastupdate",$fdate);
}

function renewPLU()
{
	global $dbObj;
	$date = new DateTime();
	$fdate = $date->format('Y-m-d H:i:s');
	$dbObj->executeSqlCommand("UPDATE player SET lastupdate='$fdate'");
}

function mergeCards($d_cards,$p_cards)
{
	$d_cards[5]=$p_cards[0];
	$d_cards[6]=$p_cards[1];
	return $d_cards;
}

function handleAction($action, $player)
{
	global $reactioncounter, $resetdata, $stage;
	global $dbObj, $params;
	$data=json_decode($player->data);
	switch($action)
		{
			case 0:
				$currentbet = intval($params->getParam('currentbet')[0]->value);
				if ($player->bet < $currentbet)
				{
					raiseBet($player->id, ($currentbet - $player->bet)); //call
				}
				break;
			case 1:
				/*$currentbet = intval($params->getParam('currentbet')[0]->value);
				raiseBet($player->id, (($currentbet - $player->bet) + $data->raise)); //call and raise bet*/
				$currentbet = intval($params->getParam('currentbet')[0]->value);
				if ($player->bet < $currentbet)
				{
					raiseBet($player->id, ($currentbet - $player->bet)); //call
				}
				raiseBet($player->id, $data->raise);
				$reactioncounter = 0; //trigger reactions
				$params->setParam("rotationid",$player->id); //set triggerer's id
				break;
			case 2:
				subtractFunds($player->id, $player->bet);
				$dbObj->executeSqlCommand("UPDATE player SET bet = -1 WHERE id = ".$player->id); //fold
				break;
		}
	$confirmdata = json_decode($dbObj->select("SELECT data FROM player WHERE id=".$player->id)[0]->data);
	$confirmdata->confirmed = 0;
	$confirmdata->laststage = $stage;
	$dbObj->executeSqlCommand("UPDATE player SET data = '".json_encode($confirmdata)."' WHERE id = ".$player->id);	
	//$dbObj->executeSqlCommand("UPDATE player SET data = '".$resetdata."' WHERE id = ".$player->id);
}

function handleReaction($action, $player)
{
	global $reactioncounter, $resetdata, $stage;
	global $dbObj, $params;
	$data=json_decode($player->data);
	switch($action)
		{
			case 0:
			case 1: //treat raise as call while reacting
				$currentbet = intval($params->getParam('currentbet')[0]->value);
				if ($player->bet < $currentbet)
				{
					raiseBet($player->id, ($currentbet - $player->bet)); //call
				}
				break;
			case 2:
				subtractFunds($player->id, $player->bet);
				$dbObj->executeSqlCommand("UPDATE player SET bet = -1 WHERE id = ".$player->id); //fold
				break;
		}
	$confirmdata = json_decode($dbObj->select("SELECT data FROM player WHERE id=".$player->id)[0]->data);
	$confirmdata->confirmed = 0;
	$confirmdata->laststage = $stage;
	$dbObj->executeSqlCommand("UPDATE player SET data = '".json_encode($confirmdata)."' WHERE id = ".$player->id);
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

function addFunds($player_id, $add_funds)
{
	global $dbObj;
	$playerFunds = $dbObj->select("SELECT funds FROM player WHERE id='$player_id'")[0]->funds;
	$newFunds = $playerFunds + $add_funds;
	$dbObj->executeSqlCommand("UPDATE player SET funds='$newFunds' WHERE id='$player_id'");
}

function raiseBet($player_id, $raise)
{
	global $params, $dbObj;
	$pot = intval($params->getParam('pot')[0]->value);
	$bet = $dbObj->select("SELECT bet FROM player WHERE id='$player_id'")[0]->bet;
	$currentbet = intval($params->getParam('currentbet')[0]->value);
	$newbet = $bet+$raise;
	
	$date = new DateTime();
	$lastupdate = $date->format('Y-m-d H:i:s');
	$id_data = array (":id" => $player_id, ":bet" => ($newbet), ":lastupdate" => $lastupdate);
	$sqlCommand = "UPDATE player SET bet = :bet, lastupdate = :lastupdate WHERE id = :id";
	$dbObj->executePreparedStatement($sqlCommand, $id_data);
	
	if ($currentbet < $newbet)
	{
		$params->setParam("currentbet",$newbet);
	}
	$params->setParam("pot",($pot + $raise));
}

function handleBetting()
{
	global $params, $dbObj;
	global $nextstage, $timer, $turntimer, $stage;
	global $reactioncounter, $rotationcounter;

	$players = $dbObj->select("SELECT id,bet,data FROM player WHERE sid <> '' AND bet <> -1 ORDER BY id ASC");
	$pcount = count($players);
	if ($pcount<=1)
	{
		if ($stage<8)
		{
			$stage = 8;  //skip to stage 9 and give away pot there
		}
		else
		{
			$nextstage=1;
		}
		return;
	} //not enough players to do any turns
	
	if ($reactioncounter < 0) //proceed with normal rotation
	{
		if ($rotationcounter >= $pcount && $reactioncounter < 0) //all players handled, proceed with next stage
		{
			$rotationcounter = 0;
			$nextstage = 1;
			renewPLU();
			renewLU();
			return;
		}
		for ($i = $rotationcounter; $i < $pcount; $i+=1)
		{
			$data=json_decode($players[$i]->data);
			if ($data->confirmed==1)
			{
				$turntimer = 0; //reset turn timer
				handleAction($data->action, $players[$i]); //and continue	
				
				//check for last-standing after possible folders again
				$test_count = intval($dbObj->select("SELECT COUNT(*) AS Num FROM player WHERE sid <> '' AND bet <> -1")[0]->Num);
				/*if ($test_count<=1)
				{
					if ($stage<8)
					{
						$stage = 9;  //skip to stage 9 and give away pot there
					}
					else
					{
						$nextstage=1;
					}
					return;
				} //not enough players to do any turns*/
				
				if ($reactioncounter>=0) //reactions triggered
				{
					//advance counter
					$rotationcounter = 1+$i; // remember where rotation was, so reaction stops there
					break; // into reactions next iteration
				}
				
				//if this was the last player as well, actually advance the counter
				if ($i >= $pcount-1) 
				{
					$rotationcounter = 1+$i;
					break;
				}
			}
			else //alternatively wait until time runs out
			{
				$timer = 4;
				$turntimer += 1;
				$params->setParam("rotationid",$players[$i]->id); //set turn-taker's id
				if ($turntimer>=4) //out of time
				{
					$timer = 0;
					$turntimer = 0;
					handleAction($data->action, $players[$i]);	
					
					//check for last-standing after possible folders again
					$test_count = intval($dbObj->select("SELECT COUNT(*) AS Num FROM player WHERE sid <> '' AND bet <> -1")[0]->Num);
					/*if ($test_count<=1)
					{
						if ($stage<8)
						{
							$stage = 9;  //skip to stage 9 and give away pot there
						}
						else
						{
							$nextstage=1;
						}
						$stage = 9;  //skip to stage 9 and give away pot there
						return;
					} //not enough players to do any turns*/
					
					if ($reactioncounter>=0) //reactions triggered
					{
						//advance counter
						$rotationcounter = 1+$i; // remember where rotation was, so reaction stops there
						break; // into reactions next iteration
					}
					
					//if this was the last player as well, actually advance the counter
					if ($i >= $pcount-1) 
					{
						$rotationcounter = 1+$i;
						break;
					}
				}
				else
				{
					$rotationcounter = $i; //resume at handled players next time
					break; //until next main loop iteration
				}
			}
		}
	}
	else //do reactions instead
	{
		if ($reactioncounter >= $rotationcounter-1) //all players handled, proceed with next stage
		{
			$reactioncounter = -1;
			$params->setParam("reactionid","-1");
			renewPLU();
			renewLU();
			return;
		}
		for ($j = $reactioncounter; $j < $rotationcounter-1; $j+=1)
		{
			$data=json_decode($players[$j]->data);
			if ($players[$j]->bet < 0) //already folded, skip, just in case
			{
				if ($j < $pcount)
				{
					$j+=1;
				}
				else
				{
					break;
				}
			}
			if ($data->confirmed==1)
			{
				$turntimer = 0; //reset turn timer
				handleReaction($data->action, $players[$j]); //and continue

				//if this was the last player as well, actually advance the counter
				if ($j >= $rotationcounter-2) 
				{
					$reactioncounter = 1+$j;
					break;
				}
			}
			else //alternatively wait until time runs out
			{
				$timer = 4;
				$turntimer += 1;
				$params->setParam("reactionid",$players[$j]->id); //set reaction-taker's id
				if ($turntimer>=4) //out of time
				{
					$timer = 0;
					$turntimer = 0;
					handleReaction($data->action, $players[$j]);
					
					//if this was the last player as well, actually advance the counter
					if ($j >= $rotationcounter-2) 
					{
						$reactioncounter = 1+$j;
						break;
					}
				}
				else
				{
					$reactioncounter = $j; //resume at handled players next time
					break; //until next main loop iteration
				}
			}
		}
	}
}

$hand = [];
$dealercards = [];
$playerlist = [];
$pot = 0;
$deckcounter = 0;
$playercount = 0;
$winners[0] = array( "id" => 0, "score" => 0 );
$resetdata = json_encode(array( "action" => 0, "confirmed" => 0, "raise" => 0, "laststage" => 0 ));
$dbObj->executeSqlCommand("UPDATE player SET sid = '', hand = '', eval = '', bet = 0, data = '".$resetdata."', quit = 0"); //justincase reset player sids in db
$params ->setParam("dealercards","");
$params->setParam("stage","0");
//main.php control
$params->setParam("handbrake","0");
$params->setParam("abort","0");
//pot handling
$params->setParam("currentbet","0");
$params->setParam("pot","0");
$params->setParam("winners",json_encode($winners));
//pertinent to turn-taking
$params->setParam("rotationid","0"); 
$params->setParam("reactionid","-1");
$rotationcounter = 0;
$reactioncounter = -1;

$maxlifetime = 15; //max session age, seconds

$handbrake = intval($params->getParam('handbrake')[0]->value);
$sessions = $dbObj->select("SELECT * FROM session");
//$players = $dbObj->select("SELECT * FROM player");

$timer = 0; //for timing game stages
$turntimer = 0; //for timing individual turn-taking 
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
						raiseBet($player->id, $entrance_fee);
					}
										
					$timer = 8;
					$nextstage = 1;

					renewLU();
				}
				else
				{
					sendMessage(time(),"Not enough players, waiting");
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
				
				renewPLU();
				renewLU();
				
				$timer = 5;//10;
				$nextstage = 1;
				
				break;
				
			case 2: //preflop rotation
				handleBetting();
				renewPLU();
				renewLU();

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
				handleBetting();
				renewPLU();
				renewLU();
				
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
				handleBetting();
				renewPLU();
				renewLU();
				
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
				handleBetting();
				renewPLU();
				renewLU();
				
				break;
				
			case 9://evaluate
				$players = $dbObj->select("SELECT id,sid,hand,bet FROM player WHERE sid <> ''");
				$active_player_count = intval($dbObj->select("SELECT COUNT(*) AS Num FROM player WHERE sid <> '' AND bet <> -1")[0]->Num);
				$dcards = $params->getParam('dealercards')[0]->value;
				$score = 0.0;
				
				if ($active_player_count > 1)
				{
					foreach ($players as $player)
					{
						$pcards = $player->hand;
						$ecards = mergeCards(json_decode($dcards),json_decode($pcards));
						$handresult = new HandEvaluation(CardDeck::parseHandJSON($ecards));
						
						$edata = array(":sid" => $player->sid, ":eval"=>json_encode(Array("score" => $handresult->Score, "note" => $handresult->Note)));
						$sqlCommand = "UPDATE player SET eval = :eval WHERE sid = :sid";
						$dbObj->executePreparedStatement($sqlCommand, $edata);
						
						$resultscore = (float)$handresult->Score;
						
						if ($player->bet>0) //-1 bets indicate folded players
						{
							//create an array of winner id and score
							if ($resultscore == $winners[0]['score'])
							{
								array_push($winners, array( "id" => $player->id, "score" => (float)$handresult->Score ));
							}
							if ($resultscore > $winners[0]['score'])
							{
								$winners = [];
								$winners[0] = array( "id" => $player->id, "score" => (float)$handresult->Score );
							}
							
							subtractFunds($player->id, $player->bet); //also subtract the actual funds and reset bets
							$dbObj->executeSqlCommand("UPDATE player SET bet=0 WHERE id=".$player->id);
						}
					}
				}
				else //only 1 player left, just give him/her the pot
				{
					$winners[0] = array( "id" => $players[0]->id, "score" => -1 );
				}
				
				$params->setParam("winners",json_encode($winners));
				
				$pot = intval($params->getParam('pot')[0]->value);
				foreach ($winners as $winner)
				{
					$split = count($winners);
					addFunds($winner['id'],$pot/$split); 
				}
				
				renewPLU();
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
				$params ->setParam("pot","0");
				$params ->setParam("currentbet","0");
				$params->setParam("rotationid","0"); 
				$params->setParam("reactionid","-1");
				$rotationcounter = 0;
				$reactioncounter = -1;
				$deckcounter = 0;
				$playercount = 0;
				$winners[0] = array( "id" => 0, "score" => 0 );
				$dbObj->executeSqlCommand("UPDATE player SET sid = '', hand = '', bet = 0, data = '".$resetdata."', quit = 0");
				$sessions = $dbObj->select("SELECT * FROM session");
				$deck->shuffleDeck(1000);
				
				renewPLU();
				$timer = 1;
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