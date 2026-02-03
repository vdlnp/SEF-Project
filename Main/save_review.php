<?php
session_start();
include "db.php";

// Ensure logged-in user is a Reviewer
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'Reviewer') {
    die("Unauthorized");
}

$reviewer_id = (int)($_SESSION['user']['id'] ?? 0);
$user_room = $_SESSION['user']['room_code'] ?? '';

// Get POST data
$proposal_id = isset($_POST['proposal_id']) ? (int)$_POST['proposal_id'] : 0;
$comments    = trim($_POST['comments'] ?? '');
$score       = isset($_POST['score']) ? (float)$_POST['score'] : null;

if ($proposal_id <= 0 || $comments === '' || $score === null) {
    die("Proposal ID, comments, and score are required.");
}

// Verify proposal exists and belongs to same room
$check_stmt = $conn->prepare("
    SELECT room_code, status 
    FROM proposals 
    WHERE id = ?
");
$check_stmt->bind_param("i", $proposal_id);
$check_stmt->execute();
$check_stmt->bind_result($proposal_room, $proposal_status);

if (!$check_stmt->fetch()) {
    $check_stmt->close();
    die("Proposal not found.");
}
$check_stmt->close();

if ($proposal_room !== $user_room) {
    die("You are not authorized to review this proposal.");
}

if ($proposal_status !== 'Approved') {
    die("Proposal is not available for review.");
}

// Update proposal with review
$update_stmt = $conn->prepare("
    UPDATE proposals 
    SET 
        reviewer_feedback = ?, 
        reviewer_score = ?, 
        status = 'Reviewed',
        reviewed_at = NOW()
    WHERE id = ?
");

$update_stmt->bind_param("sdi", $comments, $score, $proposal_id);

if ($update_stmt->execute()) {
    header("Location: ReviewerMain.php?msg=review_saved");
    exit;
} else {
    die("Failed to save review: " . $conn->error);
}

$update_stmt->close();
$conn->close();
