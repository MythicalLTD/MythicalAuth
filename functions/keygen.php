<?php 
function generate_key($email,$password) {
    $timestamp = time();
    $formatted_timestamp = date("HisdmY", $timestamp);
    $encoded_timestamp = base64_encode($formatted_timestamp);
    $key = "mythicalsystems_".base64_encode($encoded_timestamp.base64_encode($email).password_hash(base64_encode($password),PASSWORD_DEFAULT).generatePassword(12));
    return $key;
}

function generate_keynoinfo() {
    $timestamp = time();
    $formatted_timestamp = date("HisdmY", $timestamp);
    $key = "mythicalsystems_".base64_encode($formatted_timestamp.generatePassword(12));
    return $key;
}


?>