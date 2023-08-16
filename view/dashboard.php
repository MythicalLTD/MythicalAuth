<?php
include(__DIR__ . '/requirements/page.php');
include(__DIR__ . '/../include/php-csrf.php');
$csrf = new CSRF();
if (isset($_POST['edit_user'])) {
    $userdb = $conn->query("SELECT * FROM users WHERE usertoken = '" . $_COOKIE['token'] . "'")->fetch_array();
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $avatar = mysqli_real_escape_string($conn, $_POST['avatar']);
    if (!$username == "" || $firstName == "" || $lastName == "" || $email == "" || $avatar == "") {
        if (!$userdb['username'] == $username || !$email == $userdb['email']) {
            $check_query = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
            $result = mysqli_query($conn, $check_query);
            if (mysqli_num_rows($result) > 0) {
                header('location: /dashboard?e=Username or email already exists. Please choose a different one');
                $conn->close();
                die();
            }
        } else {
            $conn->query("UPDATE `users` SET `username` = '" . encrypt($username,$ekey) . "' WHERE `users`.`usertoken` = '" . $_COOKIE['token'] . "';");
            $conn->query("UPDATE `users` SET `first_name` = '" . encrypt($firstName,$ekey) . "' WHERE `users`.`usertoken` = '" . $_COOKIE['token'] . "';");
            $conn->query("UPDATE `users` SET `last_name` = '" . encrypt($lastName,$ekey) . "' WHERE `users`.`usertoken` = '" . $_COOKIE['token'] . "';");
            $conn->query("UPDATE `users` SET `avatar` = '" . $avatar . "' WHERE `users`.`usertoken` = '" . $_COOKIE['token'] . "';");
            $conn->query("UPDATE `users` SET `email` = '" . $email . "' WHERE `users`.`usertoken` = '" . $_COOKIE['token'] . "';");
            $conn->close();
            header('location: /dashboard?s=We updated the user settings in the database');
            die();
        }
    } else {
        header('location: /dashboard?e=Please fill in all the info');
        $conn->close();
        die();
    }
}
else if (isset($_POST['uresetpwd'])) {
    $user_query = "SELECT * FROM users WHERE usertoken = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $_COOKIE['token']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = trim($parts[0]);
            setcookie($name, '', time()-1000);
            setcookie($name, '', time()-1000, '/');
        }
        $skey = generate_keynoinfo();
        $conn->query("INSERT INTO `resetpasswords` (`email`, `usertoken`, `user-resetkeycode`, `ip_address`) VALUES ('" . $userdb['email'] . "', '" . $userdb['usertoken'] . "', '" . $skey . "', '" . decrypt($userdb['last_ip'],$ekey) . "');");
        $conn->close();
        header('location: /reset-password?code='.$skey);
        die();
    } else {
        header('location: /dashboard?e=Cant find this user in the database');
        $conn->close();
        die();
    }
} 
else if (isset($_POST['deleteuser'])) {
    $user_query = "SELECT * FROM users WHERE usertoken = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $_COOKIE['token']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $conn->query("DELETE FROM `users` WHERE `users`.`usertoken` = '".$_COOKIE['token']."';");
        $conn->close();
        header('location: /logout');
        die();
    } else {
        header('location: /dashboard?e=Cant find this user in the database');
        $conn->close();
        die();
    }
}
else if (isset($_POST['resetukey'])) {
    $user_query = "SELECT * FROM users WHERE usertoken = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $_COOKIE['token']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $user_info = $conn->query("SELECT * FROM users WHERE usertoken = '" . $_COOKIE['token'] . "'")->fetch_array();
        $email = $user_info['email'];
        $password = $user_info['password'];
        $skey = generate_key($email,$password);
        $conn->query("UPDATE `users` SET `usertoken` = '" . $skey . "' WHERE `users`.`usertoken` = '" . $_COOKIE['token'] . "';");
        $conn->close();
        header('location: /dashboard?s=We updated the user settings in the database');
        die();
    } else {
        header('location: /dashboard?e=Cant find this user in the database');
        $conn->close();
        die();
    }
}

?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <?php include(__DIR__ . '/requirements/head.php'); ?>
    <title>
        <?= $_CONFIG['app_name'] ?> | Dashboard
    </title>
</head>

