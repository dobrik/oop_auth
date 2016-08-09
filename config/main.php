<?php
session_start();

include_once 'db.php';

function loadClasses($class)
{
    include_once '/../Classes/Class.'.$class.'.php';
}
spl_autoload_register('loadClasses');

$user = new User($link);

//if($user->register('dobrik', '1234567', '1234567', 'dobrik1990@gmail.com')){
//    echo 'yes!';
//}
$user->activate($_GET['activate'], $_GET['user']);