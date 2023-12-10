<?php
include_once("../scripts/DBConnect.php");
session_start();
//retrieve posted information
$comment = $_POST['comment'];
$videoID = $_POST['vID'];
//insert comment into the database comment table and then respond with the comment that was posted to this script
$stmt = $mysqli->prepare("INSERT INTO Comments(VideoID, UserID, Comments) VALUES(?, ?, ?)");
$stmt->bind_param("iis", $videoID, $_SESSION['userID'], $comment);
$stmt->execute();
$stmt->store_result();
$stmt->close();
print json_encode($comment);
?>
