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

// Get tokenized IDs and decode them
$driverToken = $_POST['driver_id'] ?? '';
$weatherToken = $_POST['weather_id'] ?? '';
$trafficToken = $_POST['traffic_id'] ?? '';
$roadTypeToken = $_POST['road_type_id'] ?? '';
$visibilityToken = $_POST['visibility_id'] ?? '';
$maneuverToken = $_POST['maneuver_id'] ?? '';

// Decode tokens to get actual database IDs
$driverID = decodeToken($driverToken, 'driver');
$weatherID = decodeToken($weatherToken, 'weather');
$trafficID = decodeToken($trafficToken, 'traffic');
$roadTypeID = decodeToken($roadTypeToken, 'roadtype');
$visibilityID = decodeToken($visibilityToken, 'visibility');
$maneuverID = decodeToken($maneuverToken, 'maneuver');

// Validate required fields and token decoding
if (empty($date) || empty($startTime) || empty($endTime) || empty($mileage)) {
    $_SESSION['error'] = 'All fields are required!';
    header('Location: index.php');
    exit;
}

// Validate that tokens were decoded successfully
if ($driverID === false || $weatherID === false || $trafficID === false || 
    $roadTypeID === false || $visibilityID === false || $maneuverID === false) {
    $_SESSION['error'] = 'Invalid form data detected. Please try again.';
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

// Verify that the decoded IDs actually exist in the database
// This prevents injection of fake IDs
$verifyQueries = [
    'driver' => "SELECT COUNT(*) as count FROM Driver WHERE driverID = ?",
    'weather' => "SELECT COUNT(*) as count FROM WeatherCondition WHERE weatherID = ?",
    'traffic' => "SELECT COUNT(*) as count FROM TrafficCondition WHERE trafficID = ?",
    'roadtype' => "SELECT COUNT(*) as count FROM RoadType WHERE roadTypeID = ?",
    'visibility' => "SELECT COUNT(*) as count FROM VisibilityRange WHERE visibilityID = ?",
    'maneuver' => "SELECT COUNT(*) as count FROM Maneuvers WHERE maneuverID = ?"
];

$idsToVerify = [
    'driver' => $driverID,
    'weather' => $weatherID,
    'traffic' => $trafficID,
    'roadtype' => $roadTypeID,
    'visibility' => $visibilityID,
    'maneuver' => $maneuverID
];

// Verify each ID exists
foreach ($verifyQueries as $type => $query) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $idsToVerify[$type]);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $_SESSION['error'] = 'Invalid ' . $type . ' selected!';
        $stmt->close();
        $conn->close();
        header('Location: index.php');
        exit;
    }
    $stmt->close();
}

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

// Bind parameters (now using verified decoded IDs)
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