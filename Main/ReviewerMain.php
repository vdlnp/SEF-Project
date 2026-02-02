<?php
session_start();
include "db.php";

$reviewer_id = $_SESSION['user_id'] ?? 1; // fallback for testing

// Fetch proposals and any existing reviews by this reviewer
$query = "
    SELECT 
        pr.id AS proposal_id,
        pr.title AS proposal_title,
        pr.description AS proposal_description,
        pr.deadline AS proposal_deadline,
        r.id AS review_id,
        r.comments,
        r.score,
        r.status AS review_status
    FROM proposal pr
    LEFT JOIN proposal_reviews r
        ON pr.id = r.proposal_id
        AND r.reviewer_id = $reviewer_id
    ORDER BY pr.id DESC
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
    $status = $row['review_status'] ?? 'Pending'; // Default to Pending
    $status_text = ucfirst(strtolower($status)); // Capitalize first letter
?>
    <div class="proposal-card">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h3><?= htmlspecialchars($row['proposal_title']) ?></h3>
            <span class="status-badge <?= strtolower($status) === 'reviewed' ? 'status-reviewed' : 'status-pending' ?>">
                <?= $status_text ?>
            </span>
        </div>

        <p><?= htmlspecialchars($row['proposal_description']) ?></p>

        <?php if ($row['review_id']) { ?>
            <div style="margin-top:15px;">
                <strong>Score:</strong> <?= htmlspecialchars($row['score']) ?>/10
                <p><?= htmlspecialchars($row['comments']) ?></p>
                <button class="btn btn-primary"
                    onclick="openModal(
                        <?= $row['proposal_id'] ?>,
                        '<?= addslashes($row['comments']) ?>',
                        '<?= $row['score'] ?>'
                    )">
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
            <textarea name="comments" id="comments" required></textarea>
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
