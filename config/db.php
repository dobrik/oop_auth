<?php
$db = ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'base' => 'left4dead'];

$link = new mysqli($db['host'], $db['user'], $db['pass'], $db['base']);
if($link->connect_error){
    exit('Mysql error: '.$link->connect_error);

}