<?php include 'api/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Viewer Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="api/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Schedule Tracker</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Create Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="overview.php">Overview</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="viewer.php">Viewer</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="text-center mb-4">Viewer Schedule</h2>
        
        <!-- Date Range Download Form -->
        <div class="card p-4 mb-4 shadow-sm create-schedule-card">
            <h4 class="mb-3">Download Schedules</h4>
            <form action="api/download_pdf.php" method="get" class="row g-3">
                <div class="col-md-5">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                </div>
                <div class="col-md-5">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 create-submit-btn">Download PDF</button>
                </div>
            </form>
        </div>

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
                        <th class='earn-col'>Earn</th>
                      </tr></thead><tbody>";
                foreach ($table['rows'] as $row) {
                    $booking_price = $row['booking_price'] == intval($row['booking_price']) ? intval($row['booking_price']) : number_format($row['booking_price'], 2);
                    $sell_earn_price = $row['sell_earn_price'] == intval($row['sell_earn_price']) ? intval($row['sell_earn_price']) : number_format($row['sell_earn_price'], 2);
                    $status_class = strtolower(str_replace(' ', '-', $row['status']));
                    echo "<tr>
                            <td class='time-col'>{$row['time_slot']}</td>
                            <td class='tour-name-col tour-name-cell'>{$row['tour_name']}</td>
                            <td class='match-col'>{$row['match_info']}</td>
                            <td class='status-col status-cell {$status_class}'>{$row['status']}</td>
                            <td class='booking-col'>{$booking_price}</td>
                            <td class='earn-col'>{$sell_earn_price}</td>
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
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="api/script.js"></script>
</body>
</html>