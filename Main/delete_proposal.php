<?php
session_start();
include "db.php";

var_dump($_SESSION);
var_dump($_GET);
exit;

session_start();
include "db.php";

$lead_id = $_SESSION['user_id'] ?? 0;
$proposal_id = intval($_GET['id'] ?? 0);

if ($lead_id === 0 || $proposal_id === 0) {
    die("Invalid request.");
}

// Verify ownership
$check = mysqli_query($conn, "
    SELECT id FROM proposal
    WHERE id = $proposal_id AND lead_id = $lead_id
");

if (mysqli_num_rows($check) === 0) {
    die("Unauthorized action.");
}

// Delete attachments first
mysqli_query($conn, "
    DELETE FROM proposal_attachments
    WHERE proposal_id = $proposal_id
");

// Delete proposal
mysqli_query($conn, "
    DELETE FROM proposal
    WHERE id = $proposal_id
");

header("Location: project_lead_dashboard.php");
exit;
