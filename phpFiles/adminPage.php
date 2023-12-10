<!DOCTYPE html>
<?php
//REMINDER INNER JOIN WILL SAVE YOU HAVING TO RUN COMMENT ARRAY LOOP
  include_once("../../scripts/DBConnect.php");
  include_once("../../libraries/flagVidLib.php");
  include_once("../../libraries/flagUserIDLib.php");
  include_once("../../libraries/flagVidInfoLib.php");
  include_once("../../libraries/userLib.php");
  include_once("../../scripts/functions.php");
  include_once("../../libraries/banVidLib.php");
  session_start();
  //if user is not an administrator then redirect them to home page and send out error message
  if($_SESSION['accessLevel']== 1)
  {
    $username = $_SESSION['username'];
    $userImage = $_SESSION['userImage'];
  }
  else
  {
    $_SESSION['message'] = "You do not have access to this page";
    header("Location: ../homepage");
    exit();
  }

  //create array for flag comments, user who reported it and information related to the video
  $commentArray = array();
  $videoInfo = array();
  $resolved = "0";
  //retrieve all videos that have been flagged within the database
  $stmt = $mysqli->prepare("SELECT FlaggedVids.VideoID, FlaggedVids.UserID, Users.Username, FlaggedVids.Reason FROM FlaggedVids INNER JOIN Users ON FlaggedVids.UserID = Users.UserID WHERE FlaggedVids.Resolved = ? ORDER BY FlaggedVids.VideoID");
  $stmt->bind_param('i', $resolved);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($tmpVID, $tmpUID, $tmpUsername, $tmpReason);
  //store all data within an array with the use of objects that are connected to a php library
  while($stmt->fetch())
  {
    $reason = new flag($tmpVID, $tmpUID, $tmpUsername, $tmpReason);
    array_push($commentArray, $reason);
  }
  $stmt->close();
  //if no results are returned then display message that will show that their are no flagged videos
  if(!isset($reason))
  {
    $flagVidMessage = "There are no flags for any videos";
  }
  //else for each flag retrived run a for each loop
  else
  {
    foreach($commentArray as $val)
    {
      //if a video id equals the previous video id then end the current iteration and start the next
      if($val->vID == $i)
      {
        continue;
      }
      $i= $val->vID;
      //select video infromation that is relavent to the flag and store it in the 3rd object array
      $stmt = $mysqli->prepare("SELECT VideoID, VName, Thumbnail, Views, Banned, Flagged FROM Videos WHERE VideoID = ?");
      $stmt->bind_param('i', $val->vID);
      $stmt->execute();
      $stmt->bind_result($tmpVID, $tmpVName, $tmpThumbnail, $tmpViews, $tmpBanned, $tmpFlagged);
      $stmt->store_result();
      $stmt->fetch();
      $video = new flaggedVideo($tmpVID, $tmpVName, $tmpThumbnail, $tmpViews, $tmpBanned, $tmpFlagged);
      array_push($videoInfo, $video);
      //close statement
      $stmt->close();
    }
  }

  //find requests that users have sent in their settings page so that they can upgrade to upload videos.
  //find all of the requests and the users associated with them
  $upgrade = "1";
  $upgRequests = array();
  $stmt = $mysqli->prepare("SELECT UserID, AccessLevel, Username, UserImage, RegisteredSince, Flagged, Banned  FROM Users WHERE Upgrade = ?");
  $stmt->bind_param('i', $upgrade);
  $stmt->execute();
  $stmt->bind_result($tmpuID, $tmpAL, $tmpUsername, $tmpUImage, $tmpReg, $tmpFlagged, $tmpBanned);
  $stmt->store_result();
  while($stmt->fetch())
  {
    $request = new users($tmpuID, $tmpAL, $tmpUsername, $tmpUImage, $tmpReg, $tmpFlagged, $tmpBanned);
    array_push($upgRequests, $request);
  }
  if(!isset($request))
  {
    $requestMessage = "No new requests at this time";
  }
  //close statement
  $stmt->close();

  //Retrieve videos that have either been ignored or banned for history section
  $resolved = "1";
  //arrays to hold the previous videos information
  $historyArray = array();
  $historyVideoArray = array();
  $stmt = $mysqli->prepare("SELECT FlaggedVids.VideoID, FlaggedVids.UserID, Users.Username, FlaggedVids.Reason FROM FlaggedVids INNER JOIN Users ON FlaggedVids.UserID = Users.UserID WHERE FlaggedVids.Resolved = ? ORDER BY FlaggedVids.VideoID");
  $stmt->bind_param('i', $resolved);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($tmpVID, $tmpUID, $tmpUsername, $tmpReason);
  //store all comments associated with the video
  while($stmt->fetch())
  {
    $reason = new flag($tmpVID, $tmpUID, $tmpUsername, $tmpReason);
    array_push($historyArray, $reason);
  }
  //if no results are returned then display message that will show that their are no flagged videos
  if(!isset($historyArray))
  {
    $historyVidMessage = "There are no vidoes that have been archived yet";
  }
  else
  {
    foreach($historyArray as $val)
    {
      //if a video id equals the previous video id then end the current iteration and start the next
      if($val->vID == $l)
      {
        continue;
      }
      $l= $val->vID;
      //select video infromation that is relavent to the flag and store it in the 3rd object array
      $stmt = $mysqli->prepare("SELECT VideoID, VName, Thumbnail, Views, Banned, Flagged FROM Videos WHERE VideoID = ?");
      $stmt->bind_param('i', $val->vID);
      $stmt->execute();
      $stmt->bind_result($tmpVID, $tmpVName, $tmpThumbnail, $tmpViews, $tmpBanned, $tmpFlagged);
      $stmt->store_result();
      $stmt->fetch();
      $video = new flaggedVideo($tmpVID, $tmpVName, $tmpThumbnail, $tmpViews, $tmpBanned, $tmpFlagged);
      array_push($historyVideoArray, $video);
      //close statement
      $stmt->close();
    }
  }

  //find all videos that have been banned by an administrator
  $banned = "1";
  $adminBanArray = array();
  $stmt = $mysqli->prepare("SELECT VideoID, VName, Thumbnail, Views FROM Videos WHERE Banned = ?");
  $stmt->bind_param('i', $banned);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($tmpVID, $tmpVname, $tmpThumbnail, $tmpViews);
  while($stmt->fetch())
  {
    $banVid = new banVideo($tmpVID, $tmpVname, $tmpThumbnail, $tmpViews);
    array_push($adminBanArray, $banVid);
  }
  if(!isset($banVid))
  {
    $banMessage = "No videos are currently banned";
  }
  $stmt->close();
