<?php
/*
Del pvz ziurek hand_evaluate/example ir example2
*/

require_once('hand_evaluate/inc.cls.cardgame.php');

class hand_evaluate
{
	public $Score = 0;
	public $Note = "";
	public $Cards = array();
	public $objHand = array();
	
	public function __construct($f_cards){
		$this->Cards = $this->check($f_cards);
		$this->get_score($this->Cards);
	}
	
	function check($f_cards){
		$card = explode("," , $f_cards);
		$cards = array();
		$suit=0;
		$value=0;

		foreach ($card as $value){
			$suit = intval(substr($value, 0, 1));
			$value = intval(substr($value, 1));

			if ($value == 14)
				$value = 0;
			else
				$value --;
			
			array_push($cards,(int)($suit*13 + $value));
		}
		return $cards;
	}
	
	function get_score($f_cards){
		
		foreach ($f_cards as $value)
			array_push($this->objHand, new Card((int)$value));
			
		require_once('hand_evaluate/inc.cls.pokertexasholdem.php');
		$this->Score = pokertexasholdem::score($this->objHand);
		$this->Note = pokertexasholdem::readable_hand($this->Score);
	}
}

?>
