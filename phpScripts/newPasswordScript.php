<?php
  session_destroy();
  session_start();
  header("Content-Type: application/json");
  require_once('DBConnect.php');
  include_once('../libraries/subLib.php');

  $email = $_POST["email"];
  $password = $_POST["password"];
  //hash the new password and update the user within the database
  $password = password_hash($password, PASSWORD_DEFAULT);
  $stmt = $mysqli->prepare("UPDATE Users SET Password = ? WHERE Email = ?");
  $stmt->bind_param('ss', $password, $email);
  $stmt->execute();
  $stmt->fetch();

  //select the record from the database that matches the email and store user information
  $stmt = $mysqli->prepare("SELECT UserID, AccessLevel, Username, UserImage, Banned FROM Users WHERE Email = ?");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($userID, $accessLevel, $dbUsername, $dbPassword, $userImage, $banned);
  $stmt->fetch();

  //if user is banned then update password but show banned message and do not log them in
  if($banned == "1")
  {
    $loginSucess="false";
    $_SESSION['loggedIn'] = $loginSucess;
    $message = "YOUR USER ACCOUNT HAS BEEN BANNED PLEASE CONTACT ADMIN FOR MORE DETAILS. PASSWORD WAS STILL UPDATED";
    $_SESSION['message'] = $message;
  }
  else
  {
    $_SESSION['userID'] = $userID;
    $_SESSION['accessLevel'] = $accessLevel;
    $_SESSION['username'] = $dbUsername;
    $_SESSION['userImage'] =$userImage;
    //update time of last login
    $stmt = $mysqli->prepare("UPDATE Users SET Last_Login = CURRENT_TIMESTAMP WHERE UserID = ?");
    $stmt->bind_param('i',$userID);
    $stmt->execute();
    $stmt->close();
    $loginSucess="true";
    $_SESSION['loggedIn'] = $loginSucess;
    $message = "Your password was updated.";
    $_SESSION['message'] = $message;
  }
?>
