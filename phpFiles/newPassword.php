<!DOCTYPE html>
<?php
  include_once('../../scripts/DBConnect.php');
  include_once("../../scripts/functions.php");
  session_start();
  //if user is logged in then redirect to homepage with error message.
  if($_SESSION['loggedIn']=="true")
  {
    $message = "You cannot access this page while signed in";
    $_SESSION['message'] = $message;
    header('location: ../homepage');
    exit();
  }
  $email = $_GET['email'];
  $password = $_GET['ps'];

  //retrieve email and password. if no matches are found within the database then redirect the user to the homepage with error message.
  $stmt = $mysqli->prepare("SELECT Username, Email, Password, UserImage FROM Users WHERE Email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($dbUsername, $dbEmail, $dbPassword, $dbUserImage);
  $stmt->fetch();
  if($stmt->num_rows == 0)
  {
    $message = "Something went wrong, to reset your password please use the link under the login";
    $_SESSION['message'] = $message;
    header('location: ../homepage');
    exit();
  }
  if(!password_verify($password, $dbPassword))
  {
    $message = "Something went wrong, to reset your password please use the link under the login";
    $_SESSION['message'] = $message;
    header('location: ../homepage');
    exit();
  }

?>
<html>
<head>
<link href="https://fonts.googleapis.com/css?family=Righteous" rel="stylesheet">
<link rel="stylesheet" href="../../includes/GlobalBrowser.css"/>
<link rel="stylesheet" href="../../includes/newPassword.css"/>
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

//send the new email to be stored within the database.
function newPassword(evt)
{
  evt.preventDefault();
  var password = $("#newPassBox").val();
  var rePassword = $("#newRePassBox").val();
  var email ="<?php echo $email; ?>";
  //if the password fields do not match
  if(password !== rePassword)
  {
    document.getElementsByClassName("alert")[0].style.display = "block";
    document.getElementsByClassName("alertMessage")[0].innerHTML = "<strong>Message: </strong> Passwords do not match!";
    return;
  }
  var formdata = new FormData();
  var ajax=new XMLHttpRequest();
  var formdata = new FormData();
  formdata.append("password", password);
  formdata.append("email", email);
  ajax.open("POST", "../../scripts/newPasswordScript.php");
  ajax.send(formdata);
  ajax.onreadystatechange = function()
  {
    if (ajax.readyState == 4 && ajax.status == 200)
    {
      window.location.href = "../homepage";
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
              window.location.href = "../homepage";
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
      <form id="newPasswordForm" method="post" onsubmit="return newPassword(event)" enctype="multipart/form-data">
        <div id="passwordContainer">
          <fieldset>
            <legend>New Password</legend>
            <div id="blurb">
              Enter a new password and re enter your new password. On completion you will be signed in and your new password will be set
            </div>
            <div id="section1">
              <img id="formImage" src="<?php echo $dbUserImage; ?>" alt="UserImage" style="width:300px;height:300px;border-radius:50%;">
              <div id="formUsername"><?php echo $dbUsername; ?></div>
            </div>
            <div id="section2">
              <label id="newPassBoxlbl" for="newPassBox">Enter your Password</label>
              <input id="newPassBox" type="password" name="newPassBox" placeholder="Password.." required>
            </div>
            <div id="section3">
              <label id="newRePassBoxlbl" for="newRePassBox">Reenter your Password</label>
              <input id="newRePassBox" type="password" name="newRePassBox" placeholder="Reenter Password.." required>
            </div>
            <div id="buttonArea">
              <input id="newPasswordSubmit" type="submit" value="Reset">
            </div>
          </fieldset>
        </div>
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
