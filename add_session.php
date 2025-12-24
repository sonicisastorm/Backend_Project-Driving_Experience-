<?php
require_once 'config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Get and sanitize form data
$date = $_POST['date'] ?? '';
$startTime = $_POST['start_time'] ?? '';
$endTime = $_POST['end_time'] ?? '';
$mileage = $_POST['mileage'] ?? 0;
$driverID = $_POST['driver_id'] ?? 1;
$weatherID = $_POST['weather_id'] ?? '';
$trafficID = $_POST['traffic_id'] ?? '';
$roadTypeID = $_POST['road_type_id'] ?? '';
$visibilityID = $_POST['visibility_id'] ?? '';
$maneuverID = $_POST['maneuver_id'] ?? '';

// Validate required fields
if (empty($date) || empty($startTime) || empty($endTime) || empty($mileage) || 
    empty($weatherID) || empty($trafficID) || empty($roadTypeID) || 
    empty($visibilityID) || empty($maneuverID)) {
    $_SESSION['error'] = 'All fields are required!';
    header('Location: index.php');
    exit;
}

// Validate that end time is after start time
if (strtotime($endTime) <= strtotime($startTime)) {
    $_SESSION['error'] = 'End time must be after start time!';
    header('Location: index.php');
    exit;
}

// Get database connection
$conn = getDatabaseConnection();

// Get the next session ID
$idQuery = "SELECT MAX(sessionID) as maxID FROM DrivingSession";
$idResult = $conn->query($idQuery);
$idRow = $idResult->fetch_assoc();
$nextID = ($idRow['maxID'] ?? 100) + 1;

// Prepare SQL statement
$sql = "INSERT INTO DrivingSession (
    sessionID, sessionDate, startTime, endTime, mileage, 
    driverID, weatherID, trafficID, roadTypeID, visibilityID, maneuverID
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    $_SESSION['error'] = 'Database error: ' . $conn->error;
    header('Location: index.php');
    exit;
}

// Bind parameters
$stmt->bind_param(
    "isssdiiiiii",
    $nextID,
    $date,
    $startTime,
    $endTime,
    $mileage,
    $driverID,
    $weatherID,
    $trafficID,
    $roadTypeID,
    $visibilityID,
    $maneuverID
);

// Execute the statement
if ($stmt->execute()) {
    $_SESSION['success'] = 'Driving experience recorded successfully!';
} else {
    $_SESSION['error'] = 'Error recording driving experience: ' . $stmt->error;
}

$stmt->close();
$conn->close();

// Redirect back to index
header('Location: index.php');
exit;
?>