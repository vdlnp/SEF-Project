<?php
include "db.php";

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];

if (strlen($password) < 8) {
    echo "<script>alert('Password must be at least 8 characters'); window.location.href='index.php';</script>";
    exit;
}

$check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
if (mysqli_num_rows($check) > 0) {
    echo "<script>alert('Email already registered'); window.location.href='index.php';</script>";
    exit;
}

$sql = "INSERT INTO users (name, email, password, status)
        VALUES ('$name', '$email', '$password', 'pending')";

mysqli_query($conn, $sql);

echo "<script>
alert('Registration successful! Await admin activation.');
window.location.href='index.php';
</script>";
