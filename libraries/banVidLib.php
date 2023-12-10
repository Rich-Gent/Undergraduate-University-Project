<?php
  class banVideo
  {
    var $vID;
    var $vName;
    var $vThumbnail;
    var $views;

    function __construct($vid, $vname, $vthumbnail, $Views)
    {
      $this->vID = $vid;
      $this->vName= $vname;
      $this->vThumbnail = $vthumbnail;
      $this->views = $Views;
    }
  }



 ?>
