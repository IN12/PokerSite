<?php
class PokerSessionHandler extends SessionHandler
{
    public function destroy ($session_id)
	{
		include "modules/dbaccess.php";

		$dbObj = new Database("pokerdb",'localhost',"root","");

		$sqlCommand = 'DELETE FROM session
						WHERE sid=:sid';
		$data = array (":sid" => $session_id);
		$dbObj->executePreparedStatement($sqlCommand, $data);
		return parent::destroy(session_id);
	}
	
	public function gc ($maxlifetime)
	{
		include "modules/dbaccess.php";

		$dbObj = new Database("pokerdb",'localhost',"root","");

		$sqlCommand = 'DELETE FROM session
						WHERE TIMESTAMPDIFF(second,session.lastupdate,:time) > :seconds';
		$date = new DateTime();
		$data = array (":seconds" => $maxlifetime, ":time" => $date->format('Y-m-d H:i:s'));
		$dbObj->executePreparedStatement($sqlCommand, $data);

		return parent::gc($maxlifetime);
	}
}
?>