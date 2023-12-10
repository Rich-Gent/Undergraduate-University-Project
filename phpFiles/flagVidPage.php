<!DOCTYPE html>
<?php
  session_start();
  $videoID = $_GET['vID'];
  if($_SESSION['loggedIn']=="true")
  {
    $username = $_SESSION['username'];
    $userImage = $_SESSION['userImage'];
  }
  else if($_SESSION['loggedIn']=="false")
  {
    $_SESSION['message'] = "please log in or register to flag a video or user";
    header("location: ../viewPage/?vID=$videoID");
    exit();
  }
?>
<html>
<head>
<link href="https://fonts.googleapis.com/css?family=Righteous" rel="stylesheet">
<link rel="stylesheet" href="../../includes/GlobalBrowser.css"/>
<script>
/* Set the width of the side navigation to 250px and the left margin of the page content to 250px and add a black background color to body */
function openNav()
{
    document.getElementById("TheSideNav").style.width = "250px";
    document.getElementById("container").style.marginLeft = "250px";
}

/* Set the width of the side navigation to 0 and the left margin of the page content to 0, and the background color of body to white */
function closeNav()
{
    document.getElementById("TheSideNav").style.width = "0";
    document.getElementById("container").style.marginLeft = "0";
}

function openLogin()
{
    if(document.getElementById("LoginForm").style.visibility == "hidden")
    {
      document.getElementById("LoginForm").style.visibility = "visible";
      document.getElementById("LoginForm").style.height = "160px";
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
             }
             else
             {
                $("#LoginForm").html("Oh noes!!!");
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
    <h1>Flixel</h1>
  </div>
<div id="container">
  <div class="center">
      <!-- Use any element to open the sidenav -->
      <span class="openbtn" onclick="openNav()">
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
      </span>
      <div class="wrapper">
        <div id="thumbnail"></div>

      </div>
    </div>
  <div id="TheSideNav" class="SideNav">
      <div class="closebtn" onclick="closeNav()">
          <div class="bar"></div>
          <div class="bar"></div>
          <div class="bar"></div>
      </div>
      <form class="search_bar">
        <input type="text" name="search" placeholder="Search..">
      </form>
      <img id="UserImage" src="<?php if($_SESSION['loggedIn']=="true"){echo $userImage;}else{echo "../../../Images/UserThumbnails/Default.png";} ?>" alt="DefaultImage" style="width:220px;height:220px;border-radius:50%;">
      <a href="../userPage" id="UserText" style="<?php if(isset($_SESSION['username'])){echo 'visibility: visible;';}?>"><?php echo $username;?></a>
      <form id="LoginForm" class="LoginForm" method="post">
        <input id="UserBox" type="text" name="UserBox" placeholder="Username">
        <input id="PassBox" type="password" name="PassBox" placeholder="Password">
        <input id="LoginSubmit" type="submit" name="Login">
      </form>
      <a href="#" id="LoginLink" onclick="openLogin()">Login</a>
      <a href="../register" id="RegisterLink">Register</a>
      <div class="subscription" href="#" style="width:80%;height:5%"></div>
      <a href="../about" id="AboutLink">About</a>
      <a href="#" id="FeedbackLink">Feedback</a>
  </div>
</div>
</body>

</html>
