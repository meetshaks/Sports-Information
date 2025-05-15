<?php
// Start output buffering to catch any unintended output
ob_start();

include 'config.php';
require_once 'tcpdf/tcpdf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header("Location: ../viewer.php");
    exit;
}

$start_date = filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_STRING);
$end_date = filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_STRING);

if (!$start_date || !$end_date || !strtotime($start_date) || !strtotime($end_date)) {
    echo "<script>alert('Please provide valid start and end dates.'); window.location='../viewer.php';</script>";
    ob_end_flush();
    exit;
}

// Ensure end_date is not before start_date
if (strtotime($end_date) < strtotime($start_date)) {
    echo "<script>alert('End date cannot be before start date.'); window.location='../viewer.php';</script>";
    ob_end_flush();
    exit;
}

// Prepare and execute query
$stmt = $conn->prepare("SELECT schedule_date, time_slot, tour_name, match_info, status, booking_price, sell_earn_price 
                        FROM schedule 
                        WHERE schedule_date BETWEEN ? AND ? 
                        ORDER BY schedule_date, time_slot");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

// Initialize totals for profit calculation
$total_booking_price = 0;
$total_sell_earn_price = 0;

// Initialize TCPDF
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Schedule Tracker');
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Schedule Report');
$pdf->SetSubject('Schedule Data');
$pdf->SetKeywords('schedule, report, pdf');

// Set margins
$pdf->SetMargins(10, 10, 10);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(5);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 10);

// Generate HTML content for PDF
$html = '<h2 style="text-align: center;">Schedule Report</h2>';
$html .= '<p style="text-align: center;">From ' . htmlspecialchars($start_date) . ' to ' . htmlspecialchars($end_date) . '</p>';

if ($result->num_rows > 0) {
    $current_date = '';
    while ($row = $result->fetch_assoc()) {
        $date = $row['schedule_date'];
        if ($date !== $current_date) {
            if ($current_date !== '') {
                $html .= '</table><br><br>';
            }
            $html .= '<h3>Date: ' . htmlspecialchars($date) . '</h3>';
            $html .= '<table border="1" cellpadding="4" cellspacing="0">
                        <tr style="background-color: #f2f2f2;">
                            <th style="width: 15%;">Time</th>
                            <th style="width: 25%;">Tour Name</th>
                            <th style="width: 15%;">Match</th>
                            <th style="width: 15%;">Status</th>
                            <th style="width: 15%;">Booking</th>
                            <th style="width: 15%;">Earn</th>
                        </tr>';
            $current_date = $date;
        }
        $dateTime = new DateTime($row['time_slot']);
        $formatted_time = $dateTime->format('h:i A');
        $booking_price = $row['booking_price'] == intval($row['booking_price']) ? intval($row['booking_price']) : number_format($row['booking_price'], 2);
        $sell_earn_price = $row['sell_earn_price'] == intval($row['sell_earn_price']) ? intval($row['sell_earn_price']) : number_format($row['sell_earn_price'], 2);
        $status_color = $row['status'] === 'Qualified' ? '#28a745' : ($row['status'] === 'Disqualified' ? '#dc3545' : '#6c757d');
        $html .= '<tr>
                    <td>' . htmlspecialchars($formatted_time) . '</td>
                    <td>' . htmlspecialchars($row['tour_name']) . '</td>
                    <td>' . htmlspecialchars($row['match_info']) . '</td>
                    <td style="background-color: ' . $status_color . '; color: white; text-align: center;">' . htmlspecialchars($row['status']) . '</td>
                    <td>' . $booking_price . '</td>
                    <td>' . $sell_earn_price . '</td>
                  </tr>';
        // Accumulate totals
        $total_booking_price += (float)$row['booking_price'];
        $total_sell_earn_price += (float)$row['sell_earn_price'];
    }
    $html .= '</table>';
} else {
    $html .= '<p style="text-align: center;">No schedules found for the selected date range.</p>';
}

// Add profit summary
$profit =  $total_sell_earn_price - $total_booking_price ;
// Determine color for profit (green for positive, red for negative, black for zero)
$profit_color = $profit > 0 ? '#28a745' : ($profit < 0 ? '#dc3545' : '#000000');
$html .= '<br><br>';
$html .= '<h3 style="text-align: center;">Summary</h3>';
$html .= '<table border="1" cellpadding="4" cellspacing="0" style="width: 50%; margin: 0 auto;">
            <tr>
                <th style="background-color: #f2f2f2;">Total Booking Price</th>
                <td>' . ($total_booking_price == intval($total_booking_price) ? intval($total_booking_price) : number_format($total_booking_price, 2)) . '</td>
            </tr>
            <tr>
                <th style="background-color: #f2f2f2;">Total Sell/Earn Price</th>
                <td>' . ($total_sell_earn_price == intval($total_sell_earn_price) ? intval($total_sell_earn_price) : number_format($total_sell_earn_price, 2)) . '</td>
            </tr>
            <tr>
                <th style="background-color: #f2f2f2;">Profit (Booking - Earn)</th>
                <td style="color: ' . $profit_color . ';">' . ($profit == intval($profit) ? intval($profit) : number_format($profit, 2)) . '</td>
            </tr>
          </table>';

// Write HTML to PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Close database connection
$stmt->close();
$conn->close();

// Clean output buffer and output PDF
ob_end_clean();
$pdf->Output('schedule_' . date('Y-m-d') . '.pdf', 'D');
exit;
?>