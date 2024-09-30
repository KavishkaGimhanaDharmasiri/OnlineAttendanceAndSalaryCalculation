<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Input</title>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --background-color: #ecf0f1;
            --text-color: #333;
            --error-color: #e74c3c;
            --success-color: #2ecc71;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-color);
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: var(--secondary-color);
            margin-bottom: 30px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        input {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        input[type="submit"] {
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #2980b9;
        }

        .error {
            color: var(--error-color);
            margin-top: -15px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .success {
            color: var(--success-color);
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }

            input {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Employee Input Form</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="employeeForm">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" required max="" onfocus="setMaxDate()">

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required>

            <label for="rate">Rate:</label>
            <input type="number" id="rate" name="rate" required min="0" step="0.01">

            <label for="id">ID (12-digit number):</label>
            <input type="text" id="id" name="id" required pattern="\d{12}" title="ID must be a 12-digit number.">
            <div class="error" id="idError"></div>

            <input type="submit" value="Submit">
        </form>
        <div class="success" id="successMessage"></div>
    </div>

    <script>
        function setMaxDate() {
            const dobInput = document.getElementById('dob');
            const today = new Date();
            const minDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
            dobInput.max = minDate.toISOString().split('T')[0]; // Set max date to 18 years ago
        }

        document.getElementById('employeeForm').addEventListener('submit', function(e) {
            const idInput = document.getElementById('id');
            const idError = document.getElementById('idError');
            const idPattern = /^\d{12}$/;

            if (!idPattern.test(idInput.value)) {
                e.preventDefault();
                idError.textContent = 'ID must be a 12-digit number.';
            } else {
                idError.textContent = '';
            }
        });
    </script>

    <?php
    include 'db_connection.php'; // Include the connection file

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate ID
        $id = $_POST['id'];
        if (!preg_match('/^\d{12}$/', $id)) {
            echo "<script>document.getElementById('idError').textContent = 'ID must be a 12-digit number.';</script>";
            exit;
        }

        // Validate Date of Birth
        $dob = $_POST['dob'];
        $dobDate = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($dobDate)->y;

        if ($age < 18) {
            echo "<script>document.getElementById('idError').textContent = 'You must be at least 18 years old.';</script>";
            exit;
        }

        // Prepare and bind for duplicate check
        $stmt = $conn->prepare("SELECT * FROM employee WHERE ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>document.getElementById('idError').textContent = 'This ID already exists in the database.';</script>";
        } else {
            // Prepare and bind for insertion
            $stmt->close(); // Close the previous statement
            $stmt = $conn->prepare("INSERT INTO employee (Name, DOB, Address, Rate, ID) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssii", $name, $dob, $address, $rate, $id);

            // Set parameters
            $name = $_POST['name'];
            $address = $_POST['address'];
            $rate = $_POST['rate'];

            if ($stmt->execute()) {
                echo "<script>document.getElementById('successMessage').textContent = 'New record created successfully';</script>";
            } else {
                echo "<script>document.getElementById('idError').textContent = 'Error: " . $stmt->error . "';</script>";
            }
        }

        // Close the statement
        $stmt->close();
    }

    $conn->close();
    ?>
</body>
</html>
