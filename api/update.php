<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../overview.php");
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
$time_slot = filter_input(INPUT_POST, 'time_slot', FILTER_SANITIZE_STRING);
$tour_name = filter_input(INPUT_POST, 'tour_name', FILTER_SANITIZE_STRING);
$match_info = filter_input(INPUT_POST, 'match_info', FILTER_SANITIZE_NUMBER_INT);
$status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
$booking_price = filter_input(INPUT_POST, 'booking_price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$sell_earn_price = filter_input(INPUT_POST, 'sell_earn_price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

if (!$id || !$time_slot || !$tour_name || !$match_info || !$status) {
    echo "<script>alert('Please fill all required fields.'); window.location='edit.php?id=$id';</script>";
    exit;
}

$schedule_date = date('Y-m-d', strtotime($time_slot));

$stmt = $conn->prepare("UPDATE schedule SET schedule_date = ?, time_slot = ?, tour_name = ?, match_info = ?, status = ?, booking_price = ?, sell_earn_price = ? WHERE id = ?");
$stmt->bind_param("sssssssi", $schedule_date, $time_slot, $tour_name, $match_info, $status, $booking_price, $sell_earn_price, $id);

if ($stmt->execute()) {
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Schedule updated successfully!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => { window.location='../overview.php'; });
            });
          </script>";
} else {
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update schedule.',
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