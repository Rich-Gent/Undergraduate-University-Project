<?php
  class display
  {
    var $uID;
    var $vID;
    var $vName;
    var $vThumbnail;
    var $views;
    var $banned;

    function __construct($uid, $vid, $vname, $vthumbnail, $Views, $Banned)
    {
      $this->uID = $uid;
      $this->vID = $vid;
      $this->vName= $vname;
      $this->vThumbnail = $vthumbnail;
      $this->views = $Views;
      $this->banned = $Banned;
    }
  }



 ?>
