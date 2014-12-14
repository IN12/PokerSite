<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

function sendMsg($id , $msg) {
  echo "id: $id" . PHP_EOL;
  echo "data: {\n";
  echo "data: \"msg\": \"$msg\", \n";
  echo "data: \"id\": $id\n";
  echo "data: }\n";
  echo PHP_EOL;
  ob_flush();
  flush();
}

$maxlifetime = 15;
$startedAt = time();
include "../modules/dbaccess.php";
$dbObj = new Database("pokerdb",'localhost',"root","");
		
while(true)
{
	if ((time() % 10) % 5 == 0)
	{
		$sqlCommand = 'DELETE FROM session
						WHERE TIMESTAMPDIFF(second,session.lastupdate,:time) > :seconds';
		$ddate = new DateTime();
		$data = array (":seconds" => $maxlifetime, ":time" => $ddate->format('Y-m-d H:i:s'));
		$dbObj->executePreparedStatement($sqlCommand, $data);
		
		//die();
	}
	
	/*if ((time() - $startedAt) > 30)
	{
		die();
	}*/
	
	$renewSession=0;
	if ((time()+1 % 10) % 5 == 0)
	{
		$renewSession=1;
	}
	
	//echo "id: ".$startedAt."\n";
	$date = new DateTime();
	$fdate = $date->format('Y-m-d H:i:s');

	$sessions = $dbObj->select("SELECT * FROM session");
	
	/*echo "data: ".$date->format('Y-m-d H:i:s')."\n";
	echo "data: ".$renewSession."\n\n";*/
	echo "id: $startedAt".PHP_EOL;
	echo "data: {".PHP_EOL;
	echo "data: \"test\": \"$fdate\", ".PHP_EOL;
	echo "data: \"renewSession\": $renewSession,".PHP_EOL;
	echo "data: \"sessions\":[".PHP_EOL; 
	
	$commaiterator=0;
	$count = count($sessions);
	foreach ($sessions as $session)
	{
		$commaiterator+=1;
		$sid=$session->sid;
		$lastupdate=$session->lastupdate;
		$ip=$session->ip;
		echo "data: {".PHP_EOL;
		echo "data: \"sid\": \"".$session->sid."\",".PHP_EOL;
		echo "data: \"lastupdate\": \"".$session->lastupdate."\",".PHP_EOL;
		echo "data: \"ip\": \"".$session->ip."\"".PHP_EOL;
		echo "data: }".PHP_EOL;
		if ($commaiterator<$count)
		{
			echo "data: ,".PHP_EOL;
		}
	}
	
	echo "data: ]".PHP_EOL;
	echo "data: }".PHP_EOL;
	echo PHP_EOL;
	ob_flush();
	flush();
	sleep(1);
}
?>