?>
<html>
<head>
<link href="https://fonts.googleapis.com/css?family=Righteous" rel="stylesheet">
<link rel="stylesheet" href="../../includes/GlobalBrowser.css"/>
<link rel="stylesheet" href="../../includes/adminPage.css"/>
<script>
/* Set the width of the side navigation to 250px and the left margin of the page content to 250px and add a black background color to body */
function openNav()
{
    document.getElementById("TheSideNav").style.width = "250px";
    document.getElementById("container").style.marginLeft = "250px";
    document.getElementById("searchField").style.marginLeft = "250px";
}

/* Set the width of the side navigation to 0 and the left margin of the page content to 0, and the background color of body to white */
function closeNav()
{
    document.getElementById("TheSideNav").style.width = "0";
    document.getElementById("container").style.marginLeft = "0";
    document.getElementById("searchField").style.marginLeft = "0px";
    document.getElementById("searchField").style.display = "none";
}

function openLogin()
{
    if(document.getElementById("LoginForm").style.visibility == "hidden")
    {
      document.getElementById("LoginForm").style.visibility = "visible";
      document.getElementById("LoginForm").style.height = "220px";
      document.getElementById("LoginLink").innerHTML = "Close";
    }
    else
    {
      document.getElementById("LoginForm").style.visibility = "hidden";
      document.getElementById("LoginForm").style.height = "0px";
      document.getElementById("LoginLink").innerHTML = "Login";
    }
}

function openComments(div)
{
  if(document.getElementsByClassName("flagBox")[div].style.visibility == "hidden")
    {
      document.getElementsByClassName("flagBox")[div].style.visibility = "visible";
      document.getElementsByClassName("flagBox")[div].style.height = "450px";
      document.getElementsByClassName("flagThumbnail")[div].style.height = "auto";
      document.getElementsByClassName("flagThumbnail")[div].style.width = "800px";
      document.getElementsByClassName("topContainer")[div].style.margin = "auto";
    }
    else
    {
      document.getElementsByClassName("flagBox")[div].style.visibility = "hidden";
      document.getElementsByClassName("flagBox")[div].style.height = "0px";
      document.getElementsByClassName("flagThumbnail")[div].style.height = "220px";
      document.getElementsByClassName("flagThumbnail")[div].style.width = "210px";
      document.getElementsByClassName("topContainer")[div].style.margin = "";
    }
}

