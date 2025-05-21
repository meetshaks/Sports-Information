<?php
include 'config.php';

if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger'>No schedule ID provided.</div>";
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM schedule WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<div class='alert alert-danger'>Schedule not found.</div>";
    exit;
}

$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Edit Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Schedule Tracker</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Create Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../overview.php">Overview</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../viewer.php">Viewer</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <h2 class="text-center mb-4">Edit Schedule</h2>
        <form id="editForm" action="update.php" method="post" class="card p-4 shadow-sm create-schedule-card">
            <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
            <div class="mb-3">
                <label for="time_slot" class="form-label">Date and Time</label>
                <input type="datetime-local" class="form-control" id="time_slot" name="time_slot" value="<?php echo date('Y-m-d\TH:i', strtotime($data['time_slot'])); ?>" min="2025-05-14T15:49" required>
            </div>
            <div class="mb-3">
                <label for="tour_name" class="form-label">Tour Name</label>
                <input type="text" class="form-control" id="tour_name" name="tour_name" value="<?php echo htmlspecialchars($data['tour_name']); ?>" placeholder="Enter tour name" required>
            </div>
            <div class="mb-3">
                <label for="match_info" class="form-label">Matches</label>
                <select class="form-select" id="match_info" name="match_info" required>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $data['match_info'] == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <?php
                    $statuses = ['No Update', 'Qualified', 'Disqualified'];
                    foreach ($statuses as $status):
                    ?>
                        <option value="<?php echo $status; ?>" <?php echo $data['status'] == $status ? 'selected' : ''; ?>><?php echo $status; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="booking_price" class="form-label">Booking Price</label>
                <input type="number" step="0.01" class="form-control" id="booking_price" name="booking_price" value="<?php echo $data['booking_price'] == intval($data['booking_price']) ? intval($data['booking_price']) : $data['booking_price']; ?>" placeholder="e.g., 20">
            </div>
            <div class="mb-3">
                <label for="sell_earn_price" class="form-label">Sell/Earn Price</label>
                <input type="number" step="0.01" class="form-control" id="sell_earn_price" name="sell_earn_price" value="<?php echo $data['sell_earn_price'] == intval($data['sell_earn_price']) ? intval($data['sell_earn_price']) : $data['sell_earn_price']; ?>" placeholder="e.g., 20">
            </div>
            <button type="submit" class="btn btn-primary w-100 py-3 create-submit-btn">Update Schedule</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="script.js"></script>
</body>
</html>