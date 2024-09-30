<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance Records</title>
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
        <h1>View Attendance Records</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <label for="date">Enter Date:</label>
            <input type="date" id="date" name="date" required>
            <input type="submit" value="Get Attendance">
        </form>

        <?php
        include 'db_connection.php'; // Include the connection file

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $date = $_POST['date'];

            // Prepare and execute query to fetch attendance records for the given date
            $stmt = $conn->prepare("SELECT EmpID, Name, Present FROM attendance WHERE Date = ?");
            $stmt->bind_param("s", $date);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<table>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Present</th>
                        </tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['EmpID']) . "</td>
                            <td>" . htmlspecialchars($row['Name']) . "</td>
                            <td>" . htmlspecialchars($row['Present']) . "</td>
                          </tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='error'>No attendance records found for the selected date.</div>";
            }

            // Close the statement
            $stmt->close();
        }

        $conn->close();
        ?>
    </div>
</body>
</html>
