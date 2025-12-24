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
        w.weatherDescription,
        t.trafficDescription,
        r.roadTypeDescription,
        v.visibilityDescription,
        m.maneuverAttribute
    FROM DrivingSession ds
    JOIN WeatherCondition w ON ds.weatherID = w.weatherID
    JOIN TrafficCondition t ON ds.trafficID = t.trafficID
    JOIN RoadType r ON ds.roadTypeID = r.roadTypeID
    JOIN VisibilityRange v ON ds.visibilityID = v.visibilityID
    JOIN Maneuvers m ON ds.maneuverID = m.maneuverID
    ORDER BY ds.sessionDate DESC, ds.startTime DESC
";

$result = $conn->query($query);

// Calculate total distance
$totalQuery = "SELECT SUM(mileage) as total FROM DrivingSession";
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalDistance = $totalRow['total'] ?? 0;

// Get statistics for charts
$weatherStats = [];
$trafficStats = [];
$roadStats = [];
$visibilityStats = [];

// Weather statistics
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

// Traffic statistics
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

// Road type statistics
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

// Visibility statistics
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

// Get data for line chart (distance over time)
$lineChartQuery = "
    SELECT sessionDate, mileage
    FROM DrivingSession
    ORDER BY sessionDate ASC
";
$lineChartResult = $conn->query($lineChartQuery);
$lineChartData = [];
while ($row = $lineChartResult->fetch_assoc()) {
    $lineChartData[] = $row;
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
            max-width: 1000px;
            margin-bottom: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            box-sizing: border-box;
        }

        .total-distance {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: #FFF;
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
        }

        @media (max-width: 600px) {
            header h1 {
                font-size: 1.4rem;
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
        }
    </style>
</head>
<body>
    <header>
        <h1><i class="fa-solid fa-car"></i> Driving Experience Summary</h1>
    </header>
    
    <main>
        <div class="summary-box">
            <div class="total-distance">
                Total Distance Traveled: <span><?php echo number_format($totalDistance, 1); ?> km</span>
            </div>
            
            <h2><i class="fa-solid fa-circle-info"></i> Detailed Submissions</h2>
            
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
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
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
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

                <h2><i class="fa-solid fa-chart-simple"></i> Statistics</h2>
                <div class="toggle-buttons">
                    <button id="barChartButton" class="active">Bar Charts</button>
                    <button id="pieChartButton">Pie Charts</button>
                    <button id="lineChartButton">Line Graph</button>
                </div>
                
                <canvas id="weatherChart"></canvas>
                <canvas id="trafficChart"></canvas>
                <canvas id="roadChart"></canvas>
                <canvas id="visibilityChart"></canvas>
                <canvas id="lineChart" style="display: none;"></canvas>
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
        const lineChartData = <?php echo json_encode($lineChartData); ?>;

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

            document.getElementById("lineChart").style.display = "none";
            ["weatherChart", "trafficChart", "roadChart", "visibilityChart"].forEach(id => {
                document.getElementById(id).style.display = "block";
            });

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

        function renderLineChart() {
            chartInstances.forEach(chart => chart.destroy());
            chartInstances = [];

            document.getElementById("lineChart").style.display = "block";
            ["weatherChart", "trafficChart", "roadChart", "visibilityChart"].forEach(id => {
                document.getElementById(id).style.display = "none";
            });

            // Update button states
            document.querySelectorAll('.toggle-buttons button').forEach(btn => {
                btn.classList.remove('active');
            });
            document.getElementById('lineChartButton').classList.add('active');

            const labels = lineChartData.map(d => d.sessionDate);
            const data = lineChartData.map(d => parseFloat(d.mileage));

            const lineChart = new Chart(document.getElementById("lineChart"), {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Distance Traveled Over Time (km)",
                        data: data,
                        borderColor: "#32CD32",
                        backgroundColor: "rgba(50, 205, 50, 0.2)",
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: true, position: 'top' },
                        title: { display: true, text: "Distance Traveled Over Time" },
                        datalabels: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true },
                        x: { title: { display: true, text: "Date" } }
                    }
                }
            });

            chartInstances.push(lineChart);
        }

        <?php if ($result->num_rows > 0): ?>
        renderCharts("bar");

        document.getElementById("barChartButton").addEventListener("click", () => renderCharts("bar"));
        document.getElementById("pieChartButton").addEventListener("click", () => renderCharts("pie"));
        document.getElementById("lineChartButton").addEventListener("click", renderLineChart);
        <?php endif; ?>
    </script>
</body>
</html>