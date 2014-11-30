<?php
require_once('inc.cls.cardgame.php');
require_once('../hand_evaluate.php');

//card::$__tostring = create_function('$c', 'return substr($c->suit, 0, 1).\'\'.strtoupper($c->short);'); //Del objHand

echo 'Kortos: H6, C2, C5, SJ, HA, D10, DA<br>';
echo 'String kuris bus paduotas: 36,02,05,111,314,210,214<br>';	
echo '0-3 = C,S,D,H<br>';
echo '2-14 = 2-10,J,Q,K,A<br><br>';

//Step 1: create the dam thing
$Hand = new hand_evaluate("36,02,05,111,314,210,214"); //Grazina class su Score ir Note
//

echo 'Isversta tam kad suprastu cardgame.php<br>';
echo implode(', ', $Hand->Cards).'<br><br>';	//Isvercia i kortu numerius pagal cardgame.php

echo 'Score ir Note:<br>';
//Step 2: use whatever however
echo $Hand->Score.'<br>'.$Hand->Note.'<br><br>';	//Didziausias Score laimes, lygus pasidalins; Note - kokia kombinacija
//


//echo 'cardgame.php kortu shortai pvz(h6):<br>';
//echo implode(', ', $Hand->objHand).'<br><br>';

?>
