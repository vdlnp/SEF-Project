<?php
session_start();

// Clear all session data
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to login page (change this if your login page is named differently)
header("Location: index.php");
exit();