function openDetails(div)
{
  if(document.getElementsByClassName("userDetails")[div].style.visibility == "hidden")
    {
      document.getElementsByClassName("userDetails")[div].style.visibility = "visible";
      document.getElementsByClassName("userDetails")[div].style.height = "170px";
      document.getElementsByClassName("userThumbnail")[div].style.height = "auto";
      document.getElementsByClassName("userThumbnail")[div].style.width = "500px";
      document.getElementsByClassName("topContainer")[div].style.margin = "auto";
    }
    else
    {
      document.getElementsByClassName("userDetails")[div].style.visibility = "hidden";
      document.getElementsByClassName("userDetails")[div].style.height = "0px";
      document.getElementsByClassName("userThumbnail")[div].style.height = "230px";
      document.getElementsByClassName("userThumbnail")[div].style.width = "210px";
      document.getElementsByClassName("topContainer")[div].style.margin = "";
    }
}

function openHistComments(div)
{
  if(document.getElementsByClassName("histFlagBox")[div].style.visibility == "hidden")
    {
      document.getElementsByClassName("histFlagBox")[div].style.visibility = "visible";
      document.getElementsByClassName("histFlagBox")[div].style.height = "450px";
      document.getElementsByClassName("historyThumbnail")[div].style.height = "auto";
      document.getElementsByClassName("historyThumbnail")[div].style.width = "800px";
      document.getElementsByClassName("topContainer")[div].style.margin = "auto";
    }
    else
    {
      document.getElementsByClassName("histFlagBox")[div].style.visibility = "hidden";
      document.getElementsByClassName("histFlagBox")[div].style.height = "0px";
      document.getElementsByClassName("historyThumbnail")[div].style.height = "220px";
      document.getElementsByClassName("historyThumbnail")[div].style.width = "210px";
      document.getElementsByClassName("topContainer")[div].style.margin = "";
    }
}

function openBanComments(div)
{
  if(document.getElementsByClassName("banFlagBox")[div].style.visibility == "hidden")
    {
      document.getElementsByClassName("banFlagBox")[div].style.visibility = "visible";
      document.getElementsByClassName("banFlagBox")[div].style.height = "450px";
      document.getElementsByClassName("banThumbnail")[div].style.height = "auto";
      document.getElementsByClassName("banThumbnail")[div].style.width = "800px";
      document.getElementsByClassName("topContainer")[div].style.margin = "auto";
    }
    else
    {
      document.getElementsByClassName("banFlagBox")[div].style.visibility = "hidden";
      document.getElementsByClassName("banFlagBox")[div].style.height = "0px";
      document.getElementsByClassName("banThumbnail")[div].style.height = "220px";
      document.getElementsByClassName("banThumbnail")[div].style.width = "210px";
      document.getElementsByClassName("topContainer")[div].style.margin = "";
    }
}

function ignoreFunc(btn, div)
{
  var response = btn;
  var formdata = new FormData();
  formdata.append("response", response);
  var ajax = new XMLHttpRequest();
  ajax.open("POST","../../scripts/ignoreScript.php");
  ajax.send(formdata);
  ajax.onreadystatechange = function()
  {
    if (ajax.readyState == 4 && ajax.status == 200)
    {
      document.getElementsByClassName("flagBox")[div].style.visibility = "hidden";
      document.getElementsByClassName("flagBox")[div].style.height = "0px";
      document.getElementsByClassName("views")[div].innerHTML = "<strong>The flagged comments have been ignored</strong>";
      document.getElementsByClassName("views")[div].style.color = "#95B9C7";
      document.getElementsByClassName("flagThumbnail")[div].style.height = "230px";
      document.getElementsByClassName("flagThumbnail")[div].removeAttribute("onclick");
    }
  };
}

