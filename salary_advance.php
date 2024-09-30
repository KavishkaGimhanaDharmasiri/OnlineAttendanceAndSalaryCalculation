<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-scale=1.0">
    <title>Salary Advance</title>
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
        input {
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
        .success {
            color: green;
            margin-top: 20px;
        }
        .error {
            color: red;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Salary Advance</h1>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <label for="empID">Employee ID:</label>
            <input type="number" id="empID" name="empID" required>

            <label for="amount">Advance Amount:</label>
            <input type="number" id="amount" name="amount" required min="1">

            <input type="submit" value="Submit">
        </form>
        <div class="success" id="successMessage"></div>
        <div class="error" id="errorMessage"></div>

        <?php
        // Enable error reporting for debugging
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        include 'db_connection.php'; // Include the connection file

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Get the input values
            $empID = $_POST['empID'];
            $amount = $_POST['amount'];
            $date = date('Y-m-d'); // Get current date

            // Check if an advance already exists for this employee on the current date
            $stmtCheck = $conn->prepare("SELECT COUNT(*) as AdvanceCount FROM salaryadvance 
                                          WHERE EmpID = ? AND Date = ?");
            $stmtCheck->bind_param("is", $empID, $date);
            $stmtCheck->execute();
            $checkResult = $stmtCheck->get_result();
            $checkData = $checkResult->fetch_assoc();

            if ($checkData['AdvanceCount'] > 0) {
                echo "<div class='error'>An advance has already been recorded for Employee ID $empID on $date.</div>";
            } else {
                // Prepare and bind for insertion
                $stmt = $conn->prepare("INSERT INTO salaryadvance (EmpID, Name, Amount, Date) 
                                         SELECT EmpID, Name, ?, ? FROM employee WHERE EmpID = ?");
                $stmt->bind_param("isi", $amount, $date, $empID);

                // Execute the statement
                if ($stmt->execute()) {
                    echo "<div class='success'>Salary advance of $amount has been successfully recorded for Employee ID $empID.</div>";
                } else {
                    echo "<div class='error'>Error: " . $stmt->error . "</div>";
                }

                // Close the statement
                $stmt->close();
            }

            // Close the check statement
            $stmtCheck->close();
        }

        // Close the connection
        $conn->close();
        ?>
    </div>
</body>
</html>
