<?php
$servername = "127.0.0.1"; // Use IP to bypass socket issues
$username = "root";
$password = "";
$dbname = "event_management";
$port = "3307"; // Add the correct port

try {
  $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo $e->getMessage();
}

?>