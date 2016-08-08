<?php
session_start();

include_once 'db.php';
include_once '/../Class.User.php';
$user = new User($link);

if($user->is_logged()){
    echo 'yes!';
}