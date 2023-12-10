<?php
include_once('../scripts/DBConnect.php');
header('Content-Type: application/json');
session_start();
$videoID = $_POST['vID'];
$stmt = $mysqli->prepare("SELECT VideoID, UserID FROM Favourites WHERE VideoID = ? AND UserID = ?");
$stmt->bind_param('ii', $videoID, $_SESSION['userID']);
$stmt->execute();
$stmt->store_result();
$stmt->fetch();
//if more than one result is found then show error message
if($stmt->num_rows >= 1)
{
  $message = "This is already added to your favourites";
  $stmt->close();
}
else
{
  $stmt->close();
  $stmt = $mysqli->prepare("INSERT INTO Favourites(VideoID, UserID) VALUES(?, ?)");
  $stmt->bind_param('ii', $videoID, $_SESSION['userID']);
  $stmt->execute();
  $stmt->store_result();
  $stmt->close();
  $message = "video has been added to your favourites";
}

print json_encode($message);

?>
