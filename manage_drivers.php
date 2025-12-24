<?php
require_once 'config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driverName = $_POST['driver_name'] ?? '';
    $birthday = $_POST['birthday'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($driverName) || empty($birthday) || empty($password) || empty($confirmPassword)) {
        $_SESSION['error'] = 'All fields are required!';
    } elseif ($password !== $confirmPassword) {
        $_SESSION['error'] = 'Passwords do not match!';
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters!';
    } else {
        // Database connection
        $conn = getDatabaseConnection();

        // Check if driver name already exists
        $checkQuery = "SELECT driverID FROM Driver WHERE driverName = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $driverName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['error'] = 'Driver name already exists!';
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert new driver
            $insertQuery = "INSERT INTO Driver (driverName, driverPassword, birthday) VALUES (?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("sss", $driverName, $hashedPassword, $birthday);

            if ($insertStmt->execute()) {
                $_SESSION['success'] = 'Driver added successfully!';
                header('Location: manage_drivers.php');
                exit;
            } else {
                $_SESSION['error'] = 'Error adding driver: ' . $insertStmt->error;
            }
            $insertStmt->close();
        }
        $stmt->close();
        $conn->close();
    }
}

// Fetch all drivers
$conn = getDatabaseConnection();
$driverQuery = "SELECT driverID, driverName, birthday FROM Driver ORDER BY driverID";
$driverResult = $conn->query($driverQuery);
$conn->close();

// Handle session messages
$successMessage = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$errorMessage = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success']);
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://kit.fontawesome.com/a49a2f08e3.js" crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Drivers</title>
    <style>
        /* Background video styling */
        video {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            min-width: 100%;
            min-height: 100%;
            object-fit: cover;
            filter: saturate(50%) brightness(70%);
            z-index: 0;
        }

        .mobile-background {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #253628 0%, #49654C 100%);
            z-index: 0;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(37, 54, 40, 0.7);
            z-index: 1;
        }

        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #253628;
            color: #EBEBE9;
            justify-content: flex-start;
            align-items: center;
            text-align: center;
        }

        header {
            background-color: #49654C;
            color: #EBEBE9;
            padding: 10px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 2;
            position: relative;
            animation: bounceIn 1s ease;
        }

        header h1 {
            font-size: 1.8rem;
            margin: 0;
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
                opacity: 1;
            }
            100% {
                transform: scale(1);
            }
        }

        main {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            z-index: 2;
            position: relative;
            animation: fadeInUp 1.5s ease;
            width: 100%;
            box-sizing: border-box;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-message {
            background-color: #A3E6DA;
            color: #253628;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
            animation: fadeIn 0.5s ease;
            max-width: 600px;
            width: 100%;
            box-sizing: border-box;
        }

        .error-message {
            background-color: #FF6B6B;
            color: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
            animation: fadeIn 0.5s ease;
            max-width: 600px;
            width: 100%;
            box-sizing: border-box;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        .form-section {
            background-color: #8AA989;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }

        .form-section h2 {
            color: #253628;
            margin-top: 0;
        }

        form {
            display: grid;
            grid-template-columns: 1fr;
            grid-gap: 15px;
            width: 100%;
        }

        label {
            text-align: left;
            font-weight: bold;
            color: #253628;
        }

        input[type="text"],
        input[type="password"],
        input[type="date"] {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #C0CEB2;
            border-radius: 6px;
            background-color: #EBEBE9;
            color: #253628;
            transition: border-color 0.3s, transform 0.2s;
        }

        input:hover {
            border-color: #49654C;
            transform: scale(1.02);
        }

        input:focus {
            border-color: #A3E6DA;
            outline: none;
        }

        button {
            background-color: #49654C;
            color: #EBEBE9;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            font-weight: bold;
        }

        button:hover {
            background-color: #A3E6DA;
            transform: scale(1.05);
        }

        button:active {
            background-color: #C0CEB2;
        }

        .drivers-section {
            background-color: #8AA989;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }

        .drivers-section h2 {
            color: #253628;
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #C0CEB2;
            text-align: left;
        }

        th {
            background-color: #49654C;
            color: #EBEBE9;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #A3E6DA;
        }

        tr:nth-child(odd) {
            background-color: #C0CEB2;
        }

        .no-drivers {
            text-align: center;
            color: #253628;
            padding: 20px;
        }

        a {
            color: #A3E6DA;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
            display: inline-block;
            font-size: 1.1rem;
        }

        a:hover {
            color: #49654C;
        }

        footer {
            background-color: #49654C;
            color: #EBEBE9;
            text-align: center;
            padding: 10px;
            position: relative;
            width: 100%;
            z-index: 2;
            box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.2);
            margin-top: 30px;
        }

        @media (max-width: 600px) {
            header h1 {
                font-size: 1.4rem;
            }

            .form-section, .drivers-section {
                padding: 15px;
            }

            th, td {
                font-size: 0.9rem;
                padding: 8px;
            }

            button {
                font-size: 1rem;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <video autoplay muted loop>
        <source src="car.mp4" type="video/mp4">
    </video>

    <div class="mobile-background"></div>

    <header>
        <h1><i class="fa-solid fa-users"></i> Manage Drivers</h1>
    </header>

    <main>
        <div class="container">
            <?php if ($successMessage): ?>
                <div class="success-message">
                    <i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>

            <?php if ($errorMessage): ?>
                <div class="error-message">
                    <i class="fa-solid fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <div class="form-section">
                <h2><i class="fa-solid fa-user-plus"></i> Add New Driver</h2>
                <form method="POST">
                    <label for="driver-name"><i class="fa-solid fa-user"></i> Driver Name:</label>
                    <input type="text" id="driver-name" name="driver_name" required maxlength="100" placeholder="Enter full name">

                    <label for="birthday"><i class="fa-solid fa-cake-candles"></i> Birthday:</label>
                    <input type="date" id="birthday" name="birthday" required>

                    <label for="password"><i class="fa-solid fa-lock"></i> Password:</label>
                    <input type="password" id="password" name="password" required minlength="6" placeholder="Minimum 6 characters">

                    <label for="confirm-password"><i class="fa-solid fa-lock"></i> Confirm Password:</label>
                    <input type="password" id="confirm-password" name="confirm_password" required minlength="6" placeholder="Re-enter password">

                    <button type="submit"><i class="fa-solid fa-save"></i> Add Driver</button>
                </form>
            </div>

            <div class="drivers-section">
                <h2><i class="fa-solid fa-list"></i> Existing Drivers</h2>
                <?php if ($driverResult->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Birthday</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $driverResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['driverID']); ?></td>
                                    <td><?php echo htmlspecialchars($row['driverName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['birthday']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-drivers">
                        <p>No drivers found.</p>
                    </div>
                <?php endif; ?>
            </div>

            <a href="index.php"><i class="fa-solid fa-arrow-left"></i> Back to Form</a>
        </div>
    </main>

    <footer>
        <p><i class="fa-solid fa-copyright"></i> 2025. Driving Experience Assistant.</p>
    </footer>
</body>
</html>