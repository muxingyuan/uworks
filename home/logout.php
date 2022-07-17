<?php
// lifetime: 1 day (24 hours/day, 60 minutes/hour, and 60 seconds/minute)
session_start(['cookie_lifetime' => 24*60*60,]) or die("Cannot start the session. Are cookies enabled?");

require_once "../common/functions_defs.php";
$title = "Logout";
require_once "../common/header.php";

session_unset();
session_destroy();
echo "<h3>You have been logged out!</h3>";

echo '<br><a href="../pages/uworks_landing.php">go back to Uworks.ca</a>';
require_once "../common/footer.php";