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
    if ($csrf->validate('reset-form')) {
      $ip_address = getclientip();
      $cf_turnstile_response = $_POST["cf-turnstile-response"];
      $cf_connecting_ip = $ip_address;

      $captcha_success = validate_captcha($cf_turnstile_response, $cf_connecting_ip, $_CONFIG['cf_secret_key']);
      if ($captcha_success) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        if (!$email == "") {
          $query = "SELECT * FROM users WHERE email = '" . $email . "'";
          $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) == 1) {
              $userdbdv = $conn->query("SELECT * FROM users WHERE email = '" . $email . "'")->fetch_array();
              $smtpHost = $_CONFIG['smtpHost'];
              $smtpPort = $_CONFIG['smtpPort'];
              $smtpSecure = $_CONFIG['smtpSecure'];
              $smtpUsername = $_CONFIG['smtpUsername'];
              $smtpPassword = $_CONFIG['smtpPassword'];
              $fromEmail = $_CONFIG['smtpFromEmail'];
              $toEmail = $email;
              $skey = generate_keynoinfo();
              $first_name = decrypt($userdbdv['first_name'], $ekey);
              $last_name = decrypt($userdbdv['last_name'], $ekey);
              $usr_token = $userdbdv['usertoken'];
              $subject = $_CONFIG['smtpFromName'] . " password reset!";
              $message = '
                    <!DOCTYPE html>
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
                        <title>Reset your Password</title>
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
                        <div style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; display: none;">A request to reset
                            password was received from your ' . $_CONFIG["app_name"] . '</div>
                        <div role="article" aria-roledescription="email" aria-label="Reset your Password" lang="en"
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
                                                        <img src="' . $_CONFIG["app_logo"] . '" width="155" alt="' . $_CONFIG["app_name"] . '"
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
                                                                    A request to reset password was received from your
                                                                    <span style="font-weight: 600;">' . $_CONFIG["app_name"] . '</span> Account -
                                                                    <a href="mailto:' . $email . '" class="hover-underline"
                                                                        style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; color: #7367f0; text-decoration: none;">' . $email . '</a>
                                                                     from the IP - <span
                                                                        style="font-weight: 600;">' . $ip_address . '</span> .
                                                                </p>
                                                                <p
                                                                    style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin: 0; margin-bottom: 24px;">
                                                                    Use this link to reset your password and login.</p>
                                                                <a href="' . $appURL . '/reset-password?code=' . $skey . '"
                                                                    style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin-bottom: 24px; display: block; font-size: 16px; line-height: 100%; color: #7367f0; text-decoration: none;">' . $appURL . '/reset-password?code=' . $skey . '</a>
                                                                <table cellpadding="0" cellspacing="0" role="presentation">
                                                                    <tr>
                                                                        <td
                                                                            style="mso-line-height-rule: exactly; mso-padding-alt: 16px 24px; border-radius: 4px; background-color: #7367f0; font-family: Montserrat, -apple-system, Segoe UI, sans-serif;">
                                                                            <a href="' . $appURL . '/reset-password?code=' . $skey . '"
                                                                                style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; display: block; padding-left: 24px; padding-right: 24px; padding-top: 16px; padding-bottom: 16px; font-size: 16px; font-weight: 600; line-height: 100%; color: #ffffff; text-decoration: none;">Reset
                                                                                Password &rarr;</a>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                                <p
                                                                    style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin: 0; margin-top: 24px; margin-bottom: 24px;">
                                                                    <span style="font-weight: 600;">Note:</span> This link is valid for 1
                                                                    hour from the time it was
                                                                    sent to you and can be used to change your password only once.
                                                                </p>
                                                                <p
                                                                    style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin: 0;">
                                                                    If you did not intend to deactivate your account or need our help
                                                                    keeping the account, please
                                                                    contact us at
                                                                    <a href="mailto:' . $fromEmail . '" class="hover-underline"
                                                                        style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; color: #7367f0; text-decoration: none;">' . $fromEmail . '</a>
                                                                </p>
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
                                                                    <a href="mailto:' . $fromEmail . '" class="hover-underline"
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
              $mail = new PHPMailer(true);
              $mail->isSMTP();
              $mail->Host = $smtpHost;
              $mail->Port = $smtpPort;
              $mail->SMTPAuth = true;
              $mail->Username = $smtpUsername;
              $mail->Password = $smtpPassword;
              $mail->SMTPSecure = $smtpSecure;
              $mail->setFrom($fromEmail);
              $mail->addAddress($toEmail);
              $mail->isHTML(true);
              $mail->Subject = $subject;
              $mail->Body = $message;
              try {
                $mail->send();
                $conn->query("INSERT INTO `resetpasswords` (`email`, `usertoken`, `user-resetkeycode`, `ip_address`) VALUES ('" . $email . "', '" . $usr_token . "', '" . $skey . "', '" . $ip_address . "');");
                $conn->close();
                die('<script>window.location.href = "' . $appURL . '/login?s=We sent you a email. Please check your emails.";</script>');
              } catch (Exception $e) {
                $error_message = "Email sending failed. Please try again later.";
                header("location: /auth/forgot-password?error=" . urlencode($error_message));
                die();
              }
              
            } else {
              header("location: /forgot-password?e=We cant find this user in the database!");
              die();
            }
          } else {
            header("location: /forgot-password?e=Please fill in all required information");
            die();
          }
        } else {
          header("location: /forgot-password?e=Captcha verification failed; please refresh!");
          die();
        }
      } else {
        header("location: /forgot-password?e=CSRF verification failed; please refresh!");
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
  <title>
    <?= $_CONFIG['app_name'] ?> - Forgot Password
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
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
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
              <form class="theme-form" method="POST">
                <h6 class="mt-4">Reset Your Password</h6>
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                  if (isset($_GET['e'])) {
                    ?>
                    <div class="alert alert-danger alert-dismissible" role="alert">
                      <?= $_GET['e'] ?>
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php
                  }
                }
                ?>
                <div class="form-group">
                  <label class="col-form-label">Email</label>
                  <input class="form-control" type="email" name="email" required="" placeholder="admin@nayskutzu.xyz">
                </div>
                <?= $csrf->input('reset-form'); ?>
                <center><br>
                  <div class="cf-turnstile" data-sitekey="<?= $_CONFIG['cf_site_key'] ?>"></div>
                </center><br>
                <div class="form-group mb-0">
                  <button class="btn btn-primary btn-block w-100" name="submit" type="submit">Done </button>
                </div>
                <p class="mt-4 mb-0 text-center">Already have an password?<a class="ms-2" href="/login">Sign
                    in</a></p>
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