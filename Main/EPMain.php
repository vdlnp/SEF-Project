<?php
include "db.php"; // $conn defined here

// --- 1. SET THE LOGGED-IN ROOM ---
// Simulating the room assigned to the logged-in EP
$logged_in_room = 'CYBER99'; 

// --- 2. HANDLE APPROVAL / REJECTION ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $proposalId = $_POST['proposalId'];
    $feedback = $conn->real_escape_string($_POST['feedback'] ?? '');

    $status = ($action === 'approve') ? 'Approved' : 'Rejected';

    $sql = "UPDATE proposals SET status='$status', reviewer_feedback='$feedback', reviewed_at=NOW() WHERE id=$proposalId";
    
    if ($conn->query($sql)) {
        echo "success";
    } else {
        echo "Error: " . $conn->error;
    }
    exit;
}

// --- 3. FETCH PROPOSALS ONLY FOR THIS ROOM ---
$query = "SELECT p.*, u.name as sender_name 
          FROM proposals p 
          JOIN users u ON p.user_id = u.id 
          WHERE p.room_code = '$logged_in_room'
          ORDER BY p.submitted_at DESC";

$result = $conn->query($query);
$proposals = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Executive Dashboard</title>
<style>
/* Exact styles from original Admin/EP HTML */
body { margin:0; font-family:"Segoe UI",Arial,sans-serif; background:#1f1d29; color:#e6e6e6;}
header {background:#1abc9c; color:white; padding:16px 32px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 8px rgba(0,0,0,0.3);}
header h2 {margin:0; font-size:22px;}
.logout {text-decoration:none;color:white;font-weight:600;border:1px solid rgba(255,255,255,0.6);padding:6px 16px;border-radius:20px;transition:all 0.3s ease;}
.logout:hover {background:#2e1b36;box-shadow:0 0 15px rgba(26,188,156,0.7);transform:scale(1.05);}
.container {min-height:calc(100vh - 140px); display:flex; flex-direction:column; align-items:center; padding:40px 20px;}

/* Card Style for Proposals */
.proposal-list { width: 100%; max-width: 900px; }
.card {background:#2c2a38; padding:30px; border-radius:14px; box-shadow:0 6px 15px rgba(0,0,0,0.5); margin-bottom: 25px; border-left: 5px solid #1abc9c;}
.card h4 { color:#1abc9c; margin-top:0; font-size: 20px; }

/* Status Badges */
.status-badge { padding:4px 12px; border-radius:20px; font-size:12px; font-weight:bold; text-transform:uppercase; float: right; }
.status-Under-Review { background: #f39c12; color: white; }
.status-Approved { background: #2ecc71; color: white; }
.status-Rejected { background: #e74c3c; color: white; }

/* Form Elements within Cards */
textarea {width:100%; padding:10px 12px; background:#1f1d29; border:1px solid #444; color:white; border-radius:6px; font-size:14px; margin: 15px 0; box-sizing: border-box;}
.btn {background:#1abc9c; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-weight:bold; margin-right:10px;}
.btn-danger {background:#e74c3c;}
.btn:hover {opacity:0.85;}
.room-code {background:#1abc9c; padding:4px 10px; border-radius:4px; font-family:monospace; font-size: 14px;}

footer {background:#161421; text-align:center; padding:12px; margin-top: auto;}
</style>
</head>
<body>

<header>
    <h2>Executive Dashboard</h2>
    <a href="index.php" class="logout">Logout</a>
</header>

<div class="container">
    <div class="proposal-list">
        <h3 style="text-align:center; margin-bottom: 30px;">Proposals for Room: <span class="room-code"><?= htmlspecialchars($logged_in_room) ?></span></h3>

        <?php if(empty($proposals)): ?>
            <div class="card" style="text-align:center;">No proposals submitted for this room yet.</div>
        <?php else: ?>
            <?php foreach($proposals as $p): ?>
                <div class="card">
                    <span class="status-badge status-<?= str_replace(' ', '-', $p['status']) ?>">
                        <?= $p['status'] ?>
                    </span>
                    <h4><?= htmlspecialchars($p['title']) ?></h4>
                    <p><strong>Submitted by:</strong> <?= htmlspecialchars($p['sender_name']) ?></p>
                    <p style="color: #bbb;"><?= nl2br(htmlspecialchars($p['description'])) ?></p>
                    
                    <?php if ($p['status'] === 'Under Review'): ?>
                        <textarea id="feedback-<?= $p['id'] ?>" placeholder="Enter reviewer feedback..."></textarea>
                        <button class="btn" onclick="handleAction(<?= $p['id'] ?>, 'approve')">Approve</button>
                        <button class="btn btn-danger" onclick="handleAction(<?= $p['id'] ?>, 'reject')">Reject</button>
                    <?php else: ?>
                        <div style="background: #1f1d29; padding: 10px; border-radius: 6px; margin-top: 10px;">
                            <strong>Feedback:</strong> <?= htmlspecialchars($p['reviewer_feedback'] ?: 'No feedback provided.') ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<footer>
Project Bidding System | Executive Module
</footer>

<script>
function handleAction(id, action) {
    const feedback = document.getElementById('feedback-' + id).value;
    if (action === 'reject' && !feedback) {
        alert("Please provide feedback for rejection.");
        return;
    }

    const formData = new FormData();
    formData.append('action', action);
    formData.append('proposalId', id);
    formData.append('feedback', feedback);

    fetch('EPMain.php', { method: 'POST', body: formData })
    .then(res => res.text())
    .then(res => {
        if (res === 'success') {
            alert('Proposal ' + (action === 'approve' ? 'Approved' : 'Rejected'));
            location.reload();
        } else {
            alert(res);
        }
    });
}
</script>
</body>
</html>
