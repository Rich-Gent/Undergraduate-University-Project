<?php
include_once("../scripts/DBConnect.php");
session_start();
//retrieve posted information
$flag = $_POST['flag'];
$videoID = $_POST['vID'];
//insert flagged video into flaggedVids table along with the session user id who sent the request
$stmt = $mysqli->prepare("INSERT INTO FlaggedVids(VideoID, UserID, Reason) VALUES(?, ?, ?)");
$stmt->bind_param("iis", $videoID, $_SESSION['userID'], $flag);
$stmt->execute();
$stmt->store_result();
$stmt->close();
//send message back to be printed that will notify the user
$message = "Your complaint has been submitted. This will be reviewed by one of our moderators";
print json_encode($message);
?>
