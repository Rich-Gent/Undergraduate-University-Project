<?php
  include_once 'DBConfig.php';
  $mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
  if ($mysqli->connect_error)
  {
    die("Connection failed: " . $mysqli->connect_error);
  }
?>
