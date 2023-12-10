<?php
require_once('DBConnect.php');
session_start();
header("Content-Type: application/json");
//file paths and variable storage
$imageTarget_dir = "/home/u189228952/public_html/Images/VidThumbnails/";
$imageDB_dir = "../../../Images/VidThumbnails/";
$imageTarget_file = $imageTarget_dir . $_FILES["ChooseThumbnailSubmit"]["name"];
$imageUploadOk = 1;
$imageDefault = 0;
$imageFileType = pathinfo($imageTarget_file,PATHINFO_EXTENSION);
$videoTarget_dir = "/home/u189228952/public_html/Video/";
$genre = $_POST["genre"];
$videoDB_dir = "../../../Video/" . $genre . "/";
$videoTarget_file = $videoTarget_dir . $genre . "/" . $_FILES["ChooseFileSubmit"]["name"];
$videoUplodOk = 1;
$videoFileType = pathinfo($videoTarget_file,PATHINFO_EXTENSION);
$vidName = $_POST["vidName"];
$vidDescription = $_POST["vidDescription"];
$tags = $_POST["tags"];
$tags = $tags . ", " . $vidName;
$message = "";


//Video thumbnail upload section
//check if image file is an actual image or not
if(isset($_FILES["ChooseThumbnailSubmit"]["name"]))
{
		//check if the image file is populated
		$check = getimagesize($_FILES["ChooseThumbnailSubmit"]["tmp_name"]);
		//if it is populated return ok
		if($check !== false)
		{
			$imageUploadOk = 1;
		}
		//if not return false and stop the upload
		else
		{
			$message = "Thumbnail File is not an image please upload one, ";
			$imageUploadOk = 0;
		}
}
//else if no file selected
else
{
	$imagePath = $imageDB_dir . "Default.png";
	$imageUploadOk = 1;
	$imageDefault = 1;
}
//check if the file already exists
if(file_exists($imageTarget_file) && $imageDefault == 0)
{
	$message = $message . " Sorry, image file already exists,";
	$imageUploadOk = 0;
}

//a limit on the file size
if(($_FILES["ChooseThumbnailSubmit"]["size"] > 5000000) && $imageDefault == 0)
{
	$message = $message . " Sorry, your image file size is too large,";
	$imageUploadOk = 0;
}

//check the file has the right type of format, .jpg .png .jpeg .gif
if(($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") && $imageDefault == 0)
{
		$message = $message . " Sorry only JPG, JPEG, PNG & GIF files are allowed,";
		$imageUploadOk = 0;
}

//if the upload fails at any point then exit
if($imageUploadOk == 0)
{
	$message = $message . " The image file could not be uploaded ";
	print json_encode($message);
	exit();
}
//else if the upload is ok then carry out this
else if($imageDefault == 0)
{
		//upload file to target directory in the file host
		if(move_uploaded_file($_FILES["ChooseThumbnailSubmit"]["tmp_name"], $imageTarget_file))
		{
			//save the image path to a variable
      $imagePath = $imageDB_dir . $_FILES["ChooseThumbnailSubmit"]["name"];
			$message = "The image file " . basename( $_FILES["ChooseThumbnailSubmit"]["name"]) . " has been uploaded. ";
		}
		//if the upload fails then report an error
		else
		{
			$message = "Sorry, there was an error uploading the image file";
			$imageUploadOK = 0;
		}

}
//Video file uploader section
//Find if file is populated with a video
if(isset($_FILES["ChooseFileSubmit"]["name"]))
{
	//check if the image file is populated
	$check = filesize($_FILES["ChooseFileSubmit"]["tmp_name"]);
	//if it is populated return ok
	if($check !== false)
	{
		$videoUploadOk = 1;
	}
	//if not return false and stop the upload
	else
	{
		$message = "Wrong or no file attached please upload one";
		$videoUploadOk = 0;
	}
}
//if no file was uploaded
else
{
	$message = $message . " Please select a video file";
	$videoUploadOk = 0;
}
//If the video file already exists
if(file_exists($videoTarget_file))
{
	$message = $message . " Sorry, video file already exists,";
	$videoUploadOk = 0;
}
//a limit on the file size (max 8MB)
if($_FILES["ChooseFileSubmit"]["size"] > 8000000)
{
	$message = $message . " Sorry, your video file size is too large,";
	$videoUploadOk = 0;
}
//check the file has the right type of format which is mp4 and mp3 in this case
if($videoFileType != "mp4")
{
		$message = $message ." Sorry only mp4 files are allowed,";
		$videoUploadOk = 0;
}
//if the upload fails at any point then exit
if($videoUploadOk == 0)
{
	$message = $message . " The video file could not be uploaded";
	if($imageDefault == 0)
	{
		unlink($imageTarget_file);
	}
	print json_encode($message);
	exit();
}
//else if the upload is ok then carry out this
else
{
		//upload file to target directory in the file host
		if(move_uploaded_file($_FILES["ChooseFileSubmit"]["tmp_name"], $videoTarget_file))
		{
			//save the video path to a variable
      $videoPath = $videoDB_dir . $_FILES["ChooseFileSubmit"]["name"];
			$message = $message . " The video file " . basename( $_FILES["ChooseFileSubmit"]["name"]) . " has been uploaded. ";
		}
		//if the upload fails then report an error
		else
		{
			$message = "Sorry, there was an error uploading the video file,";
			unlink($imageTarget_file);
			$videoUploadOK = 0;
		}
}

$stmt = $mysqli->prepare("INSERT INTO Videos (UserID, VideoFile, VName, VDescription, Thumbnail, Tags, Genre) VALUES(?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('issssss', $_SESSION['userID'], $videoPath, $vidName, $vidDescription, $imagePath, $tags, $genre);
$stmt->execute();
$stmt->close();

print json_encode($message);
?>
