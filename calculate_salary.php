<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-scale=1.0">
    <title>Calculate Monthly Salary</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Monthly Salary Calculation</h1>

        <?php
        // Enable error reporting for debugging
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        include 'db_connection.php'; // Include the connection file

        // Get today's date
        $today = new DateTime();

        // Determine the start and end dates for the current salary cycle
        if ($today->day >= 11) {
            // Cycle from the 11th of the current month to the 10th of the next month
            $start_date = new DateTime($today->format('Y-m-11'));
            $end_date = (clone $start_date)->modify('next month')->modify('10 days'); // 10 days into the next month
        } else {
            // Cycle from the 11th of the previous month to the 10th of the current month
            $start_date = (clone $today)->modify('first day of last month')->modify('11 days');
            $end_date = (clone $start_date)->modify('next month')->modify('10 days'); // 10 days into the current month
        }

        // Prepare and execute query to fetch employees and their rates
        $stmt = $conn->prepare("SELECT EmpID, Name, Rate FROM employee");
        $stmt->execute();
        $employeeResult = $stmt->get_result();

        if ($employeeResult->num_rows > 0) {
            echo "<table>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Rate</th>
                        <th>Days Present</th>
                        <th>Monthly Salary</th>
                    </tr>";

            while ($employee = $employeeResult->fetch_assoc()) {
                $empID = $employee['EmpID'];
                $name = htmlspecialchars($employee['Name']);
                $rate = $employee['Rate'];

                // Calculate the number of days present in the specified cycle
                $stmtAttendance = $conn->prepare("SELECT COUNT(*) as PresentDays FROM attendance 
                                                   WHERE EmpID = ? AND Date BETWEEN ? AND ?");
                $stmtAttendance->bind_param("iss", $empID, $start_date->format('Y-m-d'), $end_date->format('Y-m-d'));
                $stmtAttendance->execute();
                $attendanceResult = $stmtAttendance->get_result();
                $attendanceData = $attendanceResult->fetch_assoc();
                $daysPresent = $attendanceData['PresentDays'];

                // Calculate the monthly salary
                $monthlySalary = $rate * $daysPresent;

                echo "<tr>
                        <td>" . htmlspecialchars($empID) . "</td>
                        <td>" . $name . "</td>
                        <td>" . htmlspecialchars($rate) . "</td>
                        <td>" . htmlspecialchars($daysPresent) . "</td>
                        <td>" . htmlspecialchars($monthlySalary) . "</td>
                      </tr>";
            }

            echo "</table>";
        } else {
            echo "<div class='error'>No employees found.</div>";
        }

        // Close the statements
        $stmt->close();
        $conn->close();
        ?>
    </div>
</body>
</html>
