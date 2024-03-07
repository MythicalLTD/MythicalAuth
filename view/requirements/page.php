<?php
include(__DIR__.'/../../functions/session.php');
$userdb = $conn->query("SELECT * FROM users WHERE usertoken = '".mysqli_real_escape_string($conn ,$_COOKIE['token']). "'")->fetch_array();
?>