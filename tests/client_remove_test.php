<?php

include '../vendor/autoload.php';

$config = [
    'host'     => '127.0.0.1',
    'port'     => 6379,
    'database' => 0,
    'password' => '',
];
$client = new \Delayer\Client($config);
$ret    = $client->remove($argv[1]);
var_dump($ret);
