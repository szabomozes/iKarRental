<?php
session_start();
$_SESSION["loged_in"] = false;
$_SESSION["user_id"] = null;

header("Location: mainpage.php");
exit();
?>