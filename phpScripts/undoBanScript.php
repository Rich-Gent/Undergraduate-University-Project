<?php
//set banned to 0 and retrieve response
include_once("DBConnect.php");
$response = $_POST['response'];
$banned = "0";

//update corresponding banned video in table to viewable again
$stmt = $mysqli->prepare("UPDATE Videos SET Banned = ? WHERE VideoID = ?");
$stmt->bind_param('ii', $banned, $response);
$stmt->execute();
$stmt->close();

exit();
?>
