<?php
include(__DIR__ . '/../../include/php-csrf.php');
session_start();
$csrf = new CSRF();
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
    if ($csrf->validate('login-form')) {
      $ip_address = getclientip();
      $cf_turnstile_response = $_POST["cf-turnstile-response"];
      $cf_connecting_ip = $ip_address;
      $captcha_success = validate_captcha($cf_turnstile_response, $cf_connecting_ip, $_CONFIG['cf_secret_key']);
      if ($captcha_success) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $upassword = mysqli_real_escape_string($conn, $_POST['password']);
        if (!$email == "" && !$upassword == "") {
          $query = "SELECT * FROM users WHERE email = '".$email."'";
          $result = mysqli_query($conn, $query);
          if ($result) {
            if (mysqli_num_rows($result) == 1) {
              $row = mysqli_fetch_assoc($result);
              $hashedPassword = $row['password'];
              $code = $row['verification_code'];
              if ($code == "") {
                if (password_verify($upassword,$hashedPassword)) {
                  $banned = $row['banned'];
                  if ($banned == "") {
                    $conn->query("UPDATE `users` SET `last_ip` = '".encrypt($ip_address,$ekey)."' WHERE `users`.`id` = ".$row['id'].";");
                    $cookie_name = 'token';
                    $cookie_value = decrypt($row['usertoken'],$ekey);
                    setcookie($cookie_name, $cookie_value, time() + (10 * 365 * 24 * 60 * 60), '/');
                    $conn->close();
                    header('location: /dashboard?s=Welcome to MythicalSystems');
                    die();
                  } else {
                    header("location: /login?e=Sorry, but this account is banned for: ".$banned);
                    $conn->close();
                    die();
                  }
                } else {
                  header("location: /login?e=Sorry, but this is the wrong password");
                  $conn->close();
                  die();
                }
              } else {
                header("location: /login?e=Sorry, but you have to verify your email first");
                  $conn->close();
                  die();
              }               
            } else {
              header("location: /login?e=Sorry, but we can't find this email in our database.");
              $conn->close();
              die();
            }
          } else {
            header("location: /login?e=There was an unexpected error. Please contact support: <code>4o6</code>");
            $conn->close();
            die();
          }
        } else {
          header("location: /login?e=Please fill in all the required information.");
          die();
        }
      } else {
        header("location: /login?e=Captcha verification failed; please refresh!");
        die();
      }
    } else {
      header("location: /login?e=CSRF verification failed; please refresh!");
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
    <?= $_CONFIG['app_name'] ?> - Login
  </title>
  <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&amp;display=swap"
    rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/assets/css/vendors/icofont.css">
  <link rel="stylesheet" type="text/css" href="/assets/css/vendors/themify.css">
  <link rel="stylesheet" type="text/css" href="/assets/css/vendors/flag-icon.css">
  <link rel="stylesheet" type="text/css" href="/assets/css/vendors/feather-icon.css">
  <link rel="stylesheet" type="text/css" href="/assets/css/vendors/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="/assets/css/style.css">
  <link id="color" rel="stylesheet" href="/assets/css/color-1.css" media="screen">
  <link rel="stylesheet" type="text/css" href="/assets/css/responsive.css">
  <link rel="stylesheet" type="text/css" href="/assets/css/preloader.css">
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-6NPWT520BE"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());

    gtag('config', 'G-6NPWT520BE');
  </script>
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<style>
  .dog-image {
    width: 400px;
    /* Adjust the width to your preference */
    height: 300px;
    /* Adjust the height to your preference */
    object-fit: cover;
    /* Controls how the image is scaled within the container */
    image-rendering: crisp-edges;
    /* Improves the image rendering quality */
  }
</style>

<body>
  <div id="preloader" class="discord-preloader">
    <div class="spinner"></div>
  </div>
  <div class="container-fluid">
    <div class="row">
      <div class="col-xl-7 order-1"><img class="bg-img-cover bg-center dog-image" src="https://api.pepsi.xshadow.xyz"
          alt="looginpage"></div>
      <div class="col-xl-5 p-0">
        <div class="login-card">
          <div>
            <div class="login-main">
              <form class="theme-form" method="POST">
                <h4>Sign in to account</h4>
                <p>Enter your email & password to login</p>
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
                  <label class="col-form-label">Email Address</label>
                  <input class="form-control" type="email" required="" name="email" placeholder="nayskutzu@gmail.com">
                </div>
                <div class="form-group">
                  <label class="col-form-label">Password</label>
                  <div class="form-input position-relative">
                    <input class="form-control" type="password" name="password" required="" placeholder="*********">
                  </div>
                </div>
                <div class="form-group mb-0">
                  <div class="checkbox p-0">
                    <input id="checkbox1" type="checkbox">
                    <label class="text-muted" for="checkbox1">Remember password</label>
                  </div><a class="link" href="/forgot-password">Forgot password?</a>
                  <?= $csrf->input('login-form'); ?>
                  <center><br>
                    <div class="cf-turnstile" data-sitekey="<?= $_CONFIG['cf_site_key'] ?>"></div>
                  </center><br>
                  <div class="text-end mt-3">
                    <button class="btn btn-primary btn-block w-100" name="submit" type="submit">Sign in</button>
                  </div>
                </div>
                <p class="mt-4 mb-0 text-center">Don't have account?<a class="ms-2" href="/register">Create
                    Account</a></p>
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