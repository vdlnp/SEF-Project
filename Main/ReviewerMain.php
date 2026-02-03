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

// Fetch proposals for this reviewer's room (only EP-approved proposals)
$query = "
    SELECT 
        p.id AS proposal_id,
        p.title AS proposal_title,
        p.description AS proposal_description,
        p.file_path AS proposal_file_path,
        p.submitted_at AS proposal_submitted,
        p.status AS proposal_status,
        p.reviewer_feedback,
        p.reviewer_score
    FROM proposals p
    WHERE p.room_code = '{$room_code}'
      AND p.status = 'Approved'
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
}

.logout {
    text-decoration: none;
    color: white;
    border: 1px solid white;
    padding: 6px 16px;
    border-radius: 20px;
}

.container {
    padding: 40px;
}

/* Proposal Card */
.proposal-card {
    background: #2c2a38;
    padding: 26px;
    border-radius: 14px;
    margin-bottom: 26px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.5);
}

/* Header */
.proposal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

/* Status Badges */
.status-badge {
    padding: 8px 18px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.status-pending {
    background: linear-gradient(135deg, #f39c12, #f1c40f);
    color: #1f1d29;
}

.status-reviewed {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    color: white;
}

/* Buttons */
.btn {
    padding: 8px 18px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-primary {
    background: #1abc9c;
    color: white;
}

.btn-primary:hover {
    background: #16a085;
}

.btn-secondary {
    background: #34495e;
    color: white;
}

.btn-secondary:hover {
    background: #2c3e50;
}

.btn-danger {
    background: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
}

/* Attachments */
.attachments {
    margin-top: 14px;
    padding: 14px;
    background: #1f1d29;
    border-radius: 10px;
}

.attachments h4 {
    margin: 0 0 10px;
    font-size: 14px;
    color: #1abc9c;
}

.attachment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    font-size: 14px;
}

.attachment-item a {
    color: #1abc9c;
    text-decoration: none;
}

.attachment-item a:hover {
    text-decoration: underline;
}

/* Review Section */
.review-box {
    margin-top: 18px;
    background: #1f1d29;
    padding: 16px;
    border-radius: 10px;
}

.review-score {
    font-weight: 700;
    color: #1abc9c;
    margin-bottom: 10px;
}

.top-info {
    display:flex;
    gap:10px;
    margin-bottom:20px;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.7);
    z-index: 1000;
}

.modal-content {
    background: #2c2a38;
    padding: 25px;
    width: 420px;
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
    // Determine review status based on whether feedback exists
    $has_feedback = !empty(trim($row['reviewer_feedback'] ?? ''));
    $review_status = $has_feedback ? 'Reviewed' : 'Pending';
    $feedback = trim($row['reviewer_feedback'] ?? '');
    $displayScore = $row['reviewer_score'] !== null ? htmlspecialchars(number_format((float)$row['reviewer_score'], 1)) : null;
?>
    <div class="proposal-card">
        <div class="proposal-header">
            <h3><?= htmlspecialchars($row['proposal_title']) ?></h3>
            <span class="status-badge <?= $has_feedback ? 'status-reviewed' : 'status-pending' ?>">
                <?= $has_feedback ? '✔ Reviewed' : '⏳ Pending' ?>
            </span>
        </div>

        <p><?= htmlspecialchars($row['proposal_description']) ?></p>

        <?php if (!empty($row['proposal_file_path'])): ?>
            <div class="attachments">
                <h4>Attachments</h4>
                <div class="attachment-item">
                    📄 <?= htmlspecialchars($row['proposal_file_path']) ?>
                    <a href="uploads/proposals/<?= htmlspecialchars($row['proposal_file_path']) ?>" target="_blank">View</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($has_feedback): ?>
            <div class="review-box">
                <?php if ($displayScore !== null): ?>
                    <div class="review-score">Score: <?= $displayScore ?>/10</div>
                <?php endif; ?>
                <p><?= nl2br(htmlspecialchars($feedback)) ?></p>
                <div style="margin-top:10px; display:flex; gap:10px;">
                    <button class="btn btn-primary"
                        onclick="openModal(<?= $row['proposal_id'] ?>, '<?= addslashes($feedback) ?>', '<?= $displayScore ?? '' ?>')">
                        Edit Review
                    </button>
                    <button class="btn btn-danger"
                        onclick="deleteReview(<?= $row['proposal_id'] ?>)">
                        Delete Review
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div style="margin-top:15px;">
                <button class="btn btn-primary"
                    onclick="openModal(<?= $row['proposal_id'] ?>)">
                    Review Proposal
                </button>
            </div>
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

<script>
function openModal(proposalId, comments = "", score = "") {
    document.getElementById("proposal_id").value = proposalId;
    document.getElementById("comments").value = comments;
    document.getElementById("score").value = score;
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
</script>

</body>
</html>