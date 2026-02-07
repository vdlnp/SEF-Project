<?php
session_start();
include "db.php";

// Ensure user is logged in and is a Reviewer
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user'];

if (($user['role'] ?? '') !== 'Reviewer') {
    header("Location: login.php");
    exit;
}

$reviewer_id = (int) ($user['id'] ?? 0);
$room_code = mysqli_real_escape_string($conn, $user['room_code'] ?? '');

// Fetch all proposals for this reviewer's room
$query = "
    SELECT 
        p.id AS id,
        p.title,
        p.description,
        p.file_path,
        p.submitted_at,
        p.status,
        p.budget_amount,
        p.timeline_weeks,
        p.team_size,
        p.start_date,
        p.end_date,
        p.methodology,
        p.payment_terms,
        p.scope_of_work,
        p.deliverables,
        p.technical_requirements,
        p.risk_assessment,
        p.reviewer_feedback,
        p.reviewer_score
    FROM proposals p
    WHERE p.room_code = '{$room_code}' AND p.status = 'Approved'
    ORDER BY p.id DESC
";



$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reviewer Dashboard</title>

<style>
body {
    margin: 0;
    font-family: "Segoe UI", Arial, sans-serif;
    background: #1f1d29;
    color: #e6e6e6;
}

header {
    background: linear-gradient(135deg, #1abc9c, #16a085);
    padding: 16px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
}

header h2 { margin: 0; font-size: 24px; }

.logout {
    text-decoration: none;
    color: white;
    border: 1px solid white;
    padding: 6px 16px;
    border-radius: 20px;
    font-weight: 600;
    transition: 0.3s;
}
.logout:hover { background: white; color: #1abc9c; }

.container {
    padding: 40px;
    max-width: 1200px;
    margin: 0 auto;
}

.top-info {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
}

<?php if(mysqli_num_rows($result) == 0): ?>
    <div style="text-align:center; margin-top:40px; color:#888;">
        No approved proposals available for review.
    </div>
<?php endif; ?>


.top-info span {
    background: #1abc9c;
    padding: 6px 12px;
    border-radius: 6px;
    font-family: monospace;
    font-weight: 600;
}

/* Proposal Card */
.proposal-card {
    background: #2c2a38;
    padding: 26px;
    border-radius: 14px;
    margin-bottom: 26px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.6);
    transition: transform 0.2s;
}
.proposal-card:hover { transform: translateY(-3px); }

.proposal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.status-badge {
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.status-pending { background: #f1c40f; color: #1f1d29; }
.status-reviewed { background: #2ecc71; color: white; }

/* Buttons */
.btn {
    padding: 8px 18px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
    font-size: 14px;
}

.btn-primary { background: #1abc9c; color: white; }
.btn-primary:hover { background: #16a085; }

.btn-secondary { background: #34495e; color: white; }
.btn-secondary:hover { background: #2c3e50; }

.btn-danger { background: #e74c3c; color: white; }
.btn-danger:hover { background: #c0392b; }

/* Bidding summary grid */
.bidding-summary {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 15px;
}
.bid-stat {
    flex: 1 1 200px;
    background: #1f1d29;
    padding: 12px;
    border-radius: 8px;
    text-align: center;
    font-size: 14px;
}
.bid-stat strong { display: block; margin-bottom: 4px; color: #1abc9c; }

/* Description & Details */
.detail-section { margin-top: 15px; }
.detail-section h4 { margin-bottom: 6px; color: #1abc9c; }
.expanded-details { margin-top: 10px; padding-top: 12px; border-top: 1px solid #444; }

/* Attachments */
.attachments {
    margin-top: 14px;
    padding: 14px;
    background: #1f1d29;
    border-radius: 10px;
}
.attachments h4 { margin-bottom: 10px; color: #1abc9c; font-size: 14px; }
.attachment-item {
    display: flex;
    align-items: center;
    gap: 8px;       /* space between icon and link */
    justify-content: flex-start; /* keep everything to the left */
}
/* Attachments link styling */
.attachment-item a {
    color: #1abc9c;      /* highlight green */
    text-decoration: none; /* remove underline */
    font-weight: 600;     /* optional: bold for clarity */
}

.attachment-item a:hover {
    color: #16a085;       /* darker green on hover */
    text-decoration: underline; /* underline on hover for feedback */
}

/* Review box */
.review-box {
    margin-top: 20px;
    background: #1f1d29;
    padding: 18px;
    border-radius: 10px;
}
.review-score {
    font-weight: 700;
    color: #1abc9c;
    margin-bottom: 10px;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.75);
    z-index: 1000;
}
.modal-content {
    background: #2c2a38;
    padding: 25px;
    width: 400px;
    margin: 10% auto;
    border-radius: 14px;
}
textarea, input {
    width: 100%;
    margin-top: 10px;
    padding: 10px;
    background: #1f1d29;
    border: 1px solid #555;
    color: white;
    border-radius: 6px;
    box-sizing: border-box;
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
    <h2>Reviewer Dashboard</h2>
    <a href="logout.php" class="logout">Logout</a>
</header>

<div class="container">

<div class="top-info">
    <span class="room-code" style="background:#1abc9c; padding:4px 10px; border-radius:4px; font-family:monospace; color:white;">
        Room: <?= htmlspecialchars($user['room_code'] ?? 'N/A') ?>
    </span>
</div>

<?php while ($row = mysqli_fetch_assoc($result)) { 
    $has_feedback = !empty(trim($row['reviewer_feedback'] ?? ''));
    $displayScore = $row['reviewer_score'] !== null ? htmlspecialchars(number_format((float)$row['reviewer_score'], 1)) : null;
?>
<div class="proposal-card">
    <div class="proposal-header">
        <h3><?= htmlspecialchars($row['title']) ?></h3>
        <span class="status-badge <?= $has_feedback ? 'status-reviewed' : 'status-pending' ?>">
            <?= $has_feedback ? '✔ Reviewed' : '⏳ Pending' ?>
        </span>
    </div>

    <!-- Meta Info -->
    <p>👤 Submitted by: <strong><?= htmlspecialchars($user['name'] ?? 'N/A') ?></strong>
       • <?= date('M d, Y g:i A', strtotime($row['submitted_at'])) ?>
    </p>

    <!-- Bidding Summary -->
    <div class="bidding-summary">
        <?php if (!empty($row['budget_amount'])): ?>
            <div class="bid-stat"><strong>Total Budget:</strong> RM <?= number_format($row['budget_amount'],2) ?></div>
        <?php endif; ?>
        <?php if (!empty($row['timeline_weeks'])): ?>
            <div class="bid-stat"><strong>Duration:</strong> <?= $row['timeline_weeks'] ?> weeks</div>
        <?php endif; ?>
        <?php if (!empty($row['team_size'])): ?>
            <div class="bid-stat"><strong>Team Size:</strong> <?= $row['team_size'] ?> members</div>
        <?php endif; ?>
        <?php if (!empty($row['start_date'])): ?>
            <div class="bid-stat"><strong>Start Date:</strong> <?= date('M d, Y', strtotime($row['start_date'])) ?></div>
        <?php endif; ?>
        <?php if (!empty($row['end_date'])): ?>
            <div class="bid-stat"><strong>End Date:</strong> <?= date('M d, Y', strtotime($row['end_date'])) ?></div>
        <?php endif; ?>
        <?php if (!empty($row['methodology'])): ?>
            <div class="bid-stat"><strong>Methodology:</strong> <?= htmlspecialchars($row['methodology']) ?></div>
        <?php endif; ?>
        <?php if (!empty($row['payment_terms'])): ?>
            <div class="bid-stat"><strong>Payment Terms:</strong> <?= htmlspecialchars($row['payment_terms']) ?></div>
        <?php endif; ?>
    </div>

    <!-- Description -->
    <?php if (!empty($row['description'])): ?>
        <div class="detail-section">
            <h4>Project Description</h4>
            <div><?= nl2br(htmlspecialchars($row['description'])) ?></div>
        </div>
    <?php endif; ?>

    <!-- Detailed sections collapsible -->
    <?php if (!empty($row['scope_of_work']) || !empty($row['deliverables']) || !empty($row['technical_requirements']) || !empty($row['risk_assessment'])): ?>
        <button class="btn btn-secondary" onclick="toggleDetails(<?= $row['id'] ?>)">View Details</button>
        <div id="details-<?= $row['id'] ?>" class="expanded-details" style="display:none; margin-top:10px;">
            <?php if (!empty($row['scope_of_work'])): ?>
                <div><strong>Scope of Work:</strong> <?= nl2br(htmlspecialchars($row['scope_of_work'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($row['deliverables'])): ?>
                <div><strong>Key Deliverables:</strong> <?= nl2br(htmlspecialchars($row['deliverables'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($row['technical_requirements'])): ?>
                <div><strong>Technical Requirements:</strong> <?= nl2br(htmlspecialchars($row['technical_requirements'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($row['risk_assessment'])): ?>
                <div><strong>Risk Assessment:</strong> <?= nl2br(htmlspecialchars($row['risk_assessment'])) ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Attachments -->
    <?php if (!empty($row['file_path'])): ?>
        <div class="attachments">
                <h4>Attachments</h4>
                <div class="attachment-item">
                    📄 <a href="uploads/proposals/<?= htmlspecialchars($row['file_path']) ?>" target="_blank">
                        <?= htmlspecialchars($row['file_path']) ?>
                    </a>
                </div>
        </div>

    <?php endif; ?>

    <!-- Review Section -->
    <?php if ($has_feedback): ?>
        <div class="review-box">
            <?php if ($displayScore !== null): ?>
                <div class="review-score">Score: <?= $displayScore ?>/10</div>
            <?php endif; ?>
            <p><?= nl2br(htmlspecialchars($row['reviewer_feedback'])) ?></p>
            <button class="btn btn-primary" onclick="openModal(<?= $row['id'] ?>)">Edit Review</button>
            <button class="btn btn-danger" onclick="deleteReview(<?= $row['id'] ?>)">Delete Review</button>
        </div>
    <?php else: ?>
        <button class="btn btn-primary" onclick="openModal(<?= $row['id'] ?>)">Review Proposal</button>
    <?php endif; ?>
</div>
<?php } ?>


</div>

<!-- MODAL -->
<div class="modal" id="reviewModal">
    <div class="modal-content">
        <h3>Review Proposal</h3>
        <form method="POST" action="save_review.php">
            <input type="hidden" name="proposal_id" id="proposal_id">
            <label for="comments">Comments</label>
            <textarea name="comments" id="comments" required></textarea>

            <label for="score">Score (0 - 10)</label>
            <input type="number" name="score" id="score" min="0" max="10" step="0.1" required>

            <div style="margin-top:15px; display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<footer>
Project Bidding System | Reviewer Portal
</footer>

<script>
function openModal(proposalId, comments = "", score = "") {
    document.getElementById("proposal_id").value = proposalId;
    document.getElementById("comments").value = ""; // always blank on edit
    document.getElementById("score").value = "";    // always blank on edit
    document.getElementById("reviewModal").style.display = "block";
}

function closeModal() {
    document.getElementById("reviewModal").style.display = "none";
}

function deleteReview(proposalId) {
    if (!confirm("Are you sure you want to delete this review?")) return;
    
    const fd = new FormData();
    fd.append('proposal_id', proposalId);
    fd.append('action', 'delete');
    
    fetch('save_review.php', { method: 'POST', body: fd })
        .then(r => r.text())
        .then(r => {
            if (r.trim() === 'success') {
                location.reload();
            } else {
                alert('Error: ' + r);
            }
        })
        .catch(err => alert('Error: ' + err));
}

function toggleDetails(id) {
    const el = document.getElementById('details-' + id);
    if (el.style.display === 'none') el.style.display = 'block';
    else el.style.display = 'none';
}

</script>

</body>
</html>
