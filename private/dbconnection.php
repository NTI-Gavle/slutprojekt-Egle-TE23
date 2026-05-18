<?php

$dbname = 'kvitter_db';
$hostname = 'localhost';
$DB_USER = 'root';
$DB_PASSWORD = 'root';

$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4");

try {
  $dbconn = new PDO(
     "mysql:host=localhost;dbname=kvitter_db;charset=utf8mb4",
    $DB_USER,
    $DB_PASSWORD,
    $options
  );
  $dbconn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo 'Connection failed: ' . $e->getMessage() . "<br />";
}