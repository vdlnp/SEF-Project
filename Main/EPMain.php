<?php
include "db.php"; // Connects to your sef_project database

// --- HANDLE APPROVAL / REJECTION ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $proposalId = $_POST['proposalId'];
    $feedback = $conn->real_escape_string($_POST['feedback'] ?? '');

    if ($action === 'approve') {
        $status = 'Approved';
    } else {
        $status = 'Rejected';
    }

    $sql = "UPDATE proposals SET status='$status', reviewer_feedback='$feedback', reviewed_at=NOW() WHERE id=$proposalId";
    
    if ($conn->query($sql)) {
        echo "success";
    } else {
        echo "error";
    }
    exit;
}

// --- FETCH PROPOSALS FOR REVIEW ---
// This fetches proposals that are 'Under Review' first
$query = "SELECT p.*, u.name as sender_name 
          FROM proposals p 
          JOIN users u ON p.user_id = u.id 
          ORDER BY p.submitted_at DESC";
$result = $conn->query($query);
$proposals = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Executive Approver Dashboard</title>
    <style>
        body { margin:0; font-family:"Segoe UI",Arial,sans-serif; background:#1f1d29; color:#e6e6e6; }
        header { background:#1abc9c; color:white; padding:16px 32px; display:flex; justify-content:space-between; align-items:center; }
        .container { padding: 40px; max-width: 1200px; margin: auto; }
        .proposal-card { background:#2c2a38; border-radius:12px; padding:20px; margin-bottom:20px; border-left: 5px solid #1abc9c; }
        .status-badge { padding:4px 12px; border-radius:20px; font-size:12px; font-weight:bold; text-transform:uppercase; }
        .status-Under-Review { background: #f39c12; color: white; }
        .status-Approved { background: #2ecc71; color: white; }
        .status-Rejected { background: #e74c3c; color: white; }
        .feedback-area { width:100%; background:#1f1d29; color:white; border:1px solid #444; padding:10px; border-radius:6px; margin: 10px 0; }
        .btn { padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; }
        .btn-approve { background:#2ecc71; color:white; margin-right:10px; }
        .btn-reject { background:#e74c3c; color:white; }
        .room-code { background:#1abc9c; padding:2px 8px; border-radius:4px; font-family:monospace; }
        a.download-link { color: #3498db; text-decoration: none; font-weight: bold; }
        a.download-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<header>
    <h2>Executive Dashboard</h2>
    <a href="logout.php" style="color:white; text-decoration:none;">Logout</a>
</header>

<div class="container">
    <h3>Pending Proposals for Review</h3>

    <?php if (empty($proposals)): ?>
        <p>No proposals have been submitted yet.</p>
    <?php else: ?>
        <?php foreach ($proposals as $row): ?>
            <div class="proposal-card">
                <div style="display:flex; justify-content:space-between;">
                    <h4><?= htmlspecialchars($row['title']) ?></h4>
                    <span class="status-badge status-<?= str_replace(' ', '-', $row['status']) ?>">
                        <?= $row['status'] ?>
                    </span>
                </div>
                <p><strong>Submitted by:</strong> <?= htmlspecialchars($row['sender_name']) ?> | <strong>Room:</strong> <span class="room-code"><?= $row['room_code'] ?></span></p>
                <p><?= nl2br(htmlspecialchars($row['description'])) ?></p>
                
                <?php if ($row['file_path']): ?>
                    <p><strong>Attachment:</strong> <a class="download-link" href="<?= $row['file_path'] ?>" target="_blank">View Document</a></p>
                <?php endif; ?>

                <?php if ($row['status'] === 'Under Review'): ?>
                    <hr style="border:0; border-top:1px solid #444;">
                    <textarea id="feedback-<?= $row['id'] ?>" class="feedback-area" placeholder="Add reviewer feedback here..."></textarea>
                    <button class="btn btn-approve" onclick="handleAction(<?= $row['id'] ?>, 'approve')">Approve Proposal</button>
                    <button class="btn btn-reject" onclick="handleAction(<?= $row['id'] ?>, 'reject')">Reject Proposal</button>
                <?php else: ?>
                    <p style="color: #bbb;"><strong>Final Feedback:</strong> <?= htmlspecialchars($row['reviewer_feedback'] ?: 'No feedback provided.') ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

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
    .then(data => {
        if (data === 'success') {
            alert('Proposal ' + (action === 'approve' ? 'Approved' : 'Rejected'));
            location.reload();
        } else {
            alert('Something went wrong.');
        }
    });
}
</script>

</body>
</html>
