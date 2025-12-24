<?php
require_once 'config.php';

// Handle driver deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $driverID = $_POST['driver_id'] ?? '';

    if (empty($driverID)) {
        $_SESSION['error'] = 'Invalid driver ID!';
    } else {
        $conn = getDatabaseConnection();

        // Start transaction
        $conn->begin_transaction();

        try {
            // First, delete all driving sessions for this driver
            $deleteSessionsQuery = "DELETE FROM DrivingSession WHERE driverID = ?";
            $stmt = $conn->prepare($deleteSessionsQuery);
            $stmt->bind_param("i", $driverID);
            
            if (!$stmt->execute()) {
                throw new Exception("Error deleting sessions: " . $stmt->error);
            }
            $stmt->close();

            // Then, delete the driver
            $deleteDriverQuery = "DELETE FROM Driver WHERE driverID = ?";
            $stmt = $conn->prepare($deleteDriverQuery);
            $stmt->bind_param("i", $driverID);
            
            if (!$stmt->execute()) {
                throw new Exception("Error deleting driver: " . $stmt->error);
            }
            $stmt->close();

            // Commit transaction
            $conn->commit();
            $_SESSION['success'] = 'Driver and all associated sessions deleted successfully!';
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $_SESSION['error'] = 'Error deleting driver: ' . $e->getMessage();
        }

        $conn->close();
        header('Location: manage_drivers.php');
        exit;
    }
}

// Handle form submission for new driver
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'delete')) {
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

// Fetch all drivers with session count
$conn = getDatabaseConnection();
$driverQuery = "
    SELECT 
        d.driverID, 
        d.driverName, 
        d.birthday,
        COUNT(ds.sessionID) as session_count,
        COALESCE(SUM(ds.mileage), 0) as total_distance
    FROM Driver d
    LEFT JOIN DrivingSession ds ON d.driverID = ds.driverID
    GROUP BY d.driverID, d.driverName, d.birthday
    ORDER BY d.driverID
";
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

        .header-nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            padding: 10px 0;
            flex-wrap: wrap;
        }

        .header-nav a {
            color: #EBEBE9;
            text-decoration: none;
            font-weight: bold;
            padding: 8px 15px;
            border-radius: 6px;
            background-color: rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s;
        }

        .header-nav a:hover {
            background-color: #A3E6DA;
            color: #253628;
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

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .delete-btn {
            background-color: #FF6B6B;
            padding: 8px 12px;
            font-size: 0.9rem;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .delete-btn:hover {
            background-color: #FF5252;
            transform: scale(1.05);
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

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background-color: #8AA989;
            margin: 10% auto;
            padding: 25px;
            border: 2px solid #49654C;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
        }

        .modal-content h2 {
            color: #253628;
            margin-top: 0;
        }

        .modal-content p {
            color: #253628;
            font-size: 1rem;
            margin: 15px 0;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .modal-buttons button {
            padding: 10px 20px;
            font-size: 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: transform 0.2s;
        }

        .confirm-btn {
            background-color: #FF6B6B;
            color: white;
        }

        .confirm-btn:hover {
            background-color: #FF5252;
            transform: scale(1.05);
        }

        .cancel-btn {
            background-color: #49654C;
            color: #EBEBE9;
        }

        .cancel-btn:hover {
            background-color: #A3E6DA;
            color: #253628;
            transform: scale(1.05);
        }

        @media (max-width: 600px) {
            header h1 {
                font-size: 1.4rem;
            }

            .header-nav {
                gap: 10px;
            }

            .header-nav a {
                font-size: 0.9rem;
                padding: 6px 12px;
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

            .delete-btn {
                padding: 6px 10px;
                font-size: 0.8rem;
            }

            .action-buttons {
                flex-direction: column;
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
        <div class="header-nav">
            <a href="index.php"><i class="fa-solid fa-home"></i> Home</a>
            <a href="manage_drivers.php"><i class="fa-solid fa-users"></i> Manage Drivers</a>
            <a href="summary.php"><i class="fa-solid fa-chart-bar"></i> Summary</a>
        </div>
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
                                <th>Sessions</th>
                                <th>Total Distance (km)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $driverResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['driverID']); ?></td>
                                    <td><?php echo htmlspecialchars($row['driverName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['birthday']); ?></td>
                                    <td><?php echo $row['session_count']; ?></td>
                                    <td><?php echo number_format($row['total_distance'], 1); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="delete-btn" onclick="openDeleteModal(<?php echo $row['driverID']; ?>, '<?php echo htmlspecialchars(addslashes($row['driverName'])); ?>')">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
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

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h2><i class="fa-solid fa-exclamation-triangle"></i> Confirm Deletion</h2>
            <p>Are you sure you want to delete driver <strong id="driverNameDisplay"></strong>?</p>
            <p style="color: #FF6B6B; font-size: 0.9rem;">⚠️ This will also delete all driving sessions associated with this driver.</p>
            <div class="modal-buttons">
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="driver_id" id="driverIDInput" value="">
                    <button type="submit" class="confirm-btn">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                </form>
                <button type="button" class="cancel-btn" onclick="closeDeleteModal()">
                    <i class="fa-solid fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </div>

    <footer>
        <p><i class="fa-solid fa-copyright"></i> 2025. Driving Experience Assistant.</p>
    </footer>

    <script>
        function openDeleteModal(driverID, driverName) {
            document.getElementById('deleteModal').style.display = 'block';
            document.getElementById('driverNameDisplay').textContent = driverName;
            document.getElementById('driverIDInput').value = driverID;
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>