function banFunc(btn, name, div)
{
  var response = btn;
  var formdata = new FormData();
  formdata.append("response", response);
  var ajax = new XMLHttpRequest();
  ajax.open("POST","../../scripts/banScript.php");
  ajax.send(formdata);
  ajax.onreadystatechange = function()
  {
    if (ajax.readyState == 4 && ajax.status == 200)
    {
      if(name == "flagThumbnail")
      {
        document.getElementsByClassName("flagBox")[div].style.visibility = "hidden";
        document.getElementsByClassName("flagBox")[div].style.height = "0px";
        document.getElementsByClassName("views")[div].innerHTML = "<strong>The video is now banned</strong>";
        document.getElementsByClassName("views")[div].style.color = "#95B9C7";
        document.getElementsByClassName("flagThumbnail")[div].style.height = "220px";
        document.getElementsByClassName("flagThumbnail")[div].removeAttribute("onclick");
      }
      else if(name == "historyThumbnail")
      {
        document.getElementsByClassName("histFlagBox")[div].style.visibility = "hidden";
        document.getElementsByClassName("histFlagBox")[div].style.height = "0px";
        document.getElementsByClassName("histViews")[div].innerHTML = "<strong>The video is now banned</strong>";
        document.getElementsByClassName("histViews")[div].style.color = "#95B9C7";
        document.getElementsByClassName("historyThumbnail")[div].style.height = "220px";
        document.getElementsByClassName("historyThumbnail")[div].removeAttribute("onclick");
      }
    }
  };
}

function enableUser(btn, div)
{
  var response = btn;
  var formdata = new FormData();
  formdata.append("response", response);
  var ajax = new XMLHttpRequest();
  ajax.open("POST","../../scripts/enableUserScript.php");
  ajax.send(formdata);
  ajax.onreadystatechange = function()
  {
    if (ajax.readyState == 4 && ajax.status == 200)
    {
      document.getElementsByClassName("userDetails")[div].style.visibility = "hidden";
      document.getElementsByClassName("userDetails")[div].style.height = "0px";
      document.getElementsByClassName("regSince")[div].innerHTML = "The user request has been granted";
      document.getElementsByClassName("userThumbnail")[div].style.height = "230px";
      document.getElementsByClassName("userThumbnail")[div].removeAttribute("onclick");
    }
  };
}


function ignoreUser(btn, div)
{
  var response = btn;
  var formdata = new FormData();
  formdata.append("response", response);
  var ajax = new XMLHttpRequest();
  ajax.open("POST","../../scripts/ignoreUserScript.php");
  ajax.send(formdata);
  ajax.onreadystatechange = function()
  {
    if (ajax.readyState == 4 && ajax.status == 200)
    {
      document.getElementsByClassName("userDetails")[div].style.visibility = "hidden";
      document.getElementsByClassName("userDetails")[div].style.height = "0px";
      document.getElementsByClassName("regSince")[div].innerHTML = "The user request has been ignored";
      document.getElementsByClassName("userThumbnail")[div].style.height = "240px";
      document.getElementsByClassName("userThumbnail")[div].removeAttribute("onclick");
    }
  };
}

function undoBan(btn, name, div)
{
  var response = btn;
  var formdata = new FormData();
  formdata.append("response", response);
  var ajax = new XMLHttpRequest();
  ajax.open("POST","../../scripts/undoBanScript.php");
  ajax.send(formdata);
  ajax.onreadystatechange = function()
  {
    if (ajax.readyState == 4 && ajax.status == 200)
    {
      if(name == "historyThumbnail")
      {
        document.getElementsByClassName("histFlagBox")[div].style.visibility = "hidden";
        document.getElementsByClassName("histFlagBox")[div].style.height = "0px";
        document.getElementsByClassName("histViews")[div].innerHTML = "<strong>This video has now been restored</strong>";
        document.getElementsByClassName("histViews")[div].style.color = "#95B9C7";
        document.getElementsByClassName("historyThumbnail")[div].style.height = "230px";
      }
      if(name == "banThumbnail")
      {
        document.getElementsByClassName("banFlagBox")[div].style.visibility = "hidden";
        document.getElementsByClassName("banFlagBox")[div].style.height = "0px";
        document.getElementsByClassName("banViews")[div].innerHTML = "<strong>This video has now been restored</strong>";
        document.getElementsByClassName("banViews")[div].style.color = "#95B9C7";
        document.getElementsByClassName("banThumbnail")[div].style.height = "230px";
      }
    }
  };
}

function enableSearch()
{
  document.getElementById("searchField").style.width = "100%";
  document.getElementById("searchField").style.display = "block";
}

