<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use InesPostventa\InesPostventa;

//Send credentials to the sdk server for authentication.
//$powerPayments = new InesPostventa('postventa@suenolar.com.ar', '1c3b7c04cb1ff3361a632b3131960543b8b37c36fd770ac74e8395702285ff5adf8aa4ded7373a7aed2f8d306757237bb2b96dbae396d2f4b5d64199');
$inesPostventa = new InesPostventa('jperalta.powerbyte@gmail.com', '1c3b7c04cb1ff3361a632b3131960543b8b37c36fd770ac74e8395702285ff5adf8aa4ded7373a7aed2f8d306757237bb2b96dbae396d2f4b5d64199');


$ticket_id = 2;

$tickets = $inesPostventa->tickets();

var_dump($tickets);