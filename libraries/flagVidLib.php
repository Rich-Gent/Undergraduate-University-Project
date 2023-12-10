<?php
  class flag
  {
    var $vID;
    var $uID;
    var $Username;
    var $flagReason;

    function __construct($vid, $uid, $username, $flagreason)
    {
      $this->vID = $vid;
      $this->uID = $uid;
      $this->username = $username;
      $this->flagReason = $flagreason;
    }
  }
?>
