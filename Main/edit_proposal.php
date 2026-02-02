<?php
session_start();
include "db.php";

$lead_id = $_SESSION['user_id'] ?? 2;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proposal_id = $_POST['proposal_id'];
    $project_id = $_POST['project_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $deadline = $_POST['deadline'] ?? null;

    // Only allow the lead who owns the proposal to edit
    $stmt = $conn->prepare("UPDATE proposal SET title=?, description=?, deadline=? WHERE id=? AND lead_id=?");
    $stmt->bind_param("sssii", $title, $description, $deadline, $proposal_id, $lead_id);
    if ($stmt->execute()) {

        // Handle new attachment upload
        if (!empty($_FILES['new_attachment']['name'])) {
            $file = $_FILES['new_attachment'];
            $filename = basename($file['name']);
            $target_dir = "uploads/proposals/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $target_file = $target_dir . time() . "_" . $filename;
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $stmt_attach = $conn->prepare("INSERT INTO proposal_attachments (proposal_id, file_name, file_path) VALUES (?, ?, ?)");
                $stmt_attach->bind_param("iss", $proposal_id, $filename, $target_file);
                $stmt_attach->execute();
                $stmt_attach->close();
            }
        }

        $stmt->close();
        header("Location: PLmain.php");
        exit;
    } else {
        echo "Error updating proposal: " . $conn->error;
    }
}
?>
