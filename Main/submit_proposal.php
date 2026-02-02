<?php
session_start();
include "db.php";

$lead_id = $_SESSION['user_id'] ?? 2; // fallback for testing

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $project_id = $_POST['project_id'] ?? null;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = 'Under Review'; // default status

    if (!$project_id) {
        die("Error: Project not selected.");
    }

    // Insert into proposal table
    $stmt = $conn->prepare("INSERT INTO proposal (project_id, lead_id, title, description, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iisss", $project_id, $lead_id, $title, $description, $status);
    $stmt->execute();
    $proposal_id = $stmt->insert_id;
    $stmt->close();

    // Handle file attachment if uploaded
    if (!empty($_FILES['attachment']['name'])) {
        $file_name = $_FILES['attachment']['name'];
        $tmp_name = $_FILES['attachment']['tmp_name'];
        $upload_dir = "uploads/proposals/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $file_path = $upload_dir . time() . "_" . basename($file_name);
        if (move_uploaded_file($tmp_name, $file_path)) {
            $stmt2 = $conn->prepare("INSERT INTO proposal_attachments (proposal_id, file_name, file_path) VALUES (?, ?, ?)");
            $stmt2->bind_param("iss", $proposal_id, $file_name, $file_path);
            $stmt2->execute();
            $stmt2->close();
        }
    }

    header("Location: PLmain.php");
    exit;
}
?>
