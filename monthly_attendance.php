<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-scale=1.0">
    <title>Monthly Attendance Calculation</title>
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
        input[type="submit"] {
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #2980b9;
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
        <h1>Calculate Monthly Attendance</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <input type="submit" value="Calculate Present Days">
        </form>

        <?php
        include 'db_connection.php'; // Include the connection file

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Calculate date range
            $today = new DateTime();
            $start_date = new DateTime($today->format('Y-m-01')); // First day of current month
            $start_date->modify('next month'); // Move to the next month
            $start_date->modify('-21 days'); // Move back to the 11th of the current month
            $end_date = new DateTime($today->format('Y-m-01')); // First day of current month
            $end_date->modify('next month'); // Move to the next month
            $end_date->modify('10 days'); // Move to the 10th of the next month

            // Prepare and execute query to fetch attendance records within the date range
            $stmt = $conn->prepare("SELECT EmpID, Name, SUM(Present) AS TotalPresent 
                                     FROM attendance 
                                     WHERE Date BETWEEN ? AND ? 
                                     GROUP BY EmpID, Name");
            $stmt->bind_param("ss", $start_date->format('Y-m-d'), $end_date->format('Y-m-d'));
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<table>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Total Present Days</th>
                        </tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['EmpID']) . "</td>
                            <td>" . htmlspecialchars($row['Name']) . "</td>
                            <td>" . htmlspecialchars($row['TotalPresent']) . "</td>
                          </tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='error'>No attendance records found for the specified period.</div>";
            }

            // Close the statement
            $stmt->close();
        }

        $conn->close();
        ?>
    </div>
</body>
</html>
