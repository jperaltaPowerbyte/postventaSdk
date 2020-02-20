<?php

use PowerPayments\InesPostventa;

require '../PowerPayments.php';

//Send credentials to the sdk server for authentication.
$powerPayments = new InesPostventa('test@user.com', 'JColombres324', 'e99766d6901328420c870b79186eb36a');

//Set enviroment sandbox true or false
$powerPayments->setEnvironment(false);

$powerPayments->setHeader([
    'articles_quantity' => rand(2, 35),
    'total_amount'      => rand(500, 15000),
    'name'              => 'Javier Peralta',
    'dni'               => "35806262",
    'address'           => 'Alem 612 Dpto. 3D',
]);

for ($i = 0; $i < rand(1, 35); $i++) {
    $powerPayments->setDetails([
        'name'     => "Article {$i}",
        'quantity' => rand(1, 15),
        'price'    => rand(15, 7500),
        'options'  => [
            "option{$i}" . rand(1, 500) => "value{$i}" . rand(1, 500),
            "option{$i}" . rand(1, 500) => "value{$i}" . rand(1, 500),
            "option{$i}" . rand(1, 500) => "value{$i}" . rand(1, 500),
            "option{$i}" . rand(1, 500) => "value{$i}" . rand(1, 500),
            "option{$i}" . rand(1, 500) => "value{$i}" . rand(1, 500),
            "option{$i}" . rand(1, 500) => "value{$i}" . rand(1, 500),
        ]
    ]);
}

try {
    $order_id = $powerPayments->storeOrder();

    $powerPayments->paymentFormRedirect($order_id);
} catch (Exception $e) {
    var_dump($e->getMessage());
    var_dump($e->getTrace());
}
