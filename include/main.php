<?php 
require(__DIR__.'/../config.php');
if ($_CONFIG['app_debug'] == true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else if ($_CONFIG['app_debug'] == false) {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
}
$error_500 = '
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        '.$_CONFIG['app_name'].' - Internal Server Error
    </title>
    <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&amp;display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap"
        rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/assets/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/icofont.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/themify.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/flag-icon.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/feather-icon.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/style.css">
    <link id="color" rel="stylesheet" href="/assets/css/color-1.css" media="screen">
    <link rel="stylesheet" type="text/css" href="/assets/css/responsive.css">
</head>

<body>
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <div class="error-wrapper">
            <div class="container"><img class="img-100" src="/assets/images/other-images/sad.png" alt="">
                <div class="error-heading">
                    <h2 class="headline font-primary">500</h2>
                </div>
                <div class="col-md-8 offset-md-2">
                    <p class="sub-content">The server encountered a situation it doesn`t know how to handle.</p>
                </div>
                <div><a class="btn btn-primary-gradien btn-lg" href="/">BACK TO HOME PAGE</a></div>
            </div>
        </div>
    </div>
    <script src="/assets/js/jquery-3.5.1.min.js"></script>
    <script src="/assets/js/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/icons/feather-icon/feather.min.js"></script>
    <script src="/assets/js/icons/feather-icon/feather-icon.js"></script>
    <script src="/assets/js/config.js"></script>
    <script src="/assets/js/script.js"></script>
</body>

</html>';
try {
    $conn = new mysqli($_CONFIG['mysql_host'] . ':' . $_CONFIG['mysql_port'], $_CONFIG['mysql_username'], $_CONFIG['mysql_password'], $_CONFIG['mysql_database']);
    if ($conn->connect_error) {
        die($error_500);
    }
} catch (Exception $ex) {
    die($error_500);
}
$prot = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$svhost = $_SERVER['HTTP_HOST'];
$appURL = $prot . '://' . $svhost;
if ($_CONFIG['app_EncryptionKey'] == "" || $_CONFIG['app_EncryptionKey'] == "1234" || $_CONFIG['app_EncryptionKey'] == "test" || $_CONFIG['app_EncryptionKey'] == "1234")  {
    die('[MythicalAuth] Faild to start MythicalAuth: Please set a strong encryption key in config.php');
} else {
    $ekey = $_CONFIG['app_EncryptionKey'];
}
?>