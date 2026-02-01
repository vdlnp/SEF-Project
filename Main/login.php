<?php
session_start();
include "db.php";

$email = $_POST['email'];
$password = $_POST['password'];
$roomCode = strtoupper($_POST['room_code'] ?? '');

$sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo "<script>alert('Invalid email or password'); window.location.href='index.php';</script>";
    exit;
}

if ($user['role'] === 'Admin') {
    $_SESSION['user'] = $user;
    header("Location: AdminMain.php");
    exit;
}

if ($user['status'] !== 'active' || empty($user['room_code'])) {
    echo "<script>alert('Account pending activation'); window.location.href='index.php';</script>";
    exit;
}

if ($roomCode !== $user['room_code']) {
    echo "<script>alert('Invalid room code'); window.location.href='index.php';</script>";
    exit;
}

$_SESSION['user'] = $user;

switch ($user['role']) {
    case 'Project Lead':
        header("Location: PLmain.html");
        break;
    case 'Reviewer':
        header("Location: ReviewerMain.html");
        break;
    case 'Executive Approver':
        header("Location: ApproverMain.html");
        break;
    default:
        echo "Invalid role";
}
