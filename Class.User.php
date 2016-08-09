<?php

class User
{
    private $link;

    function __construct($link)
    {
        $this->link = $link;
    }

    private function registerValidate($username, $pass1, $pass2, $email)
    {
        if (stripos(' ', $username) || $pass1 !== $pass2 || stripos(' ', $pass1) || strlen($pass1) < 5 || !preg_match('/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD', $email)) {
            return false;
        } else {
            return true;
        }
    }

    private function sendActivateCode($username, $email)
    {
        $_SESSION['hash'] = md5($username . $email . time());
        $_SESSION['userToActivate'] = $username;
        $subject = 'Активация аккаунта ' . $_SERVER['SERVER_NAME'];
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $link = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . '?activate=' . $_SESSION['hash'] . '&user=' . $_SESSION['userToActivate'];
        $message = <<<EOT
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>
<body>
<h1 style='margin:auto; text-aling:center;'>Почти готово!</h1>
<p>Уважаемый {$username}, ваша регистрация почти завершена, чтоб подтвердить ваш e-mail адресс перейдите по ссылке <a href="{$link}">{$link}</a></p>
</body>
</html>
EOT;
        mail($email, $subject, $message, $headers);
    }

    public function activate($hash, $username)
    {
        if ($_SESSION['hash'] == $hash && $_SESSION['userToActivate'] == $username) {
            $this->link->query("UPDATE users SET active=1 WHERE username='{$username}'");
            unset($_SESSION['hash']);
            return true;
        }
        return false;
    }

    public function loginValidate($username, $password)
    {
        if (stripos(' ', $username) || stripos(' ', $password)) {
            return false;
        } else {
            return true;
        }
    }

    public function register($username, $pass1, $pass2, $email)
    {
        if ($this->registerValidate($username, $pass1, $pass2, $email)) {
            $pass = md5($this->link->real_escape_string($pass1));
            $username = $this->link->real_escape_string($username);
            $ip = $_SERVER['REMOTE_ADDR'];
            $this->link->query("INSERT INTO `users` (`username`, `password`, `email`, `ip`) VALUES ('{$username}', '{$pass}', '{$email}', '{$ip}')");
            if (!$this->link->error) {
                $this->sendActivateCode($username, $email);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function is_active($username)
    {
        $query = $this->link->query("SELECT `active` FROM `users` WHERE username='{$username}'");
        if ($query->fetch_assocc()['active'] == 1) {
            return true;
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
                if ($this->is_active($username)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                session_destroy();
                return false;
            }
        } else {
            return false;
        }
    }

    public function singIn($username, $password)
    {
        if ($this->loginValidate($username, $password)) {
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