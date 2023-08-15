<?php
include(__DIR__ . '/../../include/php-csrf.php');
session_start();
$csrf = new CSRF();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function validate_captcha($cf_turnstile_response, $cf_connecting_ip, $cf_secret_key)
{
    $data = array(
        "secret" => $cf_secret_key,
        "response" => $cf_turnstile_response,
        "remoteip" => $cf_connecting_ip
    );

    $url = "https://challenges.cloudflare.com/turnstile/v0/siteverify";

    $options = array(
        "http" => array(
            "header" => "Content-Type: application/x-www-form-urlencoded\r\n",
            "method" => "POST",
            "content" => http_build_query($data)
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result == false) {
        return false;
    }

    $result = json_decode($result, true);

    return $result["success"];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        if ($csrf->validate('register-form')) {
            $ip_address = getclientip();
            $cf_turnstile_response = $_POST["cf-turnstile-response"];
            $cf_connecting_ip = $ip_address;
            $captcha_success = validate_captcha($cf_turnstile_response, $cf_connecting_ip, $_CONFIG['cf_secret_key']);
            if ($captcha_success) {
                $username = mysqli_real_escape_string($conn, $_POST['username']);
                $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
                $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
                $email = mysqli_real_escape_string($conn, $_POST['email']);
                $upassword = mysqli_real_escape_string($conn, $_POST['password']);
                $password = password_hash($upassword, PASSWORD_BCRYPT);
                $code = mysqli_real_escape_string($conn, md5(rand()));
                if (!$username == "" && !$email == "" && !$first_name == "" && !$last_name == "" && !$upassword == "") {
                    $insecure_passwords = array("password", "1234", "qwerty", "letmein", "admin", "pass", "123456789", "dad", "mom", "kek", "12345");
                    if (in_array($upassword, $insecure_passwords)) {
                        header('location: /register?e=Password is not secure. Please choose a different one');
                        die();
                    }
                    $blocked_usernames = array("password", "1234", "qwerty", "letmein", "admin", "pass", "123456789", "dad", "mom", "kek", "fuck", "pussy", "plexed", "badsk", "username");
                    if (in_array($username, $blocked_usernames)) {
                        header('location: /register?e=It looks like we blocked this username from being used. Please choose another username.');
                        die();
                    }
                    if (preg_match("/[^a-zA-Z]+/", $username)) {
                        header('location: /register?e=Please only use characters from <code>A-Z</code> in your username!');
                        die();
                    }
                    if (preg_match("/[^a-zA-Z]+/", $first_name)) {
                        header('location: /register?e=Please only use characters from <code>A-Z</code> in your first name!');
                        die();
                    }
                    if (preg_match("/[^a-zA-Z]+/", $last_name)) {
                        header('location: /register?e=Please only use characters from <code>A-Z</code> in your last name!');
                        die();
                    }
                    if (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE email='" . $email . "'")) > 0) {
                        header("location: /register?e=This username is already in the database.");
                        die();
                    }
                    if (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE username='" . $username . "'")) > 0) {
                        header("location: /register?e=This email is already in the database.");
                        die();
                    } else {
                        $mail = new PHPMailer(true);
                        try {
                            $mail->SMTPDebug = 0;
                            $mail->isSMTP();
                            $mail->Host = $_CONFIG['smtpHost'];
                            $mail->SMTPAuth = true;
                            $mail->Username = $_CONFIG['smtpUsername'];
                            $mail->Password = $_CONFIG['smtpPassword'];
                            $mail->SMTPSecure = $_CONFIG['smtpSecure'];
                            $mail->Port = $_CONFIG['smtpPort'];

                            //Recipients
                            $mail->setFrom($_CONFIG['smtpFromEmail']);
                            $mail->addAddress($email);
                            $dmsg = '<!DOCTYPE html>
                        <html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
                        
                        <head>
                            <meta charset="utf-8">
                            <meta name="x-apple-disable-message-reformatting">
                            <meta http-equiv="x-ua-compatible" content="ie=edge">
                            <meta name="viewport" content="width=device-width, initial-scale=1">
                            <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
                            <!--[if mso]>
                            <xml><o:officedocumentsettings><o:pixelsperinch>96</o:pixelsperinch></o:officedocumentsettings></xml>
                          <![endif]-->
                            <title>Verify your account</title>
                            <link
                                href="https://fonts.googleapis.com/css?family=Montserrat:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700"
                                rel="stylesheet" media="screen">
                            <style>
                                .hover-underline:hover {
                                    text-decoration: underline !important;
                                }
                        
                                @media (max-width: 600px) {
                                    .sm-w-full {
                                        width: 100% !important;
                                    }
                        
                                    .sm-px-24 {
                                        padding-left: 24px !important;
                                        padding-right: 24px !important;
                                    }
                        
                                    .sm-py-32 {
                                        padding-top: 32px !important;
                                        padding-bottom: 32px !important;
                                    }
                                }
                            </style>
                        </head>
                        
                        <body
                            style="margin: 0; width: 100%; padding: 0; word-break: break-word; -webkit-font-smoothing: antialiased; background-color: #eceff1;">
                            <div style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; display: none;">Please verify your email address by clicking the below button and join our creative community on ' . $_CONFIG['app_name'] . '</div>
                            <div role="article" aria-roledescription="email" aria-label="Verify your account" lang="en"
                                style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly;">
                                <table style="width: 100%; font-family: Montserrat, -apple-system, Segoe UI, sans-serif;" cellpadding="0"
                                    cellspacing="0" role="presentation">
                                    <tr>
                                        <td align="center"
                                            style="mso-line-height-rule: exactly; background-color: #eceff1; font-family: Montserrat, -apple-system, Segoe UI, sans-serif;">
                                            <table class="sm-w-full" style="width: 600px;" cellpadding="0" cellspacing="0" role="presentation">
                                                <tr>
                                                    <td class="sm-py-32 sm-px-24"
                                                        style="mso-line-height-rule: exactly; padding: 48px; text-align: center; font-family: Montserrat, -apple-system, Segoe UI, sans-serif;">
                                                        <a href="' . $appURL . '"
                                                            style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly;">
                                                            <img src="' . $_CONFIG['app_logo'] . '" width="155" alt="' . $_CONFIG['app_name'] . '"
                                                                style="max-width: 100%; vertical-align: middle; line-height: 100%; border: 0;">
                                                        </a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="center" class="sm-px-24"
                                                        style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly;">
                                                        <table style="width: 100%;" cellpadding="0" cellspacing="0" role="presentation">
                                                            <tr>
                                                                <td class="sm-px-24"
                                                                    style="mso-line-height-rule: exactly; border-radius: 4px; background-color: #ffffff; padding: 48px; text-align: left; font-family: Montserrat, -apple-system, Segoe UI, sans-serif; font-size: 16px; line-height: 24px; color: #626262;">
                                                                    <p
                                                                        style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin-bottom: 0; font-size: 20px; font-weight: 600;">
                                                                        Hey</p>
                                                                    <p
                                                                        style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin-top: 0; font-size: 24px; font-weight: 700; color: #ff5850;">
                                                                        ' . $first_name . ' ' . $last_name . '!</p>
                                                                    <p
                                                                        style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin: 0; margin-bottom: 24px;">
                                                                        Please verify your email address by clicking the below button
                                                                    </p>
                                                                    <a href="' . $appURL . '/verify?code=' . $code . '"
                                                                        style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin-bottom: 24px; display: block; font-size: 16px; line-height: 100%; color: #7367f0; text-decoration: none;">' . $appURL . '/verify?code=' . $code . '</a>
                                                                    <table cellpadding="0" cellspacing="0" role="presentation">
                                                                        <tr>
                                                                            <td
                                                                                style="mso-line-height-rule: exactly; mso-padding-alt: 16px 24px; border-radius: 4px; background-color: #7367f0; font-family: Montserrat, -apple-system, Segoe UI, sans-serif;">
                                                                                <a href="' . $appURL . '/verify?code=' . $code . '"
                                                                                    style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; display: block; padding-left: 24px; padding-right: 24px; padding-top: 16px; padding-bottom: 16px; font-size: 16px; font-weight: 600; line-height: 100%; color: #ffffff; text-decoration: none;">
                                                                                    Verify Account &rarr;</a>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                    <table style="width: 100%;" cellpadding="0" cellspacing="0"
                                                                        role="presentation">
                                                                        <tr>
                                                                            <td
                                                                                style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; padding-top: 32px; padding-bottom: 32px;">
                                                                                <div
                                                                                    style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; height: 1px; background-color: #eceff1; line-height: 1px;">
                                                                                    &zwnj;</div>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                    <p
                                                                        style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin: 0; margin-bottom: 16px;">
                                                                        Not sure why you received this email? Please
                                                                        <a href="mailto:' . $_CONFIG["smtpFromEmail"] . '" class="hover-underline"
                                                                            style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; color: #7367f0; text-decoration: none;">let
                                                                            us know</a>.
                                                                    </p>
                                                                    <p
                                                                        style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin: 0; margin-bottom: 16px;">
                                                                        Thanks, <br>The ' . $_CONFIG["app_name"] . ' Team</p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td
                                                                    style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; height: 20px;">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td
                                                                    style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; height: 16px;">
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </body>
                        
                        </html>';
                            $mail->isHTML(true);
                            $mail->Subject = 'Verify your ' . $_CONFIG['app_name'] . ' account!';
                            $mail->Body = $dmsg;

                            $mail->send();
                            $u_token = generate_key($email, $upassword);
                            $conn->query("
                      INSERT INTO `users` (
                          `username`, 
                          `email`, 
                          `first_name`, 
                          `last_name`, 
                          `password`, 
                          `usertoken`, 
                          `first_ip`, 
                          `last_ip`, 
                          `verification_code`
                      ) VALUES (
                          '" . encrypt($username,$ekey) . "', 
                          '" . $email . "',
                          '" . encrypt($first_name,$ekey) . "', 
                          '" . encrypt($last_name,$ekey) . "', 
                          '" . $password . "', 
                          '" . $u_token. "', 
                          '" . encrypt($ip_address ,$ekey). "', 
                          '" . encrypt($ip_address,$ekey) . "', 
                          '" . $code . "'
                      );
                      ");
                            $conn->close();
                            $domain = substr(strrchr($email, "@"), 1);
                            $redirections = array('gmail.com' => 'https://mail.google.com', 'yahoo.com' => 'https://mail.yahoo.com', 'hotmail.com' => 'https://outlook.live.com', 'outlook.com' => "https://outlook.live.com", 'gmx.net' => "https://gmx.net", 'icloud.com' => "https://www.icloud.com/mail", 'me.com' => "https://www.icloud.com/mail", 'mac.com' => "https://www.icloud.com/mail", );
                            if (isset($redirections[$domain])) {
                                //header("location: " . $redirections[$domain]);
                                echo '<script>window.location.href = "' . $appURL . '/login?s=We sent you a verification email. Please check your emails.";</script>';
                                die();
                            } else {
                                echo '<script>window.location.href = "' . $appURL . '/login?s=We sent you a verification email. Please check your emails.";</script>';
                                die();
                            }
                        } catch (Exception $e) {
                            die($error_500);
                        }

                    }
                } else {
                    header("location: /register?e=Please fill in all the required information.");
                    die();
                }

            } else {
                header("location: /register?e=Captcha verification failed; please refresh!");
                die();
            }
        } else {
            header("location: /register?e=CSRF verification failed; please refresh!");
            die();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    include(__DIR__ . '/../components/head.php');
    ?>
    <link rel="icon" href="<?= $_CONFIG['app_logo'] ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?= $_CONFIG['app_logo'] ?>" type="image/x-icon">
    <title>
        <?= $_CONFIG['app_name'] ?> | Register
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
    <link rel="stylesheet" type="text/css" href="/assets/css/preloader.css">
    <link id="color" rel="stylesheet" href="/assets/css/color-1.css" media="screen">
    <link rel="stylesheet" type="text/css" href="/assets/css/responsive.css">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-6NPWT520BE"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());

        gtag('config', 'G-6NPWT520BE');
    </script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>

<body>
    <div id="preloader" class="discord-preloader">
        <div class="spinner"></div>
    </div>
    <div class="container-fluid p-0">
        <div class="row m-0">
            <div class="col-xl-7 p-0"><img class="bg-img-cover bg-center" src="https://api.pepsi.xshadow.xyz"
                    alt="looginpage"></div>
            <div class="col-xl-5 p-0">
                <div>
                    <div class="login-card">
                        <div class="login-main">
                            <form method="POST" class="theme-form">
                                <h4>Create your account</h4>
                                <p>Enter your personal details to create account</p>
                                <?php
                                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                                    if (isset($_GET['e'])) {
                                        ?>
                                        <div class="alert alert-danger alert-dismissible" role="alert">
                                            <?= $_GET['e'] ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close"></button>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                                <?php
                                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                                    if (isset($_GET['s'])) {
                                        ?>
                                        <div class="alert alert-success alert-dismissible" role="alert">
                                            <?= $_GET['s'] ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close"></button>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                                <div class="form-group">
                                    <label class="col-form-label pt-0">Your Name</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input class="form-control" name="first_name" ype="text" required=""
                                                placeholder="First name">
                                        </div>
                                        <div class="col-6">
                                            <input class="form-control" name="last_name" ype="text" required=""
                                                placeholder="Last name">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Username</label>
                                    <input class="form-control" type="text" required="" name="username"
                                        placeholder="nayskutzu">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Email Address</label>
                                    <input class="form-control" type="email" required="" name="email"
                                        placeholder="admin@mythicalsystems.tech">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Password</label>
                                    <div class="form-input position-relative">
                                        <input class="form-control" type="password" name="password" required=""
                                            placeholder="*********">
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <div class="checkbox p-0">
                                        <input id="checkbox1" type="checkbox">
                                        <label class="text-muted" for="checkbox1">Agree with<a class="ms-2"
                                                href="#">Privacy
                                                Policy</a></label>
                                    </div>
                                    <?= $csrf->input('register-form'); ?>
                                    <center><br>
                                        <div class="cf-turnstile" data-sitekey="<?= $_CONFIG['cf_site_key'] ?>"></div>
                                    </center><br>
                                    <button class="btn btn-primary btn-block w-100" name="submit" type="submit">Create
                                        Account</button>
                                </div>
                                <p class="mt-4 mb-0 text-center">Already have an account?<a class="ms-2"
                                        href="/login">Sign
                                        in</a></p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/jquery-3.5.1.min.js"></script>
        <script src="/assets/js/bootstrap/bootstrap.bundle.min.js"></script>
        <script src="/assets/js/icons/feather-icon/feather.min.js"></script>
        <script src="/assets/js/icons/feather-icon/feather-icon.js"></script>
        <script src="/assets/js/config.js"></script>
        <script src="/assets/js/script.js"></script>
        <script src="/assets/js/preloader.js"></script>
    </div>
</body>

</html>