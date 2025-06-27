<?php

$hostname = 'db.be-mons1.bengt.wasmernet.com';
$port = '3306';
$user = 'ec7db74278588000182d570725d8';
$password = '0685ec7d-b742-7949-8000-17757ee943e6';
$database = 'iot_irigasi1'; // You shouldn't use the "root" database. This is just for the example. The recommended way is to create a dedicated database (and user) in PhpMyAdmin and use it then here.

$dsn = "mysql:host=$hostname;port=$port;dbname=$database;charset=utf8mb4";

$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
);

$pdo = new PDO($dsn, $user, $password, $options);

$stm = $pdo->query("SELECT VERSION()");
$version = $stm->fetch();

?>
