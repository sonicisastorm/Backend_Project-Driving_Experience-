<?php
require_once 'config.php';

$conn = getDatabaseConnection();

// Get all driving sessions with related data
$query = "
    SELECT 
        ds.sessionID,
        ds.sessionDate,
        ds.startTime,
        ds.endTime,
        ds.mileage,
        d.driverName,
        d.driverID,
        w.weatherDescription,
        t.trafficDescription,
        r.roadTypeDescription,
        v.visibilityDescription,
        m.maneuverAttribute
    FROM DrivingSession ds
    JOIN Driver d ON ds.driverID = d.driverID
    JOIN WeatherCondition w ON ds.weatherID = w.weatherID
    JOIN TrafficCondition t ON ds.trafficID = t.trafficID
    JOIN RoadType r ON ds.roadTypeID = r.roadTypeID
    JOIN VisibilityRange v ON ds.visibilityID = v.visibilityID
    JOIN Maneuvers m ON ds.maneuverID = m.maneuverID
    ORDER BY ds.sessionDate DESC, ds.startTime DESC
";

$result = $conn->query($query);

// Get all drivers
$driverQuery = "SELECT driverID, driverName FROM Driver ORDER BY driverID";
$driverResult = $conn->query($driverQuery);
$drivers = [];
while ($row = $driverResult->fetch_assoc()) {
    $drivers[$row['driverID']] = $row['driverName'];
}

// Calculate overall total distance
$totalQuery = "SELECT SUM(mileage) as total FROM DrivingSession";
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalDistance = $totalRow['total'] ?? 0;

// Get overall statistics for charts
$weatherStats = [];
$trafficStats = [];
$roadStats = [];
$visibilityStats = [];

// Overall Weather statistics
$weatherQuery = "
    SELECT w.weatherDescription, COUNT(*) as count
    FROM DrivingSession ds
    JOIN WeatherCondition w ON ds.weatherID = w.weatherID
    GROUP BY w.weatherDescription
    ORDER BY w.weatherDescription
";
$weatherResult = $conn->query($weatherQuery);
while ($row = $weatherResult->fetch_assoc()) {
    $weatherStats[$row['weatherDescription']] = $row['count'];
}

// Overall Traffic statistics
$trafficQuery = "
    SELECT t.trafficDescription, COUNT(*) as count
    FROM DrivingSession ds
    JOIN TrafficCondition t ON ds.trafficID = t.trafficID
    GROUP BY t.trafficDescription
    ORDER BY t.trafficDescription
";
$trafficResult = $conn->query($trafficQuery);
while ($row = $trafficResult->fetch_assoc()) {
    $trafficStats[$row['trafficDescription']] = $row['count'];
}

// Overall Road type statistics
$roadQuery = "
    SELECT r.roadTypeDescription, COUNT(*) as count
    FROM DrivingSession ds
    JOIN RoadType r ON ds.roadTypeID = r.roadTypeID
    GROUP BY r.roadTypeDescription
    ORDER BY r.roadTypeDescription
";
$roadResult = $conn->query($roadQuery);
while ($row = $roadResult->fetch_assoc()) {
    $roadStats[$row['roadTypeDescription']] = $row['count'];
}

// Overall Visibility statistics
$visibilityQuery = "
    SELECT v.visibilityDescription, COUNT(*) as count
    FROM DrivingSession ds
    JOIN VisibilityRange v ON ds.visibilityID = v.visibilityID
    GROUP BY v.visibilityDescription
    ORDER BY v.visibilityDescription
";
$visibilityResult = $conn->query($visibilityQuery);
while ($row = $visibilityResult->fetch_assoc()) {
    $visibilityStats[$row['visibilityDescription']] = $row['count'];
}

