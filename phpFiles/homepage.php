<!DOCTYPE html>
<?php
  session_start();
  include_once("../../scripts/DBConnect.php");
  include_once("../../libraries/classLib.php");
  include_once("../../scripts/functions.php");
  if($_SESSION['loggedIn']=="true")
  {
    $username = $_SESSION['username'];
    $userImage = $_SESSION['userImage'];
  }

  //fetch newest videos function
  $banned = "0";
  $newVideos = array();
  $stmt = $mysqli->prepare("SELECT VideoID, VName, Thumbnail, Views, Banned FROM Videos WHERE Banned = ? ORDER BY UploadTD DESC LIMIT 10");
  $stmt->bind_param("i", $banned);
  $stmt->execute();
  $stmt->bind_result($tmpVID, $tmpVName, $tmpThumbnail, $tmpViews, $tmpBanned);
  while($stmt->fetch())
  {
    $video = new display($uploaderUserID, $tmpVID, $tmpVName, $tmpThumbnail, $tmpViews, $tmpBanned);
    array_push($newVideos, $video);
  }

  //action genre videos function
  $actionVideos = array();
  $actionVideos = thumbnails("ActionAndAdventure","", $mysqli);

  //animation genre videos function
  $animationVideos = array();
  $animationVideos = thumbnails("Animation","", $mysqli);

  //beauty and fashion genre videos function
  $fashionVideos = array();
  $fashionVideos = thumbnails("BeautyAndFashion","", $mysqli);

  //comedy genre videos function
  $comedyVideos = array();
  $comedyVideos = thumbnails("Comedy","", $mysqli);

  //documentary genre videos function
  $docVideos = array();
  $docVideos = thumbnails("Documentary","", $mysqli);

  //entertainment genre videos function
  $entertainmentVideos = array();
  $entertainmentVideos = thumbnails("Entertainment","", $mysqli);

  //family genre videos function
  $familyVideos = array();
  $familyVideos = thumbnails("Family","", $mysqli);

  //food genre videos function
  $foodVideos = array();
  $foodVideos = thumbnails("Food","", $mysqli);

  //gaming genre videos function
  $gamingVideos = array();
  $gamingVideos = thumbnails("Gaming","", $mysqli);

  //health and fitness genre videos function
  $heatlhVideos = array();
  $healthVideos = thumbnails("HealthAndFitness","", $mysqli);

  //home and garden genre videos function
  $gardenVideos = array();
  $gardenVideos = thumbnails("HomeAndGarden","", $mysqli);

  //learning and education genre videos function
  $learningVideos = array();
  $learningVideos = thumbnails("LearningAndEducation","", $mysqli);

  //nature genre videos function
  $natureVideos = array();
  $natureVideos = thumbnails("Nature","", $mysqli);

  //news genre videos function
  $newsVideos = array();
  $newsVideos = thumbnails("News","", $mysqli);

  //science and tech genre videos function
  $scienceVideos = array();
  $scienceVideos = thumbnails("ScienceAndTech","", $mysqli);

  //science fiction genre videos function
  $scifiVideos = array();
  $scifiVideos = thumbnails("ScienceFiction","", $mysqli);

  //sports genre videos function
  $sportsVideos = array();
  $sportsVideos = thumbnails("Sports","", $mysqli);

  //travel genre videos function
  $travelVideos = array();
  $travelVideos = thumbnails("Travel","", $mysqli);

?>
<html>
<head>
<link href="https://fonts.googleapis.com/css?family=Righteous" rel="stylesheet">
<link rel="stylesheet" href="../../includes/GlobalBrowser.css"/>
<link rel="stylesheet" href="../../includes/homepage.css"/>
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
  document.getElementById("searchField").style.display = "block";
  document.getElementById("searchField").style.width = "100%";
}

function disableSearch()
{
  if(document.getElementById("search").value == "")
  {
    document.getElementById("searchField").style.display = "none";
    document.getElementById("searchField").style.width = "0%";
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
      <div id='newVideoField'>
        <h2>Newly uploaded videos:</h2>
        <?php
          if(empty($newVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($newVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='actionVideoField'>
        <h2>Action and Adventure videos:</h2>
        <?php
          if(empty($actionVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($actionVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='animationVideoField'>
        <h2>Animation videos:</h2>
        <?php
          if(empty($animationVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($animationVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='fashionVideoField'>
        <h2>Beauty and Fashion videos:</h2>
        <?php
          if(empty($fashionVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($fashionVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='comedyVideoField'>
        <h2>Comedy videos:</h2>
        <?php
        if(empty($comedyVideos))
        {
          echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
        }
        else
        {
          foreach($comedyVideos as $val)
          {
             $page = "../viewPage/?vID=" . $val->vID;
             echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
             echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                   <div class='videoInfo'>".  $val->vName . "</div>\n
                   <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                   </div>";
          }
        }
        ?>
      </div>
      <div id='docVideoField'>
        <h2>Documentary videos:</h2>
        <?php
          if(empty($docVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($docVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='entertainmentVideoField'>
        <h2>Entertainment videos:</h2>
        <?php
          if(empty($entertainmentVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($entertainmentVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='familyVideoField'>
        <h2>Family videos:</h2>
        <?php
          if(empty($familyVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($familyVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='foodVideoField'>
        <h2>Food videos:</h2>
        <?php
          if(empty($foodVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($foodVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='gamingVideoField'>
        <h2>Gaming videos:</h2>
        <?php
          if(empty($gamingVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($gamingVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='heatlhVideoField'>
        <h2>Health and Fitness videos:</h2>
        <?php
          if(empty($heatlhVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($healthVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='gardenVideoField'>
        <h2>Home and Garden videos:</h2>
        <?php
          if(empty($gardenVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($gardenVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='learningVideoField'>
        <h2>Learning and Education videos:</h2>
        <?php
          if(empty($learningVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($learningVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='natureVideoField'>
        <h2>Natrue videos:</h2>
        <?php
          if(empty($natureVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($natureVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='natureVideoField'>
        <h2>Nature videos:</h2>
        <?php
          if(empty($natureVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($natureVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='newsVideoField'>
        <h2>News videos:</h2>
        <?php
          if(empty($newsVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($newsVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='scienceVideoField'>
        <h2>Science and Tehcnology videos:</h2>
        <?php
          if(empty($scienceVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($scienceVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='scifiVideoField'>
        <h2>Science Fiction videos:</h2>
        <?php
          if(empty($scifiVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($scifiVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='sportsVideoField'>
        <h2>Sports videos:</h2>
        <?php
          if(empty($sportsVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($sportsVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
            }
          }
        ?>
      </div>
      <div id='travelVideoField'>
        <h2>Travel videos:</h2>
        <?php
          if(empty($travelVideos))
          {
            echo "<div class='thumbMessage'>There are no videos of this type yet</div>\n";
          }
          else
          {
            foreach($travelVideos as $val)
            {
               $page = "../viewPage/?vID=" . $val->vID;
               echo "<div class='thumbnail' onclick= 'location.href=" . "\"$page\"" . "'>\n";
               echo "<img alt='VideoImage' class='videoImage' src=" . $val->vThumbnail  . "> \n
                     <div class='videoInfo'>".  $val->vName . "</div>\n
                     <div class='views'>Views:<b>" . $val->views . "</b></div> \n
                     </div>";
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
