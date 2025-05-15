
<?php
$host = "localhost";
$user = "root";
$password = "";
$db = "schedule_tracker";

//Main Backup On -E:\VS\Web Development\Sports-Information-For-Dorin.zip



$conn = new mysqli($host, $user, $password, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
