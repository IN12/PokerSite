<?php
include "../modules/dbaccess.php";
$params = new Entities();

$handbrake = intval($params->getParam('handbrake')[0]->value);

if ($handbrake)
{
	$params->setParam("handbrake","0");
}
else
{
	$params->setParam("handbrake","1");
}
$handbrake = ($handbrake) ? 0 : 1;  
echo $handbrake;
?>