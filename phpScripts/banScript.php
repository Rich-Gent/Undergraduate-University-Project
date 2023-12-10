<?php

include_once("DBConnect.php");
$response = $_POST['response'];
$banned = "1";
$resolved = "1";

//update corresponding flagged video in table to be banned from viewing
$stmt = $mysqli->prepare("UPDATE Videos SET Banned = ? WHERE VideoID = ?");
$stmt->bind_param('ii', $banned, $response);
$stmt->execute();
$stmt->close();

//update corresponding flagged video in table to resolved
$stmt = $mysqli->prepare("UPDATE FlaggedVids SET Resolved = ? WHERE VideoID = ?");
$stmt->bind_param('ii', $resolved, $response);
$stmt->execute();
$stmt->close();

exit();
?>
