<?php
session_start();
include "db.php";

$lead_id = $_SESSION['user_id'] ?? 2; // fallback for testing

// Fetch projects and proposals for this lead
$proposals_query = "
    SELECT 
        p.id AS project_id, p.title AS project_title, p.description AS project_description, p.deadline AS project_deadline, p.room_code,
        pr.id AS proposal_id, pr.title AS proposal_title, pr.description AS proposal_description, pr.deadline AS proposal_deadline,
        pr.status AS proposal_status
    FROM project p
    LEFT JOIN proposal pr
        ON p.id = pr.project_id AND pr.lead_id = $lead_id
    ORDER BY p.id DESC
";
$proposals_result = mysqli_query($conn, $proposals_query);

// Fetch all attachments for proposals
$attachments_query = "SELECT * FROM proposal_attachments";
$attachments_result = mysqli_query($conn, $attachments_query);

$attachments = [];
while($att = mysqli_fetch_assoc($attachments_result)){
    $attachments[$att['proposal_id']][] = $att;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Project Lead Dashboard</title>
<style>
body {margin:0; font-family:"Segoe UI", Arial, sans-serif; background:#1f1d29; color:#e6e6e6;}
header {background:#1abc9c; color:white; padding:16px 32px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 8px rgba(0,0,0,0.3);}
header h1 {margin:0; font-size:22px;}
.nav-right {display:flex; align-items:center; gap:20px;}
.notification {position:relative; font-size:22px; color:white; text-decoration:none;}
.badge {position:absolute; top:-6px; right:-10px; background:#e74c3c; color:white; font-size:11px; padding:3px 6px; border-radius:50%;}
.logout {text-decoration:none; color:white; font-weight:600; border:1px solid rgba(255,255,255,0.6); padding:6px 16px; border-radius:20px; transition:all 0.3s ease;}
.logout:hover {background:#2e1b36; box-shadow:0 0 15px rgba(26,188,156,0.7); transform:scale(1.05);}
.container {min-height:calc(100vh - 140px); display:flex; flex-direction:column; align-items:center; justify-content:center; padding:40px 20px;}
.welcome {margin-bottom:40px; font-size:18px; color:#cfcfcf; text-align:center;}
.cards {display:flex; gap:40px;}
.card {background:#2c2a38; padding:46px 80px; border-radius:14px; box-shadow:0 6px 15px rgba(0,0,0,0.5); font-size:18px; font-weight:600; text-align:center; color:#1abc9c; cursor:pointer; transition:all 0.4s ease;}
.card:hover {transform:translateY(-8px) scale(1.05); color:white; background:linear-gradient(135deg, #1abc9c, #2e1b36); box-shadow:0 12px 30px rgba(0,0,0,0.8),0 0 20px rgba(26,188,156,0.5);}
.modal {display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.7); overflow-y:auto;}
.modal-content {background-color:#2c2a38; margin:3% auto; padding:30px; border-radius:14px; width:90%; max-width:700px; box-shadow:0 8px 20px rgba(0,0,0,0.6);}
.close {color:#aaa; float:right; font-size:28px; font-weight:bold; cursor:pointer;}
.close:hover {color:#1abc9c;}
.modal h2 {color:#1abc9c; margin-top:0; margin-bottom:25px;}
input, textarea {width:100%; padding:12px; margin-top:12px; border-radius:8px; border:none; background:#1f1d29; color:#e6e6e6;}
.submit-btn {margin-top:15px; background:#1abc9c; border:none; padding:12px 20px; color:white; border-radius:10px; cursor:pointer;}
.proposal {background:#1f1d29; padding:18px; border-radius:10px; margin-top:15px; display:flex; justify-content:space-between; align-items:center;}
.proposal-info {max-width:70%;}
.status {padding:4px 10px; border-radius:20px; font-size:12px;}
.review {background:#fbbf24; color:#78350f;}
.approved {background:#34d399; color:#064e3b;}
.rejected {background:#f87171; color:#7f1d1d;}
.view-btn {background:#1abc9c; border:none; padding:8px 16px; border-radius:8px; color:white; cursor:pointer;}
footer {background:#161421; color:#bbb; text-align:center; padding:14px; font-size:14px; border-top:1px solid #333333;}
.attachment-item {display:flex; justify-content:space-between; margin-top:8px;}
.attachment-item a {color:#1abc9c; text-decoration:none;}
</style>
</head>
<body>

<header>
<h1>Project Lead Dashboard</h1>
<div class="nav-right">
<a href="#" class="notification" title="Notifications">🔔<span class="badge">2</span></a>
<a href="logout.php" class="logout">Logout</a>
</div>
</header>

<div class="container">
<div class="welcome">Manage and track your proposals</div>
<div class="cards">
    <div class="card" onclick="openModal('submitProposalModal')">Submit Proposal</div>
    <div class="card" onclick="openModal('myProposalsModal')">My Proposals</div>
</div>
</div>

<!-- Submit Proposal Modal -->
<div id="submitProposalModal" class="modal">
<div class="modal-content">
    <span class="close" onclick="closeModal('submitProposalModal')">&times;</span>
    <h2>Submit Proposal</h2>
    <form method="POST" action="submit_proposal.php" enctype="multipart/form-data">
        <label>Select Project:</label>
        <select name="project_id" required>
            <option value="">-- Select Project --</option>
            <?php
            $projects = mysqli_query($conn, "SELECT id, title FROM project WHERE room_code = (SELECT room_code FROM users WHERE id = $lead_id) ORDER BY id ASC");
            while($proj = mysqli_fetch_assoc($projects)){
                echo "<option value='{$proj['id']}'>" . htmlspecialchars($proj['title']) . "</option>";
            }
            ?>
        </select>

        <input type="text" name="title" placeholder="Proposal Title" required>
        <textarea name="description" rows="4" placeholder="Proposal Description" required></textarea>
        <label>Attach File:</label>
        <input type="file" name="attachment">
        <button type="submit" class="submit-btn">Submit Proposal</button>
    </form>
</div>
</div>


<!-- My Proposals Modal -->
<div id="myProposalsModal" class="modal">
<div class="modal-content">
<span class="close" onclick="closeModal('myProposalsModal')">&times;</span>
<h2>My Proposals</h2>

<?php while($proposal = mysqli_fetch_assoc($proposals_result)):
    $review_status = $proposal['review_status'] ?? 'Under Review'; // default if NULL
    $status_class = $review_status === 'reviewed' ? 'approved' : 'review';

    $proj_attachments = $attachments[$proposal['proposal_id']] ?? [];
    
    // Use proposal deadline if exists, otherwise fallback to project deadline
    $display_deadline = $proposal['proposal_deadline'] ?? $proposal['project_deadline'];
?>
<div class="proposal"
     data-proposalid="<?= $proposal['proposal_id'] ?>"
     data-title="<?= htmlspecialchars($proposal['proposal_title'] ?? $proposal['project_title'] ?? $proposal['title']) ?>"
     data-description="<?= htmlspecialchars($proposal['proposal_description'] ?? $proposal['project_description']) ?>"
     data-deadline="<?= htmlspecialchars($display_deadline) ?>">
    <div class="proposal-info">
        <strong><?= htmlspecialchars($proposal['proposal_title'] ?? $proposal['project_title'] ?? $proposal['title']) ?></strong><br>
        <span class="status <?= $status_class ?>"><?= $proposal['review_status'] ?? 'Under Review' ?></span>
    </div>
    <button class="view-btn" onclick="viewProposal(<?= $proposal['proposal_id'] ?>)">View Proposal</button>
</div>
<?php endwhile; ?>

</div>

<!-- View Proposal Modal -->
<div id="viewProposalModal" class="modal">
<div class="modal-content">
<span class="close" onclick="closeModal('viewProposalModal')">&times;</span>
<h2 id="modalTitle">Proposal Title</h2>
<form id="editProposalForm" method="POST" action="edit_proposal.php" enctype="multipart/form-data">
    <input type="hidden" name="proposal_id" id="modalProposalId">
    <input type="hidden" name="project_id" id="modalProjectId">
    <label>Title:</label>
    <input type="text" name="title" id="modalInputTitle" required>
    <label>Description:</label>
    <textarea name="description" id="modalInputDescription" rows="4" required></textarea>
    <label>Deadline:</label>
    <input type="date" name="deadline" id="modalInputDeadline">
    <div id="modalAttachments"><h4>Attachments:</h4></div>
    <label>Add New File:</label>
    <input type="file" name="new_attachment">
    <button type="submit" class="submit-btn">Save Changes</button>
</form>
</div>
</div>

<footer>Project Bidding System | Project Lead Portal</footer>

<script>
const attachments = <?= json_encode($attachments) ?>;

function openModal(id){ document.getElementById(id).style.display='block'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }

function viewProposal(proposalId){
    const modal = document.getElementById('viewProposalModal');
    const modalTitle = document.getElementById('modalTitle');
    const inputTitle = document.getElementById('modalInputTitle');
    const inputDesc = document.getElementById('modalInputDescription');
    const inputDeadline = document.getElementById('modalInputDeadline');
    const inputProposalId = document.getElementById('modalProposalId');
    const attContainer = document.getElementById('modalAttachments');

    // Find the proposal div by proposalId
    const proposalDiv = document.querySelector(`.proposal[data-proposalid='${proposalId}']`);
    if (!proposalDiv) return;

    // Load data from data-* attributes
    const title = proposalDiv.dataset.title;
    const description = proposalDiv.dataset.description;
    const deadline = proposalDiv.dataset.deadline;

    modalTitle.innerText = title;
    inputTitle.value = title;
    inputDesc.value = description;
    inputDeadline.value = deadline;
    inputProposalId.value = proposalId;

    // Load attachments from PHP array
    attContainer.innerHTML = "<h4>Attachments:</h4>";
    if (attachments[proposalId]) {
        attachments[proposalId].forEach(att => {
            const div = document.createElement('div');
            div.className = 'attachment-item';
            div.innerHTML = `${att.file_name} <a href="${att.file_path}" target="_blank">View</a>`;
            attContainer.appendChild(div);
        });
    }

    openModal('viewProposalModal');
}

</script>
</body>
</html>
