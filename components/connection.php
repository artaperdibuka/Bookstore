<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Vetëm nëse sesioni nuk është aktiv
}

$db_name = "mysql:host=localhost;dbname=shop_db";
$db_user = "root";
$db_password = "";

$conn = new PDO($db_name, $db_user, $db_password);

function uniqueid()  {
    $chars = '0123456789abcdefghjiklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charLength = strlen($chars);
    $randomString = '';
    for($i = 0; $i < 20; $i++) {
        $randomString .= $chars[mt_rand(0, $charLength - 1)];
    }
    return $randomString;
}

?>