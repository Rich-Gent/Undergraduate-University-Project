<?php

include_once("DBConnect.php");
$response = $_POST['response'];
$resolved = "1";

//update corresponding flagged video in table to resolved
$stmt = $mysqli->prepare("UPDATE FlaggedVids SET Resolved = ? WHERE VideoID = ?");
$stmt->bind_param('ii', $resolved, $response);
$stmt->execute();
$stmt->close();

exit();
?>
