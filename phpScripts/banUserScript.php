<?php
include_once("DBConnect.php");
$response = $_POST['response'];
$banned = "1";

//update corresponding user in table to be banned from signing in and ban all their content.
$stmt = $mysqli->prepare("UPDATE Users SET Banned = ? WHERE UserID = ?");
$stmt->bind_param('ii', $banned, $response);
$stmt->execute();
$stmt->close();

//update corresponding videos that the user has uploaded to be all banned
$stmt = $mysqli->prepare("UPDATE Videos SET Banned = ? WHERE UserID = ?");
$stmt->bind_param('ii', $banned, $response);
$stmt->execute();
$stmt->close();

//update subscriptions to remove users from accessing banned subcriptions
$stmt = $mysqli->prepare("UPDATE Subscriptions SET Banned = ? WHERE UserSubID = ?");
$stmt->bind_param('ii', $banned, $response);
$stmt->execute();
$stmt->close();


exit();
?>
