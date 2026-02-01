<?php
$conn = mysqli_connect("localhost", "root", "", "sef_project");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
