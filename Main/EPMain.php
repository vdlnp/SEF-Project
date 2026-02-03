<?php
session_start();
include "db.php";

/* =========================
   1. AUTH & ROOM CHECK
========================= */
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'Executive Approver') {
    header("Location: login.php");
    exit;
}

$logged_in_room = $_SESSION['user']['room_code'] ?? '';
if (empty($logged_in_room)) {
    die("No room assigned to your account.");
}

/* =========================
   2. HANDLE APPROVE / DECLINE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'] ?? '';
    $proposalId = (int)($_POST['proposalId'] ?? 0);

    // Debug logging
    error_log("Action received: " . $action);
    error_log("Proposal ID: " . $proposalId);

    if (!in_array($action, ['approve', 'decline'])) {
        echo "Invalid action: " . $action;
        exit;
    }

    $status = ($action === 'approve') ? 'Approved' : 'Rejected';
    error_log("Status to set: " . $status);

    // Verify proposal belongs to same room
    $check = $conn->prepare("SELECT room_code FROM proposals WHERE id = ?");
    $check->bind_param("i", $proposalId);
    $check->execute();
    $check->bind_result($proposal_room);

    if (!$check->fetch()) {
        echo "Proposal not found";
        exit;
    }
    $check->close();

    if ($proposal_room !== $logged_in_room) {
        echo "Unauthorized";
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE proposals 
        SET status = ?, reviewed_at = NOW() 
        WHERE id = ?
    ");
    $stmt->bind_param("si", $status, $proposalId);

    error_log("Executing UPDATE with status='$status' for id=$proposalId");
    
    if ($stmt->execute()) {
        error_log("UPDATE successful. Rows affected: " . $stmt->affected_rows);
        echo "success";
    } else {
        error_log("UPDATE failed: " . $stmt->error);
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    
    // Verify what was actually saved
    $verify = $conn->prepare("SELECT status FROM proposals WHERE id = ?");
    $verify->bind_param("i", $proposalId);
    $verify->execute();
    $verify->bind_result($saved_status);
    $verify->fetch();
    error_log("Status after UPDATE: " . ($saved_status ?? 'NULL'));
    $verify->close();
    
    exit;
}

/* =========================
   3. FETCH PROPOSALS
========================= */
$query = "
    SELECT p.*, u.name AS sender_name
    FROM proposals p
    JOIN users u ON p.user_id = u.id
    WHERE p.room_code = ?
    ORDER BY p.submitted_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $logged_in_room);
