<?php

class User
{
    private $link;

    function __construct($link)
    {
        $this->link = $link;
    }

    private function RegisterValidate($username, $pass1, $pass2, $email)
    {
        if (stripos(' ', $username) || $pass1 !== $pass2 || stripos(' ', $pass1) || strlen($pass1) < 5 || !preg_match('/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD', $email)) {
            return false;
        } else {
            return true;
        }
    }

    public function LoginValidate($username, $password)
    {
        if (stripos(' ', $username) || stripos(' ', $password)) {
            return false;
        } else {
            return true;
        }
    }

    public function register($username, $pass1, $pass2, $email)
    {
        if ($this->RegisterValidate($username, $pass1, $pass2, $email)) {
            $pass = md5($this->link->real_escape_string($pass1));
            $username = $this->link->real_escape_string($username);
            $this->link->query("INSERT INTO users (username, password, email) VALUES ('{$username}', '{$pass}', '{$email}')");
            if (!$this->link->error) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function is_logged()
    {
        if (!empty($_SESSION['username']) && !empty($_SESSION['password']) && $_COOKIE['login']) {
            $username = $_SESSION['username'];
            $password = $_SESSION['password'];
            $query = $this->link->query("SELECT id FROM users WHERE username='{$username}' and password='{$password}'");
            if ($query->num_rows) {
                return true;
            } else {
                session_destroy();
                return false;
            }
        }else{
            return false;
        }
    }

    public function singIn($username, $password)
    {
        if ($this->LoginValidate($username, $password)) {
            $password = md5($this->link->real_escape_string($password));
            $username = $this->link->real_escape_string($username);
            $query = $this->link->query("SELECT id FROM users WHERE username='{$username}' and password='{$password}'");
            if ($query->num_rows) {
                $_SESSION['username'] = $username;
                $_SESSION['password'] = $password;
                setcookie('login', true, time() + 86400);
                return true;
            } else {
                return false;
            }
        }
    }

}