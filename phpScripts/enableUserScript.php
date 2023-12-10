<?php

include_once("DBConnect.php");
$response = $_POST['response'];
$revert = "0";
$accessLevel = "2";

//update corresponding user in table to access level 2 and revert upgrade back to 0
$stmt = $mysqli->prepare("UPDATE Users SET AccessLevel =?, Upgrade = ? WHERE UserID = ?");
$stmt->bind_param('iii', $accessLevel, $revert, $response);
$stmt->execute();
$stmt->close();

exit();
?>
