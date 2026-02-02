<?php
session_start();
include "db.php";

$reviewer_id = $_SESSION['user_id'] ?? 1; // fallback for testing

// Get POST data
$proposal_id = $_POST['proposal_id'] ?? null;
$comments    = $_POST['comments'] ?? '';
$score       = $_POST['score'] ?? null;

if (!$proposal_id || $score === null) {
    die("Proposal ID and score are required.");
}

// Check if this reviewer has already reviewed this proposal
$check_query = "SELECT id FROM proposal_reviews WHERE proposal_id = ? AND reviewer_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $proposal_id, $reviewer_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Update existing review
    $update_query = "UPDATE proposal_reviews 
                     SET comments = ?, score = ?, status = 'reviewed' 
                     WHERE proposal_id = ? AND reviewer_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sdii", $comments, $score, $proposal_id, $reviewer_id);
    if ($update_stmt->execute()) {
        header("Location: ReviewerMain.php?msg=updated");
        exit;
    } else {
        die("Failed to update review: " . $conn->error);
    }
} else {
    // Insert new review
    $insert_query = "INSERT INTO proposal_reviews (proposal_id, reviewer_id, comments, score, status) 
                     VALUES (?, ?, ?, ?, 'reviewed')";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("iisd", $proposal_id, $reviewer_id, $comments, $score);
    if ($insert_stmt->execute()) {
        header("Location: ReviewerMain.php?msg=added");
        exit;
    } else {
        die("Failed to save review: " . $conn->error);
    }
}

$stmt->close();
$conn->close();
?>
