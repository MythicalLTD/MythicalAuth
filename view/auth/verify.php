<?php
if (isset($_GET['code']) && !$_GET['code'] == "") {
    $code = mysqli_real_escape_string($conn, $_GET['code']);
    $query = "SELECT * FROM users WHERE verification_code = '".mysqli_real_escape_string($conn, $code)."'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $conn->query("UPDATE `users` SET `verification_code` = NULL WHERE `users`.`id` = ".mysqli_real_escape_string($conn, $row['id']).";");
            $conn->close();
            header('location: /login?s=Email verified. You can log in now.');
            die();
        } else {
            header("location: /login?e=We cant find this code in the database");
            die();
        }
    } else {
        header('location: /login');
        die();
    }
} else {
    header('location: /login');
    die();
}
?>