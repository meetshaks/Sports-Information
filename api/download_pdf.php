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

// Initialize match statistics
$total_matches = 0;
$total_no_update = 0;
$total_qualified = 0;
$total_disqualified = 0;

// Initialize TCPDF
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Schedule Tracker');
$pdf->SetAuthor('Schedule Tracker');
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
$html = '<h1 style="text-align: center; color: #007bff; margin-bottom: 10px;">Schedule Report</h1>';
$html .= '<p style="text-align: center; color: #666; margin-bottom: 20px;">From ' . htmlspecialchars($start_date) . ' to ' . htmlspecialchars($end_date) . '</p>';

// Add report generation timestamp
$html .= '<p style="text-align: right; color: #666; font-size: 8pt;">Generated on: ' . date('Y-m-d H:i:s') . '</p>';

if ($result->num_rows > 0) {
    $current_date = '';
    while ($row = $result->fetch_assoc()) {
        $date = $row['schedule_date'];
        if ($date !== $current_date) {
            if ($current_date !== '') {
                $html .= '</table><br><br>';
            }
            $html .= '<h3 style="color: #333; background-color: #f8f9fa; padding: 5px; border-radius: 5px;">Date: ' . htmlspecialchars($date) . '</h3>';
            $html .= '<table border="1" cellpadding="4" cellspacing="0" style="width: 100%; border-collapse: collapse;">
                        <tr style="background-color: #007bff; color: white;">
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
                    <td style="font-weight: bold;">' . htmlspecialchars($formatted_time) . '</td>
                    <td>' . htmlspecialchars($row['tour_name']) . '</td>
                    <td>' . htmlspecialchars($row['match_info']) . '</td>
                    <td style="background-color: ' . $status_color . '; color: white; text-align: center; font-weight: bold;">' . htmlspecialchars($row['status']) . '</td>
                    <td style="text-align: right;">' . $booking_price . '</td>
                    <td style="text-align: right;">' . $sell_earn_price . '</td>
                  </tr>';
        // Accumulate totals
        $total_booking_price += (float)$row['booking_price'];
        $total_sell_earn_price += (float)$row['sell_earn_price'];
        // Accumulate match statistics
        $total_matches++;
        if ($row['status'] === 'No Update') {
            $total_no_update++;
        } elseif ($row['status'] === 'Qualified') {
            $total_qualified++;
        } elseif ($row['status'] === 'Disqualified') {
            $total_disqualified++;
        }
    }
    $html .= '</table>';
} else {
    $html .= '<p style="text-align: center; color: #666;">No schedules found for the selected date range.</p>';
}

// Add summary and statistics section
$profit = $total_sell_earn_price - $total_booking_price;
$profit_color = $profit > 0 ? '#28a745' : ($profit < 0 ? '#dc3545' : '#000000');
$profit_percentage = $total_booking_price > 0 ? ($profit / $total_booking_price) * 100 : 0;

$html .= '<br><br>';
$html .= '<h2 style="text-align: center; color: #007bff;">Summary Report</h2>';
$html .= '<div style="display: flex; justify-content: space-between; gap: 20px;">';

// Financial Summary Table (Left)
$html .= '<div style="width: 45%;">';
$html .= '<h3 style="color: #333; background-color: #f8f9fa; padding: 5px; border-radius: 5px;">Financial Summary</h3>';
$html .= '<table border="1" cellpadding="4" cellspacing="0" style="width: 100%; border-collapse: collapse;">
            <tr style="background-color: #f8f9fa;">
                <th style="width: 60%;">Description</th>
                <th style="width: 40%; text-align: right;">Amount</th>
            </tr>
            <tr>
                <td>Total Booking Price</td>
                <td style="text-align: right;">' . ($total_booking_price == intval($total_booking_price) ? intval($total_booking_price) : number_format($total_booking_price, 2)) . '</td>
            </tr>
            <tr>
                <td>Total Sell/Earn Price</td>
                <td style="text-align: right;">' . ($total_sell_earn_price == intval($total_sell_earn_price) ? intval($total_sell_earn_price) : number_format($total_sell_earn_price, 2)) . '</td>
            </tr>
            <tr style="background-color: #f8f9fa;">
                <td><strong>Profit (Earn - Booking)</strong></td>
                <td style="text-align: right; color: ' . $profit_color . '; font-weight: bold;">' . ($profit == intval($profit) ? intval($profit) : number_format($profit, 2)) . '</td>
            </tr>
            <tr>
                <td>Profit Percentage</td>
                <td style="text-align: right; color: ' . $profit_color . ';">' . number_format($profit_percentage, 2) . '%</td>
            </tr>
          </table>';
$html .= '</div>';

// Match Statistics Table (Right)
$html .= '<div style="width: 45%;">';
$html .= '<h3 style="color: #333; background-color: #f8f9fa; padding: 5px; border-radius: 5px;">Match Statistics</h3>';
$html .= '<table border="1" cellpadding="4" cellspacing="0" style="width: 100%; border-collapse: collapse;">
            <tr style="background-color: #f8f9fa;">
                <th style="width: 60%;">Description</th>
                <th style="width: 40%; text-align: right;">Count</th>
            </tr>
            <tr>
                <td>Total Matches Played</td>
                <td style="text-align: right;">' . $total_matches . '</td>
            </tr>
            <tr>
                <td>No Update</td>
                <td style="text-align: right;">' . $total_no_update . ' (' . number_format(($total_no_update / $total_matches) * 100, 1) . '%)</td>
            </tr>
            <tr>
                <td>Qualified</td>
                <td style="text-align: right;">' . $total_qualified . ' (' . number_format(($total_qualified / $total_matches) * 100, 1) . '%)</td>
            </tr>
            <tr>
                <td>Disqualified</td>
                <td style="text-align: right;">' . $total_disqualified . ' (' . number_format(($total_disqualified / $total_matches) * 100, 1) . '%)</td>
            </tr>
          </table>';
$html .= '</div>';

$html .= '</div>';

// Add footer with page numbers
$html .= '<div style="text-align: center; margin-top: 20px; font-size: 8pt; color: #666;">';
$html .= 'Page {PAGENO} of {nbpg}';
$html .= '</div>';

// Write HTML to PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Close database connection
$stmt->close();
$conn->close();

// Clean output buffer and output PDF
ob_end_clean();
$pdf->Output('schedule_report_' . date('Y-m-d') . '.pdf', 'D');
exit;
?>