<body>
    <div id="preloader" class="discord-preloader">
        <div class="spinner"></div>
    </div>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include(__DIR__ . '/components/sidebar.php') ?>
            <div class="layout-page">
                <?php include(__DIR__ . '/components/navbar.php') ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">User /</span> Edit</h4>
                        <?php include(__DIR__ . '/components/alert.php') ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card mb-4">
                                    <h5 class="card-header">Profile Details</h5>
                                    <!-- Account -->
                                    <div class="card-body">
                                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                                            <img src="<?= $userdb['avatar'] ?>" alt="user-avatar"
                                                class="d-block w-px-100 h-px-100 rounded" id="uploadedAvatar" />
                                        </div>
                                    </div>
                                    <hr class="my-0" />
                                    <div class="card-body">
                                        <form action="/dashboard" method="POST">
                                            <div class="row">
                                                <div class="mb-3 col-md-6">
                                                    <label for="username" class="form-label">Username</label>
                                                    <input class="form-control" type="text" id="username"
                                                        name="username" value="<?= decrypt($userdb['username'],$ekey) ?>"
                                                        placeholder="jhondoe" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="firstName" class="form-label">First Name</label>
                                                    <input class="form-control" type="text" id="firstName"
                                                        name="firstName" value="<?= decrypt($userdb['first_name'],$ekey) ?>"
                                                        autofocus />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="lastName" class="form-label">Last Name</label>
                                                    <input class="form-control" type="text" name="lastName"
                                                        id="lastName" value="<?= decrypt($userdb['last_name'],$ekey) ?>" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="email" class="form-label">E-mail</label>
                                                    <input class="form-control" type="email" id="email" name="email"
                                                        value="<?= $userdb['email'] ?>"
                                                        placeholder="john.doe@example.com" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="avatar" class="form-label">Avatar</label>
                                                    <input class="form-control" type="text" id="avatar" name="avatar"
                                                        value="<?= $userdb['avatar'] ?>" />
                                                </div>

                                                <div class="mb-3 col-md-6">
                                                    <label for="avatar" class="form-label">Secret Key</label><br>
                                                    <button type="button" data-bs-toggle="modal"
                                                        data-bs-target="#viewkey" class="btn btn-primary btn-sm me-2"
                                                        value="true">View secret key</button>
                                                </div>
                                            </div>
    
                                            <div class="mt-2">
                                                <button type="submit" name="edit_user" class="btn btn-primary me-2"
                                                    value="true">Save changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="card">
                                    <h5 class="card-header">Danger Zone</h5>
                                    <div class="card-body">
                                        <div class="mb-3 col-12 mb-0">
                                            <div class="alert alert-warning">
                                                <h5 class="alert-heading mb-1">Make sure you read what the button does!
                                                </h5>
                                                <p class="mb-0">Once you press a button, there is no going back. Please
                                                    be certain.</p>
                                            </div>
                                        </div>
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#resetPwd"
                                            class="btn btn-danger deactivate-account">Reset Password</button>
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#resetKey"
                                            class="btn btn-danger deactivate-account">Reset Secret Key</button>
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#deleteacc"
                                            class="btn btn-danger deactivate-account">Delete Account</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="viewkey" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                            <div class="modal-content p-3 p-md-5">
                                <div class="modal-body">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                    <div class="text-center mb-4">
                                        <h3 class="mb-2">View secret key</h3>
                                        <p class="text-muted">Here is your secret key that can be used to access our
                                            API and this is your login security token, so make sure not to share
                                            it!
                                        </p>
                                        <code><?= $userdb['usertoken'] ?></code>
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                            aria-label="Close">Cancel </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="deleteacc" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                            <div class="modal-content p-3 p-md-5">
                                <div class="modal-body">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                    <div class="text-center mb-4">
                                        <h3 class="mb-2">Delete this user?</h3>
                                        <p class="text-muted">When you choose to delete this user, please be aware that
                                            all associated user data will be permanently wiped. This action is
                                            irreversible, so proceed with caution!
                                        </p>
                                    </div>
                                    <form method="POST" action="/dashboard" class="row g-3">
                                        <div class="col-12 text-center">
                                            <button type="submit" name="deleteuser"
                                                class="btn btn-danger me-sm-3 me-1">Delete user</button>
                                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                                aria-label="Close">Cancel </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="resetKey" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                            <div class="modal-content p-3 p-md-5">
                                <div class="modal-body">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                    <div class="text-center mb-4">
                                        <h3 class="mb-2">Reset user secret key?</h3>
                                        <p class="text-muted">After updating the key, the user will have to login again.
                                        </p>
                                    </div>
                                    <form method="POST" action="/dashboard" class="row g-3">
                                        <div class="col-12 text-center">
                                            <button type="submit" name="resetukey"
                                                class="btn btn-danger me-sm-3 me-1">Reset key</button>
                                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                                aria-label="Close">Cancel </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="resetPwd" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                            <div class="modal-content p-3 p-md-5">
                                <div class="modal-body">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                    <div class="text-center mb-4">
                                        <h3 class="mb-2">Reset user password?</h3>
                                        <p class="text-muted">After updating the password, you will be logged out and redirected to the reset password page!!</p>
                                    </div>
                                    <form method="POST" action="/dashboard" class="row g-3">
                                        <div class="col-12 text-center">
                                                <button type="submit" name="uresetpwd"
                                                class="btn btn-danger me-sm-3 me-1">Reset password</button>
                                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                                aria-label="Close">Cancel </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php include(__DIR__ . '/components/footer.php') ?>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>
    <?php include(__DIR__ . '/requirements/footer.php') ?>
    <!-- Page JS -->
    <script src="<?= $appURL ?>/assets/js/pages-account-settings-account.js"></script>
</body>

</html>