<?php
include(__DIR__.'/../../functions/session.php');
$userdb = $conn->query("SELECT * FROM users WHERE usertoken = '".$_COOKIE['token']. "'")->fetch_array();
?>