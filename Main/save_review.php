<?php
session_start();
include "db.php";


if (!isset($_SESSION['user'])) {
    echo "Unauthorized: Not logged in";
    exit;
}

$user = $_SESSION['user'];

if (($user['role'] ?? '') !== 'Reviewer') {
    echo "Unauthorized: Not a reviewer";
    exit;
}

$room_code = mysqli_real_escape_string($conn, $user['room_code'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proposal_id = (int)($_POST['proposal_id'] ?? 0);
    $action = $_POST['action'] ?? 'save';
    
    if ($proposal_id <= 0) {
        echo "Invalid proposal ID";
        exit;
    }
    
    // Verify proposal belongs to reviewer's room
    $check = $conn->prepare("SELECT room_code FROM proposals WHERE id = ?");
    $check->bind_param("i", $proposal_id);
    $check->execute();
    $check->bind_result($proposal_room);
    
    if (!$check->fetch()) {
        echo "Proposal not found";
        exit;
    }
    $check->close();
    
    if ($proposal_room !== $room_code) {
        echo "Unauthorized: Proposal not in your room";
        exit;
    }
    
    // Handle delete action
    if ($action === 'delete') {
        $stmt = $conn->prepare("
            UPDATE proposals 
            SET reviewer_feedback = NULL, reviewer_score = NULL 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $proposal_id);
        
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Error deleting review: " . $stmt->error;
        }
        $stmt->close();
        exit;
    }
    
    // Handle save/update action
    $comments = trim($_POST['comments'] ?? '');
    $score = $_POST['score'] ?? null;
    
    if (empty($comments)) {
        echo "Comments are required";
        exit;
    }
    
    if ($score === null || $score === '') {
        echo "Score is required";
        exit;
    }
    
    $score = (float)$score;
    
    if ($score < 0 || $score > 10) {
        echo "Score must be between 0 and 10";
        exit;
    }
    
    $stmt = $conn->prepare("
        UPDATE proposals 
        SET reviewer_feedback = ?, reviewer_score = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("sdi", $comments, $score, $proposal_id);
    
    if ($stmt->execute()) {
        echo "success";
        header("Location: ReviewerMain.php");
        exit;
    } else {
        echo "Error saving review: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Invalid request method";
}
?>