<?php
$servername = "localhost";
$username = "root"; 
$password = "";
$dbname = "images";
$ocon = new mysqli($servername, $username, $password, $dbname);
if ($ocon->connect_error) {
    die("Lỗi kết nối: " . $ocon->connect_error);
} else {
    //echo "Kết nối thành công";
}
?>