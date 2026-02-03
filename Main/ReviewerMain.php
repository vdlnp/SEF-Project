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
      AND p.status IN ('Approved', 'Reviewed')
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
.container { padding: 40px; }

.proposal-card {
    background: #2c2a38;
    padding: 26px;
    border-radius: 14px;
    margin-bottom: 26px;
}

.status-badge {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}
.status-pending { background: #f39c12; color: #1f1d29; }
.status-reviewed { background: #2ecc71; }

.btn {
    padding: 8px 18px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}
.btn-primary { background: #1abc9c; color: white; }
.btn-secondary { background: #34495e; color: white; }

.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.7);
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
}
</style>
</head>

<body>

<header>
    <h2>Reviewer Dashboard</h2>
    <a href="logout.php" class="logout">Logout</a>
</header>

<div class="container">


<?php while ($row = mysqli_fetch_assoc($result)) { 
    $feedback = trim($row['reviewer_feedback'] ?? '');
    $status = $feedback ? 'Reviewed' : ($row['proposal_status'] ?? 'Under Review');
    $status_text = $status; 
?>
    <div class="proposal-card">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h3><?= htmlspecialchars($row['proposal_title']) ?></h3>
            <span class="status-badge <?= strtolower($status) === 'reviewed' ? 'status-reviewed' : 'status-pending' ?>">
                <?= $status_text ?>
            </span>
        </div>

        <p><?= htmlspecialchars($row['proposal_description']) ?></p>

        <?php if (!empty($row['proposal_file_path'])): ?>
            <p style="margin-top:8px;">
                <strong>Attachment:</strong>
                <a href="uploads/proposals/<?= htmlspecialchars($row['proposal_file_path']) ?>" target="_blank" style="color:#1abc9c; margin-left:8px; text-decoration:none;">📎 Download File</a>
            </p>
        <?php endif; ?>

        <?php if ($feedback) { 
                $displayScore = $row['reviewer_score'] !== null ? htmlspecialchars(number_format((float)$row['reviewer_score'], 1)) : null;
            ?>
            <div style="margin-top:15px;">
                <?php if ($displayScore !== null): ?>
                    <strong>Score:</strong> <?= $displayScore ?>/10
                    <br>
                <?php endif; ?>

                <strong>Feedback:</strong>
                <p><?= nl2br(htmlspecialchars($feedback)) ?></p>

                <button class="btn btn-primary"
                    onclick="openModal(<?= $row['proposal_id'] ?>, '<?= addslashes($feedback) ?>', '<?= $displayScore ?? '' ?>')">
                    Edit Review
                </button>
            </div>
        <?php } else { ?>
            <button class="btn btn-primary"
                onclick="openModal(<?= $row['proposal_id'] ?>)">
                Review Proposal
            </button>
        <?php } ?>
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
</script>

</body>
</html>
