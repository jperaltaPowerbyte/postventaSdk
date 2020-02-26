<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use InesPostventa\InesPostventa;

//Send credentials to the sdk server for authentication.
//$powerPayments = new InesPostventa('postventa@suenolar.com.ar', '1c3b7c04cb1ff3361a632b3131960543b8b37c36fd770ac74e8395702285ff5adf8aa4ded7373a7aed2f8d306757237bb2b96dbae396d2f4b5d64199');
$inesPostventa = new InesPostventa('jperalta.powerbyte@gmail.com', '1c3b7c04cb1ff3361a632b3131960543b8b37c36fd770ac74e8395702285ff5adf8aa4ded7373a7aed2f8d306757237bb2b96dbae396d2f4b5d64199');
//$userData = $powerPayments->getUserData();

$ticket_id = 5;
$ticket = $inesPostventa->ticket(5);

/*
 * ALLOWED STATUSES
 * Open	2
 * Pending	3
 * Resolved	4
 * Closed	5
 */

if ($ticket) {
    $updatedTicket = $inesPostventa->updateTicketStatus($ticket['freshdesk_id'], 5); //Return true or false;
    if($updatedTicket){
        echo "Ticket {$ticket_id} actualizado";
    }else{
        echo "Ocurrió un error al actualizar el Ticket {$ticket_id}.";
    }
}


