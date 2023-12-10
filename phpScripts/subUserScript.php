<?php
include_once('../scripts/DBConnect.php');
header('Content-Type: application/json');
session_start();
$userID = $_POST['response'];
$stmt = $mysqli->prepare("SELECT UserID, UserSubID FROM Subscriptions WHERE UserID = ? AND UserSubID = ?");
$stmt->bind_param('ii', $_SESSION['userID'], $userID);
$stmt->execute();
$stmt->store_result();
$stmt->fetch();
//if results are found then show error message
if($stmt->num_rows >= 1)
{
  $stmt->close();
}
else
{
  $stmt->close();
  $stmt = $mysqli->prepare("INSERT INTO Subscriptions(UserID, UserSubID) VALUES(?, ?)");
  $stmt->bind_param('ii', $_SESSION['userID'], $userID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->close();
}
?>
