<?php
$host = "127.0.0.1";
$dbname = "mydb";
$username = "root";  // bedel hada ila 3ndek username akhor
$password = "";      // bedel hada ila 3ndek password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>