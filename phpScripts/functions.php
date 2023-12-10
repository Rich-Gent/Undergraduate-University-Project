<?php
include_once('../../libraries/classLib.php');
include_once('../../libraries/commentLib.php');
include_once('../../libraries/flagUserIDLib.php');
include_once('../../libraries/flagVidInfoLib.php');
include_once('../../libraries/flagVidLib.php');
include_once('../../libraries/userLib.php');
include_once('../../libraries/vidInfoLib.php');
include_once('../../libraries/subLib.php');
//retrieve video thumbnails and infomration corresponding to the function that calls this function
function thumbnails($genre , $tags, MySQLi $mysqli)
{
  $array = array();
  $banned = "0";

  if($genre == "*")
  {
    $stmt = $mysqli->prepare("SELECT VideoID, VName, Thumbnail, Views, Banned FROM Videos WHERE Tags LIKE ? AND Banned = ? ORDER BY UploadTD DESC LIMIT 10");
    $stmt->bind_param("si", $tags, $banned);
    $stmt->execute();
    $stmt->bind_result($tmpVID, $tmpVName, $tmpThumbnail, $tmpViews, $tmpBanned);
  }
  else if($tags == "*")
  {
    $stmt = $mysqli->prepare("SELECT VideoID, VName, Thumbnail, Views, Banned FROM Videos WHERE Genre = ?  AND Banned = ? ORDER BY UploadTD DESC LIMIT 10");
    $stmt->bind_param("si", $genre, $banned);
    $stmt->execute();
    $stmt->bind_result($tmpVID, $tmpVName, $tmpThumbnail, $tmpViews, $tmpBanned);
  }
  else
  {
    $stmt = $mysqli->prepare("SELECT VideoID, VName, Thumbnail, Views, Banned FROM Videos WHERE (Genre = ? OR Tags LIKE ?) AND Banned = ? ORDER BY UploadTD DESC LIMIT 10");
    $stmt->bind_param("ssi", $genre , $tags, $banned);
    $stmt->execute();
    $stmt->bind_result($tmpVID, $tmpVName, $tmpThumbnail, $tmpViews, $tmpBanned);
  }
  while($stmt->fetch())
  {
    $video = new display($uploaderUserID, $tmpVID, $tmpVName, $tmpThumbnail, $tmpViews, $tmpBanned);
    array_push($array, $video);
  }
  $stmt->close();
  return $array;
}

//funcion for finding a users video thumbnails, specific to one user
function userVideoThumbnails($userID , $genre, $tags, MySQLi $mysqli)
{
  $array = array();

  if($genre == "*")
  {
    $stmt = $mysqli->prepare("SELECT VideoID, VName, Thumbnail, Views, Banned FROM Videos WHERE Tags LIKE ? AND UserID = ? ORDER BY UploadTD DESC LIMIT 10");
    $stmt->bind_param("si", $tags, $userID);
    $stmt->execute();
    $stmt->bind_result($tmpVID, $tmpVName, $tmpThumbnail, $tmpViews, $tmpBanned);
  }
  else if($tags == "*")
  {
    $stmt = $mysqli->prepare("SELECT VideoID, VName, Thumbnail, Views, Banned FROM Videos WHERE Genre = ?  AND UserID = ? ORDER BY UploadTD DESC LIMIT 10");
    $stmt->bind_param("si", $genre, $userID);
    $stmt->execute();
    $stmt->bind_result($tmpVID, $tmpVName, $tmpThumbnail, $tmpViews, $tmpBanned);
  }
  else
  {
    $stmt = $mysqli->prepare("SELECT VideoID, VName, Thumbnail, Views, Banned FROM Videos WHERE (Genre = ? OR Tags LIKE ?) AND UserID = ? ORDER BY UploadTD DESC LIMIT 10");
    $stmt->bind_param("ssi", $genre , $tags, $userID);
    $stmt->execute();
    $stmt->bind_result($tmpVID, $tmpVName, $tmpThumbnail, $tmpViews, $tmpBanned);
  }
  while($stmt->fetch())
  {
    $video = new display($userID, $tmpVID, $tmpVName, $tmpThumbnail, $tmpViews, $tmpBanned);
    array_push($array, $video);
  }
  $stmt->close();
  return $array;
}

function displaySubs($userID, MySQLi $mysqli)
{
  $banned = "0";
  $array = array();
  $stmt = $mysqli->prepare("SELECT Users.UserID, Users.Username FROM Subscriptions INNER JOIN Users ON Subscriptions.UserSubID = Users.UserID WHERE Subscriptions.UserID = ? AND Subscriptions.Banned = ?");
  $stmt->bind_param("ii", $userID, $banned);
  $stmt->execute();
  $stmt->bind_result($tmpUID, $tmpUsername);
  while($stmt->fetch())
  {
    $user = new sub($tmpUID, $tmpUsername);
    array_push($array, $user);
  }
  $stmt->close();
  return $array;
}

?>