// Get driver-specific statistics
$driverStats = [];
foreach ($drivers as $driverID => $driverName) {
    $driverQuery = "
        SELECT 
            COUNT(ds.sessionID) as total_sessions,
            SUM(ds.mileage) as total_distance,
            AVG(ds.mileage) as avg_distance,
            MIN(ds.sessionDate) as first_session,
            MAX(ds.sessionDate) as last_session
        FROM DrivingSession ds
        WHERE ds.driverID = ?
    ";
    $stmt = $conn->prepare($driverQuery);
    $stmt->bind_param("i", $driverID);
    $stmt->execute();
    $driverRow = $stmt->get_result()->fetch_assoc();
    $driverStats[$driverID] = [
        'name' => $driverName,
        'sessions' => $driverRow['total_sessions'] ?? 0,
        'distance' => $driverRow['total_distance'] ?? 0,
        'avg_distance' => $driverRow['avg_distance'] ?? 0,
        'first_session' => $driverRow['first_session'],
        'last_session' => $driverRow['last_session']
    ];
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driving Experience Summary</title>
    <script src="https://kit.fontawesome.com/a49a2f08e3.js" crossorigin="anonymous"></script>
    <style>
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
            box-sizing: border-box;
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
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

        .summary-box {
            background-color: #8AA989;
            padding: 25px;
            border-radius: 12px;
            width: 100%;
            max-width: 1100px;
            margin-bottom: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            box-sizing: border-box;
        }

        .summary-box h2 {
            color: #253628;
            margin-top: 0;
        }

        .total-distance {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: #253628;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            overflow-x: auto;
            display: block;
        }

        th, td {
            padding: 10px;
            border: 1px solid #C0CEB2;
            text-align: left;
            word-wrap: break-word;
        }

        th {
            background-color: #49654C;
            color: #EBEBE9;
        }

        tr:nth-child(even) {
            background-color: #A3E6DA;
        }

        tr:nth-child(odd) {
            background-color: #C0CEB2;
        }

        .toggle-buttons {
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        .toggle-buttons button {
            margin: 5px;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            background-color: #49654C;
            color: #EBEBE9;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            font-size: 1rem;
        }

        .toggle-buttons button:hover {
            background-color: #A3E6DA;
            transform: scale(1.05);
        }

        .toggle-buttons button.active {
            background-color: #A3E6DA;
            color: #253628;
        }

        canvas {
            margin-bottom: 30px;
            max-width: 100%;
        }

        a {
            color: #A3E6DA;
            text-decoration: none;
            font-weight: bold;
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
        }

        .no-data {
            text-align: center;
            padding: 20px;
            font-size: 1.2rem;
            color: #253628;
        }

        .driver-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .driver-card {
            background-color: #C0CEB2;
            padding: 20px;
            border-radius: 8px;
            border-left: 5px solid #49654C;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .driver-card h3 {
            color: #49654C;
            margin-top: 0;
            font-size: 1.3rem;
        }

        .driver-card p {
            color: #253628;
            margin: 8px 0;
            text-align: left;
        }

        .driver-card strong {
            color: #49654C;
        }

        .stats-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #49654C;
        }

        .stats-section h2 {
            color: #253628;
            margin-top: 0;
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

            table th, table td {
                font-size: 0.8rem;
                padding: 8px;
            }

            .toggle-buttons button {
                font-size: 0.9rem;
                padding: 8px 16px;
            }

            canvas {
                max-width: 90%;
            }

            .driver-stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1><i class="fa-solid fa-car"></i> Driving Experience Summary</h1>
        <div class="header-nav">
            <a href="index.php"><i class="fa-solid fa-home"></i> Home</a>
            <a href="manage_drivers.php"><i class="fa-solid fa-users"></i> Manage Drivers</a>
            <a href="summary.php"><i class="fa-solid fa-chart-bar"></i> Summary</a>
        </div>
    </header>
    
    <main>
        <div class="summary-box">
            <div class="total-distance">
                <i class="fa-solid fa-globe"></i> Total Distance Traveled (All Drivers): <span><?php echo number_format($totalDistance, 1); ?> km</span>
            </div>
            
            <h2><i class="fa-solid fa-circle-info"></i> Detailed Submissions</h2>
            
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Driver</th>
                            <th>Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Mileage (km)</th>
                            <th>Weather</th>
                            <th>Traffic</th>
                            <th>Road Type</th>
                            <th>Visibility</th>
                            <th>Maneuver</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $result->data_seek(0); ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['driverName']); ?></td>
                                <td><?php echo htmlspecialchars($row['sessionDate']); ?></td>
                                <td><?php echo htmlspecialchars($row['startTime']); ?></td>
                                <td><?php echo htmlspecialchars($row['endTime']); ?></td>
                                <td><?php echo number_format($row['mileage'], 1); ?></td>
                                <td><?php echo htmlspecialchars($row['weatherDescription']); ?></td>
                                <td><?php echo htmlspecialchars($row['trafficDescription']); ?></td>
                                <td><?php echo htmlspecialchars($row['roadTypeDescription']); ?></td>
                                <td><?php echo htmlspecialchars($row['visibilityDescription']); ?></td>
                                <td><?php echo htmlspecialchars($row['maneuverAttribute']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <div class="stats-section">
                    <h2><i class="fa-solid fa-chart-simple"></i> Overall Statistics</h2>
                    <div class="toggle-buttons">
                        <button id="barChartButton" class="active">Bar Charts</button>
                        <button id="pieChartButton">Pie Charts</button>
                    </div>
                    
                    <canvas id="weatherChart"></canvas>
                    <canvas id="trafficChart"></canvas>
                    <canvas id="roadChart"></canvas>
                    <canvas id="visibilityChart"></canvas>
                </div>

                <div class="stats-section">
                    <h2><i class="fa-solid fa-user-group"></i> Driver Statistics</h2>
                    <div class="driver-stats-grid">
                        <?php foreach ($driverStats as $driverID => $stats): ?>
                            <?php if ($stats['sessions'] > 0): ?>
                                <div class="driver-card">
                                    <h3><?php echo htmlspecialchars($stats['name']); ?></h3>
                                    <p><strong>Total Sessions:</strong> <?php echo $stats['sessions']; ?></p>
                                    <p><strong>Total Distance:</strong> <?php echo number_format($stats['distance'], 1); ?> km</p>
                                    <p><strong>Average Distance:</strong> <?php echo number_format($stats['avg_distance'], 1); ?> km</p>
                                    <p><strong>First Session:</strong> <?php echo htmlspecialchars($stats['first_session']); ?></p>
                                    <p><strong>Last Session:</strong> <?php echo htmlspecialchars($stats['last_session']); ?></p>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <p>No driving experiences recorded yet.</p>
                </div>
            <?php endif; ?>
        </div>

        <a href="index.php"><i class="fa-solid fa-backward"></i> Back to Form</a>
    </main>
    
    <footer>
        <p><i class="fa-solid fa-copyright"></i> 2025. Driving Experience Assistant.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script>
        // PHP data to JavaScript
        const weatherStats = <?php echo json_encode($weatherStats); ?>;
        const trafficStats = <?php echo json_encode($trafficStats); ?>;
        const roadStats = <?php echo json_encode($roadStats); ?>;
        const visibilityStats = <?php echo json_encode($visibilityStats); ?>;

        Chart.register(ChartDataLabels);
        let chartInstances = [];

        function prepareChartData(stats) {
            return {
                labels: Object.keys(stats),
                data: Object.values(stats)
            };
        }

        function renderCharts(chartType) {
            chartInstances.forEach(chart => chart.destroy());
            chartInstances = [];

            // Update button states
            document.querySelectorAll('.toggle-buttons button').forEach(btn => {
                btn.classList.remove('active');
            });
            if (chartType === 'bar') {
                document.getElementById('barChartButton').classList.add('active');
            } else if (chartType === 'pie') {
                document.getElementById('pieChartButton').classList.add('active');
            }

            const chartData = [
                { ctx: "weatherChart", title: "Weather Conditions", data: prepareChartData(weatherStats) },
                { ctx: "trafficChart", title: "Traffic Conditions", data: prepareChartData(trafficStats) },
                { ctx: "roadChart", title: "Road Types", data: prepareChartData(roadStats) },
                { ctx: "visibilityChart", title: "Visibility Ranges", data: prepareChartData(visibilityStats) }
            ];

            chartData.forEach(({ ctx, title, data }) => {
                const total = data.data.reduce((sum, value) => sum + value, 0);
                if (total === 0) return;

                const chartInstance = new Chart(document.getElementById(ctx), {
                    type: chartType,
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: title,
                            data: data.data,
                            backgroundColor: ['#9ACD32', '#32CD32', '#00FA9A', '#66CDAA', '#228B22', '#7CFC00']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: true, position: 'top' },
                            title: { display: true, text: title },
                            datalabels: chartType === 'pie' ? {
                                formatter: (value) => `${((value / total) * 100).toFixed(1)}%`,
                                color: '#fff',
                                font: { weight: 'bold' }
                            } : false
                        },
                        scales: chartType === 'bar' ? { y: { beginAtZero: true } } : {}
                    }
                });
                chartInstances.push(chartInstance);
            });
        }

        <?php if ($result->num_rows > 0): ?>
        renderCharts("bar");

        document.getElementById("barChartButton").addEventListener("click", () => renderCharts("bar"));
        document.getElementById("pieChartButton").addEventListener("click", () => renderCharts("pie"));
        <?php endif; ?>
    </script>
</body>
</html>