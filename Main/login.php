<?php
session_start();
include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $roomCode = strtoupper($_POST['room_code'] ?? '');

    $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        $error = "Invalid email or password";
    } elseif ($user['role'] === 'Admin' || $user['role'] === 'Content Coordinator') {
        $_SESSION['user'] = $user;
        header("Location: AdminMain.php");
        exit;
    } elseif ($user['status'] !== 'active' || empty($user['room_code'])) {
        $error = "Account pending activation. Please wait for admin approval.";
    } elseif ($roomCode !== $user['room_code']) {
        $error = "Invalid room code for your account";
    } else {
        $_SESSION['user'] = $user;

        switch ($user['role']) {
            case 'Project Lead':
                header("Location: PLmain.php");
                break;
            case 'Reviewer':
                header("Location: ReviewerMain.php");
                break;
            case 'Executive Approver':
                header("Location: ApproverMain.php");
                break;
            default:
                $error = "Invalid role assigned";
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - Project Bidding System</title>
<style>
body {
    margin: 0;
    font-family: "Segoe UI", Arial, sans-serif;
    background: #1f1d29;
    color: #e6e6e6;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

.login-container {
    background: #2c2a38;
    padding: 40px;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.6);
    width: 90%;
    max-width: 400px;
}

h2 {
    color: #1abc9c;
    text-align: center;
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    color: #cfcfcf;
}

input {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: none;
    background: #1f1d29;
    color: #e6e6e6;
    box-sizing: border-box;
}

input::placeholder {
    color: #888;
}

.btn {
    width: 100%;
    background: #1abc9c;
    border: none;
    padding: 12px 20px;
    color: white;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s ease;
}

.btn:hover {
    background: #16a085;
    transform: translateY(-2px);
}

.error {
    background: #f87171;
    color: white;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 20px;
    text-align: center;
}

.register-link {
    text-align: center;
    margin-top: 20px;
    color: #cfcfcf;
}

.register-link a {
    color: #1abc9c;
    text-decoration: none;
}

.register-link a:hover {
    text-decoration: underline;
}

.info-note {
    background: #3498db;
    color: white;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 20px;
    text-align: center;
    font-size: 14px;
}
</style>
</head>
<body>

<div class="login-container">
    <h2>Project Bidding System</h2>
    
    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="info-note">
        💡 Non-admin users need a room code to login
    </div>
    
    <form method="POST" action="login.php">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required placeholder="Enter your email">
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Enter your password">
        </div>
        
        <div class="form-group">
            <label>Room Code (Leave blank if Admin)</label>
            <input type="text" name="room_code" placeholder="Enter room code" style="text-transform: uppercase;">
        </div>
        
        <button type="submit" class="btn">Login</button>
    </form>
    
    <div class="register-link">
        Don't have an account? <a href="register.php">Register here</a>
    </div>
</div>

</body>
</html>