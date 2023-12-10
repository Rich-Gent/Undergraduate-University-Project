<?php
class userSearch
{
  var $uID;
  var $Username;
  var $userImage;
  var $lastLogin;
  var $Banned;
  var $uLink;

  function __construct($uid, $username, $userimage, $lastlogin, $banned, $ulink)
  {
    $this->uID = $uid;
    $this->username = $username;
    $this->userImage = $userimage;
    $this->lastLogin = $lastlogin;
    $this->banned = $banned;
    $this->uLink = $ulink;
  }
}
?>
