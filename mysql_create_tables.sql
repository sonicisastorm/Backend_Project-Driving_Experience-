-- ============================================
-- Supervised Driving Experience Database
-- Complete setup with sample data
-- ============================================

DROP TABLE IF EXISTS DrivingSession;
DROP TABLE IF EXISTS Maneuvers;
DROP TABLE IF EXISTS VisibilityRange;
DROP TABLE IF EXISTS RoadType;
DROP TABLE IF EXISTS TrafficCondition;
DROP TABLE IF EXISTS WeatherCondition;
DROP TABLE IF EXISTS Driver;

-- ============================================
-- CREATE TABLES
-- ============================================

-- Driver Table
CREATE TABLE Driver (
    driverID TINYINT PRIMARY KEY AUTO_INCREMENT,
    driverName VARCHAR(100) NOT NULL,
    driverPassword VARCHAR(255) NOT NULL,
    birthday DATE NOT NULL,
    UNIQUE KEY (driverName)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Weather Condition Table
CREATE TABLE WeatherCondition (
    weatherID TINYINT PRIMARY KEY AUTO_INCREMENT,
    weatherDescription VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Traffic Condition Table
CREATE TABLE TrafficCondition (
    trafficID TINYINT PRIMARY KEY AUTO_INCREMENT,
    trafficDescription VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Road Type Table
CREATE TABLE RoadType (
    roadTypeID TINYINT PRIMARY KEY AUTO_INCREMENT,
    roadTypeDescription VARCHAR(100) NOT NULL,
    difficulty VARCHAR(50) NOT NULL,
    UNIQUE KEY (roadTypeDescription)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Visibility Range Table
CREATE TABLE VisibilityRange (
    visibilityID TINYINT PRIMARY KEY AUTO_INCREMENT,
    visibilityDescription VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Maneuvers Table
CREATE TABLE Maneuvers (
    maneuverID TINYINT PRIMARY KEY AUTO_INCREMENT,
    maneuverAttribute VARCHAR(100) NOT NULL,
    difficulty VARCHAR(50) NOT NULL,
    UNIQUE KEY (maneuverAttribute)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Driving Session Table (Main table with foreign keys)
CREATE TABLE DrivingSession (
    sessionID INT PRIMARY KEY AUTO_INCREMENT,
    sessionDate DATE NOT NULL,
    startTime TIME NOT NULL,
    endTime TIME NOT NULL,
    mileage DECIMAL(6,1) NOT NULL CHECK (mileage >= 0),
    driverID TINYINT NOT NULL,
    weatherID TINYINT NOT NULL,
    trafficID TINYINT NOT NULL,
    roadTypeID TINYINT NOT NULL,
    visibilityID TINYINT NOT NULL,
    maneuverID TINYINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driverID) REFERENCES Driver(driverID) ON DELETE RESTRICT,
    FOREIGN KEY (weatherID) REFERENCES WeatherCondition(weatherID) ON DELETE RESTRICT,
    FOREIGN KEY (trafficID) REFERENCES TrafficCondition(trafficID) ON DELETE RESTRICT,
    FOREIGN KEY (roadTypeID) REFERENCES RoadType(roadTypeID) ON DELETE RESTRICT,
    FOREIGN KEY (visibilityID) REFERENCES VisibilityRange(visibilityID) ON DELETE RESTRICT,
    FOREIGN KEY (maneuverID) REFERENCES Maneuvers(maneuverID) ON DELETE RESTRICT,
    INDEX idx_date (sessionDate),
    INDEX idx_driver (driverID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- INSERT SAMPLE DATA
-- ============================================

-- Insert Drivers
INSERT INTO Driver (driverID, driverName, driverPassword, birthday) VALUES
(1, 'John Doe', '$2y$10$abcdefghijklmnopqrstuvwxyz1234567890ABCD', '1995-06-15'),
(2, 'Jane Smith', '$2y$10$zyxwvutsrqponmlkjihgfedcba0987654321DCBA', '1992-11-03');

-- Insert Weather Conditions (from JSON + original SQL)
INSERT INTO WeatherCondition (weatherID, weatherDescription) VALUES
(1, 'Sunny'),
(2, 'Rainy'),
(3, 'Snowy'),
(4, 'Windy'),
(5, 'Foggy');

-- Insert Traffic Conditions (from JSON + original SQL)
INSERT INTO TrafficCondition (trafficID, trafficDescription) VALUES
(1, 'Rush Hour'),
(2, 'Rural Roads'),
(3, 'Highways'),
(4, 'Urban Areas');

-- Insert Road Types (from JSON + original SQL)
INSERT INTO RoadType (roadTypeID, roadTypeDescription, difficulty) VALUES
(1, 'Dry and Clear', 'Easy'),
(2, 'Gravel or Loose Surface', 'Medium'),
(3, 'Construction Zones', 'Hard'),
(4, 'Debris', 'Hard');

-- Insert Visibility Ranges (from JSON + original SQL)
INSERT INTO VisibilityRange (visibilityID, visibilityDescription) VALUES
(1, 'Dust or Smoke'),
(2, 'Heavy Rain'),
(3, 'Snowfall'),
(4, 'Sun Glare');

-- Insert Maneuvers
INSERT INTO Maneuvers (maneuverID, maneuverAttribute, difficulty) VALUES
(1, 'Lane Change', 'Medium'),
(2, 'Sharp Turn', 'Hard'),
(3, 'Parallel Parking', 'Hard'),
(4, 'Highway Merge', 'Medium'),
(5, 'U-Turn', 'Hard'),
(6, 'Three-Point Turn', 'Medium');

-- Insert Sample Driving Sessions
INSERT INTO DrivingSession (sessionID, sessionDate, startTime, endTime, mileage, driverID, weatherID, trafficID, roadTypeID, visibilityID, maneuverID) VALUES
(101, '2025-05-01', '08:00:00', '09:00:00', 25.4, 1, 1, 1, 1, 4, 1),
(102, '2025-05-02', '21:00:00', '22:15:00', 15.8, 1, 2, 4, 3, 2, 2),
(103, '2025-05-03', '07:30:00', '08:30:00', 32.7, 2, 3, 2, 2, 3, 3),
(104, '2025-05-05', '14:00:00', '15:30:00', 42.3, 1, 1, 3, 1, 4, 4),
(105, '2025-05-07', '09:00:00', '10:15:00', 28.6, 2, 5, 4, 1, 1, 1);

-- ============================================
-- CREATE USEFUL VIEWS
-- ============================================

-- View: Complete driving session details
CREATE OR REPLACE VIEW vw_DrivingSessionDetails AS
SELECT 
    ds.sessionID,
    ds.sessionDate,
    ds.startTime,
    ds.endTime,
    TIMESTAMPDIFF(MINUTE, ds.startTime, ds.endTime) AS duration_minutes,
    ds.mileage,
    d.driverName,
    w.weatherDescription,
    t.trafficDescription,
    r.roadTypeDescription,
    r.difficulty AS roadDifficulty,
    v.visibilityDescription,
    m.maneuverAttribute,
    m.difficulty AS maneuverDifficulty,
    ds.created_at
FROM DrivingSession ds
JOIN Driver d ON ds.driverID = d.driverID
JOIN WeatherCondition w ON ds.weatherID = w.weatherID
JOIN TrafficCondition t ON ds.trafficID = t.trafficID
JOIN RoadType r ON ds.roadTypeID = r.roadTypeID
JOIN VisibilityRange v ON ds.visibilityID = v.visibilityID
JOIN Maneuvers m ON ds.maneuverID = m.maneuverID;

-- View: Driver statistics
CREATE OR REPLACE VIEW vw_DriverStatistics AS
SELECT 
    d.driverID,
    d.driverName,
    COUNT(ds.sessionID) AS total_sessions,
    SUM(ds.mileage) AS total_kilometers,
    AVG(ds.mileage) AS avg_kilometers_per_session,
    MIN(ds.sessionDate) AS first_session_date,
    MAX(ds.sessionDate) AS last_session_date
FROM Driver d
LEFT JOIN DrivingSession ds ON d.driverID = ds.driverID
GROUP BY d.driverID, d.driverName;

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Check table counts
SELECT 'Drivers' AS TableName, COUNT(*) AS RecordCount FROM Driver
UNION ALL
SELECT 'Weather Conditions', COUNT(*) FROM WeatherCondition
UNION ALL
SELECT 'Traffic Conditions', COUNT(*) FROM TrafficCondition
UNION ALL
SELECT 'Road Types', COUNT(*) FROM RoadType
UNION ALL
SELECT 'Visibility Ranges', COUNT(*) FROM VisibilityRange
UNION ALL
SELECT 'Maneuvers', COUNT(*) FROM Maneuvers
UNION ALL
SELECT 'Driving Sessions', COUNT(*) FROM DrivingSession;

-- Show total kilometers
SELECT SUM(mileage) AS total_kilometers_driven FROM DrivingSession;