function disableSearch()
{
  if(document.getElementById("search").value == "")
  {
    document.getElementById("searchField").style.width = "0%";
    document.getElementById("searchField").style.display = "none";
  }
}
//search function for video thumbnails
function searchFunction()
{
  //on enter of search box then print title of search field
  document.getElementById("searchField").innerHTML = "<h2>Search</h2>";
  //retrieve value of the search input
  var key =$("#search").val();
  //if search is empty then stop the function
  if(key == "")
  {
    document.getElementById("searchField").innerHTML= "<h2>Search</h2>";
    return;
  }
  //post the search query to the searchScript.php
  var ajax=new XMLHttpRequest();
  var formdata = new FormData();
  formdata.append("key", key);
  ajax.open("POST", "../../scripts/searchScript.php");
  ajax.send(formdata);
  //on success recieve the response from the script
  ajax.onreadystatechange = function()
  {
    if (ajax.readyState == 4 && ajax.status == 200)
    {
        var response = this.responseText;
        var video = JSON.parse(response);
        var link;
        var div;
        //print the videos and the users thumbnails based upon the data passed to it. if no results then print message to user expaining theres no results
        if(video.length != 0)
        {
          div = "<div id='videoSearch'><h2>Videos:</h2>";
          //for length of array print the associated videos
          for(var i = 0; i<video.length; i++)
          {
            if(video[i]['vID'])
            {
              link = video[i]['vLink'];
              div +="<div class='thumbnail' onclick='location.href="+ "\""+ link + "\"" +"'> \n";
              div +="<img alt='VideoImage' class='videoImage' src=" + video[i]['vThumbnail'] + "> \n";
              div +="<div class='videoInfo'> <b>" + video[i]['vName'] + " </b> </div>\n";
              if(video[i]['banned'] == "1")
              {
                div += "<div class='views'>THIS VIDEO IS BANNED</div>\n";
                div += "</div>\n";
              }
              else
              {
                div += "<div class='views'>Views:</b>" + video[i]['views'] + "</b></div> \n";
                div += "</div>\n";
              }
            }
          }
          div += "</div>";
          //for length of array print out users assocaited with the array
          div += "<div id='userSearch'><h2>Users:</h2>";
          for(var i = 0; i<video.length; i++)
          {
            if(video[i]['username'])
            {
              link = video[i]['uLink'];
              div +="<div class='userThumbnail' onclick='location.href="+ "\""+ link + "\"" +"'> \n";
              div +="<img alt='UserImage' class='userImage' src=" + video[i]['userImage'] + "> \n";
              div +="<div class='userInfo'> <b>" + video[i]['username'] + " </b> </div>\n";
              if(video[i]['banned'] == "1")
              {
                div += "<div class='lastLogin'>THIS USER IS BANNED</div>\n";
                div += "</div>\n";
              }
              else
              {
                div += "<div class='lastLogin'>Last Login:</b><br>" + video[i]['lastLogin'] + "</b></div> \n";
                div += "</div>\n";
              }
            }
          }
          div += "</div>";
        }
        else
        {
          div = "<div class='searchMessage'>No results were found</div>";
        }
        //display results within a specified div to hold all of this information
        document.getElementById("searchField").innerHTML+= div;
        //check that the search fields are not empty. If so then return message explaining there are no results.
        checkEmpty();
    }
  };
}

function checkEmpty()
{
  if(document.getElementById("videoSearch").innerHTML === "<h2>Videos:</h2>")
  {
    document.getElementById("videoSearch").innerHTML = "<h2>Videos:</h2><div class='searchMessage'>No results were found</div>"
  }
  if(document.getElementById("userSearch").innerHTML === "<h2>Users:</h2>")
  {
    document.getElementById("userSearch").innerHTML = "<h2>Users:</h2><div class='searchMessage'>No results were found</div>"
  }
}

</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script>

