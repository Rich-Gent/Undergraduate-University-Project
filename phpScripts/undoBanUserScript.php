<?php
//set banned to 0 and retrieve response
include_once("DBConnect.php");
$response = $_POST['response'];
$banned = "0";

//update user profile to be viewable again
$stmt = $mysqli->prepare("UPDATE Users SET Banned = ? WHERE UserID = ?");
$stmt->bind_param('ii', $banned, $response);
$stmt->execute();
$stmt->close();

//update corresponding banned videos in table to viewable again
$stmt = $mysqli->prepare("UPDATE Videos SET Banned = ? WHERE UserID = ?");
$stmt->bind_param('ii', $banned, $response);
$stmt->execute();
$stmt->close();

//update subscriptions to allow users to access previously banned subcriptions
$stmt = $mysqli->prepare("UPDATE Subscriptions SET Banned = ? WHERE UserSubID = ?");
$stmt->bind_param('ii', $banned, $response);
$stmt->execute();
$stmt->close();

exit();
?>
