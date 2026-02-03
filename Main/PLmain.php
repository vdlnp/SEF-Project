<?php
session_start();
include "db.php"; // $conn defined here

// Check if user is logged in and is a Project Lead
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Project Lead') {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$userId = $user['id'];
$userName = $user['name'];
$userRoom = $user['room_code'];

// Handle AJAX actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Submit Proposal
    if ($action === 'submitProposal') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $fileName = '';

        // Handle file upload (robust checks and helpful error messages)
        if (isset($_FILES['file'])) {
            $fileError = $_FILES['file']['error'];
            if ($fileError === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/proposals/';
                if (!file_exists($uploadDir)) {
                    if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                        echo 'Failed to create upload directory';
                        exit;
                    }
                }

                $originalName = basename($_FILES['file']['name']);
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $allowed = ['pdf','doc','docx','txt','ppt','pptx'];
                if (!in_array(strtolower($extension), $allowed)) {
                    echo 'Invalid file type. Allowed: ' . implode(',', $allowed);
                    exit;
                }

                // Sanitize filename and prepend timestamp
                $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $originalName);
                $targetPath = $uploadDir . $fileName;

                if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                    echo 'Error moving uploaded file';
                    exit;
                }
            } else {
                $errMap = [
                    UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
                    UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
                    UPLOAD_ERR_PARTIAL => 'Partial upload',
                    UPLOAD_ERR_NO_FILE => 'No file uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
                ];
                $msg = $errMap[$fileError] ?? 'Unknown upload error';
                echo 'Upload error: ' . $msg;
                exit;
            }
        }

        $stmt = $conn->prepare("INSERT INTO proposals (user_id, room_code, title, description, file_path, status, submitted_at) VALUES (?, ?, ?, ?, ?, 'Under Review', NOW())");
        $stmt->bind_param("issss", $userId, $userRoom, $title, $description, $fileName);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'Error: ' . $conn->error;
        }
        exit;
    }
}

// Fetch project info for the user's room
$projectInfo = null;
if ($userRoom) {
    $stmt = $conn->prepare("SELECT * FROM project WHERE room_code = ?");
    $stmt->bind_param("s", $userRoom);
    $stmt->execute();
    $result = $stmt->get_result();
    $projectInfo = $result->fetch_assoc();
}

