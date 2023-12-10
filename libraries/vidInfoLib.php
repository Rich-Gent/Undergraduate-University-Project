<?php
  class video
  {
    var $vID;
    var $uID;
    var $vName;
    var $vDescription;
    var $uTD;
    var $vThumbnail;
    var $views;
    var $vLink;
    var $Banned;

    function __construct($vid, $uid, $vname, $vdescription, $utd, $vthumbnail, $views, $vlink, $banned)
    {
      $this->vID = $vid;
      $this->uID = $uid;
      $this->vName = $vname;
      $this->vdescription = $vDescription;
      $this->uTD = $utd;
      $this->vThumbnail = $vthumbnail;
      $this->views = $views;
      $this->vLink = $vlink;
      $this->banned = $banned;
    }
  }



 ?>
