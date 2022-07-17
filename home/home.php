<?php
session_start(['cookie_lifetime' => 24*60*60,]) or die("Cannot start the session. Are cookies enabled?");

$title = "Uworks.ca Home Page";
if (!isset($_SESSION["username"])) {
    $_SESSION['msg'] = "You have to log in first";
    header('location: ../pages/login.php');
} else {
    $name=$_SESSION["username"];
}

require_once "../common/header.php";
require_once "navigation1.php";
require_once "uworks_resources.php";
require_once "../common/footer.php";