// Fetch user's proposals
$proposals = [];
$stmt = $conn->prepare("SELECT * FROM proposals WHERE user_id = ? ORDER BY submitted_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$proposals = $result->fetch_all(MYSQLI_ASSOC);

// Count notifications (proposals with feedback/status changes)
$notificationCount = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM proposals WHERE user_id = ? AND (status = 'Approved' OR status = 'Rejected')");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$notificationData = $result->fetch_assoc();
$notificationCount = $notificationData['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Project Lead Dashboard</title>

<style>
body {
    margin: 0;
    font-family: "Segoe UI", Arial, sans-serif;
    background: #1f1d29;
    color: #e6e6e6;
}

header {
    background: #1abc9c;
    color: white;
    padding: 16px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

header h1 {
    margin: 0;
    font-size: 22px;
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.notification {
    position: relative;
    font-size: 22px;
    color: white;
    text-decoration: none;
}

.badge {
    position: absolute;
    top: -6px;
    right: -10px;
    background: #e74c3c;
    color: white;
    font-size: 11px;
    padding: 3px 6px;
    border-radius: 50%;
}

.logout {
    text-decoration: none;
    color: white;
    font-weight: 600;
    border: 1px solid rgba(255,255,255,0.6);
    padding: 6px 16px;
    border-radius: 20px;
    transition: all 0.3s ease;
}

.logout:hover {
    background: #2e1b36;
    box-shadow: 0 0 15px rgba(26,188,156,0.7);
    transform: scale(1.05);
}

.container {
    min-height: calc(100vh - 140px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
}

.welcome {
    margin-bottom: 40px;
    font-size: 18px;
    color: #cfcfcf;
    text-align: center;
}

.project-info {
    background: #2c2a38;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    max-width: 600px;
    text-align: center;
}

.project-info h3 {
    color: #1abc9c;
    margin-top: 0;
}

.cards {
    display: flex;
    gap: 40px;
}

.card {
    background: #2c2a38;
    padding: 46px 80px;
    border-radius: 14px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.5);
    font-size: 18px;
    font-weight: 600;
    text-align: center;
    color: #1abc9c;
    cursor: pointer;
    transition: all 0.4s ease;
}

.card:hover {
    transform: translateY(-8px) scale(1.05);
    color: white;
    background: linear-gradient(135deg, #1abc9c, #2e1b36);
    box-shadow: 0 12px 30px rgba(0,0,0,0.8), 0 0 20px rgba(26,188,156,0.5);
}

/* MODAL */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.7);
    overflow-y: auto;
}

.modal-content {
    background-color: #2c2a38;
    margin: 3% auto;
    padding: 30px;
    border-radius: 14px;
    width: 90%;
    max-width: 700px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.6);
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #1abc9c;
}

.modal h2 {
    color: #1abc9c;
    margin-top: 0;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    color: #cfcfcf;
}

input, textarea {
    width: 100%;
    padding: 12px;
    margin-top: 12px;
    border-radius: 8px;
    border: none;
    background: #1f1d29;
    color: #e6e6e6;
}

input[type="file"] {
    padding: 8px;
}

.submit-btn {
    margin-top: 15px;
    background: #1abc9c;
    border: none;
    padding: 12px 20px;
    color: white;
    border-radius: 10px;
    cursor: pointer;
}

.proposal {
    background: #1f1d29;
    padding: 18px;
    border-radius: 10px;
    margin-top: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.proposal-info {
    max-width: 70%;
}

.status {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
}

.review, .under-review { background: #fbbf24; color: #78350f; }
.approved { background: #34d399; color: #064e3b; }
.rejected { background: #f87171; color: #7f1d1d; }

.view-btn {
    background: #1abc9c;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    color: white;
    cursor: pointer;
}

footer {
    background: #161421;
    color: #bbb;
    text-align: center;
    padding: 14px;
    font-size: 14px;
    border-top: 1px solid #333333;
}

.no-proposals {
    text-align: center;
    padding: 40px;
    color: #888;
}
</style>
</head>

<body>

<header>
    <h1>Project Lead Dashboard</h1>

    <div class="nav-right">
        <a href="#" class="notification" title="Notifications">
            🔔
            <?php if ($notificationCount > 0): ?>
            <span class="badge"><?= $notificationCount ?></span>
            <?php endif; ?>
        </a>
        <a href="index.php" class="logout">Logout</a>
    </div>
</header>

<div class="container">
    <div class="welcome">
        Welcome, <?= htmlspecialchars($userName) ?>! Manage and track your proposals
    </div>

    <?php if ($projectInfo): ?>
    <div class="project-info">
        <h3><?= htmlspecialchars($projectInfo['title']) ?></h3>
        <p><?= htmlspecialchars($projectInfo['description']) ?></p>
        <p><strong>Deadline:</strong> <?= date('F d, Y', strtotime($projectInfo['deadline'])) ?></p>
        <p><strong>Room Code:</strong> <span style="background:#1abc9c;padding:4px 10px;border-radius:4px;font-family:monospace;"><?= $projectInfo['room_code'] ?></span></p>
    </div>
    <?php endif; ?>

    <div class="cards">
        <div class="card" onclick="openModal('submitProposalModal')">
            Submit Proposal
        </div>

        <div class="card" onclick="openModal('myProposalsModal')">
            My Proposals
        </div>
    </div>
</div>

<!-- Submit Proposal Modal -->
<div id="submitProposalModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('submitProposalModal')">&times;</span>
        <h2>Submit Proposal</h2>
        
        <form id="proposalForm" enctype="multipart/form-data">
            <input type="text" id="proposalTitle" placeholder="Proposal Title" required>
            <textarea id="proposalDesc" rows="4" placeholder="Proposal Description" required></textarea>
            <div class="file-input">
                <label>Attach File:</label><br>
                <input type="file" id="proposalFile" name="file" accept=".pdf,.doc,.docx,.txt,.ppt,.pptx">
            </div>
            <button type="submit" class="submit-btn">Submit Proposal</button>
        </form>
    </div>
</div>

<!-- My Proposals Modal -->
<div id="myProposalsModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('myProposalsModal')">&times;</span>
        <h2>My Proposals</h2>

        <?php if (count($proposals) > 0): ?>
            <?php foreach($proposals as $p): ?>
            <div class="proposal">
                <div class="proposal-info">
                    <strong><?= htmlspecialchars($p['title']) ?></strong><br>
                    <span class="status <?= strtolower(str_replace(' ', '-', $p['status'])) ?>"><?= $p['status'] ?></span>
                </div>
                <button class="view-btn" onclick='viewProposal(<?= json_encode($p) ?>)'>View Details</button>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-proposals">
                <p>No proposals submitted yet</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Proposal Details Modal -->
<div id="proposalDetailsModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('proposalDetailsModal')">&times;</span>
        <h2 id="detailTitle"></h2>
        
        <div style="background: #1f1d29; padding: 20px; border-radius: 10px; margin-top: 20px;">
            <div style="margin-bottom: 15px;">
                <strong style="color: #1abc9c;">Status:</strong>
                <span id="detailStatus" class="status"></span>
            </div>
            
            <div style="margin-bottom: 15px;">
                <strong style="color: #1abc9c;">Submitted:</strong>
                <span id="detailDate"></span>
            </div>
            
            <div style="margin-bottom: 15px;">
                <strong style="color: #1abc9c;">Description:</strong>
                <p id="detailDescription" style="margin-top: 8px; line-height: 1.6;"></p>
            </div>
            
            <div id="detailFileSection" style="margin-bottom: 15px; display: none;">
                <strong style="color: #1abc9c;">Attached File:</strong>
                <a id="detailFileLink" href="#" target="_blank" style="color: #1abc9c; margin-left: 10px;">
                    📎 Download File
                </a>
            </div>
            
            <div id="detailFeedbackSection" style="margin-top: 20px; display: none;">
                <strong style="color: #1abc9c;">Reviewer Feedback:</strong>
                <p id="detailFeedback" style="margin-top: 8px; padding: 15px; background: #2c2a38; border-radius: 8px; line-height: 1.6;"></p>
            </div>
        </div>
    </div>
</div>

<footer>
    <span>Project Bidding System | Project Lead Portal</span>
</footer>

<script>
function openModal(id) {
    document.getElementById(id).style.display = 'block';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Handle proposal submission
document.getElementById('proposalForm').onsubmit = function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'submitProposal');
    formData.append('title', document.getElementById('proposalTitle').value);
    formData.append('description', document.getElementById('proposalDesc').value);
    
    const fileInput = document.getElementById('proposalFile');
    if (fileInput.files.length > 0) {
        formData.append('file', fileInput.files[0]);
    }
    
    fetch('PLmain.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(res => {
        if (res === 'success') {
            alert('Proposal submitted successfully!');
            location.reload();
        } else {
            alert('Error: ' + res);
        }
    })
    .catch(err => {
        alert('Error submitting proposal');
        console.error(err);
    });
};

function viewProposal(proposal) {
    // Populate the details modal
    document.getElementById('detailTitle').textContent = proposal.title;
    document.getElementById('detailDescription').textContent = proposal.description;
    document.getElementById('detailDate').textContent = formatDate(proposal.submitted_at);
    
    // Set status
    const statusElement = document.getElementById('detailStatus');
    statusElement.textContent = proposal.status;
    statusElement.className = 'status ' + proposal.status.toLowerCase().replace(' ', '-');
    
    // Handle file attachment
    if (proposal.file_path) {
        document.getElementById('detailFileSection').style.display = 'block';
        document.getElementById('detailFileLink').href = 'uploads/proposals/' + proposal.file_path;
    } else {
        document.getElementById('detailFileSection').style.display = 'none';
    }
    
    // Handle reviewer feedback
    if (proposal.reviewer_feedback) {
        document.getElementById('detailFeedbackSection').style.display = 'block';
        document.getElementById('detailFeedback').textContent = proposal.reviewer_feedback;
    } else {
        document.getElementById('detailFeedbackSection').style.display = 'none';
    }
    
    // Close the proposals list modal and open details modal
    closeModal('myProposalsModal');
    openModal('proposalDetailsModal');
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

</body>
</html>