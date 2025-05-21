<?php
session_start();
include 'api/config.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Get statistics
$stats = array();

// Total matches
$result = $conn->query("SELECT COUNT(*) as total FROM schedule");
$stats['total_matches'] = $result->fetch_assoc()['total'];

// Total qualified
$result = $conn->query("SELECT COUNT(*) as total FROM schedule WHERE status = 'Qualified'");
$stats['qualified'] = $result->fetch_assoc()['total'];

// Total disqualified
$result = $conn->query("SELECT COUNT(*) as total FROM schedule WHERE status = 'Disqualified'");
$stats['disqualified'] = $result->fetch_assoc()['total'];

// Total no update
$result = $conn->query("SELECT COUNT(*) as total FROM schedule WHERE status = 'No Update'");
$stats['no_update'] = $result->fetch_assoc()['total'];

// Total booking price
$result = $conn->query("SELECT SUM(booking_price) as total FROM schedule");
$stats['total_booking'] = $result->fetch_assoc()['total'] ?? 0;

// Total earnings
$result = $conn->query("SELECT SUM(sell_earn_price) as total FROM schedule");
$stats['total_earnings'] = $result->fetch_assoc()['total'] ?? 0;

// Calculate profit
$stats['profit'] = $stats['total_earnings'] - $stats['total_booking'];

// Get status distribution for chart
$result = $conn->query("SELECT status, COUNT(*) as count FROM schedule GROUP BY status");
$status_data = array();
while ($row = $result->fetch_assoc()) {
    $status_data[$row['status']] = $row['count'];
}

// Get monthly statistics
$result = $conn->query("SELECT 
    DATE_FORMAT(schedule_date, '%Y-%m') as month,
    COUNT(*) as total_matches,
    SUM(CASE WHEN status = 'Qualified' THEN 1 ELSE 0 END) as qualified,
    SUM(CASE WHEN status = 'Disqualified' THEN 1 ELSE 0 END) as disqualified,
    SUM(CASE WHEN status = 'No Update' THEN 1 ELSE 0 END) as no_update,
    SUM(booking_price) as total_booking,
    SUM(sell_earn_price) as total_earnings
    FROM schedule 
    GROUP BY DATE_FORMAT(schedule_date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12");
$monthly_stats = array();
while ($row = $result->fetch_assoc()) {
    $monthly_stats[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Schedule Tracker</title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/3652/3652191.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="api/style.css">
    <link rel="stylesheet" href="api/navbar.css">
    <link rel="stylesheet" href="api/variables.css">
    <link rel="stylesheet" href="api/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            min-height: 150vh; /* Ensure content is scrollable */
            overflow-y: auto;
        }
        .custom-scrollbar {
            opacity: 1 !important; /* Force scrollbar to be visible */
        }
    </style>
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
                        <a class="nav-link" href="index.php"><i class="bi bi-plus-circle me-1"></i>Create Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="overview.php"><i class="bi bi-grid me-1"></i>Overview</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="viewer.php"><i class="bi bi-eye me-1"></i>Viewer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php?from=overview"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
            <div class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4 g-4">
            <div class="col-md-3">
                <div class="dashboard-card stat-card">
                    <div class="number"><i class="bi bi-trophy me-2"></i><?php echo $stats['total_matches']; ?></div>
                    <div class="label">Total Matches</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stat-card" style="border-left-color: var(--success-color);">
                    <div class="number"><i class="bi bi-check-circle me-2"></i><?php echo $stats['qualified']; ?></div>
                    <div class="label">Qualified</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stat-card" style="border-left-color: var(--danger-color);">
                    <div class="number"><i class="bi bi-x-circle me-2"></i><?php echo $stats['disqualified']; ?></div>
                    <div class="label">Disqualified</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stat-card" style="border-left-color: var(--warning-color);">
                    <div class="number"><i class="bi bi-question-circle me-2"></i><?php echo $stats['no_update']; ?></div>
                    <div class="label">No Update</div>
                </div>
            </div>
        </div>

        <!-- Financial Overview -->
        <div class="row mb-4 g-4">
            <div class="col-md-4">
                <div class="dashboard-card stat-card" style="border-left-color: var(--info-color);">
                    <div class="number"><i class="bi bi-currency-dollar me-2"></i><?php echo number_format($stats['total_booking'], 2); ?></div>
                    <div class="label">Total Booking</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card stat-card" style="border-left-color: var(--success-color);">
                    <div class="number"><i class="bi bi-graph-up-arrow me-2"></i><?php echo number_format($stats['total_earnings'], 2); ?></div>
                    <div class="label">Total Earnings</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card stat-card" style="border-left-color: <?php echo $stats['profit'] >= 0 ? 'var(--success-color)' : 'var(--danger-color)'; ?>">
                    <div class="number" style="color: <?php echo $stats['profit'] >= 0 ? 'var(--success-color)' : 'var(--danger-color)'; ?>">
                        <i class="bi bi-cash-stack me-2"></i><?php echo number_format($stats['profit'], 2); ?>
                    </div>
                    <div class="label">Profit</div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h3><i class="bi bi-pie-chart me-2"></i>Status Distribution</h3>
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h3><i class="bi bi-graph-up me-2"></i>Monthly Overview</h3>
                    <div class="chart-container">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Scrollbar -->
    <div class="custom-scrollbar">
        <div class="scrollbar-progress"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="api/script.js"></script>
    <script>
        // Initialize scrollbar immediately
        $(document).ready(function() {
            // Force scrollbar to be visible
            $('.custom-scrollbar').css('opacity', '1');
            
            // Initialize scrollbar
            updateScrollProgress();
            
            // Add scroll event listener
            $(window).on('scroll', function() {
                updateScrollProgress();
            });
        });

        // Status Distribution Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Qualified', 'Disqualified', 'No Update'],
                datasets: [{
                    data: [
                        <?php echo $status_data['Qualified'] ?? 0; ?>,
                        <?php echo $status_data['Disqualified'] ?? 0; ?>,
                        <?php echo $status_data['No Update'] ?? 0; ?>
                    ],
                    backgroundColor: [
                        '#28a745',  // Success green
                        '#dc3545',  // Danger red
                        '#6c757d'   // Secondary gray
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });

        // Monthly Overview Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column(array_reverse($monthly_stats), 'month')); ?>,
                datasets: [{
                    label: 'Qualified',
                    data: <?php echo json_encode(array_column(array_reverse($monthly_stats), 'qualified')); ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8
                }, {
                    label: 'Disqualified',
                    data: <?php echo json_encode(array_column(array_reverse($monthly_stats), 'disqualified')); ?>,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#dc3545',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8
                }, {
                    label: 'No Update',
                    data: <?php echo json_encode(array_column(array_reverse($monthly_stats), 'no_update')); ?>,
                    borderColor: '#6c757d',
                    backgroundColor: 'rgba(108, 117, 125, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#6c757d',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 12,
                        callbacks: {
                            title: function(tooltipItems) {
                                return 'Month: ' + tooltipItems[0].label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    </script>
</body>
</html> 