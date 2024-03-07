<?php
use MythicalSystems\CloudFlare\CloudFlare;
use MythicalSystems\CloudFlare\Turnstile;
use MythicalSystems\Utils\CSRFHandler;

session_start();
$csrf = new CSRFHandler();
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['code']) && !empty($_GET['code'])) {
        $mmmcode = mysqli_real_escape_string($conn, $_GET['code']);
        $queryd = "SELECT * FROM `resetpasswords` WHERE `resetpasswords`.`user-resetkeycode` = '".mysqli_real_escape_string($conn, $mmmcode)."';";
        $resultd = mysqli_query($conn, $queryd);
        if (mysqli_num_rows($resultd) > 0) {
            $ucode = mysqli_fetch_assoc($resultd);     
            if (isset($_GET['password'])) {
                if ($csrf->validate('reset-form')) {
                    $upassword = mysqli_real_escape_string($conn, $_GET['password']);
                    $password = password_hash($upassword, PASSWORD_BCRYPT);
                    $updateQuery = "UPDATE `users` SET `password` = '".mysqli_real_escape_string($conn, $password)."' WHERE `users`.`usertoken` = '".mysqli_real_escape_string($conn, $ucode['usertoken'])."'";
                    $deleteQuery = "DELETE FROM resetpasswords WHERE `resetpasswords`.`id` = '".mysqli_real_escape_string($conn, $ucode['id'])."'";                    
                    $conn->query($updateQuery);
                    $conn->query($deleteQuery);
                    $conn->close();
                    
                    header('location: /login');
                    exit();
                } else {
                    header('location: /forgot-password?e=CSRF Verification Failed');
                    exit();
                }
            } else {
                ?>
                <!DOCTYPE html>
                    <html lang="en">

                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                        <meta http-equiv="X-UA-Compatible" content="IE=edge">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <?php
                        include(__DIR__ . '/../components/embed_head.php');
                        ?>
                        <title>
                            <?= $_CONFIG['app_name'] ?> - Reset Password
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
                        <link rel="stylesheet" type="text/css" href="/assets/css/vendors/scrollbar.css">
                        <link rel="stylesheet" type="text/css" href="/assets/css/preloader.css">
                        <link rel="stylesheet" type="text/css" href="/assets/css/vendors/bootstrap.css">
                        <link rel="stylesheet" type="text/css" href="/assets/css/style.css">
                        <link id="color" rel="stylesheet" href="/assets/css/color-1.css" media="screen">
                        <link rel="stylesheet" type="text/css" href="/assets/css/responsive.css">
                        <script async src="https://www.googletagmanager.com/gtag/js?id=G-6NPWT520BE"></script>
                        <script>
                            window.dataLayer = window.dataLayer || [];
                            function gtag() { dataLayer.push(arguments); }
                            gtag('js', new Date());

                            gtag('config', 'G-6NPWT520BE');
                        </script>
                    </head>

                    <body>
                        <div id="preloader" class="discord-preloader">
                            <div class="spinner"></div>
                        </div>
                        <div class="tap-top"><i data-feather="chevrons-up"></i></div>
                        <div class="page-wrapper">
                            <div class="container-fluid p-0">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="login-card">
                                            <div class="login-main">
                                                <form class="theme-form">
                                                    <h6 class="mt-4">Create your new password</h6>
                                                    <div class="form-group">
                                                        <label class="col-form-label">New Password</label>
                                                        <div class="form-input position-relative">
                                                            <input class="form-control" type="password" name="password" required=""
                                                                placeholder="*********">
                                                        </div>
                                                    </div>
                                                    <br>
                                                    <?= $csrf->input('reset-form'); ?>
                                                    <div class="form-group mb-0">
                                                        <button class="btn btn-primary btn-block w-100" type="submit">Done </button>
                                                    </div>
                                                    <input type="hidden" name="code" value="<?= $_GET['code']?>">
                                                    <p class="mt-4 mb-0 text-center">Already have an password?<a class="ms-2"
                                                            href="/login">Sign in</a></p>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script src="/assets/js/jquery-3.5.1.min.js"></script>
                        <script src="/assets/js/bootstrap/bootstrap.bundle.min.js"></script>
                        <script src="/assets/js/icons/feather-icon/feather.min.js"></script>
                        <script src="/assets/js/icons/feather-icon/feather-icon.js"></script>
                        <script src="/assets/js/scrollbar/simplebar.js"></script>
                        <script src="/assets/js/scrollbar/custom.js"></script>
                        <script src="/assets/js/config.js"></script>
                        <script src="/assets/js/sidebar-menu.js"></script>
                        <script src="/assets/js/script.js"></script>
                        <script src="/assets/js/preloader.js"></script>
                    </body>

                    </html>
                <?php
            }
        } 
    } 
}
?>