$(document).ready(function()
{
  $("#LoginSubmit").click(function()
  {
    username=$("#UserBox").val();
    password=$("#PassBox").val();
    $.ajax({
         type:"POST",
         url: "../../scripts/loginScript.php",
         data: "username="+username+"&password="+password,
         dataType:"json",
         success:function(data)
         {
           if(data[0]=='true')
           {
              $("#UserText").html(data[2]);
              $('#UserImage').attr('src', data[1]);
              document.getElementById("UserText").style.visibility = "visible";
              document.getElementById("LoginForm").style.visibility = "hidden";
              document.getElementById("LoginForm").style.height = "0px";
              document.getElementById("topBar").innerHTML = "<a href='../../scripts/logoutScript.php' id='logoutLink'>Logout</a>";
              document.getElementById("subArea").innerHTML = "<div id='subTitle'><u>Your Subscriptions</u></div>";
              if(data[4])
              {
                for(var j=0 ; j<data[4].length; j++)
                {
                  document.getElementById("subArea").innerHTML += "<div class='sub' onclick='location.href=\"../subPage?uID=" + data[4][j]['uID'] + "\"'>" + data[4][j]['username'] + "</div>\n";
                }
              }
              else
              {
                document.getElementById("subArea").innerHTML += "<div id='subMessage'><u>You Dont have any subscriptions yet</u></div>";
              }
              if(data[3] == '1')
              {
                document.getElementById("topBar").innerHTML += "<a href='../adminPage' id='adminLink'>Admin Page</a>";
              }
              $("#logMessage").html("");
           }
           else
           {
              document.getElementById("UserBox").style.border = "2px solid #ccc";
              document.getElementById("PassBox").style.border = "2px solid #ccc";
              $("#logMessage").html(data[1]);
              document.getElementById(data[2]).style.border = "2px solid #FF0000";
           }
        }
    });
    return false;
  });
});


</script>
</head>
<body>
  <div id= "header">
    <h1 onclick= "location.href='../homepage'">Flixel</h1>
  </div>
