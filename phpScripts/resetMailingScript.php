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
//post variable to be used to find the email of the user
$email = $_POST["email"];
//check to see if email exists in the datbase. if so then proceed. Also return Token that is assigned to the user and use this to be default password for the reset password page.
$stmt = $mysqli->prepare("SELECT Token FROM Users WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($token);
$stmt->fetch();
if($stmt->num_rows == 0)
{
  $message = "This email does not exist";
  print json_encode($message);
  exit();
}
//store the token as a password hash
$password = password_hash($token, PASSWORD_DEFAULT);
//set new token
$newToken = str_shuffle($token);
//update the users password to a unique one that will be sent to their email via a link
$stmt = $mysqli->prepare("UPDATE Users SET Password = ?, Token = ? WHERE Email = ?");
$stmt->bind_param('sss', $password, $newToken, $email);
$stmt->execute();
$stmt->store_result();
$stmt->fetch();

//email the user with the link
$recipient = $email;
$subject = "PASSWORD RESET REQUEST";
$urlLink = "rcattest.esy.es/Webpages/pages/newPassword?email=$email" . "&ps=$token";
$emailMessage = "Dear user,\n\n It appears that you have requested a password reset from flixel.com\n\n
To reset your password, please click the link below. If you cannot click it, please paste it into your web browser's address bar.\n\n" . $urlLink . "\n\nThanks,\nThe Administration";
$extra = "From: flixel@noreply.com \r\n";
mail($recipient, $subject, $emailMessage, $extra);

//print out a message to tell the user that an email has been sent to them
$message = "An email has been sent to your account, please use this email to reset your password";
print json_encode($message);
?>
