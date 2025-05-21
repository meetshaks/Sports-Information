<?php
session_start();
include 'api/config.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Create Schedule - Schedule Tracker</title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/3652/3652191.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="api/variables.css">
    <link rel="stylesheet" href="api/style.css">
    <link rel="stylesheet" href="api/navbar.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="bi bi-calendar-check me-2"></i>Schedule Tracker</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="bi bi-plus-circle me-1"></i>Create Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="overview.php"><i class="bi bi-grid me-1"></i>Overview</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="viewer.php"><i class="bi bi-eye me-1"></i>Viewer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php?from=overview"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <h2 class="text-center mb-4">Create a Schedule</h2>
        <form id="scheduleForm" action="api/submit.php" method="post" class="card p-4 shadow-sm create-schedule-card">
            <div class="mb-3">
                <label for="time_slot" class="form-label">Date and Time</label>
                <input type="datetime-local" class="form-control" id="time_slot" name="time_slot" min="2025-05-14T15:49" required>
            </div>
            <div class="mb-3">
                <label for="tour_name" class="form-label">Tour Name</label>
                <input type="text" class="form-control" id="tour_name" name="tour_name" placeholder="Enter tour name" required>
            </div>
            <div class="mb-3">
                <label for="match_info" class="form-label">Matches</label>
                <select class="form-select" id="match_info" name="match_info" required>
                    <option value="" disabled selected>Select number of matches</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="No Update">No Update</option>
                    <option value="Qualified">Qualified</option>
                    <option value="Disqualified">Disqualified</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="booking_price" class="form-label">Booking Price</label>
                <input type="number" step="0.01" class="form-control" id="booking_price" name="booking_price" placeholder="e.g., 20">
            </div>
            <div class="mb-3">
                <label for="sell_earn_price" class="form-label">Sell/Earn Price</label>
                <input type="number" step="0.01" class="form-control" id="sell_earn_price" name="sell_earn_price" placeholder="e.g., 20">
            </div>
            <button type="submit" class="btn btn-primary w-100 py-3 create-submit-btn">Submit Schedule</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="api/script.js"></script>
</body>
</html>