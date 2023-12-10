<?php

include_once("DBConnect.php");
$response = $_POST['response'];

//update corresponding user in table to original setting
$stmt = $mysqli->prepare("UPDATE Users SET Upgrade = ? WHERE UserID = ?");
$stmt->bind_param('i', $response);
$stmt->execute();
$stmt->close();

exit();
?>
