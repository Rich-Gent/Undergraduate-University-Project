<?php
include_once('DBConnect.php');
session_start();
$userID = $_SESSION['userID'];
//delete all records that match with the user ID
$stmt = $mysqli->prepare("DELETE FROM History WHERE UserID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$stmt->close();

//send message confirming that the history has been cleared
$message = "All history has been cleared";
$_SESSION['message'] = $message;
header("Location: ../scripts/historyPage/?uID=$userID");
exit();
?>
