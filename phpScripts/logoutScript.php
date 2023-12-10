<?php
session_start();
session_destroy();
session_start();
$_SESSION['message'] = "You have been successfully logged out";
header("Location: ../pages/homepage");
?>
