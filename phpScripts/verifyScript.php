<?php
include_once("DBConnect.php");
header("Content-Type: application/json");
session_start();
//if user that is logged in goes to this page for any reason
if($_SESSION['loggedIn'] == "true")
{
  $message = "You cannot register if you are already logged in!";
  $_SESSION['message'] = $message;
  header("Location: ../pages/homepage");
  exit();
}

//get variables from the url
$userID = $_GET['uID'];
$token = $_GET['tkn'];

//select relavant data for the tokens within the database
$stmt = $mysqli->prepare("SELECT Token, Token_Validity, Registered FROM Users WHERE UserID = ?");
$stmt->bind_param('i', $userID);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($tokenDB, $tokenValidDB, $registered);
$stmt->fetch();
//if no results are found then show error message
if($stmt->num_rows == 0)
{
  $stmt->close();
  $message = "Oops it looks like you were not registered properly \n\n" . "Please register again";
  $_SESSION['message'] = $message;
  header("Location: ../pages/homepage");
  exit();
}
$stmt->close();

//if user is already registered then send them back to the homepage
if($registered == "1")
{
  $message = "This user is already registered";
  $_SESSION['message'] = $message;
  header("Location: ../pages/homepage");
  exit();
}

//check to see if token matches
//if token does not match then print error code, drop associated row with user ID and exit
if($token !== $tokenDB)
{
  $message = "Registration tokens do not match, you must register again";
  $_SESSION['message'] = $message;
  $stmt = $mysqli->prepare("DELETE FROM Users WHERE UserID = ?");
  $stmt->bind_param('i', $userID);
  $stmt->execute();
  $stmt->close();
  header("Location: ../pages/homepage");
  exit();
}
//if token validity timestamp is less than the current time stamp then print error message and drop assoicated row with user ID and exit.
else if(date('Y-m-d H:i:s') > $tokenValidDB)
{
  $message = "Register link is no longer valid, you must register again";
  $_SESSION['message'] = $message;
  $stmt = $mysqli->prepare("DELETE FROM Users WHERE UserID = ?");
  $stmt->bind_param('i', $userID);
  $stmt->execute();
  $stmt->close();
  header("Location: ../pages/homepage");
  exit();
}
//else if both clauses check out return registered to true and send back confirmation message
else
{
  $update = "1";
  $message = "You have now been registered to the website successfully, for video upload privelages please submit a request on your personal page";
  $_SESSION['message'] = $message;
  $stmt = $mysqli->prepare("UPDATE Users SET Registered = ? WHERE UserID = ?");
  $stmt->bind_param('ii', $update, $userID);
  $stmt->execute();
  $stmt->close();

  $stmt = $mysqli->prepare("SELECT AccessLevel, Username, UserImage FROM Users WHERE UserID = ?");
  $stmt->bind_param('i', $userID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($accessLevel, $dbUsername, $userImage);
  $stmt->fetch();

  $_SESSION['userID'] = $userID;
  $_SESSION['accessLevel'] = $accessLevel;
  $_SESSION['username'] = $dbUsername;
  $_SESSION['userImage'] =$userImage;
  $stmt = $mysqli->prepare("UPDATE Users SET Last_Login = CURRENT_TIMESTAMP WHERE UserID = ?");
  $stmt->bind_param('i',$userID);
  $stmt->execute();
  $stmt->close();
  $loginSucess="true";
  $_SESSION['loggedIn'] = $loginSucess;
  header("Location: ../pages/homepage");
  exit();
}


?>
