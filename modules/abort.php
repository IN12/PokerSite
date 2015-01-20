<?php
include "../modules/dbaccess.php";
$params = new Entities();

$abort = intval($params->getParam('abort')[0]->value);

if ($abort)
{
	$params->setParam("abort","0");
}
else
{
	$params->setParam("abort","1");
}
$abort = ($abort) ? 0 : 1; 
echo $abort;
?>