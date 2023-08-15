<?php 
/**
 * @var array $_CONFIG
 */
# PLEASE READ ALL THE COMMENTS TO HELP YOU LEARN HOW TO CONFIGURE MythicalAuth
# NO HELP WILL BE PROVIDED IF THE QUESTION CAN BE ANSWERED IN THIS CONFIG FILE.
# ============================================
# Thanks for installing MythicalAuth!
# This is your configuration file. You can learn
# more about what you can do in the documentation.
# 
# This file is included in 90% of the pages. You can access them using the '$_CONFIG' variable. 
#
# <!> This is not the place where you will edit the languages <!>
#

# APP Settings:
$_CONFIG['app_name'] = 'MythicalSystems';
$_CONFIG['app_logo'] = 'https://avatars.githubusercontent.com/u/117385445';
$_CONFIG['app_debug'] = false; # Turn it on only if you know what it does!

# Database:
# We are using this database to store our cache 
$_CONFIG['mysql_host'] = 'localhost';
$_CONFIG['mysql_port'] = '3306';
$_CONFIG['mysql_username'] = 'mythicalsystems';
$_CONFIG['mysql_password'] = '';
$_CONFIG['mysql_database'] = 'mythicalauth';

# CloudFlare Turnstile
$_CONFIG['cf_site_key'] = '00000000000000000000000000';
$_CONFIG['cf_secret_key'] = '0000000000000000000000000000000000';

# Mail Server
$_CONFIG['smtpHost'] = "smtp.domain.com";
$_CONFIG['smtpPort'] = "587";
$_CONFIG['smtpSecure'] = "SSL"; // SSL / TLS
$_CONFIG['smtpUsername'] = "info@domain.com";
$_CONFIG['smtpPassword'] = "pwdIsThisDomainNotHere";
$_CONFIG['smtpFromEmail'] = "info@domain.com";
$_CONFIG['smtpFromName'] = $_CONFIG['app_name'];
#$_CONFIG['smtpFromName'] = "";

# User data:

/*
This encryption key is essential for safeguarding user data within our database. When initiating the installation, it's crucial to establish a robust key. 
After this point, it's imperative not to alter the key; its consistency is mandatory. 
Altering the key subsequent to the storage of user data will render data retrieval impossible, as only the initial key utilized to sign the user data permits its interpretation and presentation. 
Consequently, we strongly advise against modifying this key subsequent to setting up the authentication system.

Please ensure the safekeeping of this key in a secure location and abstain from sharing it with any third parties. It's important to note that Mythical Systems will never request access to this key.
*/
$_CONFIG['app_EncryptionKey'] = ''; # READ UP 
?>