<?php
error_reporting(E_ALL);

use InesPostventa\InesPostventa;

require '../InesPostventa.php';

//Send credentials to the sdk server for authentication.
$powerPayments = new InesPostventa('jperalta.powerbyte@gmail.com', '1c3b7c04cb1ff3361a632b3131960543b8b37c36fd770ac74e8395702285ff5adf8aa4ded7373a7aed2f8d306757237bb2b96dbae396d2f4b5d64199');
$userData = $powerPayments->getUserData();

$ticket_id = 2;

$tickets = $powerPayments->tickets();

$ticket = $powerPayments->ticket($ticket_id);

//var_dump($tickets);
var_dump($ticket);
//Set enviroment sandbox true or false
//$powerPayments->setEnvironment(false);

