<?php
include_once('DBConnect.php');
session_start();
header("Content-Type: application/json");

$videoID = $_POST['vID'];
$userID = $_SESSION['userID'];
//delete specific video that the user has chosen
$stmt = $mysqli->prepare("DELETE FROM Favourites WHERE VideoID = ? AND UserID = ?");
$stmt->bind_param("ii", $videoID, $userID);
$stmt->execute();
$stmt->close();

//send message confirming that the video has been cleared
$message = "video has been removed from your favourites";
print json_encode($message);

?>