<div id="container">
  <div class="center">
      <!-- Use any element to open the sidenav -->
      <span class="openbtn" onclick="openNav()">
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
      </span>
      <div id="videoArea">
        <h1>Recently reported videos:</h1>
      <?php
      if(isset($flagVidMessage))
      {
        echo $flagVidMessage;
      }
      else
      {
        foreach($videoInfo as $val)
        {
          $j+=0;
             echo "<div class='flagThumbnail' onclick='openComments($j)'>\n";
             echo "<div class='topContainer'><u>Flagged Video</u><br>\n
                     <img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                  </div>
                     <div class='flagBox' style='visibility: hidden; height: 0px;'>Recently flagged comments:\n";
             echo "<div class='thumbList'>\n";
             echo "<div class='thumbFlagTitle'>\n";
             echo "<div class='thumbFlagUser'>User</div>\n";
             echo "<div class='thumbFlagReason'>Report Reason</div>\n";
             echo "</div>\n";
             echo "<div class='list'>\n";
             foreach($historyArray as $key)
             {
               $a+= 0;
               if($a == "0")
               {
                 echo "<div class='historyTitle'><strong><u>Previous flag Comments:</strong></u></div>";
               }
               if($key->vID === $val->videoID)
               {
                 echo "<div class='thumbComment'>\n";
                 echo "<div class='thumbUser'>" . $key->username . "</div> \n";
                 echo "<div class='thumbReason'>" . $key->flagReason . "</div> \n";
                 echo "</div>\n";
               }
               $a++;
             }
            foreach($commentArray as $key)
            {
              $b+= 0;
              if($b == "0")
              {
                echo "<div class='newTitle'><strong><u>New flag Comments:</strong></u></div>";
              }
              if($key->vID === $val->videoID)
              {
                echo "<div class='thumbComment'>\n";
                echo "<div class='thumbUser'>" . $key->username . "</div> \n";
                echo "<div class='thumbReason'>" . $key->flagReason . "</div> \n";
                echo "</div>\n";
              }
              $b++;
            }
            echo "</div>\n";
            echo "</div>\n";
            echo "<div class='buttonContainer'>\n";
            echo "<input class='ignoreSubmit' type='button' value='Ignore' onclick='ignoreFunc($val->videoID, $j)'>\n
                  <input class='banSubmit' type='button' value='Ban Video' onclick='banFunc($val->videoID, \"flagThumbnail\", $j)'>\n
                  <input class='viewButton' type='button' value='View Video' onclick='location.href=\"../viewPage/?vID=$val->videoID\"'>\n";
            echo "</div>\n";
            echo "</div>\n";
            echo "</div>\n";
            $j++;
        }
      }
      ?>
    </div>
    <div id="userRequests">
      <h1>User upgrade requests:</h1>
    <?php
    if(!isset($request))
    {
      echo $requestMessage;
    }
    else
    {
      foreach($upgRequests as $val)
      {
        $h+=0;
        echo "<div class='userThumbnail' onclick='openDetails($h)'>\n";
        echo "<div class='topContainer'><u>Upgrade Request</u><br>\n";
        echo "<img alt='userImage' class='userImage' src=" . $val->userImage  . "> \n
             <div class='username'>Username:".  $val->username . "</div>\n
             <div class='regSince'>Registered Since:<br><b>" . $val->regSince . "</b></div> \n
             </div>\n
             <div class='userDetails' style='visibility: hidden; height: 0px;'>Other Details:<br>\n";
        echo "<div class='details'>";
        echo  "This users access level is " . $val->accessLevel . "<br>";
        if($val->flagged == 1)
        {
          echo "This user has been flagged already <br>";
        }
        else if($val->banned == 1)
        {
          echo "This user has been banned <br>";
        }
        else
        {
          echo "This user has no flags or bans<br>";
        }
        echo "</div>";
        echo "<div class='buttonContainer'>";
        echo "<input class='ignoreSubmit' type='button' value='Ignore' onclick='ignoreUser($val->uID, $h)'>\n
             <input class='enableSubmit' type='button' value='enable uploads' onclick='enableUser($val->uID, $h)'>\n
             <input class='viewButton' type='button' value='View User' onclick='location.href=\"../subPage?uID=$val->uID\"'>\n";
        echo "</div>";
        echo "</div>\n";
        echo "</div>\n";
        $h++;
      }
    }
    ?>
    </div>
    <div id="previousVids">
      <h1>Previous videos:</h1>
    <?php
    if(!isset($historyArray))
    {
      echo $historyVidMessage;
    }
    else
    {
      foreach($historyVideoArray as $val)
      {
        $k+=0;
        if($val->banned == 0)
        {
             echo "<div class='historyThumbnail' onclick='openHistComments($k)'>\n";
             echo "<div class='topContainer'><u>Ignored Video</u><br>\n
                      <img alt='VideoImage' class='histVideoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='histVideoInfo'>".  $val->vName . "</div>\n
                     <div class='histViews'>Views:<b>" . $val->views . "</b></div> \n
                  </div>\n
                     <div class='histFlagBox' style='visibility: hidden; height: 0px;'>Flaged for these reasons:\n";
             echo "<div class='histList'>\n";
             echo "<div class='histFlagTitle'>\n";
             echo "<div class='histFlagUser'>User</div>\n";
             echo "<div class='histFlagReason'>Report Reason</div>\n";
             echo "</div>\n";
             echo "<div class='list'>\n";
            foreach ($historyArray as $key)
            {
              if($key->vID === $val->videoID)
              {
                echo "<div class='histComment'>\n";
                echo "<div class='histUser'>" . $key->username . "</div> \n";
                echo "<div class='histReason'>" . $key->flagReason . "</div> \n";
                echo "</div>\n";
              }
            }
            echo "</div>\n";
            echo "</div>\n";
            echo "<div class='buttonContainer'>\n";
            echo "<input class='banSubmit' type='button' value='Ban Video' onclick='banFunc($val->videoID, \"historyThumbnail\", $k)'>\n
                  <input class='viewButton' type='button' value='View Video' onclick='location.href=\"../viewPage/?vID=$val->videoID\"'>\n";
            echo "</div>\n";
            echo "</div>\n";
            echo "</div>\n";
            $k++;
        }

      }
    }
    ?>
    </div>
    <div id="videoBanArea">
      <h1>Admin Banned Videos:</h1>
    <?php
    if(isset($banMessage))
    {
      echo $banMessage;
    }
    else
    {
      foreach($adminBanArray as $val)
      {
        $m+=0;
           echo "<div class='banThumbnail' onclick='openBanComments($m)'>\n";
           echo "<div class='topContainer'><u>Banned Video</u><br>\n
                   <img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                   <div class='videoInfo'>".  $val->vName . "</div>\n
                   <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                </div>
                   <div class='banFlagBox' style='visibility: hidden; height: 0px;'>Recently flagged comments (if any are present):\n";
           echo "<div class='banList'>\n";
           echo "<div class='banFlagTitle'>\n";
           echo "<div class='banFlagUser'>User</div>\n";
           echo "<div class='banFlagReason'>Report Reason</div>\n";
           echo "</div>\n";
           echo "<div class='list'>\n";
           foreach($historyArray as $key)
           {
             if($key->vID === $val->vID)
             {
               echo "<div class='banComment'>\n";
               echo "<div class='banUser'>" . $key->username . "</div> \n";
               echo "<div class='banReason'>" . $key->flagReason . "</div> \n";
               echo "</div>\n";
             }
           }
          foreach($commentArray as $key)
          {
            if($key->vID === $val->vID)
            {
              echo "<div class='banComment'>\n";
              echo "<div class='banUser'>" . $key->username . "</div> \n";
              echo "<div class='banReason'>" . $key->flagReason . "</div> \n";
              echo "</div>\n";
            }
          }
          echo "</div>\n";
          echo "</div>\n";
          echo "<div class='buttonContainer'>\n";
          echo "<input class='undoBanSubmit' type='button' value='Undo Ban' onclick='undoBan($val->vID, \"banThumbnail\", $m)'>\n
                <input class='viewButton' type='button' value='View Video' onclick='location.href=\"../viewPage/?vID=$val->vID\"'>\n";
          echo "</div>\n";
          echo "</div>\n";
          echo "</div>\n";
          $m++;
      }
    }
    ?>
    <div id="searchField">
        <h2>Search</h2>
    </div>
  </div>
  <div id="TheSideNav" class="SideNav">
      <div class="closebtn" onclick="closeNav()">
          <div class="bar"></div>
          <div class="bar"></div>
          <div class="bar"></div>
      </div>
      <form class="search_bar">
        <input type="text" id="search" name="search" placeholder="Search.." onfocus="enableSearch()" onfocusout="disableSearch()" onkeyup="searchFunction()">
      </form>
      <img id="UserImage" src="<?php if($_SESSION['loggedIn']=="true"){echo $userImage;}else{echo "../../../Images/UserThumbnails/Default.png";} ?>" alt="DefaultImage" style="width:220px;height:220px;border-radius:50%;">
      <a href="../userPage" id="UserText" style="<?php if(isset($_SESSION['username'])){echo 'visibility: visible;';}?>"><?php echo $username;?></a>
      <form id="LoginForm" class="LoginForm" method="post" style="visibility: hidden; height: 0px;">
        <input id="UserBox" type="text" name="UserBox" placeholder="Username" required>
        <input id="PassBox" type="password" name="PassBox" placeholder="Password" required>
