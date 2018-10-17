<?php

include '../vendor/autoload.php';

$config  = [
    'host'     => '127.0.0.1',
    'port'     => 6379,
    'database' => 0,
    'password' => '',
];
$client  = new \Delayer\Client($config);
$message = $client->pop('close_order');
var_dump($message);
