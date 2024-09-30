<?php
// Disable displaying errors to avoid premature output
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php-error.log');

// Start output buffering
ob_start();

// Include required libraries and connection
include 'db_connection.php'; // Ensure no extra spaces or new lines here
require_once 'tcpdf/tcpdf.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $empID = $_POST['empID'];
    $currentMonth = date('Y-m');

    // Fetch employee details and attendance
    $stmt = $conn->prepare("SELECT e.Name, e.Rate, COUNT(a.Date) as PresentDays 
                            FROM employee e 
                            LEFT JOIN attendance a ON e.EmpID = a.EmpID AND DATE_FORMAT(a.Date, '%Y-%m') = ?
                            WHERE e.EmpID = ? 
                            GROUP BY e.EmpID");
    $stmt->bind_param("si", $currentMonth, $empID); // Ensure correct parameter binding
    $stmt->execute();
    $salaryResult = $stmt->get_result();

    if ($salaryResult->num_rows > 0) {
        $salaryData = $salaryResult->fetch_assoc();
        $name = $salaryData['Name'];
        $rate = $salaryData['Rate'];
        $presentDays = $salaryData['PresentDays'];
        $monthlySalary = $rate * $presentDays;

        // Calculate total salary after deducting advances
        $stmtAdvance = $conn->prepare("SELECT SUM(Amount) as TotalAdvance
                                        FROM salaryadvance
                                        WHERE EmpID = ? AND DATE_FORMAT(Date, '%Y-%m') = ?");
        $stmtAdvance->bind_param("is", $empID, $currentMonth);
        $stmtAdvance->execute();
        $advanceResult = $stmtAdvance->get_result();
        $advanceData = $advanceResult->fetch_assoc();
        $totalAdvance = $advanceData['TotalAdvance'] ?? 0;

        // Fetch bonus amount for the current month
        $stmtBonus = $conn->prepare("SELECT SUM(Amount) as TotalBonus 
                                      FROM bonus 
                                      WHERE EmpID = ? AND DATE_FORMAT(Date, '%Y-%m') = ?");
        $stmtBonus->bind_param("is", $empID, $currentMonth);
        $stmtBonus->execute();
        $bonusResult = $stmtBonus->get_result();
        $bonusData = $bonusResult->fetch_assoc();
        $totalBonus = $bonusData['TotalBonus'] ?? 0;

        // Fetch EPF amount for the current month
        $stmtEpf = $conn->prepare("SELECT EPFAmount 
                                    FROM epf 
                                    WHERE EmpID = ? AND DATE_FORMAT(DateCalculated, '%Y-%m') = ?");
        $stmtEpf->bind_param("is", $empID, $currentMonth);
        $stmtEpf->execute();
        $epfResult = $stmtEpf->get_result();
        $epfData = $epfResult->fetch_assoc();
        $epfAmount = $epfData['EPFAmount'] ?? 0;

        // Calculate total salary after advances, including bonus, and deducting EPF
        $totalSalary = $monthlySalary - $totalAdvance + $totalBonus - $epfAmount;

        // Create PDF document
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Your Company');
        $pdf->SetTitle('Salary Pay Sheet');
        $pdf->SetHeaderData('', '', 'Salary Pay Sheet', '');
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // Set margins to position content on the left side (smaller left margin, larger right margin)
        $pdf->SetMargins(10, 10, 122); // Left 10mm, Top 10mm, Right 122mm
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->AddPage('P', 'A4');

        // Add content to PDF
        $html = "<h1>Salary Pay Sheet for $name</h1>
                 <p><strong>Rate:</strong> Rs.-$rate</p>
                 <p><strong>Present Days:</strong> $presentDays</p>
                 <p><strong>Monthly Salary:</strong> Rs.-$monthlySalary</p>
                 <p><strong>Salary Advance:</strong> Rs.-$totalAdvance</p>
                 <p><strong>Bonus:</strong> Rs.-$totalBonus</p>
                 <p><strong>EPF Deduction:</strong> Rs.-$epfAmount</p>
                 <p><strong>Total Salary after Advances, Bonus, and EPF:</strong> Rs.-$totalSalary</p>";

        $pdf->writeHTML($html, true, false, true, false, '');

        // Clear the buffer before outputting the PDF
        ob_end_clean();
        $pdf->Output('pay_sheet_' . $empID . '.pdf', 'I'); // Output the PDF

    } else {
        echo "No attendance records found for the selected employee this month.";
    }

    $stmt->close();
    $stmtAdvance->close();
    $stmtBonus->close();
    $stmtEpf->close();
}

// Close the connection
$conn->close();
?>
