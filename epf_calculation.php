<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-scale=1.0">
    <title>Calculate EPF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input[type="number"], input[type="submit"] {
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
        <h1>Calculate EPF</h1>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <label for="empID">Employee ID:</label>
            <input type="number" id="empID" name="empID" required>

            <label for="basicSalary">Basic Salary:</label>
            <input type="number" id="basicSalary" name="basicSalary" required step="0.01" min="0">

            <input type="submit" value="Calculate EPF">
        </form>

        <?php
        // Enable error reporting for debugging
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        include 'db_connection.php'; // Include the connection file

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Get the input values
            $empID = $_POST['empID'];
            $basicSalary = $_POST['basicSalary'];

            // Calculate EPF (8% of Basic Salary)
            $epfAmount = $basicSalary / 100 * 8;
            $dateCalculated = date('Y-m-d'); // Get current date

            // Prepare and execute query to insert EPF record
            $stmt = $conn->prepare("INSERT INTO epf (EmpID, BasicSalary, EPFAmount, DateCalculated) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idss", $empID, $basicSalary, $epfAmount, $dateCalculated);

            if ($stmt->execute()) {
                echo "<div class='result'>EPF calculated successfully: Rs.$epfAmount has been recorded for Employee ID $empID.</div>";
            } else {
                echo "<div class='result'>Error: " . $stmt->error . "</div>";
            }

            // Close the statement
            $stmt->close();
        }

        // Close the connection
        $conn->close();
        ?>
    </div>
</body>
</html>
