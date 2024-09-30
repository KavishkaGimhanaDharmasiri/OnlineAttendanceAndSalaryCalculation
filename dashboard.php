<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Navigation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --text-color: #333;
            --background-color: #f4f4f4;
            --card-color: #ffffff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .navbar {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }

        .navbar h2 {
            color: var(--secondary-color);
            margin-bottom: 20px;
            font-size: 24px;
            text-align: center;
        }

        .nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }

        .nav-item {
            background-color: var(--card-color);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .nav-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        .nav-item i {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .nav-item span {
            display: block;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .nav-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .nav-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h2>Dashboard Menu</h2>
        <div class="nav-grid">
            <a href="today_attendance.php" class="nav-item">
                <i class="fas fa-calendar-day"></i>
                <span>Today's Attendance</span>
            </a>
            <a href="view_attendance.php" class="nav-item">
                <i class="fas fa-eye"></i>
                <span>View Records</span>
            </a>
            <a href="input.php" class="nav-item">
                <i class="fas fa-user-plus"></i>
                <span>Add Employees</span>
            </a>
            <a href="attendance.php" class="nav-item">
                <i class="fas fa-clipboard-list"></i>
                <span>Attendance</span>
            </a>
            <a href="monthly_attendance.php" class="nav-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Monthly Attendance</span>
            </a>
            <a href="salary_advance.php" class="nav-item">
                <i class="fas fa-money-bill-wave"></i>
                <span>Salary Advance</span>
            </a>
            <a href="total_salary.php" class="nav-item">
                <i class="fas fa-calculator"></i>
                <span>Calculate Salary</span>
            </a>
            <a href="reports.php" class="nav-item">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
            <a href="settings.php" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            <a href="logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</body>
</html>