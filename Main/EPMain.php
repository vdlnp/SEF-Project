<?php
session_start();
include "db.php"; // $conn defined here

// --- 1. SET THE LOGGED-IN ROOM ---
// Use the logged-in user's room and ensure the user is an Executive Approver
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'Executive Approver') {
    header("Location: login.php");
    exit;
}
$logged_in_room = $_SESSION['user']['room_code'] ?? '';
if (empty($logged_in_room)) {
    die('No room assigned to your account.');
}


// --- 2. HANDLE APPROVAL / REJECTION ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $proposalId = isset($_POST['proposalId']) ? (int)$_POST['proposalId'] : 0;

    $status = ($action === 'approve') ? 'Approved' : 'Rejected';

    // Verify proposal exists and belongs to this room
    $check_stmt = $conn->prepare("SELECT room_code FROM proposals WHERE id = ?");
    $check_stmt->bind_param("i", $proposalId);
    $check_stmt->execute();
    $check_stmt->bind_result($proposal_room);
    if (!$check_stmt->fetch()) {
        $check_stmt->close();
        echo "Proposal not found";
        exit;
    }
    $check_stmt->close();

    if ($proposal_room !== $logged_in_room) {
        echo "Unauthorized";
        exit;
    }

    $stmt = $conn->prepare("UPDATE proposals SET status = ?, reviewed_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $status, $proposalId);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
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

/* Confirmation Modal */
.confirm-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 2000; }
.confirm-modal-content { background: #2c2a38; padding: 20px; width: 420px; margin: 12% auto; border-radius: 10px; }
.btn-secondary { background: #34495e; color: white; border: none; padding:10px 20px; border-radius:6px; cursor:pointer; }

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

                    <?php if (!empty($p['file_path'])): ?>
                        <p style="margin-top:8px;"><strong>Attachment:</strong>
                            <a href="uploads/proposals/<?= htmlspecialchars($p['file_path']) ?>" target="_blank" style="color:#1abc9c; margin-left:8px; text-decoration:none;">📎 Download</a>
                        </p>
                    <?php endif; ?>

                    <!-- Reviewer feedback (visible to EP) -->
                    <div style="background: #1f1d29; padding: 10px; border-radius: 6px; margin-top: 10px;">
                        <strong>Reviewer Feedback:</strong>
                        <?php if (!empty($p['reviewer_feedback'])): ?>
                            <div style="margin-top:8px; color: #ddd;"><?= nl2br(htmlspecialchars($p['reviewer_feedback'])) ?></div>
                            <?php if (!empty($p['reviewed_at'])): ?>
                                <div style="margin-top:8px; font-size:12px; color:#bbb;">Reviewed: <?= date('M d, Y H:i', strtotime($p['reviewed_at'])) ?></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#bbb; margin-left:8px;">No feedback provided.</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($p['status'] === 'Under Review'): ?>
                        <div style="margin-top:12px;">
                            <button class="btn" onclick="openConfirmModal(<?= $p['id'] ?>, 'approve')">Approve</button>
                            <button class="btn btn-danger" onclick="openConfirmModal(<?= $p['id'] ?>, 'reject')">Reject</button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="confirm-modal" style="display:none;">
    <div class="confirm-modal-content">
        <h3 id="confirmTitle">Confirm Action</h3>
        <p id="confirmMessage">Are you sure?</p>
        <div style="margin-top:15px; display:flex; gap:10px; justify-content:flex-end;">
            <button class="btn-secondary" onclick="closeConfirmModal()">Cancel</button>
            <button class="btn btn-primary" id="confirmBtn" onclick="confirmAction()">Confirm</button>
        </div>
    </div>
</div>

<footer>
Project Bidding System | Executive Module
</footer>

<script>
let _confirmProposalId = null;
let _confirmAction = null;

function openConfirmModal(id, action) {
    _confirmProposalId = id;
    _confirmAction = action;
    const title = action === 'approve' ? 'Confirm Approval' : 'Confirm Rejection';
    const msg = action === 'approve' ? 'Are you sure you want to approve this proposal?' : 'Are you sure you want to reject this proposal? This cannot be undone.';
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').textContent = msg;
    document.getElementById('confirmBtn').textContent = action === 'approve' ? 'Approve' : 'Reject';
    document.getElementById('confirmModal').style.display = 'block';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
    _confirmProposalId = null;
    _confirmAction = null;
}

function confirmAction() {
    if (!_confirmProposalId || !_confirmAction) return;
    const formData = new FormData();
    formData.append('action', _confirmAction);
    formData.append('proposalId', _confirmProposalId);

    fetch('EPMain.php', { method: 'POST', body: formData })
    .then(res => res.text())
    .then(res => {
        if (res === 'success') {
            alert('Proposal ' + (_confirmAction === 'approve' ? 'Approved' : 'Rejected'));
            location.reload();
        } else {
            alert(res);
            closeConfirmModal();
        }
    })
    .catch(err => {
        alert('Error: ' + err);
        closeConfirmModal();
    });
}
</script>
</body>
</html>
