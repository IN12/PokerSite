<?php

require_once('hand_evaluation.php');
require_once('card_deck.php');

include "dbaccess.php";
$params = new Entities();
$dbObj = new Database("pokerdb",'localhost',"root","");
$dealercards = $params->getParam('dealercards')[0]->value;
$playercards = $dbObj->select("SELECT id,sid,hand FROM player WHERE id = 1")[0]->hand;
//Testuojama kortu kolekcija - geriausia kombinacija is 7 kortu
$cards = Array (
    new Card(0, 7, "image"),
    new Card(1, 9, "image"),
    new Card(1, 4, "image"),
    new Card(1, 2, "image"),
    new Card(1, 6, "image"),
    new Card(1, 14, "image"),
    new Card(3, 12, "image")
);

$dcards = json_decode($dealercards);
$pcards = json_decode($playercards);

$dcards[5]=$pcards[0];
$dcards[6]=$pcards[1];
//$cards = $dcards;

var_dump($dbObj->select("SELECT id,sid,hand FROM player WHERE sid <> ''"));
echo '<br><br>';
var_dump($dcards);
echo '<br><br>';
var_dump($pcards);
echo '<br><br>';
var_dump($cards);
echo '<br><br>';

$str = CardDeck::parseHandJSON($dcards);
$hand = new HandEvaluation($str);
echo $hand->Score.'<br>'.$hand->Note."\n";	//Didziausias Score laimes, lygus pasidalins; Note - kokia kombinacija
?>