$stmt->execute();
$result = $stmt->get_result();
$proposals = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Pending count
$pendingCount = count(array_filter($proposals, fn($p) => $p['status'] === 'Under Review'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Executive Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'Poppins', sans-serif;
    background:#1f1d29;
    color:#e6e6e6;
    min-height:100vh;
    display:flex;
    flex-direction:column;
}

/* Header */
header {
    background:#1abc9c;
    padding:16px 32px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 4px 8px rgba(0,0,0,0.3);
}
header h1 { font-size:22px; }

.logout {
    text-decoration:none;
    color:white;
    font-weight:600;
    border:1px solid rgba(255,255,255,0.6);
    padding:6px 16px;
    border-radius:20px;
    transition:0.3s;
}
.logout:hover {
    background:#2e1b36;
    box-shadow:0 0 15px rgba(26,188,156,0.7);
    transform:scale(1.05);
}

/* Container */
.container {
    max-width:1000px;
    width:95%;
    margin:30px auto;
    flex:1;
}

.top-info {
    display:flex;
    gap:10px;
    margin-bottom:20px;
}

.pending-count {
    background:#fbbf24;
    color:#78350f;
    padding:6px 16px;
    border-radius:20px;
    font-size:13px;
    font-weight:600;
}

.room-code {
    background:#1abc9c;
    color:white;
    padding:6px 16px;
    border-radius:20px;
    font-size:13px;
    font-weight:600;
}

/* Cards */
.submissions-list {
    display:flex;
    flex-direction:column;
    gap:20px;
}

.submission-card {
    background:#2c2a38;
    padding:24px;
    border-radius:14px;
    box-shadow:0 6px 15px rgba(0,0,0,0.4);
    transition:0.3s;
}

.submission-card:hover {
    transform:translateY(-4px) scale(1.02);
    background:linear-gradient(135deg,#1abc9c,#2c2a38);
}

.submission-title {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:10px;
}

.submission-title h3 { font-size:18px; font-weight:500; }

.status-badge {
    padding:4px 12px;
    border-radius:20px;
    font-size:12px;
    text-transform:uppercase;
    font-weight:500;
}
.status-under-review { background:#fbbf24; color:#78350f; }
.status-approved { background:#34d399; color:#064e3b; }
.status-declined { background:#f87171; color:#7f1d1d; }
.status-rejected { background:#f87171; color:#7f1d1d; }

.submission-meta {
    font-size:13px;
    color:#cfcfcf;
    margin-bottom:12px;
}

.submission-description {
    font-size:14px;
    color:#b0b8c4;
    margin-bottom:15px;
    line-height:1.5;
}

/* Attachments */
.attachments h4 { font-size:14px; margin-bottom:6px; }
.attachments a {
    font-size:13px;
    color:#1abc9c;
    text-decoration:none;
}
.attachments a:hover { text-decoration:underline; }

/* Actions */
.submission-actions {
    display:flex;
    gap:10px;
    margin-top:10px;
}

.action-btn {
    flex:1;
    padding:10px;
    border-radius:8px;
    border:none;
    font-size:14px;
    font-weight:500;
    cursor:pointer;
    transition:0.3s;
    color:white;
}

.approve-btn { background:#10b981; }
.reject-btn { background:#ef4444; }

.processed { opacity:0.6; }
.processed .submission-actions {
    pointer-events:none;
    opacity:0.5;
}

/* Footer */
footer {
    background:#161421;
    text-align:center;
    padding:14px;
    color:#bbb;
    font-size:14px;
}
</style>
</head>

<body>

<header>
    <h1>Executive Approver Dashboard</h1>
    <a href="index.php" class="logout">Logout</a>
</header>

<div class="container">

    <div class="top-info">
        <span class="pending-count"><?= $pendingCount ?> Pending</span>
        <span class="room-code">Room: <?= htmlspecialchars($logged_in_room) ?></span>
    </div>

    <div class="submissions-list">
        <?php if (empty($proposals)): ?>
            <div class="submission-card">No proposals submitted.</div>
        <?php else: ?>
            <?php foreach ($proposals as $p): ?>
                <div class="submission-card <?= $p['status'] !== 'Under Review' ? 'processed' : '' ?>">
                    <div class="submission-title">
                        <h3><?= htmlspecialchars($p['title']) ?></h3>
                        <span class="status-badge status-<?= strtolower(str_replace(' ','-',$p['status'])) ?>">
                            <?= $p['status'] ?>
                        </span>
                    </div>

                    <p class="submission-meta">
                        Submitted by <?= htmlspecialchars($p['sender_name']) ?>
                        • <?= date('M d, Y', strtotime($p['submitted_at'])) ?>
                    </p>

                    <p class="submission-description">
                        <?= nl2br(htmlspecialchars($p['description'])) ?>
                    </p>

                    <?php if (!empty($p['file_path'])): ?>
                        <div class="attachments">
                            <h4>Attachment:</h4>
                            <a href="uploads/proposals/<?= htmlspecialchars($p['file_path']) ?>" target="_blank">
                                📎 <?= htmlspecialchars($p['file_path']) ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($p['status'] === 'Under Review'): ?>
                        <div class="submission-actions">
                            <button class="action-btn approve-btn"
                                onclick="handleAction(<?= $p['id'] ?>,'approve')">
                                Approve
                            </button>
                            <button class="action-btn reject-btn"
                                onclick="handleAction(<?= $p['id'] ?>,'decline')">
                                Decline
                            </button>
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
    if (!confirm(`Are you sure you want to ${action} this proposal?`)) return;

    const fd = new FormData();
    fd.append('proposalId', id);
    fd.append('action', action);

    fetch('EPMain.php', { method:'POST', body:fd })
        .then(r => r.text())
        .then(r => {
            if (r === 'success') {
                location.reload();
            } else {
                alert('Response: ' + r);
            }
        })
        .catch(err => alert('Error: ' + err));
}
</script>

</body>
</html>