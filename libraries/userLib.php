<?php
  class users
  {
    var $uID;
    var $accessLevel;
    var $Username;
    var $userImage;
    var $regSince;
    var $Flagged;
    var $Banned;

    function __construct($uid, $accesslevel, $username, $userimage, $regsince, $flagged, $banned)
    {
      $this->uID = $uid;
      $this->accessLevel = $accesslevel;
      $this->username = $username;
      $this->userImage = $userimage;
      $this->regSince = $regsince;
      $this->flagged = $flagged;
      $this->banned = $banned;
    }
  }
?>
