<?php
class comment
{
  var $uID;
  var $Username;
  var $userImage;
  var $Comment;
  var $Timestamp;

  function __construct($uid, $username, $userimage, $comment, $timestamp)
  {
    $this->uID = $uid;
    $this->username = $username;
    $this->userImage = $userimage;
    $this->comment= $comment;
    $this->time = $timestamp;
  }
}

?>
