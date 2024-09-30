<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-scale=1.0">
    <title>Calculate Salary</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        select, input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #2980b9;
        }
        .result {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Calculate Salary</h1>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <label for="empID">Select Employee:</label>
            <select id="empID" name="empID" required>
                <option value="">--Select Employee--</option>
                <?php
                include 'db_connection.php'; // Include the connection file

                // Fetch employee names and IDs
                $stmt = $conn->prepare("SELECT EmpID, Name FROM employee");
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($row['EmpID']) . "'>" . htmlspecialchars($row['Name']) . "</option>";
                }

                $stmt->close();
                ?>
            </select>

            <input type="submit" value="Calculate Salary">
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Get selected employee ID
            $empID = $_POST['empID'];

            // Calculate the start and end dates for the salary cycle (11th of the current month to 10th of the next month)
            $currentDate = date('Y-m-d');
            $startDate = date('Y-m-11', strtotime($currentDate)); // 11th of the current month
            $endDate = date('Y-m-10', strtotime('+1 month', strtotime($currentDate))); // 10th of the next month

            // Prepare and execute query to calculate present days in the given salary cycle
            $stmt = $conn->prepare("SELECT e.Name, e.Rate, COUNT(a.Date) as PresentDays 
                                    FROM employee e 
                                    LEFT JOIN attendance a ON e.EmpID = a.EmpID 
                                    WHERE e.EmpID = ? AND a.Date BETWEEN ? AND ? 
                                    GROUP BY e.EmpID");
            $stmt->bind_param("iss", $empID, $startDate, $endDate);
            $stmt->execute();
            $salaryResult = $stmt->get_result();

            if ($salaryResult->num_rows > 0) {
                $salaryData = $salaryResult->fetch_assoc();
                $name = htmlspecialchars($salaryData['Name']);
                $rate = $salaryData['Rate'];
                $presentDays = $salaryData['PresentDays'];
                $monthlySalary = $rate * $presentDays;

                // Calculate total salary after deducting advances
                $stmtAdvance = $conn->prepare("SELECT SUM(Amount) as TotalAdvance 
                                                FROM salaryadvance 
                                                WHERE EmpID = ? AND Date BETWEEN ? AND ?");
                $stmtAdvance->bind_param("iss", $empID, $startDate, $endDate);
                $stmtAdvance->execute();
                $advanceResult = $stmtAdvance->get_result();
                $advanceData = $advanceResult->fetch_assoc();
                $totalAdvance = $advanceData['TotalAdvance'] ?? 0;

                // Fetch bonus amount for the current cycle
                $stmtBonus = $conn->prepare("SELECT SUM(Amount) as TotalBonus 
                                              FROM bonus 
                                              WHERE EmpID = ? AND Date BETWEEN ? AND ?");
                $stmtBonus->bind_param("iss", $empID, $startDate, $endDate);
                $stmtBonus->execute();
                $bonusResult = $stmtBonus->get_result();
                $bonusData = $bonusResult->fetch_assoc();
                $totalBonus = $bonusData['TotalBonus'] ?? 0;

                // Fetch EPF amount for the current cycle
                $stmtEpf = $conn->prepare("SELECT EPFAmount 
                                            FROM epf 
                                            WHERE EmpID = ? AND DateCalculated = CURDATE()");
                $stmtEpf->bind_param("i", $empID);
                $stmtEpf->execute();
                $epfResult = $stmtEpf->get_result();
                $epfData = $epfResult->fetch_assoc();
                $epfAmount = $epfData['EPFAmount'] ?? 0;

                // Calculate total salary after advances, including bonus, and deducting EPF
                $totalSalary = $monthlySalary - $totalAdvance + $totalBonus - $epfAmount;

                echo "<div class='result'>
                        <h3>Salary Calculation for $name</h3>
                        <p>Rate: Rs.$rate</p>
                        <p>Present Days: $presentDays</p>
                        <p>Monthly Salary: Rs.$monthlySalary</p>
                        <p>Salary Advance: Rs.$totalAdvance</p>
                        <p>Bonus: Rs.$totalBonus</p>
                        <p>EPF Deduction: Rs.$epfAmount</p>
                        <p>Total Salary after Advances, Bonus, and EPF: Rs.$totalSalary</p>
                      </div>";
            } else {
                echo "<div class='result'>No attendance records found for the selected employee in this cycle.</div>";
            }

            $stmt->close();
            $stmtAdvance->close();
            $stmtBonus->close();
            $stmtEpf->close();
        }

        // Close the connection
        $conn->close();
        ?>
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <button onclick="printPaysheet()">Print Pay Sheet</button>
            <form action="generate_pdf.php" method="POST" style="margin-top: 10px;">
                <input type="hidden" name="empID" value="<?php echo htmlspecialchars($empID); ?>">
                <input type="submit" value="Generate PDF Report">
            </form>
        <?php endif; ?>

    </div>
</body>
</html>
