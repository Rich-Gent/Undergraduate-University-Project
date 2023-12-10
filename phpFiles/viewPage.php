<!DOCTYPE html>
<?php
  include_once("../../scripts/DBConnect.php");
  include_once("../../libraries/commentLib.php");
  include_once("../../scripts/functions.php");
  session_start();
  $videoID = $_GET['vID'];
  //get video information from the video table
  $stmt = $mysqli->prepare("SELECT UserID, VideoFile, VName, VDescription, UploadTD, Genre, Views, Banned FROM Videos WHERE VideoID = ? ");
  $stmt->bind_param("i", $videoID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($userID, $videoFile, $vName, $vDescription, $uploadTD, $genre, $views, $banned);
  $stmt->fetch();
  //if the video is banned then redirect to the homepage with error message
  if($banned == 1 && $_SESSION['accessLevel'] !== 1)
  {
    $_SESSION['message'] = "This video has been banned by moderators, please select another video";
    header('Location: ../homepage');
    exit();
  }
  if($_SESSION['loggedIn']=="true")
  {
    $enabled = "true";
    $username = $_SESSION['username'];
    $userImage = $_SESSION['userImage'];
    //fetch history of the user.
    $stmt = $mysqli->prepare("SELECT VideoID, UserID FROM History WHERE VideoID = ? AND UserID = ?");
    $stmt->bind_param('ii', $videoID, $_SESSION['userID']);
    $stmt->execute();
    $stmt->store_result();
    $stmt->fetch();
    //if results are found then ignore
    if($stmt->num_rows >= 1)
    {
      $stmt->close();
    }
    else
    {
      //if no results are found then add to user history
      $stmt = $mysqli->prepare("INSERT INTO History(VideoID, UserID) VALUES(?, ?)");
      $stmt->bind_param('ii', $videoID, $_SESSION['userID']);
      $stmt->execute();
      $stmt->store_result();
      $stmt->close();
    }
    //fetch if the signed in user has subscribed to this videos uploader
    $stmt = $mysqli->prepare("SELECT UserID, UserSubID FROM Subscriptions WHERE userID = ? AND UserSubID = ?");
    $stmt->bind_param('ii', $_SESSION['userID'], $userID);
    $stmt->execute();
    $stmt->store_result();
    $stmt->fetch();
    //if a record is fetched then return true, otherwise then retun false
    if($stmt->num_rows >= 1)
    {
      $subscribed = "true";
      $stmt->close();
    }
    else
    {
      $subscribed = "false";
      $stmt->close();
    }
  }
  else
  {
    $enabled = "false";
  }
  //insert that the video has been viewed
  $views = $views+1;
  $stmt = $mysqli->prepare("UPDATE Videos SET Views= ? WHERE VideoID = ?");
  $stmt->bind_param("ii", $views, $videoID);
  $stmt->execute();
  $stmt->store_result();

  //fetch relevant information from the uploader of the video
  $stmt = $mysqli->prepare("SELECT Username, UserImage FROM Users WHERE UserID = ?");
  $stmt->bind_param("i", $userID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($uploaderUsername, $uploaderImage);
  $stmt->fetch();
  $stmt->close();

  //fetch all comments associated with this video
  $commentArray = array();
  $stmt = $mysqli->prepare("SELECT Comments.UserID, Users.Username, Users.UserImage, Comments.Comments, Comments.CommentTS FROM Comments INNER JOIN Users ON Comments.UserID = Users.UserID WHERE Comments.VideoID = ? ORDER BY Comments.CommentTS");
  $stmt->bind_param("i", $videoID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($tmpUserCommentID, $tmpUsername, $tmpUserimage, $tmpComment, $tmpTimestamp);
  while($stmt->fetch())
  {
    $comment = new comment($tmpUserCommentID, $tmpUsername, $tmpUserimage, $tmpComment, $tmpTimestamp);
    array_push($commentArray, $comment);
  }
  $stmt->close();

  //retrieve videos that are the same genre that is related to this video
  $genreVideos = array();
  $genreVideos = thumbnails($genre,"", $mysqli);

?>
<html>
<head>
<link href="https://fonts.googleapis.com/css?family=Righteous" rel="stylesheet">
<link rel="stylesheet" href="../../includes/GlobalBrowser.css"/>
<link rel="stylesheet" href="../../includes/viewPage.css"/>
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

function openFlag()
{
  if(document.getElementById("flagBox").style.visibility == "hidden")
  {
    document.getElementById("flagBox").style.visibility = "visible";
    document.getElementById("flagBox").style.height = "160px";
    document.getElementById("flagLink").innerHTML = "Cancel complaint";
  }
  else
  {
    document.getElementById("flagBox").style.visibility = "hidden";
    document.getElementById("flagBox").style.height = "0px";
    document.getElementById("flagLink").innerHTML = "Flag this video";
  }
}

function reloadcss()
{
    document.stylesheets.reload();
}

function boxValidation(div)
{
  //get results from the flag box
  var value = document.getElementById(div).value;
  //check to see if the box is not empty. if so then return error message
  if(value == "")
  {
    document.getElementsByClassName("alert")[0].style.display = "block";
    document.getElementsByClassName("alertMessage")[0].innerHTML = "<strong>Message: </strong> Please enter a value into the text box";
    return false;
  }
}

function loginValidation()
{
  //check to see if the user is logged in. if not then return error message
  var loginCheck = <?php echo $enabled; ?>;
  if(loginCheck == false)
  {
    document.getElementsByClassName("alert")[0].style.display = "block";
    document.getElementsByClassName("alertMessage")[0].innerHTML = "<strong>Message: </strong> You must be signed in to use this feture!";
    return false;
  }
}
function flagPost(div , evt)
{
  evt.preventDefault();
  //validate login and box. if false then exit script
  var result1 = loginValidation();
  var result2 = boxValidation(div);
  if(result1 == false)
  {
    return;
  }
  if(result2 == false)
  {
    return;
  }
  //get results from the flag box
  var flag = $("#flagInfoBox").val();
  //recieve the video id it is associated with
  var vidID = <?php echo $videoID; ?>;
  //append variables to form data and send it via XMLHttpRequest to flagVideoScript.php
  var formdata = new FormData();
  formdata.append("flag", flag);
  formdata.append("vID", vidID);
  var ajax = new XMLHttpRequest();
  ajax.open("POST","../../scripts/flagVideoScript.php");
  ajax.send(formdata);
  //on sucesss of the script recieve response message and change the flag link to response message
  ajax.onreadystatechange = function()
  {
    if (ajax.readyState == 4 && ajax.status == 200)
    {
      var response = this.responseText;
      var message = JSON.parse(response);
      document.getElementById("flagBox").style.visibility = "hidden";
      document.getElementById("flagBox").style.height = "0px";
      document.getElementById("flagLink").removeAttribute("href");
      document.getElementById("flagLink").removeAttribute("onclick");
      document.getElementById("flagLink").innerHTML = message;
    }
  };
}

function commentPost(div , evt)
{
  evt.preventDefault();
  //validate login and box. if false then exit script
  var result1 = loginValidation();
  var result2 = boxValidation(div);
  if(result1 == false)
  {
    return;
  }
  if(result2 == false)
  {
    return;
  }
  //get results from the flag box
  var comment = $("#commentBox").val();
  //recieve the video id it is associated with
  var vidID = <?php echo $videoID; ?>;
  //append variables to form data and send it via XMLHttpRequest to commentScript.php
  var formdata = new FormData();
  formdata.append("comment", comment);
  formdata.append("vID", vidID);
  var ajax = new XMLHttpRequest();
  ajax.open("POST","../../scripts/commentScript.php");
  ajax.send(formdata);
  //on sucesss of the script recieve response message and update the comments section with the new comment
  ajax.onreadystatechange = function()
  {
    if (ajax.readyState == 4 && ajax.status == 200)
    {
      var response = this.responseText;
      var message = JSON.parse(response);
      document.getElementById("previousComments").innerHTML += "<div class='comment'>" + message + "</div>\n";
    }
  };
}

//function for adding favourite videos for a particular user
function addFav()
{
  var result = loginValidation();
  if(result == false)
  {
    return;
  }
  var ajax = new XMLHttpRequest();
  var formdata = new FormData();
  formdata.append("vID", <?php echo $videoID; ?>);
  ajax.open("POST", "../../scripts/favouriteScript.php");
  ajax.send(formdata);
  //on sucesss of the script recieve response message and update the comments section with the new comment
  ajax.onreadystatechange = function()
  {
    if (ajax.readyState == 4 && ajax.status == 200)
    {
      var response = this.responseText;
      var message = JSON.parse(response);
      document.getElementsByClassName("alert")[0].style.display = "block";
      document.getElementsByClassName("alertMessage")[0].innerHTML = "<strong>Message: </strong>" + message;
    }
  };
}

function banFunc()
{
  //if called then send video id to ban script
  var response = <?php echo $videoID; ?>;
  var formdata = new FormData();
  formdata.append("response", response);
  var ajax = new XMLHttpRequest();
  ajax.open("POST","../../scripts/banScript.php");
  ajax.send(formdata);
  ajax.onreadystatechange = function()
  {
    if (ajax.readyState == 4 && ajax.status == 200)
    {
        //remove ban button and show message that the action has been performed
        document.getElementById("banButton").style.visibility = "hidden";
        document.getElementsByClassName("alert")[0].style.display = "block";
        document.getElementsByClassName("alertMessage")[0].innerHTML = "<strong>Message: </strong> This video has now been banned";
    }
  };
}

function undoBan()
{
  //if called then send video id to undo ban script
  var response = <?php echo $videoID; ?>;
  var formdata = new FormData();
  formdata.append("response", response);
  var ajax = new XMLHttpRequest();
  ajax.open("POST","../../scripts/undoBanScript.php");
  ajax.send(formdata);
  ajax.onreadystatechange = function()
  {
    if (ajax.readyState == 4 && ajax.status == 200)
    {
      //remove undo ban button and show message that the action has been performed
      document.getElementById("undoBanButton").style.visibility = "hidden";
      document.getElementsByClassName("alert")[0].style.display = "block";
      document.getElementsByClassName("alertMessage")[0].innerHTML = "<strong>Message: </strong> This video has now been verified for viewing";
    }
  };
}

function addSub()
{
  //send the user id to the subUserScript.php
  var response = <?php echo $userID; ?>;
  var formdata = new FormData();
  formdata.append("response", response);
  var ajax = new XMLHttpRequest();
  ajax.open("POST","../../scripts/subUserScript.php");
  ajax.send(formdata);
  ajax.onreadystatechange = function()
  {
    if(ajax.readyState == 4 && ajax.status == 200)
    {
      //on return remove subscribe button and update the subscription field unless user has already subbed before
      document.getElementById("subButton").style.background = "grey";
      document.getElementById("subButton").value = "Subscribed";
      document.getElementById("subButton").removeAttribute("onclick");
      document.getElementById("subArea").innerHTML += "<div class='sub' onclick='location.href=\"../subPage?uID=<?php echo $userID; ?>\"'><?php echo $uploaderUsername; ?></div>";
    }
  };
}

function removeSub()
{
  //send the user id to the removeSubScript.php
  var response = <?php echo $userID; ?>;
  var formdata = new FormData();
  formdata.append("response", response);
  var ajax = new XMLHttpRequest();
  ajax.open("POST","../../scripts/removeSubScript.php");
  ajax.send(formdata);
  ajax.onreadystatechange = function()
  {
    if (ajax.readyState == 4 && ajax.status == 200)
    {
      //on return remove subscribe button and update the subscription field
      document.getElementById("unsubButton").style.background = "grey";
      document.getElementById("unsubButton").value = "un-subscribed";
      document.getElementById("unsubButton").removeAttribute("onclick");
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
      <!-- Use bars to open the sidenav -->
      <span class="openbtn" onclick="openNav()">
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
      </span>
      <div class="videoContainer">
        <div class="videoArea">
          <video id="video" controls>
          <source src="<?php echo $videoFile; ?>" type="video/mp4">
            Your browser does not support HTML 5 videos
          </video>
        </div>
        <div class="infoBox">
          <div id="section1">
            <div id="videoName">
              <?php echo $vName; if($banned == "1"){ echo " (BANNED VIDEO)";} ?>
            </div>
            <div id="subSec1">
              <a href="../subPage?uID=<?php echo $userID; ?>">
              <img id="uploaderImage" src="<?php echo $uploaderImage; ?>" href="../subPage?uID=<?php echo $userID; ?>" alt="uploaderImage" style="width:70px;height:70px;border-radius:50%;">
              </a>
              <div id="uploaderUsernameLink">
                <a id="uploaderName" href="../subPage?uID=<?php echo $userID; ?>"><?php echo $uploaderUsername; ?></a>
              </div>
              <?php
                if($_SESSION['userID'] == $userID)
                {

                }
                else if($_SESSION['loggedIn'] == "true" && $subscribed == "false")
                {
                  echo "<div id='subButtonArea'>\n";
                  echo "<input id='subButton' type='button' onclick='addSub()' value='Subscribe'>\n";
                  echo "</div>\n";
                }
                else if($_SESSION['loggedIn'] == "true" && $subscribed == "true")
                {
                  echo "<div id='subButtonArea'>\n";
                  echo "<input id='unsubButton' type='button' onclick='removeSub()' value='un-Subscribe'>\n";
                  echo "</div>\n";
                }

              if($_SESSION['accessLevel'] == "1" || $_SESSION['userID'] == $userID)
              {
                echo "<div id='editButtonArea'>\n";
                echo "<input id='editButton' type='button' onclick='location.href=\"../vSettings/?vID=$videoID\"' value='Edit Video'>\n";
                echo "</div>\n";
              }

              if($_SESSION['accessLevel'] == "1" && $banned == "0")
              {
                echo "<input id='banButton' type='button' value='BAN VIDEO' onclick='banFunc()'>";
                echo "</div>";
              }
              else if($_SESSION['accessLevel'] == "1" && $banned == "1")
              {
                echo "<input id='undoBanButton' type='button' value='UN-BAN VIDEO' onclick='undoBan()'>";
                echo "</div>";
              }
              else
              {
                echo "<a id='flagLink' href='#' onclick='openFlag()'>Flag this video</a>\n
                  </div>\n
                  <div id='flagBox' style='visibility: hidden; height: 0px;'>\n
                    <form id='flagForm' class='flagForm' method:'post' onsubmit='return flagPost(\"flagInfoBox\", event)' enctype='multipart/form-data'>\n
                      <div id='flagDescription'>Please give a reason as to why you want to flag this video</div>\n
                      <textarea id='flagInfoBox' type='text' name='flagInfoBox' maxlength='1000' placeholder='Type your comment in here...' required></textarea>\n
                      <input id='flagSubmit' type='submit' value='Submit Complaint'>\n
                    </form>\n
                  </div>\n";
              }
            ?>
          </div>
          <div id='section2'>
            <div id="videoDesc">
              <strong>Published: <?php echo $uploadTD;?></strong><br>
              <?php echo $vDescription; ?>
              <a id="favourite" href="#" onclick="addFav()">Add to Favourites</a>
            </div>
          </div>
          <div id="section3">
            <div id="commentSection">
              <form id="commentForm" class="commentForm" method:"post" onsubmit="return commentPost('commentBox', event)" enctype="multipart/form-data">
                <textarea id="commentBox" type="text" name="commentBox" maxlength="1000" placeholder="Add a comment to the video...." required></textarea>
                <input id="commentSubmit" type="submit" value="Submit Comment">
              </form>
              <div id="previousComments">
                <?php
                foreach($commentArray as $val)
                {
                   echo "<div class='comment'>";
                   echo "<div class='commentInfo'>\n
                   <img class='commentImage' src='$val->userImage' href='../subPage?uID=". $val->uID . "' alt='commenterImage' style='width:70px;height:70px;border-radius:50%;'>\n
                   <a href='../subPage?uID=" . $val->uID . "'>". $val->username ."</a><br> Posted:" . $val->time . "</div>\n";
                   echo "<div class='commentText'>" . $val->comment . "</div>\n </div>";
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id='genreVideoField'>
        <div id=genreTitle>Related videos:</div>
        <?php
          if(sizeof($genreVideos) == 1)
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($genreVideos as $val)
            {
              if($val->vID != $videoID)
              {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='genreThumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
              }
            }
          }
        ?>
      </div>
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
  <div class="alert" style="display: none;">
  <div class="closeAlert" onclick="this.parentElement.style.display='none';">&times;</div>
  <div class="alertMessage"></div>
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
