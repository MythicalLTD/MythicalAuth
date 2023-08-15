<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <?php
    include(__DIR__ . '/../components/head.php');
    ?>
    <title>
        <?= $_CONFIG['app_name'] ?> - Forbidden
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
                    <h2 class="headline font-success">403</h2>
                </div>
                <div class="col-md-8 offset-md-2">
                    <p class="sub-content">The server understood the request, but it refuses to authorize it.</p>
                </div>
                <div><a class="btn btn-success-gradien btn-lg" href="/">BACK TO HOME PAGE</a></div>
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

</html>