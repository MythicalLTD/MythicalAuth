<?php 
require(__DIR__.'/../../functions/session.php');
try {
    if (isset($_SERVER['HTTP_COOKIE'])) {
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = trim($parts[0]);
            setcookie($name, '', time()-1000);
            setcookie($name, '', time()-1000, '/');
        }
    }
    header('location: /login');
    die();
} catch (Exception $ex) {
    header('location: /login');
    die();
}


?>