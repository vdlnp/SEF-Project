<?php
// 1. INCLUDE DATABASE CONNECTION
include "db.php"; // Uses your existing $conn

// 2. HANDLE FORM SUBMISSIONS (CREATE PROJECT)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'createProject') {
    $title = $conn->real_escape_string($_POST['title']);
    $desc = $conn->real_escape_string($_POST['description']);
    $deadline = $_POST['deadline'];
    $room = $_POST['room_code'];

    $sql = "INSERT INTO project (title, description, deadline, room_code) VALUES ('$title', '$desc', '$deadline', '$room')";
    if ($conn->query($sql)) {
        header("Location: EPMain.php?msg=ProjectCreated");
        exit();
    }
}

// 3. HANDLE FORM SUBMISSIONS (ADD USER)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'addUser') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $role = $_POST['role'];
    $room = $_POST['room'];
    // Default password for new users based on your SQL
    $pass = 'password123'; 

    $sql = "INSERT INTO users (name, email, password, role, room_code) VALUES ('$name', '$email', '$pass', '$role', '$room')";
    if ($conn->query($sql)) {
        header("Location: EPMain.php?msg=UserAdded");
        exit();
    }
}

// 4. FETCH DATA FOR TABLES
$projectResults = $conn->query("SELECT * FROM project ORDER BY id DESC");
$userResults = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body { margin: 0; font-family: "Segoe UI", Arial, sans-serif; background: #1f1d29; color: #e6e6e6; }
        header { background: #1abc9c; color: white; padding: 16px 32px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 8px rgba(0,0,0,0.3); }
        header h1 { margin: 0; font-size: 22px; }
        .logout { text-decoration: none; color: white; font-weight: 600; border: 1px solid rgba(255,255,255,0.6); padding: 6px 16px; border-radius: 20px; transition: all 0.3s ease; }
        .logout:hover { background: #2e1b36; box-shadow: 0 0 15px rgba(26,188,156,0.7); transform: scale(1.05); }
        .container { min-height: calc(100vh - 140px); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px; }
        .cards { display: flex; gap: 40px; justify-content: center; flex-wrap: wrap; }
        .card { background: #2c2a38; padding: 46px 80px; border-radius: 14px; box-shadow: 0 6px 15px rgba(0,0,0,0.5); font-size: 18px; font-weight: 600; text-align: center; color: #1abc9c; cursor: pointer; transition: all 0.4s ease; }
        .card:hover { transform: translateY(-8px) scale(1.05); color: white; background: linear-gradient(135deg, #1abc9c, #2e1b36); box-shadow: 0 12px 30px rgba(0,0,0,0.8), 0 0 20px rgba(26,188,156,0.5); }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); overflow-y: auto; }
        .modal-content { background-color: #2c2a38; margin: 3% auto; padding: 30px; border-radius: 14px; width: 90%; max-width: 700px; box-shadow: 0 8px 20px rgba(0,0,0,0.6); }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close:hover { color: #1abc9c; }
        .modal h2 { color: #1abc9c; margin-top: 0; margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 6px; }
        input, textarea, select { width: 100%; padding: 10px 12px; background: #1f1d29; border: 1px solid #444; color: white; border-radius: 6px; font-size: 14px; box-sizing: border-box;}
        textarea { min-height: 80px; }
        input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1); cursor: pointer; }
        .btn { background: #1abc9c; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; margin-right: 10px; margin-top: 5px; }
        .btn-info { background: #3498db; }
        .btn:hover { opacity: 0.85; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #1abc9c; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #444; }
        .room-code { background: #1abc9c; padding: 4px 10px; border-radius: 4px; font-family: monospace; }
        footer { background: #161421; text-align: center; padding: 12px; margin-top: 20px; }
    </style>
</head>

<body>

<header>
    <h2>Admin Dashboard</h2>
    <a href="logout.php" class="logout">Logout</a>
</header>

<div class="container">
    <div class="cards">
        <div class="card" onclick="openModal('projectModal')">Create Project Opening</div>
        <div class="card" onclick="openModal('userModal')">Manage Users</div>
    </div>
</div>

<div id="projectModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('projectModal')">&times;</span>
        <h2>Create Project Opening</h2>
        <form action="EPMain.php" method="POST">
            <input type="hidden" name="action" value="createProject">
            <div class="form-group">
                <label>Project Title *</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Project Description *</label>
                <textarea name="description" required></textarea>
            </div>
            <div class="form-group">
                <label>Deadline *</label>
                <input type="date" name="deadline" required>
            </div>
            <div class="form-group">
                <label>Room Code (Auto-generated)</label>
                <input type="text" id="roomCode" name="room_code" readonly required>
            </div>
            <button type="button" class="btn btn-info" onclick="generateRoomCode()">Generate Room Code</button>
            <button type="submit" class="btn">Create Project</button>
        </form>

        <h3>Existing Projects</h3>
        <table>
            <tr><th>Title</th><th>Deadline</th><th>Room Code</th></tr>
            <?php while($p = $projectResults->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($p['title']) ?></td>
                <td><?= $p['deadline'] ?></td>
                <td><span class="room-code"><?= $p['room_code'] ?></span></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<div id="userModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('userModal')">&times;</span>
        <h2>Manage Users</h2>
        <form action="EPMain.php" method="POST">
            <input type="hidden" name="action" value="addUser">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Role *</label>
                <select name="role" required>
                    <option value="">Select role...</option>
                    <option value="Project Lead">Project Lead</option>
                    <option value="Reviewer">Reviewer</option>
                    <option value="Executive Approver">Executive Approver</option>
                </select>
            </div>
            <div class="form-group">
                <label>Project Room *</label>
                <select name="room" required>
                    <option value="">Select project...</option>
                    <?php 
                    // Reset project pointer and populate dropdown
                    $projectResults->data_seek(0);
                    while($p = $projectResults->fetch_assoc()): ?>
                        <option value="<?= $p['room_code'] ?>"><?= htmlspecialchars($p['title']) ?> (<?= $p['room_code'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn">Add User</button>
        </form>

        <h3>Existing Users</h3>
        <table>
            <tr><th>Name</th><th>Email</th><th>Role</th><th>Room</th></tr>
            <?php while($u = $userResults->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td><span class="room-code"><?= $u['room_code'] ?></span></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<footer>Project Bidding System | Admin Module</footer>

<script>
function openModal(id) { document.getElementById(id).style.display = "block"; }
function closeModal(id) { document.getElementById(id).style.display = "none"; }
function generateRoomCode() {
    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    let code = "";
    for (let i = 0; i < 8; i++) { code += chars[Math.floor(Math.random() * chars.length)]; }
    document.getElementById('roomCode').value = code;
}
window.onclick = e => { if (e.target.classList.contains("modal")) e.target.style.display = "none"; };
</script>

</body>
</html>