<a id="forgotLink" href="../resetPassword">Forgot Password?</a>
        <input id="LoginSubmit" type="submit" name="Login">
        <div id="logMessage"></div>
      </form>
      <div id="topBar">
        <?php
        if(isset($_SESSION['username']))
        {
          echo "<a href='../../scripts/logoutScript.php' id='logoutLink'>Logout</a>";
        }
        else
        {
          echo "<a href='#' id='LoginLink' onclick='openLogin()'>Login</a>";
          echo "<a href='../register' id='RegisterLink'>Register</a>";
        }

        if($_SESSION['accessLevel'] == 1)
        {
          echo "<a href='../adminPage' id='adminLink'>Admin Page</a>";
        }
        ?>
      </div>
      <div id="subArea">
        <?php
          if($_SESSION['loggedIn'] == "true")
          {
            echo "<div id='subTitle'><u>Your Subscriptions</u></div>";
            $subArray = array();
            $subArray = displaySubs($_SESSION['userID'], $mysqli);
            if(empty($subArray))
            {
              echo "<div id='subMessage'>You Dont have any subscriptions yet</div>";
            }
            foreach($subArray as $val)
            {
              echo "<div class='sub' onclick='location.href=\"../subPage?uID=$val->uID\"'>$val->username</div>\n";
            }
          }
        ?>
      </div>
      <a href="../about" id="AboutLink">About</a>
      <a href="../feedbackPage" id="FeedbackLink">Feedback</a>
  </div>
</div>
<?php
if(isset($_SESSION['message']) && $_SESSION['message']!="")
{
echo "<div class='alert'>\n
   <div class='closeAlert' onclick='this.parentElement.style.display=\"none\";'>&times;</div>\n
   <strong>Message: </strong>" . $_SESSION['message'] . "\n
   </div>";
unset($_SESSION['message']);
}
?>
</body>

</html>
