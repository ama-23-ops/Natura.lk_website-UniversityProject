<?php

$host = "localhost"; // database host
$db_name = "natura"; // database name
$username = "root"; // database username
$password = ""; // database password


try {
  $conn = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
  die();
}

?>