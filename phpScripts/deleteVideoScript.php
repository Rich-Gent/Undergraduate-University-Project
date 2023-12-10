<?php
include_once('DBConnect.php');
session_start();
header("Content-Type: application/json");

$videoID = $_POST['id'];
//delete all instances of this video in database and delete from file host
$stmt = $mysqli->prepare("SELECT VideoFile, Thumbnail, Genre FROM Videos WHERE VideoID = ?");
$stmt->bind_param('i', $videoID);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($videoPath, $thumbnail, $genre);
$stmt->fetch();
$stmt->close();
$videoTarget_dir = "/home/u189228952/public_html/Video/";
$videoFile = str_replace("../../../Video/", "", $videoPath);
$videoDirectory = $videoTarget_dir . $videoFile;
unlink($videoDirectory);

$imageTarget_dir = "/home/u189228952/public_html/Images/VidThumbnails/";
$imageFile = str_replace("../../../Images/VidThumbnails", "", $thumbnail);
if($imageFile !== "Default.png")
{
  $imageDirectory = $imageTarget_dir . $imageFile;
  unlink($imageDirectory);
}

$stmt = $mysqli->prepare("DELETE FROM Videos WHERE VideoID = ?");
$stmt->bind_param("i", $videoID);
$stmt->execute();
$stmt->close();

$stmt = $mysqli->prepare("DELETE FROM FlaggedVids WHERE VideoID = ?");
$stmt->bind_param("i", $videoID);
$stmt->execute();
$stmt->close();

$stmt = $mysqli->prepare("DELETE FROM Favourites WHERE VideoID = ?");
$stmt->bind_param("i", $videoID);
$stmt->execute();
$stmt->close();

$stmt = $mysqli->prepare("DELETE FROM Comments WHERE VideoID = ?");
$stmt->bind_param("i", $videoID);
$stmt->execute();
$stmt->close();

$stmt = $mysqli->prepare("DELETE FROM History WHERE VideoID = ?");
$stmt->bind_param("i", $videoID);
$stmt->execute();
$stmt->close();

//send message confirming that the video has been deleted
$message = "Video has been deleted";
$_SESSION['message'] = $message;

?>
