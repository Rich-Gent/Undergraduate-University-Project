<?php
require_once('DBConnect.php');
session_start();
header("Content-Type: application/json");
echo "HELLO";
$videoID = $_GET['vID'];

//fetch file path for the original video file and thumbnail image
$stmt = $mysqli->prepare("SELECT VideoFile, VName, VDescription, Thumbnail, Tags, Genre FROM Videos WHERE VideoID = ?");
$stmt->bind_param('i', $videoID);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($oldVideoPath, $oldVidName, $oldVidDescription, $oldImagePath, $oldTags, $oldGenre);
$stmt->fetch();
$stmt->close();
//../../../Video/Entertainment/Meme - WoW (UAU) Chroma Key - Editions.mp4
//find the old image and video directories and replace the location to where the source directory actually is
$oldImage_Dir = str_replace("../../../Images/VidThumbnails/", "", $oldImagePath);
$oldVideo_Dir = str_replace("../../../Video/", "", $oldVideoPath);
$videoFile = str_replace("../../../Video/$oldGenre/", "", $oldVideoPath);

//file paths and variable storage
$imageTarget_dir = "/home/u189228952/public_html/Images/VidThumbnails/";
$imageDB_dir = "../../../Images/VidThumbnails/";
$imageTarget_file = $imageTarget_dir . $_FILES["imageSubmit"]["name"];
$imageUploadOk = 1;
$imageDefault = 0;
$imageFileType = pathinfo($imageTarget_file,PATHINFO_EXTENSION);
$videoTarget_dir = "/home/u189228952/public_html/Video/";
$genre = $_POST["genreBox"];
$videoDB_dir = "../../../Video/" . $genre . "/" . $videoFile;
$videoTarget_file = $videoTarget_dir . $genre . "/" . $videoFile;
$videoUploadOk = 1;
$videoDefault= 0;
$vidName = $_POST["nameBox"];
$vidDescription = $_POST["descriptionBox"];
$tags = $_POST["tagsBox"];

//old video and image target directories
$oldImage_Dir = $imageTarget_dir . $oldImage_Dir;
$oldVideo_Dir = $videoTarget_dir . $oldVideo_Dir;

//if the video name already exist's in the database then ignore the user input
$stmt = $mysqli->prepare("SELECT VName FROM Videos WHERE VName = ?");
$stmt->bind_param("s", $vidName);
$stmt->execute();
$stmt->bind_result($existVidName);
$stmt->store_result();
$stmt->fetch();
if($stmt->num_rows > 0)
{
	if($existVidName == $vidName)
	{
		$message = $message . "Video name already exists please choose something different. ";
	}
	$vidName = $oldVidName;
}
//parameters for text box values. if any are null then revert to the previous data in the database
if($vidName == "")
{
	$vidName = $oldVidName;
}
if($vidDescription == "")
{
	$vidDescription = $oldVidDescription;
}
if($tags == "")
{
	$tags = $oldTags;
}
else
{
  $tags = $tags . ", " . $vidName . ", " . $vidDescription;
}
$message = "";

//Video thumbnail upload section
//check if image file is an actual image or not
if($_FILES["imageSubmit"]["name"] !== "")
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
			$message = "Thumbnail File is not an image please upload one, ";
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
	$message = $message . " Sorry, image file already exists Please rename it";
	$imageUploadOk = 0;
}

//a limit on the file size
if(($_FILES["imageSubmit"]["size"] > 5000000) && $imageDefault == 0)
{
	$message = $message . " Sorry, your image file size is too large. \n";
	$imageUploadOk = 0;
}

//check the file has the right type of format, .jpg .png .jpeg .gif
if(($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") && $imageDefault == 0)
{
		$message = $message . " Sorry only JPG, JPEG, PNG & GIF files are allowed. \n";
		$imageUploadOk = 0;
}

//if the upload fails at any point then ignore the upload and notify the user
if($imageUploadOk == 0)
{
	$message = $message . " The image file could not be uploaded. ";
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
			$message = $message . "The image file " . basename( $_FILES["imageSubmit"]["name"]) . " has been uploaded";
		}
		//if the upload fails then report an error
		else
		{
			$message = "Sorry, there was an error uploading the image file";
			$imageUploadOK = 0;
		}

}
//Video file change directory section
//if the old file path matches the new file path then ignore
if($videoDB_dir == $oldVideoPath)
{
  $videoUplodOk = 1;
  $videoDefault= 1;
  $videoPath = $oldVideoPath;
}

//If the video find the video file and move it to the new location chosen by the user
if(file_exists($oldVideo_Dir) && $videoDefault == 0)
{
  copy($oldVideo_Dir, $videoTarget_file);
  //delete the video in the previous folder it was in
  unlink($oldVideo_Dir);
  //record the new video path
  $videoPath = $videoDB_dir;
  $message = $message .  " The video has been relocated to the $genre genre.";
}
else
{
  $videoUploadOk == 0;
}
//if the upload fails at any point then exit
if($videoUploadOk == 0)
{
	$message = $message . " The video file could not be relocated";
}


$stmt = $mysqli->prepare("UPDATE Videos SET VideoFile = ?, VName = ?, VDescription = ?, Thumbnail = ?, Tags = ?, Genre = ? WHERE VideoID = ? ");
$stmt->bind_param('ssssssi', $videoPath, $vidName, $vidDescription, $imagePath, $tags, $genre, $videoID);
$stmt->execute();
$stmt->close();

$message = $message. " Changes were saved";
$_SESSION['message'] = $message;
header("Location: ../pages/vSettings/?vID=$videoID");
?>
