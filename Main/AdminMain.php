<?php
include "db.php"; // $conn defined here

// Handle AJAX actions first
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1️⃣ Create Project
    if ($action === 'createProject') {
        $title = $_POST['title'] ?? '';
        $desc = $_POST['description'] ?? '';
        $deadline = $_POST['deadline'] ?? '';
        $roomCode = $_POST['roomCode'] ?? '';

        $stmt = $conn->prepare("INSERT INTO project (title, description, deadline, room_code) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $desc, $deadline, $roomCode);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'Error: ' . $conn->error;
        }
        exit;
    }

    // 2️⃣ Assign Role to Pending User
    if ($action === 'assignRole') {
        $userId = $_POST['userId'] ?? '';
        $role = $_POST['role'] ?? '';
        $room = $_POST['room'] ?? '';

        $stmt = $conn->prepare("UPDATE users SET role=?, room_code=?, status='active' WHERE id=?");
        $stmt->bind_param("ssi", $role, $room, $userId);


        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'Error: ' . $conn->error;
        }
        exit;
    }

    // 3️⃣ Remove User (reset role & room)
    if ($action === 'removeUser') {
        $userId = $_POST['userId'] ?? '';

        $stmt = $conn->prepare("UPDATE users SET role=NULL, room_code=NULL WHERE id=?");
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'Error: ' . $conn->error;
        }
        exit;
    }
}

// -------------------------
// Fetch data for dashboard
// -------------------------

// Fetch all projects
$result = mysqli_query($conn, "SELECT * FROM project");
$projects = mysqli_fetch_all($result, MYSQLI_ASSOC);

$result = mysqli_query($conn, "SELECT * FROM users WHERE role IS NOT NULL AND role != '' AND role != 'Content Coordinator'");
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);


