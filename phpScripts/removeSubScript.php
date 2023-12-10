<?php
include_once('../scripts/DBConnect.php');
header('Content-Type: application/json');
session_start();
//delete the subscription record from the table corresponding to the user and sub chosen
$userID = $_POST['response'];
$stmt = $mysqli->prepare("DELETE FROM Subscriptions WHERE UserID = ? AND UserSubID = ?");
$stmt->bind_param('ii', $_SESSION['userID'], $userID);
$stmt->execute();

?>
