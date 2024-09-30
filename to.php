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
            $currentMonth = date('Y-m'); // Get the current month in 'YYYY-MM' format

            // Prepare and execute query to calculate present days and salary
            $stmt = $conn->prepare("SELECT e.Name, e.Rate, COUNT(a.Date) as PresentDays 
                                    FROM employee e 
                                    LEFT JOIN attendance a ON e.EmpID = a.EmpID 
                                    WHERE e.EmpID = ? AND DATE_FORMAT(a.Date, '%Y-%m') = ? 
                                    GROUP BY e.EmpID");
            $stmt->bind_param("is", $empID, $currentMonth);
            $stmt->execute();
            $salaryResult = $stmt->get_result();

            if ($salaryResult->num_rows > 0) {
                $salaryData = $salaryResult->fetch_assoc();
                $name = htmlspecialchars($salaryData['Name']);
                $rate = $salaryData['Rate'];
                $presentDays = $salaryData['PresentDays'];
                $monthlySalary = $rate * $presentDays;

                echo "<div class='result'>
                        <h3>Salary Calculation for $name</h3>
                        <p>Rate: $$rate</p>
                        <p>Present Days: $presentDays</p>
                        <p>Total Salary for Current Month: $$monthlySalary</p>
                      </div>";
            } else {
                echo "<div class='result'>No attendance records found for the selected employee this month.</div>";
            }

            $stmt->close();
        }

        // Close the connection
        $conn->close();
        ?>
    </div>
</body>
</html>
