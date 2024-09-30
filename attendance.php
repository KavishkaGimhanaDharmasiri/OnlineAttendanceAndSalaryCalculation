<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Input</title>
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
            padding: 8px;
            margin-bottom: 10px;
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
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Attendance Input Form</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <label for="empID">Employee ID:</label>
            <input type="text" id="empID" name="empID" required>

            <input type="submit" value="Submit">
        </form>
        <div class="error" id="errorMessage"></div>
        <div class="success" id="successMessage"></div>
    </div>

    <?php
    include 'db_connection.php'; // Include the connection file

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $empID = $_POST['empID'];
        $date = date('Y-m-d'); // Set the date to today

        // Fetch employee name based on EmpID
        $stmt = $conn->prepare("SELECT Name FROM employee WHERE EmpID = ?");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $name = $row['Name'];

            // Check if the attendance record already exists
            $stmt->close();
            $stmt = $conn->prepare("SELECT * FROM attendance WHERE EmpID = ? AND Date = ?");
            $stmt->bind_param("is", $empID, $date);
            $stmt->execute();
            $attendanceResult = $stmt->get_result();

            if ($attendanceResult->num_rows > 0) {
                echo "<script>document.getElementById('errorMessage').textContent = 'Attendance already recorded for this employee today.';</script>";
            } else {
                // Insert new attendance record (present = 1)
                $stmt->close();
                $stmt = $conn->prepare("INSERT INTO attendance (EmpID, Name, Present, Date) VALUES (?, ?, 1, ?)");
                $stmt->bind_param("iss", $empID, $name, $date);

                if ($stmt->execute()) {
                    echo "<script>document.getElementById('successMessage').textContent = 'Attendance recorded successfully.';</script>";
                } else {
                    echo "<script>document.getElementById('errorMessage').textContent = 'Error: " . $stmt->error . "';</script>";
                }
            }
        } else {
            echo "<script>document.getElementById('errorMessage').textContent = 'Invalid Employee ID.';</script>";
        }

        // Close the statement
        $stmt->close();
    }

    $conn->close();
    ?>
</body>
</html>
