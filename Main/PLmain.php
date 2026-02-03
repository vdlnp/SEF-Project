<?php
session_start();
include "db.php";

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

                $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $originalName);
                $targetPath = $uploadDir . $fileName;

                if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                    echo 'Error moving uploaded file';
                    exit;
                }
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

    // Resubmit Rejected Proposal
    if ($action === 'resubmitProposal') {
        $proposalId = (int)($_POST['proposalId'] ?? 0);
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $fileName = '';

        // Verify the proposal belongs to the user and is rejected
        $check = $conn->prepare("SELECT file_path, status FROM proposals WHERE id = ? AND user_id = ?");
        $check->bind_param("ii", $proposalId, $userId);
        $check->execute();
        $check->bind_result($oldFile, $currentStatus);
        
        if (!$check->fetch()) {
            echo 'Proposal not found or unauthorized';
            exit;
        }
        $check->close();

        if ($currentStatus !== 'Rejected') {
            echo 'Only rejected proposals can be resubmitted';
            exit;
        }

        // Handle file upload
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/proposals/';
            $originalName = basename($_FILES['file']['name']);
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $allowed = ['pdf','doc','docx','txt','ppt','pptx'];
            
            if (in_array(strtolower($extension), $allowed)) {
                $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $originalName);
                $targetPath = $uploadDir . $fileName;
                move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);
            } else {
                $fileName = $oldFile;
            }
        } else {
            $fileName = $oldFile;
        }

        // Update the proposal
        $stmt = $conn->prepare("
            UPDATE proposals 
            SET title = ?, 
                description = ?, 
                file_path = ?, 
                status = 'Under Review',
                reviewer_feedback = NULL,
                reviewer_score = NULL,
                reviewed_at = NULL,
                submitted_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        $stmt->bind_param("sssii", $title, $description, $fileName, $proposalId, $userId);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'Error: ' . $conn->error;
        }
        exit;
    }
}

// Fetch project info
$projectInfo = null;
if ($userRoom) {
    $stmt = $conn->prepare("SELECT * FROM project WHERE room_code = ?");
    $stmt->bind_param("s", $userRoom);
    $stmt->execute();
    $result = $stmt->get_result();
    $projectInfo = $result->fetch_assoc();
}

// Fetch proposals
$proposals = [];
$stmt = $conn->prepare("SELECT * FROM proposals WHERE user_id = ? ORDER BY submitted_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$proposals = $result->fetch_all(MYSQLI_ASSOC);

// Count notifications
$notificationCount = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM proposals WHERE user_id = ? AND reviewer_feedback IS NOT NULL AND reviewer_feedback != ''");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$notificationData = $result->fetch_assoc();
$notificationCount = $notificationData['count'];

// Fetch notifications
$notifications = [];
$stmt = $conn->prepare("SELECT id, title, reviewer_feedback, reviewer_score, submitted_at FROM proposals WHERE user_id = ? AND reviewer_feedback IS NOT NULL AND reviewer_feedback != '' ORDER BY submitted_at DESC LIMIT 10");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
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
    cursor: pointer;
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
    font-weight: bold;
}

.notification-dropdown {
    display: none;
    position: absolute;
    top: 50px;
    right: 100px;
    background: #2c2a38;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.6);
    width: 350px;
    max-height: 400px;
    overflow-y: auto;
    z-index: 1000;
}

.notification-dropdown.show {
    display: block;
}

.notification-header {
    padding: 15px 20px;
    border-bottom: 1px solid #1f1d29;
    font-weight: 600;
    color: #1abc9c;
}

.notification-item {
    padding: 15px 20px;
    border-bottom: 1px solid #1f1d29;
    transition: background 0.3s;
}

.notification-item:hover {
    background: #1f1d29;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-title {
    font-weight: 600;
    color: #1abc9c;
    margin-bottom: 5px;
}

.notification-message {
    font-size: 13px;
    color: #cfcfcf;
    margin-bottom: 5px;
}

