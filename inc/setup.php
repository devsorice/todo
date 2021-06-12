<?php
require('config.php');
require('functions.php');
require('google.php');
require('vendor/autoload.php');
session_start();
$router = new AltoRouter();
$connection_url = MONGO_URL;
$client = new MongoDB\Client($connection_url,[],['typeMap' => ['root' => 'array', 'document' => 'array']]);   
$db = $client->{DB_NAME};
