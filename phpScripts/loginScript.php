<?php
  session_start();
  header("Content-Type: application/json");
  require_once('DBConnect.php');
  include_once('../libraries/subLib.php');
  $input = $_POST["username"];
  $password = $_POST["password"];
  //select the record from the database that matches the username or email and store user information
  $stmt = $mysqli->prepare("SELECT UserID, AccessLevel, Username, Password, UserImage, Registered, Banned FROM Users WHERE Email = ? OR Username = ?");
  $stmt->bind_param('ss', $input, $input);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($userID, $accessLevel, $dbUsername, $dbPassword, $userImage, $registered, $banned);
  $stmt->fetch();
  //if no results are found, reply with an error message and exit the script
  if($stmt->num_rows == 0)
  {
    $loginSucess="false";
    $_SESSION['loggedIn'] = $loginSucess;
    $message = "Incorrect username or email given";
    $box = "UserBox";
    $data = Array($loginSucess, $message, $box);
  }
  //if user is not registered to the site properly yet then refuse sign in
  else if($registered == "0")
  {
    $loginSucess="false";
    $_SESSION['loggedIn'] = $loginSucess;
    $message = "Please register your account fully to sign in";
    $box = "UserBox";
    $data = Array($loginSucess, $message, $box);
  }
  else if($banned == "1")
  {
    $loginSucess="false";
    $_SESSION['loggedIn'] = $loginSucess;
    $message = "YOUR USER ACCOUNT HAS BEEN BANNED PLEASE CONTACT ADMIN FOR MORE DETAILS";
    $box = "UserBox";
    $data = Array($loginSucess, $message, $box);
  }
  //if results are found and passwords are met then store variables in session variables
  else if(password_verify($password , $dbPassword))
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
    //select the users subcriptions if they have them.
    $subArray = array();
    $stmt = $mysqli->prepare("SELECT Users.UserID, Users.Username FROM Subscriptions INNER JOIN Users ON Subscriptions.UserSubID = Users.UserID WHERE Subscriptions.UserID = ? AND Subscriptions.Banned = ?");
    $stmt->bind_param('ii', $_SESSION['userID'], $banned);
    $stmt->execute();
    $stmt->bind_result($subID, $subUsername);
    while($stmt->fetch())
    {
        $sub = new sub($subID, $subUsername);
        array_push($subArray, $sub);
    }
    $loginSucess="true";
    $_SESSION['loggedIn'] = $loginSucess;
    $data = Array($loginSucess, $userImage, $dbUsername, $accessLevel, $subArray);
  }
  //else if passwords dont match then send back error message and exit
  else
  {
    $loginSucess="false";
    $_SESSION['loggedIn'] = $loginSucess;
    $message = "Password is incorrect";
    $box = "PassBox";
    $data = Array($loginSucess, $message, $box);
  }
  print json_encode($data);
?>
