<?php
session_start();
include 'api/config.php';

// Check if user is logged in and session is still active
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 120))) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Schedule Overview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="api/style.css">
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
                        <a class="nav-link" href="index.php">Create Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="overview.php">Overview</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="viewer.php">Viewer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <h2 class="text-center mb-4">Schedule Overview</h2>
        <div class="mobile-scroll-hint d-block d-md-none text-center mb-3">Swipe left/right to view all columns</div>
        <?php
        $query = "SELECT * FROM schedule ORDER BY schedule_date, time_slot";
        $result = $conn->query($query);
        $current_date = '';
        $tables = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $date = $row['schedule_date'];
                if (!isset($tables[$date])) {
                    $tables[$date] = [
                        'header' => "<div class='date-header'>Date: {$date}</div>",
                        'rows' => []
                    ];
                }
                $dateTime = new DateTime($row['time_slot']);
                $formatted_time = $dateTime->format('h:i A');
                $tables[$date]['rows'][] = [
                    'id' => $row['id'],
                    'time_slot' => $formatted_time,
                    'tour_name' => htmlspecialchars($row['tour_name']),
                    'match_info' => $row['match_info'],
                    'status' => $row['status'],
                    'booking_price' => $row['booking_price'],
                    'sell_earn_price' => $row['sell_earn_price']
                ];
            }

            foreach ($tables as $date => $table) {
                echo $table['header'];
                echo "<div class='table-responsive'>";
                echo "<table class='table table-striped table-bordered data-table' width='100%'>";
                echo "<thead><tr>
                        <th class='time-col'>Time</th>
                        <th class='tour-name-col'>Tour Name</th>
                        <th class='match-col'>Match</th>
                        <th class='status-col'>Status</th>
                        <th class='booking-col'>Booking</th>
                        <th class='earn-col'>Earn/Sell</th>
                        <th class='actions-col'>Actions</th>
                      </tr></thead><tbody>";
                foreach ($table['rows'] as $row) {
                    $booking_price = $row['booking_price'] == intval($row['booking_price']) ? intval($row['booking_price']) : number_format($row['booking_price'], 2);
                    $sell_earn_price = $row['sell_earn_price'] == intval($row['sell_earn_price']) ? intval($row['sell_earn_price']) : number_format($row['sell_earn_price'], 2);
                    $status_class = strtolower(str_replace(' ', '-', $row['status']));
                    echo "<tr>
                            STOPPED HERE<td class='time-col'>{$row['time_slot']}</td>
                            <td class='tour-name-col tour-name-cell'>{$row['tour_name']}</td>
                            <td class='match-col'>{$row['match_info']}</td>
                            <td class='status-col status-cell {$status_class}'>{$row['status']}</td>
                            <td class='booking-col'>{$booking_price}</td>
                            <td class='earn-col'>{$sell_earn_price}</td>
                            <td class='actions-col'>
                                <div class='btn-group' role='group'>
                                    <a href='api/edit.php?id={$row['id']}' class='btn btn-sm btn-success'>Edit</a>
                                    <button class='btn btn-sm btn-danger delete-btn' data-id='{$row['id']}'>Delete</button>
                                </div>
                            </td>
                          </tr>";
                }
                echo "</tbody></table>";
                echo "</div>";
            }
        } else {
            echo "<div class='alert alert-info text-center'>No schedules found.</div>";
        }
        $conn->close();
        ?>
        <button id="backToTop" class="btn btn-primary back-to-top d-none">Back to Top</button>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="api/script.js"></script>
</body>
</html>