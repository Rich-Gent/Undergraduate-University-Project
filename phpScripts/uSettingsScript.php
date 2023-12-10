<?php
require_once('DBConnect.php');
session_start();
header("Content-Type: application/json");
//file paths and variable storage
$imageTarget_dir = "/home/u189228952/public_html/Images/UserThumbnails/";
$imageDB_dir = "../../../Images/UserThumbnails/";
$imageTarget_file = $imageTarget_dir . $_FILES["imageSubmit"]["name"];
$imageUploadOk = 1;
$imageDefault = 0;
$imageFileType = pathinfo($imageTarget_file,PATHINFO_EXTENSION);
$bannerTarget_dir = "/home/u189228952/public_html/Images/BannerImages/";
$bannerDB_dir = "../../../Images/BannerImages/";
$bannerTarget_file = $bannerTarget_dir  . $_FILES["bannerSubmit"]["name"];
$bannerUplodOk = 1;
$bannerDefault = 0;
$bannerFileType = pathinfo($bannerTarget_file,PATHINFO_EXTENSION);
$username = $_POST["nameBox"];
$email = $_POST["emailBox"];
$about = $_POST["aboutBox"];
$upgrade = $_POST["request"];
$message = " ";
$changeOk = 0;
//fetch file path for the original thumbnail and user image
$stmt = $mysqli->prepare("SELECT Username, Email, UserImage, BannerImage, About FROM Users WHERE UserID = ?");
$stmt->bind_param('i', $_SESSION['userID']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($oldUsername, $oldEmail, $oldImagePath, $oldBannerPath, $oldAbout);
$stmt->fetch();
$stmt->close();
//remove file path and replace it with the true filepath for the file host.
$oldImage_Dir = str_replace("../../../Images/UserThumbnails/", "", $oldImagePath);
$oldBanner_Dir = str_replace("../../../Images/BannerImages/", "", $oldBannerPath);
$oldImage_Dir = $imageTarget_dir . $oldImage_Dir;
$oldBanner_Dir = $bannerTarget_dir . $oldBanner_Dir;
$relocate = "../pages/uSettings/?uID=" . $_SESSION['userID'];
//user thumbnail upload section
//check if image file is an actual image or not
if(($_FILES["imageSubmit"]["name"]) !== "")
{
		//check if the image file is populated
		$check = getimagesize($_FILES["imageSubmit"]["tmp_name"]);
		//if it is populated return ok
		if($check !== false)
		{
			$imageUploadOk = 1;
		}
		//if not return false and stop the upload
		else
		{
			$message = "File is not an image please upload one. \n";
			$imageUploadOk = 0;
		}
}
//else if no file selected
else
{
	$imagePath = $oldImagePath;
	$imageUploadOk = 1;
	$imageDefault = 1;
}
//check if the file already exists
if(file_exists($imageTarget_file) && $imageDefault == 0)
{
	$message = $message . "Sorry, image file already exists, Please rename it. \n";
	$imageUploadOk = 0;
}

//a limit on the file size
if(($_FILES["imageSubmit"]["size"] > 5000000) && $imageDefault == 0)
{
	$message = $message . "Sorry, your image file size is too large. \n";
	$imageUploadOk = 0;
}

//check the file has the right type of format, .jpg .png .jpeg .gif
if(($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") && $imageDefault == 0)
{
		$message = $message . "Sorry only JPG, JPEG, PNG & GIF files are allowed. \n";
		$imageUploadOk = 0;
}

//if the upload fails at any point then ignore upload and notify user
if($imageUploadOk == 0)
{
	$message = $message . "The image file could not be uploaded. \n";
	$imagePath = $oldImagePath;
}
//else if the upload is ok then carry out this
else if($imageDefault == 0)
{
		//upload file to target directory in the file host
		if(move_uploaded_file($_FILES["imageSubmit"]["tmp_name"], $imageTarget_file))
		{
			//save the image path to a variable and remove the old image file
			if($oldImage_Dir !== ($imageTarget_dir . "Default.png"))
			{
				unlink($oldImage_Dir);
			}
      $imagePath = $imageDB_dir . $_FILES["imageSubmit"]["name"];
			$message = $message . "The image file " . basename( $_FILES["imageSubmit"]["name"]) . " has been uploaded. ";
		}
		//if the upload fails then report an error
		else
		{
			$message = "Sorry, there was an error uploading the user image file. ";
			$imageUploadOK = 0;
		}

}

//banner file uploader section
//Find if file is populated with an image
if($_FILES["bannerSubmit"]["name"] !== "")
{
	//check if the image file is populated
	$check = getimagesize($_FILES["bannerSubmit"]["tmp_name"]);
	//if it is populated return ok
	if($check !== false)
	{
		$bannerUploadOk = 1;
	}
	//if not return false and stop the upload
	else
	{
		$message = "Wrong or no file attached please upload one. ";
		$bannerUploadOk = 0;
	}
}
//if no file was uploaded
else
{
	$bannerPath = $oldBannerPath;
	$bannerUploadOk = 1;
	$bannerDefault = 1;
}
//If the banner file already exists
if(file_exists($bannerTarget_file) && $bannerDefault == 0)
{
	$message = $message . " Sorry, banner image file already exists, Please rename it. ";
	$bannerUploadOk = 0;
}
//a limit on the file size (max 5MB)
if(($_FILES["bannerSubmit"]["size"] > 5000000) && $bannerDefault == 0)
{
	$message = $message . " Sorry, your banner image file size is too large. \n";
	$bannerUploadOk = 0;
}
//check the file has the right type of format which is jpg, png, jpeg and gif in this case
if(($bannerFileType != "jpg" && $bannerFileType != "png" && $bannerFileType != "jpeg" && $bannerFileType != "gif") && $bannerDefault == 0)
{
		$message = $message ." Sorry only jpg, png or gif files are allowed. \n";
		$bannerUploadOk = 0;
}
//if the upload fails at any point then ignore and send user message
if($bannerUploadOk == 0)
{
	$message = $message . " The banner image file could not be uploaded. \n";
	$bannerPath = $oldBannerPath;
}
//else if the upload is ok then carry out this
else if($bannerDefault == 0)
{
		//upload file to target directory in the file host
		if(move_uploaded_file($_FILES["bannerSubmit"]["tmp_name"], $bannerTarget_file))
		{
			//save the banner image path to a variable and remove the old image
			if($oldBanner_Dir !== ($bannerTarget_dir . "Default.png"))
			{
				unlink($oldBanner_Dir);
			}
      $bannerPath = $bannerDB_dir . $_FILES["bannerSubmit"]["name"];
			$message = $message . "The banner image file " . basename( $_FILES["bannerSubmit"]["name"]) . " has been uploaded. ";
		}
		//if the upload fails then report an error
		else
		{
			$message = "Sorry, there was an error uploading the banner image file. ";
			$bannerUploadOK = 0;
		}
}

//if the username or email fields already exist in the database then ignore the user input
$stmt = $mysqli->prepare("SELECT Username, Email FROM Users WHERE Username = ? OR Email = ?");
$stmt->bind_param("ss", $username , $email);
$stmt->execute();
$stmt->bind_result($existUser, $existEmail);
$stmt->store_result();
$stmt->fetch();
if($stmt->num_rows > 0)
{
	if($existUser == $username)
	{
		$message = $message . "Username already exists please choose something different. ";
	}
	if($existEmail == $email)
	{
		$message = $message . "Email already exists please choose something different. ";
	}
	$username = $oldUsername;
	$email = $oldEmail;
}
else
{
	$changeOk = 1;
}
$stmt->close();
//parameters for text box values. if any are null then revert to the previous data in the database
if($username == "")
{
	$username = $oldUsername;
}

if($email == "")
{
	$email = $oldEmail;
}

if($about == "")
{
	$about = $oldAbout;
}

//if the user has requested an upgrade to upload vidoes then make it true in the database, else do another update funtion without this option
if(isset($upgrade))
{
	$upgrade = "1";
	$stmt = $mysqli->prepare("UPDATE Users SET Username = ? , Email = ? , UserImage = ? , BannerImage = ? , About = ?, Upgrade = ? WHERE UserID = ?");
	$stmt->bind_param('sssssii', $username, $email, $imagePath, $bannerPath, $about, $upgrade, $_SESSION['userID']);
	$stmt->execute();
	$stmt->close();
	$message = $message . "You have requested to upload videos successfully. ";
}
else
{
	$stmt = $mysqli->prepare("UPDATE Users SET Username = ? , Email = ? , UserImage = ? , BannerImage = ? , About = ? WHERE UserID = ?");
	$stmt->bind_param('sssssi', $username, $email, $imagePath, $bannerPath, $about, $_SESSION['userID']);
	$stmt->execute();
	$stmt->close();
}

//if any problems occur than show message explaining so. if no erros then print confirmation message
if(($changeOk || $imageUploadOK || $bannerUplodOk) == 0)
{
	$message = $message . " See errors that have occured, all other changes were saved.";
}
else
{
	$message = $message. " Changes were saved";
}

$_SESSION['username'] = $username;
$_SESSION['userImage'] = $imagePath;
$_SESSION['message'] = $message;
header('Location: ' . $relocate);
?>
