<?php

include '../vendor/autoload.php';

$config  = [
    'host'     => '127.0.0.1',
    'port'     => 6379,
    'database' => 0,
    'password' => '',
];
$client  = new \Delayer\Client($config);
$data    = [
    'orderID' => '2018101712578956648885474',
    'action'  => 'close',
];
$message = new \Delayer\Message([
    'id'    => md5(uniqid(mt_rand(), true)),
    'topic' => 'close_order',
    'body'  => json_encode($data),
]);
$ret     = $client->push($message, 20, 604800);
var_dump($ret);
