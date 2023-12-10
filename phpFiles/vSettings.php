<!DOCTYPE html>
<?php
  session_start();
  include_once("../../scripts/DBConnect.php");
  include_once("../../scripts/functions.php");
  $videoID = $_GET['vID'];
  //retrieve video information
  $stmt = $mysqli->prepare("SELECT UserID, VideoFile, VName, VDescription, UploadTD, Thumbnail, Tags, Genre, Views, Banned FROM Videos WHERE VideoID = ?");
  $stmt->bind_param("i", $videoID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($userID, $videoFile, $vName, $vDescription, $uploadTD, $thumbnail, $tags, $genre, $views, $banned);
  $stmt->fetch();
  $stmt->close();
  if($_SESSION['loggedIn']=="true" || $_SESSION['accessLevel'] == 1)
  {
    $username = $_SESSION['username'];
    $userImage = $_SESSION['userImage'];
  }
  else if($_SESSION['loggedIn']=="false")
  {
    $_SESSION['message'] = "You do not have access to this page";
    header('Location: ../homepage');
    exit();
  }
  else if($_SESSION['userID'] != $userID)
  {
    $_SESSION['message'] = "You tried to access an unknown settings page";
    header('Location: ../homepage');
    exit();
  }
?>
<html>
<head>
<link href="https://fonts.googleapis.com/css?family=Righteous" rel="stylesheet">
<link rel="stylesheet" href="../../includes/GlobalBrowser.css"/>
<link rel="stylesheet" href="../../includes/vSettings.css"/>
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

function reloadcss()
{
    document.stylesheets.reload();
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

function openUpload(lbl, div)
{
  var input = document.getElementById(div);
  var lbl = document.getElementById(lbl);
  if(lbl.style.display == "none" && input.style.display == "none")
  {
    lbl.style.display = "block";
    input.style.display = "block";
  }
  else
  {
    lbl.style.display = "none";
    input.style.display = "none";
    input.value = "";
  }
}

function openText(div)
{
  var box = document.getElementById(div);
  if(box.style.display == "none")
  {
    box.style.display = "inline-block";
  }
  else
  {
    box.style.display = "none";
    box.value = "";
  }
}

function openDescision()
{
  document.getElementById("deleteArea").style.display = "block";
}

function closeDescision()
{
  document.getElementById("deleteArea").style.display = "none";
}

function deleteScript()
{
  var id = <?php echo $videoID;?>;
  var ajax=new XMLHttpRequest();
  var formdata = new FormData();
  formdata.append("id", id);
  ajax.open("POST", "../../scripts/deleteVideoScript.php");
  ajax.send(formdata);
  ajax.onreadystatechange = function()
  {
    if (ajax.readyState == 4 && ajax.status == 200)
    {
      window.location.href = "../homepage";
    }
  };
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
      <form id="settingsContainer" action="../../scripts/editVideoScript.php?vID=<?php echo $videoID; ?>" method="post" enctype="multipart/form-data">
        <fieldset>
          <legend>Video Settings</legend>
          <div class="videoArea">
            <video id="video" controls>
            <source src="<?php echo $videoFile; ?>" type="video/mp4">
              Your browser does not support HTML 5 videos
            </video>
          </div>
          <div id="section1">
            <div id="sub1Sec1">
              <label id="nameBoxlbl" for="nameBox">Name:<?php echo $vName; ?></label> <a id="chgName"  onclick="openText('nameBox')">Change Video Name</a>
              <input id="nameBox" type="text" name="nameBox" placeholder="Enter new name" maxlength="30" style="display: none;">
            </div>
            <div id="sub1Sec2">
              <div id="uploadDate">Uploaded on: <?php echo $uploadTD;?></div>
              <div id="views">Views: <?php echo $views;?></div>
            </div>
          </div>
          <div id="section2">
            <label id="vidThumbnaillbl" for="vidThunbnail">Thumbnail Image:</label>
            <img id="vidThumbnail" name="vidThumbnail" src="<?php echo $thumbnail;?>" alt="videoThumbnail" style="width:480px;height:320px;">
            <a id="chgImage"  onclick="openUpload('imageSubmitlbl', 'imageSubmit')">Change User Image</a>
            <label id="imageSubmitlbl" for="imageSubmit" style="display: none;">Select new image to upload</label>
            <input id="imageSubmit" type="file" name="imageSubmit" style="display: none;">
          </div>
          <div id="section3">
            <label id="descriptionArealbl" for="descriptionBox">Description:</label>
            <div id="descriptionArea"><?php echo $vDescription; ?></div>
            <a id="openDesc"  onclick="openUpload('descriptionBoxlbl', 'descriptionBox')">Change Description</a>
            <label id="descriptionBoxlbl" for="descriptionBox" style="display: none;">Enter new Description:</label>
            <textarea id="descriptionBox" name="descriptionBox" placeholder="Enter new description" maxlength="1000" style="display: none;"></textarea>
          </div>
          <div id="section4">
            <label id="tagArealbl" for="tagsArea">Tags associated with this video:</label>
            <div id="tagArea"><?php echo $tags; ?></div>
            <a id="chgTags"  onclick="openUpload('tagsBoxlbl', 'tagsBox')" >Change tags</a>
            <label id="tagsBoxlbl" for="tagsArea" style="display: none;">Please write in the new tags:</label>
            <textarea id="tagsBox" name="tagsBox" style="display: none;" placeholder="Enter new tags.."></textarea>
          </div>
          <div id="section5">
            <div id=currentGenre>Current Genre:  <?php echo $genre; ?></div>
            <label id="genreBoxlbl" for="genreBox">Select a different Genre:</label>
            <select id="genreBox" name="genreBox">
              <option value="ActionAndAdventure">Action and Adventure</option>
              <option value="Animation">Animation</option>
              <option value="BeautyAndFashion">Beauty and Fashion</option>
              <option value="Comedy">Comedy</option>
              <option value="Documentary">Documentary</option>
              <option value="Entertainment">Entertainment</option>
              <option value="Family">Family</option>
              <option value="Food">Food</option>
              <option value="Gaming">Gaming</option>
              <option value="HealthAndFitness">Health and Fitness</option>
              <option value="HomeAndGarden">Home and Garden</option>
              <option value="LearningAndEducation">Learning and Education</option>
              <option value="Natue">Nature</option>
              <option value="News">News</option>
              <option value="ScienceAndTech">Science and Tech</option>
              <option value="ScienceFiction">Science Fiction</option>
              <option value="Sports">Sports</option>
              <option value="Travel">Travel</option>
            </select>
          </div>
          <div id="buttonArea">
            <input id="saveSubmit" type="submit" value="Save Changes">
            <input id='deleteVideo' type='button' value='DELETE VIDEO' onclick="openDescision()">
          </div>
          <div id="deleteArea" style="display: none;">
            <div id="deleteMessage"><strong>Are you sure you want to delete this video? This action can not be reverted</strong></div>
            <input id="yesButton" type="button" value="YES" onclick="deleteScript()">
            <input id='noButton' type='button' value='NO' onclick="closeDescision()">
          </div>
        </fieldset>
      </form>
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
