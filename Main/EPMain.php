<?php
session_start();
include "db.php";

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'Executive Approver') {
    header("Location: login.php");
    exit;
}

$logged_in_room = $_SESSION['user']['room_code'] ?? '';
if (empty($logged_in_room)) {
    die("No room assigned to your account.");
}

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


.container {
    max-width:1200px;
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
    transform:translateY(-2px);
    box-shadow:0 8px 20px rgba(0,0,0,0.5);
}

.submission-title {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:10px;
}

.submission-title h3 { 
    font-size:20px; 
    font-weight:600;
    color:#1abc9c;
}

.status-badge {
    padding:5px 14px;
    border-radius:20px;
    font-size:12px;
    text-transform:uppercase;
    font-weight:600;
}
.status-under-review { background:#fbbf24; color:#78350f; }
.status-approved { background:#34d399; color:#064e3b; }
.status-declined { background:#f87171; color:#7f1d1d; }
.status-rejected { background:#f87171; color:#7f1d1d; }

.submission-meta {
    font-size:13px;
    color:#cfcfcf;
    margin-bottom:15px;
    padding-bottom:15px;
    border-bottom:1px solid #3a3847;
}

.bidding-summary {
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));
    gap:15px;
    margin-bottom:20px;
    padding:15px;
    background:#1f1d29;
    border-radius:10px;
}

.bid-stat {
    display:flex;
    flex-direction:column;
    gap:5px;
}

.bid-stat-label {
    font-size:11px;
    color:#888;
    text-transform:uppercase;
    font-weight:600;
    letter-spacing:0.5px;
}

.bid-stat-value {
    font-size:16px;
    color:#e6e6e6;
    font-weight:600;
}

.bid-stat-value.highlight {
    color:#1abc9c;
    font-size:18px;
}

.detail-section {
    margin-top:15px;
    padding-top:15px;
    border-top:1px solid #3a3847;
}

.detail-section h4 {
    font-size:14px;
    color:#1abc9c;
    margin-bottom:8px;
    font-weight:600;
    display:flex;
    align-items:center;
    gap:8px;
}

.detail-content {
    font-size:13px;
    color:#b0b8c4;
    line-height:1.6;
    padding:10px;
    background:#1f1d29;
    border-radius:8px;
    white-space:pre-line;
}

.detail-list {
    list-style:none;
    padding-left:0;
}

.detail-list li {
    padding:8px 0;
    border-bottom:1px solid #2c2a38;
}

.detail-list li:last-child {
    border-bottom:none;
}

.attachments {
    margin-top:15px;
    padding:12px;
    background:#1f1d29;
    border-radius:8px;
}

.attachments h4 { 
    font-size:14px; 
    margin-bottom:8px;
    color:#1abc9c;
}

.attachments a {
    font-size:13px;
    color:#1abc9c;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:5px;
}

.attachments a:hover { 
    text-decoration:underline;
}

.view-more-btn {
    background:#34495e;
    color:white;
    border:none;
    padding:8px 16px;
    border-radius:6px;
    font-size:13px;
    cursor:pointer;
    margin-top:10px;
    transition:0.3s;
}

.view-more-btn:hover {
    background:#4a5f7f;
}

.expanded-details {
    display:none;
    margin-top:15px;
}

.expanded-details.show {
    display:block;
}

.submission-actions {
    display:flex;
    gap:10px;
    margin-top:20px;
    padding-top:15px;
    border-top:1px solid #3a3847;
}

.action-btn {
    flex:1;
    padding:12px;
    border-radius:8px;
    border:none;
    font-size:14px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
    color:white;
}

.approve-btn { 
    background:#10b981;
}

.approve-btn:hover {
    background:#059669;
    transform:translateY(-2px);
    box-shadow:0 4px 12px rgba(16,185,129,0.4);
}

.reject-btn { 
    background:#ef4444;
}

.reject-btn:hover {
    background:#dc2626;
    transform:translateY(-2px);
    box-shadow:0 4px 12px rgba(239,68,68,0.4);
}

.processed { 
    opacity:0.7;
}

.processed .submission-actions {
    pointer-events:none;
    opacity:0.5;
}

.empty-state {
    text-align:center;
    padding:60px 20px;
    color:#888;
}

.empty-state-icon {
    font-size:64px;
    margin-bottom:15px;
}

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
        <span class="pending-count"><?= $pendingCount ?> Pending Review</span>
        <span class="room-code">Room: <?= htmlspecialchars($logged_in_room) ?></span>
    </div>

    <div class="submissions-list">
        <?php if (empty($proposals)): ?>
            <div class="submission-card">
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <p>No project bids submitted yet</p>
                </div>
            </div>
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
                        👤 Submitted by <strong><?= htmlspecialchars($p['sender_name']) ?></strong>
                        • <?= date('M d, Y g:i A', strtotime($p['submitted_at'])) ?>
                    </p>

                    <!-- Bidding Summary Grid -->
                    <?php if (!empty($p['budget_amount']) || !empty($p['timeline_weeks']) || !empty($p['team_size'])): ?>
                    <div class="bidding-summary">
                        <?php if (!empty($p['budget_amount']) && $p['budget_amount'] > 0): ?>
                        <div class="bid-stat">
                            <div class="bid-stat-label">Total Budget</div>
                            <div class="bid-stat-value highlight">
                                RM <?= number_format($p['budget_amount'], 2) ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($p['timeline_weeks'])): ?>
                        <div class="bid-stat">
                            <div class="bid-stat-label">Duration</div>
                            <div class="bid-stat-value"><?= $p['timeline_weeks'] ?> weeks</div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($p['start_date'])): ?>
                        <div class="bid-stat">
                            <div class="bid-stat-label">Start Date</div>
                            <div class="bid-stat-value"><?= date('M d, Y', strtotime($p['start_date'])) ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($p['end_date'])): ?>
                        <div class="bid-stat">
                            <div class="bid-stat-label">End Date</div>
                            <div class="bid-stat-value"><?= date('M d, Y', strtotime($p['end_date'])) ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($p['team_size'])): ?>
                        <div class="bid-stat">
                            <div class="bid-stat-label">👥 Team Size</div>
                            <div class="bid-stat-value"><?= $p['team_size'] ?> members</div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($p['methodology'])): ?>
                        <div class="bid-stat">
                            <div class="bid-stat-label">Methodology</div>
                            <div class="bid-stat-value"><?= htmlspecialchars($p['methodology']) ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($p['payment_terms'])): ?>
                        <div class="bid-stat">
                            <div class="bid-stat-label">Payment Terms</div>
                            <div class="bid-stat-value"><?= htmlspecialchars($p['payment_terms']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Description -->
                    <?php if (!empty($p['description'])): ?>
                    <div class="detail-section">
                        <h4>Project Description</h4>
                        <div class="detail-content">
                            <?= nl2br(htmlspecialchars($p['description'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Scope of Work (collapsed by default) -->
                    <?php if (!empty($p['scope_of_work']) || !empty($p['deliverables']) || !empty($p['technical_requirements']) || !empty($p['risk_assessment'])): ?>
                    <button class="view-more-btn" onclick="toggleDetails(<?= $p['id'] ?>)">
                        View Detailed Bid Information
                    </button>

                    <div id="details-<?= $p['id'] ?>" class="expanded-details">
                        <?php if (!empty($p['scope_of_work'])): ?>
                        <div class="detail-section">
                            <h4>Scope of Work</h4>
                            <div class="detail-content">
                                <?= nl2br(htmlspecialchars($p['scope_of_work'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($p['technical_requirements'])): ?>
                        <div class="detail-section">
                            <h4>Technical Requirements & Stack</h4>
                            <div class="detail-content">
                                <?= nl2br(htmlspecialchars($p['technical_requirements'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($p['deliverables'])): ?>
                        <div class="detail-section">
                            <h4>Key Deliverables</h4>
                            <div class="detail-content">
                                <?= nl2br(htmlspecialchars($p['deliverables'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($p['risk_assessment'])): ?>
                        <div class="detail-section">
                            <h4>⚠️ Risk Assessment & Mitigation</h4>
                            <div class="detail-content">
                                <?= nl2br(htmlspecialchars($p['risk_assessment'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Attachments -->
                    <?php if (!empty($p['file_path'])): ?>
                    <div class="attachments">
                        <h4>📎 Supporting Documents</h4>
                        <a href="uploads/proposals/<?= htmlspecialchars($p['file_path']) ?>" target="_blank">
                            📄 <?= htmlspecialchars($p['file_path']) ?>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Review Actions -->
                    <?php if ($p['status'] === 'Under Review'): ?>
                        <div class="submission-actions">
                            <button class="action-btn approve-btn"
                                onclick="handleAction(<?= $p['id'] ?>,'approve')">
                                Approve Bid
                            </button>
                            <button class="action-btn reject-btn"
                                onclick="handleAction(<?= $p['id'] ?>,'decline')">
                                Decline Bid
                            </button>
                        </div>
                    <?php else: ?>
                        <div style="margin-top:15px;padding:10px;background:#1f1d29;border-radius:8px;text-align:center;color:#888;">
                            Decision made: <strong style="color:#1abc9c;"><?= $p['status'] ?></strong>
                            <?php if (!empty($p['reviewed_at'])): ?>
                                on <?= date('M d, Y g:i A', strtotime($p['reviewed_at'])) ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<footer>
Project Bidding System | Executive Approver Portal
</footer>

<script>
function toggleDetails(id) {
    const detailsDiv = document.getElementById('details-' + id);
    const btn = event.target;
    
    if (detailsDiv.classList.contains('show')) {
        detailsDiv.classList.remove('show');
        btn.textContent = 'View Detailed Bid Information';
    } else {
        detailsDiv.classList.add('show');
        btn.textContent = 'Hide Detailed Bid Information';
    }
}

function handleAction(id, action) {
    const actionText = action === 'approve' ? 'approve' : 'decline';
    if (!confirm(`Are you sure you want to ${actionText} this bid?`)) return;

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