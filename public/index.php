<?php 
try {
    if (file_exists('../vendor/autoload.php')) { 
        require("../vendor/autoload.php");
    } else {
        die('Hello, it looks like you did not run:  "<code>composer install --no-dev --optimize-autoloader</code>". Please run that and refresh the page');
    }
} catch (Exception $e) {
    die('Hello, it looks like you did not run:  <code>composer install --no-dev --optimize-autoloader</code> Please run that and refresh');
}
require("../functions/https.php");
if (!isHTTPS()) {
   die('Hello, it looks like you are trying to access the application over HTTP when the application only runs on HTTPS! Please use HTTPS for the application to run!');
}
$router = new \Router\Router();

$router->add('/', function () {
    require("../include/main.php");
    require("../view/index.php");
});

$router->add('/login', function () {
    require("../include/main.php");
    require("../view/auth/login.php");
});

$router->add('/register', function () {
    require("../include/main.php");
    require("../view/auth/register.php");
});

$router->add('/verify', function () {
    require("../include/main.php");
    require("../view/auth/verify.php");
});

$router->add('/forgot-password', function () {
    require("../include/main.php");
    require("../view/auth/forgot-password.php");
});

$router->add('/reset-password', function () {
    require("../include/main.php");
    require("../view/auth/reset-password.php");
});

$router->add('/e/404', function () {
    require("../include/main.php");
    require("../view/errors/404.php");
});

$router->add('/e/400', function () {
    require("../include/main.php");
    require("../view/errors/400.php");
});

$router->add('/e/401', function () {
    require("../include/main.php");
    require("../view/errors/401.php");
});

$router->add('/e/403', function () {
    require("../include/main.php");
    require("../view/errors/403.php");
});

$router->add('/e/500', function () {
    require("../include/main.php");
    require("../view/errors/500.php");
});

$router->add('/e/503', function () {
    require("../include/main.php");
    require("../view/errors/503.php");
});

$router->add("/(.*)", function () {
    require("../include/main.php");
    require("../view/errors/404.php");
});
$router->route();
?>