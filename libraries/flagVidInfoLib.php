<?php
class flaggedVideo
{
  var $VideoID;
  var $vName;
  var $vThumbnail;
  var $Views;
  var $Banned;
  var $Flagged;

  function __construct($videoID, $vname, $vthumbnail, $views, $banned, $flagged)
  {
    $this->videoID = $videoID;
    $this->vName = $vname;
    $this->vThumbnail = $vthumbnail;
    $this->views = $views;
    $this->banned = $banned;
    $this->flagged = $flagged;
  }
}

?>
