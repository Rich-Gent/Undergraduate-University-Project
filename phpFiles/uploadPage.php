<!DOCTYPE html>
<?php
  session_start();
  include_once("../../scripts/DBConnect.php");
  include_once("../../scripts/functions.php");
  $userID = $_SESSION['userID'];
  //select the user access level in case the user has been given access to upload videos while they have been logged on
  $stmt = $mysqli->prepare("SELECT AccessLevel FROM Users WHERE UserID = ?");
  $stmt->bind_param("i", $userID);
  $stmt->store_result();
  $stmt->bind_result($_SESSION['accessLevel']);
  $stmt->execute();
  $stmt->fetch();
  $stmt->close();
  if($_SESSION['accessLevel'] == 3)
  {
      $_SESSION['message']="You do not have access to upload videos yet, please request this from your settings page";
      header('Location: ../homepage');
      exit();
  }
  else if($_SESSION['loggedIn'] == "true")
  {
    $username = $_SESSION['username'];
    $userImage = $_SESSION['userImage'];
  }
  else
  {
    $_SESSION['message']="Please log in to upload videos";
    header('Location: ../homepage');
    exit();
  }
?>
<html>
<head>
<link href="https://fonts.googleapis.com/css?family=Righteous" rel="stylesheet">
<link rel="stylesheet" href="../../includes/GlobalBrowser.css"/>
<link rel="stylesheet" href="../../includes/UploadPage.css"/>
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

/**
function checkFields()
{
  //define elements as boxes
  var Name = document.getElementById("UplVidNameBox");
  var Desc = document.getElementById("UplVidDescBox");
  var Tags = document.getElementById("UplTagBox");
  var check = 1;

  //check boxes have data inside them
  if((Name.value.length || Desc.value.length || Tags.value.length) < 1)
  {
    document.getElementById("message").innerHTML = "Please Fill out all text boxes";
    check = 0;
  }

  //limit size of the video title
  if(Name.value.length > 30)
  {
    document.getElementById("message").innerHTML = "Name of the video is too large, this must be less than 30 characters";
    check = 0;
  }

  //limit size of the video description
  if(Desc.value.length > 1000)
  (
    document.getElementById("message").innerHTML = "Description cannot be longer than 1000 characters";
    check = 0;
  )

  //limit size of the amount of tags that are allowed
  if(Tags.value.length > 1000)
  {
    document.getElementById("message").innerHTML = "Too many tags have been entered, please remove some";
    check = 0;
  }

  if(check == 0)
  {
  }
  else
  {
    document.getElementById("message").innerHTML = "IT WORKS";
    uploadVideo();
  }

}
**/
function uploadFiles(evt)
{
  evt.preventDefault();
  document.getElementById("loader").style.display = "block";
  var vidFile = document.getElementById("ChooseFileSubmit").files[0];
  var thumbFile = document.getElementById("ChooseThumbnailSubmit").files[0];
  var vidTitle = $("#UplVidNameBox").val();
  var vidDescription = $("#UplVidDescBox").val();
  var tags = $("#UplTagBox").val();
  var genre = $("#GenreBox").val();
  var formdata = new FormData();
  formdata.append("ChooseFileSubmit", vidFile);
  formdata.append("ChooseThumbnailSubmit", thumbFile);
  formdata.append("vidName", vidTitle);
  formdata.append("vidDescription", vidDescription);
  formdata.append("tags", tags);
  formdata.append("genre", genre);
  var ajax = new XMLHttpRequest();
  ajax.open("POST","../../scripts/fileUploadScript.php");
  ajax.send(formdata);
  ajax.onreadystatechange = function()
  {
    if (ajax.readyState == 4 && ajax.status == 200)
    {
      document.getElementById("loader").style.display = "none";
      var response = this.responseText;
      var message = JSON.parse(response);
      document.getElementsByClassName("alert")[0].style.display = "block";
      document.getElementsByClassName("alertMessage")[0].innerHTML = "<strong>Message: </strong>" + message;
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
      <form id="uploadContainer" method="post" onsubmit="return uploadFiles(event)" enctype="multipart/form-data">
        <fieldset>
          <legend>Upload Video</legend>
          <div id="section1">
            <label id="UplVidNameBoxlbl" for="UplVidNameBox">Enter your Video Name</label>
            <input id="UplVidNameBox" type="text" name="UplVidNameBox" placeholder="Video Name" maxlength="30" required>
          </div>
          <div id="section2">
            <label id="UplVidDescBoxlbl" for="UplVidDescBox">Enter the description</label>
            <textarea id="UplVidDescBox" type="text" name="UplVidDescBox" placeholder="Video Description" maxlength="1000" required></textarea>
          </div>
          <div id="section3">
            <label id="GenreBoxlbl" for="GenreBox">Please select the genre of the video</label>
            <select id="GenreBox">
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
          <div id="section4">
            <label id="UplTagBoxlbl" for="UplTagBox">Enter tags relating to the video (seperate tags using ,)</label>
            <textarea id="UplTagBox" type="text" name="UplTagBox" placeholder="Enter Tags" maxlength="1000" required></textarea>
          </div>
          <div id="section5">
            <label id="ChooseFileSubmitlbl" for="ChooseFileSubmit">Choose video to upload</label>
            <input id="ChooseFileSubmit" type="file" name="Choose File..." required>
          </div>
          <div id="section6">
            <label id="ChooseThumbnailSubmitlbl" for="ChooseThumbnailSubmit">Choose thumbnail to upload with video</label>
            <input id="ChooseThumbnailSubmit" type="file" name="Choose File...">
          </div>
          <input id="UploadSubmit" type="submit" value="Upload">
        </fieldset>
    </form>
    <div id=loader></div>
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
            $subArray = displaySubs($userID, $mysqli);
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