// Fetch pending users for role assignment
$result = mysqli_query($conn, "SELECT * FROM users WHERE role IS NULL OR role=''");
$pendingUsers = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<style>
/* Keep your existing CSS from your previous file */
body { margin:0; font-family:"Segoe UI",Arial,sans-serif; background:#1f1d29; color:#e6e6e6;}
header {background:#1abc9c; color:white; padding:16px 32px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 8px rgba(0,0,0,0.3);}
header h1 {margin:0; font-size:22px;}
.logout {text-decoration:none;color:white;font-weight:600;border:1px solid rgba(255,255,255,0.6);padding:6px 16px;border-radius:20px;transition:all 0.3s ease;}
.logout:hover {background:#2e1b36;box-shadow:0 0 15px rgba(26,188,156,0.7);transform:scale(1.05);}
.container {min-height:calc(100vh - 140px); display:flex; flex-direction:column; align-items:center; justify-content:center; padding:40px 20px;}
.cards {display:flex; gap:40px; justify-content:center; flex-wrap:wrap;}
.card {background:#2c2a38;padding:46px 80px;border-radius:14px;box-shadow:0 6px 15px rgba(0,0,0,0.5);font-size:18px;font-weight:600;text-align:center;color:#1abc9c;cursor:pointer;transition:all 0.4s ease;}
.card:hover {transform:translateY(-8px) scale(1.05); color:white; background:linear-gradient(135deg, #1abc9c, #2e1b36); box-shadow:0 12px 30px rgba(0,0,0,0.8),0 0 20px rgba(26,188,156,0.5);}
.modal {display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.7); overflow-y:auto;}
.modal-content {background-color:#2c2a38; margin:3% auto; padding:30px; border-radius:14px; width:90%; max-width:700px; box-shadow:0 8px 20px rgba(0,0,0,0.6);}
.close {color:#aaa; float:right; font-size:28px; font-weight:bold; cursor:pointer;}
.close:hover {color:#1abc9c;}
.modal h2 {color:#1abc9c; margin-top:0; margin-bottom:25px;}
.form-group {margin-bottom:15px;}
label {display:block; margin-bottom:6px;}
input, textarea, select {width:100%; padding:10px 12px; background:#1f1d29; border:1px solid #444; color:white; border-radius:6px; font-size:14px;}
input::placeholder, textarea::placeholder, select option[value=""] {color:#bbb; font-style:italic;}
textarea {min-height:80px;}
input[type="date"]::-webkit-calendar-picker-indicator {filter: invert(1); cursor:pointer;}
.btn {background:#1abc9c; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-weight:bold; margin-right:10px;}
.btn-danger {background:#e74c3c;}
.btn-info {background:#3498db;}
.btn-secondary {background:#6c757d;}
.btn:hover {opacity:0.85;}
table {width:100%; border-collapse:collapse; margin-top:20px;}
th {background:#1abc9c; padding:10px;}
td {padding:10px; border-bottom:1px solid #444;}
.room-code {background:#1abc9c; padding:4px 10px; border-radius:4px; font-family:monospace;}
footer {background:#161421; text-align:center; padding:12px;}
</style>
</head>
<body>

<header>
    <h2>Admin Dashboard</h2>
    <a href="index.php" class="logout">Logout</a>
</header>

<div class="container">
    <div class="cards">
        <div class="card" onclick="openProjectModal()">Create Project Opening</div>
        <div class="card" onclick="openUserModal()">Manage Users</div>
    </div>
</div>

<!-- CREATE PROJECT MODAL -->
<div id="projectModal" class="modal">
<div class="modal-content">
<span class="close" onclick="closeModal('projectModal')">&times;</span>
<h2>Create Project Opening</h2>

<form id="projectForm">
    <div class="form-group">
        <label>Project Title *</label>
        <input type="text" id="projectTitle" placeholder="Enter project title..." required>
    </div>
    <div class="form-group">
        <label>Project Description *</label>
        <textarea id="projectDesc" placeholder="Enter project description..." required></textarea>
    </div>
    <div class="form-group">
        <label>Deadline *</label>
        <input type="date" id="projectDeadline" required>
    </div>
    <div class="form-group">
        <label>Room Code (Auto-generated)</label>
        <input type="text" id="roomCode" readonly>
    </div>

    <button type="button" class="btn btn-info" onclick="generateRoomCode()">Generate Room Code</button>
    <button type="submit" class="btn">Create Project</button>
</form>

<div id="projectTable">
<?php if(count($projects) > 0): ?>
<table>
<tr><th>Title</th><th>Deadline</th><th>Room Code</th></tr>
<?php foreach($projects as $p): ?>
<tr>
<td><?= htmlspecialchars($p['title']) ?></td>
<td><?= $p['deadline'] ?></td>
<td><span class="room-code"><?= $p['room_code'] ?></span></td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p>No projects yet</p>
<?php endif; ?>
</div>
</div>
</div>

<!-- MANAGE USERS MODAL -->
<div id="userModal" class="modal">
<div class="modal-content">
<span class="close" onclick="closeModal('userModal')">&times;</span>
<h2>Manage Users</h2>

<form id="userForm">
    <div class="form-group">
        <label>Select User *</label>
        <select id="userSelect" required>
            <option value="">Select user...</option>
            <?php foreach($pendingUsers as $u): ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Role *</label>
        <select id="userRole" required>
            <option value="">Select role...</option>
            <option>Project Lead</option>
            <option>Reviewer</option>
            <option>Executive Approver</option>
        </select>
    </div>

    <div class="form-group">
        <label>Project Room *</label>
        <select id="userRoom" required>
            <option value="">Select project...</option>
            <?php foreach($projects as $p): ?>
            <option value="<?= $p['room_code'] ?>"><?= $p['title'] ?> (<?= $p['room_code'] ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>

    <button class="btn">Assign Role</button>
</form>

<div id="userTable">
<?php if(count($users) > 0): ?>
<table>
<tr><th>Name</th><th>Email</th><th>Role</th><th>Room</th><th>Action</th></tr>
<?php foreach($users as $u): ?>
<tr>
<td><?= htmlspecialchars($u['name']) ?></td>
<td><?= htmlspecialchars($u['email']) ?></td>
<td><?= $u['role'] ?></td>
<td><span class="room-code"><?= $u['room_code'] ?></span></td>
<td>
<button class="btn btn-danger" onclick="removeUser(<?= $u['id'] ?>)">Remove</button>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p>No users added</p>
<?php endif; ?>
</div>
</div>
</div>

<footer>
Project Bidding System | Admin Module
</footer>

<script>
function generateRoomCode() {
    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    let code = "";
    for (let i = 0; i < 8; i++) code += chars[Math.floor(Math.random() * chars.length)];
    document.getElementById('roomCode').value = code;
}

document.getElementById('projectForm').onsubmit = function(e){
    e.preventDefault();
    const formData = new FormData();
    formData.append('action','createProject');
    formData.append('title', projectTitle.value);
    formData.append('description', projectDesc.value);
    formData.append('deadline', projectDeadline.value);
    formData.append('roomCode', roomCode.value);

    fetch('AdminMain.php',{method:'POST', body: formData})
    .then(res=>res.text())
    .then(res=>{
        if(res==='success'){ alert('Project created'); location.reload(); }
        else alert('Error: ' + res);
    });
};

document.getElementById('userForm').onsubmit = function(e){
    e.preventDefault();
    const formData = new FormData();
    formData.append('action','assignRole');
    formData.append('userId', userSelect.value);
    formData.append('role', userRole.value);
    formData.append('room', userRoom.value);

    fetch('AdminMain.php',{method:'POST', body: formData})
    .then(res=>res.text())
    .then(res=>{
        if(res==='success'){ alert('User role assigned'); location.reload(); }
        else alert('Error: ' + res);
    });
};

function removeUser(userId) {
    if(!confirm('Are you sure you want to remove this user?')) return;
    const formData = new FormData();
    formData.append('action', 'removeUser');
    formData.append('userId', userId);

    fetch('AdminMain.php',{method:'POST', body: formData})
    .then(res=>res.text())
    .then(res=>{
        if(res==='success'){ alert('User removed'); location.reload(); }
        else alert('Error: ' + res);
    });
}

// Modal functions
function openProjectModal(){document.getElementById('projectModal').style.display='block'}
function openUserModal(){document.getElementById('userModal').style.display='block'}
function closeModal(id){document.getElementById(id).style.display='none'}
window.onclick = e => { if(e.target.classList.contains('modal')) e.target.style.display='none'; }
</script>

</body>
</html>
