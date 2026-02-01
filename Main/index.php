<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Project Bidding System - Login</title>

<style>
    body {
        margin: 0;
        font-family: "Segoe UI", Arial, sans-serif;
        background: #1f1d29;
        color: #e6e6e6;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    header {
        background: #1abc9c;
        color: white;
        padding: 16px 32px;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .container {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .auth-box {
        background: #2c2a38;
        padding: 40px;
        border-radius: 14px;
        width: 100%;
        max-width: 450px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.6);
    }

    .tabs {
        display: flex;
        border-bottom: 2px solid #444;
        margin-bottom: 30px;
    }

    .tab {
        flex: 1;
        text-align: center;
        padding: 12px;
        cursor: pointer;
        font-weight: 600;
        color: #cfcfcf;
        border-bottom: 3px solid transparent;
    }

    .tab.active {
        color: #1abc9c;
        border-bottom: 3px solid #1abc9c;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    h2 {
        color: #1abc9c;
        text-align: center;
        margin-bottom: 25px;
    }

    .form-group {
        margin-bottom: 18px;
    }

    label {
        display: block;
        margin-bottom: 6px;
        font-size: 14px;
        font-weight: 600;
    }

    input {
        width: 100%;
        padding: 12px;
        background: #1f1d29;
        border: 1px solid #444;
        border-radius: 6px;
        color: #e6e6e6;
        font-size: 14px;
    }

    .btn {
        width: 100%;
        background: #1abc9c;
        color: white;
        padding: 14px;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn:hover {
        background: #16a085;
    }

    .note {
        text-align: center;
        margin-top: 18px;
        font-size: 13px;
        color: #aaa;
    }

    .msg {
        margin-bottom: 15px;
        font-size: 14px;
        text-align: center;
        color: #ff6b6b;
        display: none;
    }

    footer {
        background: #161421;
        text-align: center;
        padding: 14px;
        font-size: 14px;
        color: #bbb;
    }
</style>

<script>
    function switchTab(tab) {
        document.getElementById("loginTab").classList.remove("active");
        document.getElementById("registerTab").classList.remove("active");
        document.getElementById("loginBtn").classList.remove("active");
        document.getElementById("registerBtn").classList.remove("active");

        document.getElementById(tab + "Tab").classList.add("active");
        document.getElementById(tab + "Btn").classList.add("active");
    }
</script>
</head>

<body>

<header>
    <h1>Project Bidding System</h1>
</header>

<div class="container">
    <div class="auth-box">

        <div class="tabs">
            <div id="loginBtn" class="tab active" onclick="switchTab('login')">Login</div>
            <div id="registerBtn" class="tab" onclick="switchTab('register')">Register</div>
        </div>

        <!-- LOGIN TAB -->
        <div id="loginTab" class="tab-content active">
            <h2>Welcome Back</h2>

            <div class="msg">Invalid email or password</div>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="your@email.com" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>

                <div class="form-group">
                    <label>Room Code</label>
                    <input type="text" name="room_code" placeholder="Enter your assigned room code" style="text-transform: uppercase;">
                    <div class="helper-text">Room code is assigned by the Content Coordinator. Admins can leave this blank.</div>
                </div>

                <button type="submit" class="btn">Login</button>
            </form>
        </div>

        <!-- REGISTER TAB -->
        <div id="registerTab" class="tab-content">
            <h2>Create Account</h2>

            <form method="POST" action="register.php">
                <div class="form-group">
                    <label>Full Name / Company Name</label>
                    <input type="text" name="name" placeholder="John Doe or ABC Company" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="your@email.com" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn">Register</button>

                <div class="note">
                    After registration, the Content Coordinator will assign your role and room code.
                </div>
            </form>
        </div>

    </div>
</div>

<footer>
    Project Bidding System | Secure Access Portal
</footer>

</body>
</html>
