<?php 
if (isset($_COOKIE['token'])) {
    if (isset($_GET['url']) && !$_GET['url'] == "")
    {

    } else {
        
    }
} else {
    if (isset($_GET['url']) && !$_GET['url'] == "")
    {
        header('location: '.$appURL.'/login?r='.$appURL.'/authorize?url='.$_GET['url']);
        die();
    } else {
        header('location: /login');
    }
}
?>