<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_nursery = "example.com";
$database_nursery = "nursery_example";
$username_nursery = "user";
$password_nursery = "password";
$nursery = mysql_pconnect($hostname_nursery, $username_nursery, $password_nursery) or trigger_error(mysql_error(),E_USER_ERROR); 
?>