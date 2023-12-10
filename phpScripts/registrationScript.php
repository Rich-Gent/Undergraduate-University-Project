<?php
include_once("DBConnect.php");
session_start();
header("Content-Type: application/json");
//if a user is set then end script and show message

if($_SESSION['loggedIn']== "true")
{
  $message = "User is logged in";
  print json_encode($message);
  exit();
}
//post variables to be used for registering a user
$username = $_POST["username"];
$email = $_POST["email"];
//store the password as a hash
$password = password_hash($_POST["password"], PASSWORD_DEFAULT);
//check to see if email or user name exists in the datbase. if so return false
$stmt = $mysqli->prepare("SELECT Username, Email FROM Users WHERE Username = ? OR Email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($usernameCheck, $emailCheck);
$stmt->fetch();
if($username == $usernameCheck)
{
  $message = "Username already exists, please choose another username";
  $stmt->close();
  print json_encode($message);
  exit();
}
if($email == $emailCheck)
{
  $message = "Email already exists as an account, please choose a different email";
  $stmt->close();
  print json_encode($message);
  exit();
}
//first add user to the database
$stmt = $mysqli->prepare("INSERT INTO Users(Username, Email, Password) VALUES(?, ?, ?)");
$stmt->bind_param('sss', $username, $email, $password);
$stmt->execute();


//retrieve user ID and timestamp to make the register link unique and available for a short time
$stmt = $mysqli->prepare("SELECT UserID, RegisteredSince FROM Users WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($userID, $timestamp);
$stmt->fetch();
//add timestamp and user ID to register link to make it unique
$token = strtotime($timestamp)+ $userID;
//add 10 minutes to token validity
$tokenValid = date("Y-m-d H:i:s", strtotime($timestamp) + 600);
//create uniqe url link
$urlLink = "rcattest.esy.es/Webpages/scripts/verifyScript.php?uID=$userID" . "&tkn=$token";
//store token and timestamp within the database
$stmt = $mysqli->prepare("UPDATE Users SET Token= ?, Token_Validity=? WHERE UserID = ?");
$stmt->bind_param('ssi', $token, $tokenValid, $userID);
$stmt->execute();
$stmt->close();

//email the user
$recipient = $email;
$subject = "ACCOUNT ACTIVATION";
$emailMessage = "Hi " . $username . " thank you for registering with us! \n\n";
$emailMessage = $emailMessage . "Here is your registration link: " . $urlLink ."\n";
$extra = "From: flixel@noreply.com \r\n";
mail($recipient, $subject, $emailMessage, $extra);

$message = "User has been provisionally registered to the website, please check your email for a link to fully register your account";
$_SESSION['message'] = $message;
print json_encode($message);
?>
