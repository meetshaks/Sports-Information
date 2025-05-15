<?php
session_start();
include 'api/config.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    error_log("Delete.php: No schedule ID provided.");
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No schedule ID provided.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => { window.location='overview.php'; });
            });
          </script>";
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("DELETE FROM schedule WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Schedule deleted successfully!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => { window.location='overview.php'; });
            });
          </script>";
} else {
    error_log("Delete.php: Failed to delete schedule ID $id. Error: " . $stmt->error);
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete schedule.',
                    showConfirmButton: true
                });
            });
          </script>";
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing...</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body></body>
</html>