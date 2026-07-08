<?php
session_start();
session_unset();
session_destroy();
header("Location: landing-page.php"); // relative path within users/
exit;
?>