.notification-time {
    font-size: 11px;
    color: #888;
}

.no-notifications {
    padding: 30px 20px;
    text-align: center;
    color: #888;
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

input, textarea {
    width: 100%;
    padding: 12px;
    margin-top: 12px;
    border-radius: 8px;
    border: none;
    background: #1f1d29;
    color: #e6e6e6;
    box-sizing: border-box;
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
    font-weight: 600;
}

.submit-btn:hover {
    background: #16a085;
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

.under-review { background: #fbbf24; color: #78350f; }
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

.btn-secondary {
    background: #34495e;
    border: none;
    padding: 12px 20px;
    color: white;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}

footer {
    background: #161421;
    color: #bbb;
    text-align: center;
    padding: 14px;
    font-size: 14px;
    border-top: 1px solid #333333;
}
</style>
</head>

<body>

<header>
    <h1>Project Lead Dashboard</h1>

    <div class="nav-right">
        <div style="position: relative;">
            <a class="notification" onclick="toggleNotifications()" title="Notifications">
                🔔
                <?php if ($notificationCount > 0): ?>
                <span class="badge"><?= $notificationCount ?></span>
                <?php endif; ?>
            </a>
            
            <div id="notificationDropdown" class="notification-dropdown">
                <div class="notification-header">
                    Notifications (<?= $notificationCount ?>)
                </div>
                
                <?php if (count($notifications) > 0): ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="notification-item">
                            <div class="notification-title"><?= htmlspecialchars($notif['title']) ?></div>
                            <div class="notification-message">
                                Your proposal was reviewed! Score: <?= $notif['reviewer_score'] ?? 'N/A' ?>/10
                            </div>
                            <div class="notification-time">
                                Reviewed at <?= date('M d, Y g:i A', strtotime($notif['submitted_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-notifications">
                        No new notifications
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
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
            <div style="text-align:center;padding:40px;color:#888;">
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
            
            <!-- View Mode -->
            <div id="viewMode">
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

                <div id="detailScoreSection" style="margin-bottom: 15px; display: none;">
                    <strong style="color: #1abc9c;">Reviewer Score:</strong>
                    <span id="detailScore" style="margin-left: 10px; font-weight: bold;"></span>
                </div>
                
                <div id="detailFeedbackSection" style="margin-top: 20px; display: none;">
                    <strong style="color: #1abc9c;">Reviewer Feedback:</strong>
                    <p id="detailFeedback" style="margin-top: 8px; padding: 15px; background: #2c2a38; border-radius: 8px; line-height: 1.6;"></p>
                </div>

                <div id="editButtonSection" style="margin-top: 20px; display: none;">
                    <button class="submit-btn" onclick="enableEditMode()">
                        ✏️ Edit & Resubmit Proposal
                    </button>
                </div>
            </div>

            <!-- Edit Mode -->
            <div id="editMode" style="display: none;">
                <form id="resubmitForm" enctype="multipart/form-data">
                    <input type="hidden" id="resubmitProposalId">
                    
                    <div style="margin-bottom: 15px;">
                        <label style="color: #1abc9c; display: block; margin-bottom: 8px;">Title:</label>
                        <input type="text" id="editTitle" required style="width: 100%; padding: 10px; background: #2c2a38; border: 1px solid #555; color: white; border-radius: 6px;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="color: #1abc9c; display: block; margin-bottom: 8px;">Description:</label>
                        <textarea id="editDescription" rows="5" required style="width: 100%; padding: 10px; background: #2c2a38; border: 1px solid #555; color: white; border-radius: 6px;"></textarea>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="color: #1abc9c; display: block; margin-bottom: 8px;">Attachment:</label>
                        <div id="currentFileInfo" style="margin-bottom: 10px; padding: 10px; background: #2c2a38; border-radius: 6px;">
                            <span style="color: #cfcfcf;">Current file: </span>
                            <span id="currentFileName" style="color: #1abc9c;"></span>
                        </div>
                        <input type="file" id="editFile" name="file" accept=".pdf,.doc,.docx,.txt,.ppt,.pptx" style="width: 100%; padding: 8px; background: #2c2a38; border: 1px solid #555; color: white; border-radius: 6px;">
                        <small style="color: #888; display: block; margin-top: 5px;">Leave empty to keep current file, or upload a new one to replace it</small>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="button" class="btn-secondary" onclick="cancelEditMode()" style="flex: 1;">
                            Cancel
                        </button>
                        <button type="submit" class="submit-btn" style="flex: 1;">
                            Resubmit Proposal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<footer>
    <span>Project Bidding System | Project Lead Portal</span>
</footer>

<script>
let currentProposal = null;

function openModal(id) {
    document.getElementById(id).style.display = 'block';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.classList.toggle('show');
}

document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('notificationDropdown');
    const bell = document.querySelector('.notification');
    
    if (!bell.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

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
    });
};

document.getElementById('resubmitForm').onsubmit = function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'resubmitProposal');
    formData.append('proposalId', document.getElementById('resubmitProposalId').value);
    formData.append('title', document.getElementById('editTitle').value);
    formData.append('description', document.getElementById('editDescription').value);
    
    const fileInput = document.getElementById('editFile');
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
            alert('Proposal resubmitted successfully!');
            location.reload();
        } else {
            alert('Error: ' + res);
        }
    });
};

