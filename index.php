<?php
require_once 'config.php';

// Fetch dropdown options from database
$conn = getDatabaseConnection();

// Get drivers
$driverQuery = "SELECT driverID, driverName FROM Driver ORDER BY driverID";
$driverResult = $conn->query($driverQuery);

// Get weather conditions
$weatherQuery = "SELECT weatherID, weatherDescription FROM WeatherCondition ORDER BY weatherID";
$weatherResult = $conn->query($weatherQuery);

// Get traffic conditions
$trafficQuery = "SELECT trafficID, trafficDescription FROM TrafficCondition ORDER BY trafficID";
$trafficResult = $conn->query($trafficQuery);

// Get road types
$roadQuery = "SELECT roadTypeID, roadTypeDescription FROM RoadType ORDER BY roadTypeID";
$roadResult = $conn->query($roadQuery);

// Get visibility ranges
$visibilityQuery = "SELECT visibilityID, visibilityDescription FROM VisibilityRange ORDER BY visibilityID";
$visibilityResult = $conn->query($visibilityQuery);

// Get maneuvers
$maneuverQuery = "SELECT maneuverID, maneuverAttribute FROM Maneuvers ORDER BY maneuverID";
$maneuverResult = $conn->query($maneuverQuery);

// Calculate total distance
$totalQuery = "SELECT SUM(mileage) as total FROM DrivingSession";
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalDistance = $totalRow['total'] ?? 0;

// Handle success message
$successMessage = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['success']);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://kit.fontawesome.com/a49a2f08e3.js" crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driving Experience Assistant</title>
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
            justify-content: center;
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
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        form {
            display: grid;
            grid-template-columns: 1fr;
            grid-gap: 15px;
            width: 100%;
            max-width: 500px;
            background-color: #8AA989;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        label {
            text-align: left;
            font-weight: bold;
            color: #253628;
        }

        input[type="date"],
        input[type="time"],
        input[type="number"],
        select {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #C0CEB2;
            border-radius: 6px;
            background-color: #EBEBE9;
            color: #253628;
            transition: border-color 0.3s, transform 0.2s;
        }

        input:hover, select:hover {
            border-color: #49654C;
            transform: scale(1.02);
        }

        input:focus, select:focus {
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
        }

        button:hover {
            background-color: #A3E6DA;
            transform: scale(1.05);
        }

        button:active {
            background-color: #C0CEB2;
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
        }

        #summary {
            margin-top: 20px;
            animation: fadeInUp 1.5s ease;
        }

        a {
            color: #A3E6DA;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            color: #49654C;
        }

        @media (max-width: 600px) {
            video {
                display: none;
            }

            .mobile-background {
                display: block;
            }

            header h1 {
                font-size: 1.4rem;
            }

            button {
                padding: 10px;
            }

            input, select {
                font-size: 1em;
            }
        }

        @media (min-width: 601px) {
            video {
                display: block;
            }

            .mobile-background {
                display: none;
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
        <h1><i class="fa-solid fa-car"></i> Driving Experience Assistant</h1>
    </header>

    <main>
        <?php if ($successMessage): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <form action="add_session.php" method="POST">
            <label for="date"><i class="fa-solid fa-calendar-days"></i> Date:</label>
            <input type="date" id="date" name="date" required value="<?php echo date('Y-m-d'); ?>">

            <label for="start-time"><i class="fa-solid fa-hourglass-start"></i> Start Time:</label>
            <input type="time" id="start-time" name="start_time" required value="<?php echo date('H:i'); ?>">

            <label for="end-time"><i class="fa-solid fa-hourglass-end"></i> End Time:</label>
            <input type="time" id="end-time" name="end_time" required>

            <label for="mileage"><i class="fa-solid fa-road"></i> Mileage (in km):</label>
            <input type="number" id="mileage" name="mileage" step="0.1" min="0" required inputmode="decimal">

            <label for="driver"><i class="fa-solid fa-user"></i> Driver:</label>
            <select id="driver" name="driver_id" required>
                <option value="">-- Choose --</option>
                <?php while ($row = $driverResult->fetch_assoc()): ?>
                    <option value="<?php echo $row['driverID']; ?>">
                        <?php echo htmlspecialchars($row['driverName']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="weather"><i class="fa-solid fa-cloud"></i> Weather Condition:</label>
            <select id="weather" name="weather_id" required>
                <option value="">-- Choose --</option>
                <?php while ($row = $weatherResult->fetch_assoc()): ?>
                    <option value="<?php echo $row['weatherID']; ?>">
                        <?php echo htmlspecialchars($row['weatherDescription']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="traffic-conditions"><i class="fa-solid fa-traffic-light"></i> Traffic Conditions:</label>
            <select id="traffic-conditions" name="traffic_id" required>
                <option value="">-- Choose --</option>
                <?php while ($row = $trafficResult->fetch_assoc()): ?>
                    <option value="<?php echo $row['trafficID']; ?>">
                        <?php echo htmlspecialchars($row['trafficDescription']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="road-type"><i class="fa-solid fa-road"></i> Road Type:</label>
            <select id="road-type" name="road_type_id" required>
                <option value="">-- Choose --</option>
                <?php while ($row = $roadResult->fetch_assoc()): ?>
                    <option value="<?php echo $row['roadTypeID']; ?>">
                        <?php echo htmlspecialchars($row['roadTypeDescription']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="visibility-range"><i class="fa-solid fa-eye"></i> Visibility Range:</label>
            <select id="visibility-range" name="visibility_id" required>
                <option value="">-- Choose --</option>
                <?php while ($row = $visibilityResult->fetch_assoc()): ?>
                    <option value="<?php echo $row['visibilityID']; ?>">
                        <?php echo htmlspecialchars($row['visibilityDescription']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="maneuver"><i class="fa-solid fa-person-walking"></i> Maneuver:</label>
            <select id="maneuver" name="maneuver_id" required>
                <option value="">-- Choose --</option>
                <?php while ($row = $maneuverResult->fetch_assoc()): ?>
                    <option value="<?php echo $row['maneuverID']; ?>">
                        <?php echo htmlspecialchars($row['maneuverAttribute']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit">Submit</button>
        </form>

        <div id="summary">
            <h2><i class="fa-solid fa-list"></i> Summary</h2>
            <p>Total Distance Traveled: <?php echo number_format($totalDistance, 1); ?> km</p>
            <a href="summary.php"><i class="fa-solid fa-forward"></i> View Detailed Summary</a>
        </div>
    </main>

    <footer>
        <p><i class="fa-solid fa-copyright"></i> 2025. Driving Experience Assistant.</p>
    </footer>
</body>
</html>