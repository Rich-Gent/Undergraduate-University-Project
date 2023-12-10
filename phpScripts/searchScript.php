<?php
include_once("DBConnect.php");
include_once("../libraries/vidInfoLib.php");
include_once("../libraries/userSearchLib.php");
header("Content-Type: application/json");
session_start();
//get search query from url
$key=$_POST['key'];
$key = "%". $key ."%";
//array for the data to display in
$data = array();
//search for videos that match the search query
//if logged in user is an admin then show banned videos
if($_SESSION['accessLevel'] == "1")
{
  $stmt= $mysqli->prepare("SELECT VideoID, UserID, VName, VDescription, UploadTD, Thumbnail, Views, Banned FROM Videos WHERE Tags LIKE ? ORDER BY UploadTD DESC");
  $stmt->bind_param('s', $key);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($tmpVidID, $tmpUserID, $tmpVName, $tmpVDescription, $tmpUploadTD, $tmpThumbnail, $tmpViews, $tmpBanned);
}
else
{
  $banned = "0";
  $stmt= $mysqli->prepare("SELECT VideoID, UserID, VName, VDescription, UploadTD, Thumbnail, Views, Banned FROM Videos WHERE Banned = ? AND Tags LIKE ? ORDER BY UploadTD DESC");
  $stmt->bind_param('is', $banned, $key);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($tmpVidID, $tmpUserID, $tmpVName, $tmpVDescription, $tmpUploadTD, $tmpThumbnail, $tmpViews, $tmpBanned);
}
//if no rows come back then exit the sctipt

//fetch all results
while($stmt->fetch())
{
  $tmpVidLink = "../viewPage?vID=".$tmpVidID;
  $video = new video($tmpVidID, $tmpUserID, $tmpVName, $tmpVDescription, $tmpUploadTD, $tmpThumbnail, $tmpViews, $tmpVidLink, $tmpBanned);
  array_push($data, $video);
}
$stmt->close();

//fetch users for the search to use
//if access level is one then show the banned users
if($_SESSION['accessLevel'] == "1")
{
  $stmt= $mysqli->prepare("SELECT UserID, Username, UserImage, Last_Login, Banned FROM Users WHERE Username LIKE ? ORDER BY Last_Login DESC");
  $stmt->bind_param('s', $key);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($tmpUserID, $tmpUsername, $tmpUserImage, $tmpLogin, $tmpBanned);
}
else
{
  $banned = "0";
  $stmt= $mysqli->prepare("SELECT UserID, Username, UserImage, Last_Login, Banned FROM Users WHERE Banned = ? AND Username LIKE ? ORDER BY Last_Login DESC");
  $stmt->bind_param('is', $banned, $key);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($tmpUserID, $tmpUsername, $tmpUserImage, $tmpLogin, $tmpBanned);
}

//fetch all results
while($stmt->fetch())
{
  $tmpVidLink = "../subPage/?uID=".$tmpUserID;
  $user = new userSearch($tmpUserID, $tmpUsername, $tmpUserImage, $tmpLogin, $tmpBanned, $tmpVidLink);
  array_push($data, $user);
}
$stmt->close();

print json_encode($data);
?>