function viewProposal(proposal) {
    currentProposal = proposal;
    
    document.getElementById('viewMode').style.display = 'block';
    document.getElementById('editMode').style.display = 'none';
    
    document.getElementById('detailTitle').textContent = proposal.title;
    document.getElementById('detailDescription').textContent = proposal.description;
    document.getElementById('detailDate').textContent = formatDate(proposal.submitted_at);

    const statusElement = document.getElementById('detailStatus');
    statusElement.textContent = proposal.status;
    statusElement.className = 'status ' + proposal.status.toLowerCase().replace(' ', '-');

    if (proposal.file_path) {
        document.getElementById('detailFileSection').style.display = 'block';
        document.getElementById('detailFileLink').href = 'uploads/proposals/' + proposal.file_path;
    } else {
        document.getElementById('detailFileSection').style.display = 'none';
    }

    if (proposal.reviewer_feedback) {
        document.getElementById('detailFeedbackSection').style.display = 'block';
        document.getElementById('detailFeedback').textContent = proposal.reviewer_feedback;
    } else {
        document.getElementById('detailFeedbackSection').style.display = 'none';
    }

    if (proposal.reviewer_score !== undefined && proposal.reviewer_score !== null) {
        document.getElementById('detailScoreSection').style.display = 'block';
        document.getElementById('detailScore').textContent = proposal.reviewer_score + '/10';
    } else {
        document.getElementById('detailScoreSection').style.display = 'none';
    }

    if (proposal.status === 'Rejected') {
        document.getElementById('editButtonSection').style.display = 'block';
    } else {
        document.getElementById('editButtonSection').style.display = 'none';
    }

    closeModal('myProposalsModal');
    openModal('proposalDetailsModal');
}

function enableEditMode() {
    document.getElementById('viewMode').style.display = 'none';
    document.getElementById('editMode').style.display = 'block';
    
    document.getElementById('resubmitProposalId').value = currentProposal.id;
    document.getElementById('editTitle').value = currentProposal.title;
    document.getElementById('editDescription').value = currentProposal.description;
    
    if (currentProposal.file_path) {
        document.getElementById('currentFileInfo').style.display = 'block';
        document.getElementById('currentFileName').textContent = currentProposal.file_path;
    } else {
        document.getElementById('currentFileInfo').style.display = 'none';
    }
    
    document.getElementById('detailTitle').textContent = 'Edit & Resubmit: ' + currentProposal.title;
}

function cancelEditMode() {
    document.getElementById('viewMode').style.display = 'block';
    document.getElementById('editMode').style.display = 'none';
    document.getElementById('detailTitle').textContent = currentProposal.title;
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