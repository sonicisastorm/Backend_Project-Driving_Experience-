-- Drop tables if they exist (optional, for testing)
DROP TABLE IF EXISTS DrivingSession;
DROP TABLE IF EXISTS Maneuvers;
DROP TABLE IF EXISTS VisibilityRange;
DROP TABLE IF EXISTS RoadType;
DROP TABLE IF EXISTS TrafficCondition;
DROP TABLE IF EXISTS WeatherCondition;
DROP TABLE IF EXISTS Driver;

-- Create Driver Table
CREATE TABLE Driver (
    driverID TINYINT PRIMARY KEY,
    driverName VARCHAR(100) NOT NULL,
    driverPassword VARCHAR(100) NOT NULL,
    birthday DATE NOT NULL
);

-- Create WeatherCondition Table
CREATE TABLE WeatherCondition (
    weatherID TINYINT PRIMARY KEY,
    weatherDescription VARCHAR(50) NOT NULL
);

-- Create TrafficCondition Table
CREATE TABLE TrafficCondition (
    trafficID TINYINT PRIMARY KEY,
    trafficDescription VARCHAR(50) NOT NULL
);

-- Create RoadType Table
CREATE TABLE RoadType (
    roadTypeID TINYINT PRIMARY KEY,
    roadTypeDescription VARCHAR(100) NOT NULL,
    difficulty VARCHAR(50) NOT NULL
);

-- Create VisibilityRange Table
CREATE TABLE VisibilityRange (
    visibilityID TINYINT PRIMARY KEY,
    visibilityDescription VARCHAR(100) NOT NULL
);

-- Create Maneuvers Table
CREATE TABLE Maneuvers (
    maneuverID TINYINT PRIMARY KEY,
    maneuverAttribute VARCHAR(100) NOT NULL,
    difficulty VARCHAR(50) NOT NULL
);

-- Create DrivingSession Table
CREATE TABLE DrivingSession (
    sessionID INT PRIMARY KEY,
    sessionDate DATE NOT NULL,
    startTime TIME NOT NULL,
    endTime TIME NOT NULL,
    mileage DECIMAL(5,1) NOT NULL,
    driverID TINYINT NOT NULL,
    weatherID TINYINT NOT NULL,
    trafficID TINYINT NOT NULL,
    roadTypeID TINYINT NOT NULL,
    visibilityID TINYINT NOT NULL,
    maneuverID TINYINT NOT NULL,
    FOREIGN KEY (driverID) REFERENCES Driver(driverID),
    FOREIGN KEY (weatherID) REFERENCES WeatherCondition(weatherID),
    FOREIGN KEY (trafficID) REFERENCES TrafficCondition(trafficID),
    FOREIGN KEY (roadTypeID) REFERENCES RoadType(roadTypeID),
    FOREIGN KEY (visibilityID) REFERENCES VisibilityRange(visibilityID),
    FOREIGN KEY (maneuverID) REFERENCES Maneuvers(maneuverID)
);

-- Insert Drivers
INSERT INTO Driver (driverID, driverName, driverPassword, birthday) VALUES
(1, 'John Doe', 'hashed_password_123', '1995-06-15'),
(2, 'Jane Smith', 'hashed_password_456', '1992-11-03');

-- Insert Weather Conditions
INSERT INTO WeatherCondition (weatherID, weatherDescription) VALUES
(1, 'Sunny'),
(2, 'Rainy'),
(3, 'Snowy');

-- Insert Traffic Conditions
INSERT INTO TrafficCondition (trafficID, trafficDescription) VALUES
(1, 'Rush Hour'),
(2, 'Rural Roads'),
(3, 'Urban Areas');

-- Insert Road Types
INSERT INTO RoadType (roadTypeID, roadTypeDescription, difficulty) VALUES
(1, 'Dry and Clear', 'Easy'),
(2, 'Gravel', 'Medium'),
(3, 'Construction Zone', 'Hard');

-- Insert Visibility Ranges
INSERT INTO VisibilityRange (visibilityID, visibilityDescription) VALUES
(1, 'Dust or Smoke'),
(2, 'Heavy Rain'),
(3, 'Sun Glare');

-- Insert Maneuvers
INSERT INTO Maneuvers (maneuverID, maneuverAttribute, difficulty) VALUES
(1, 'Lane Change', 'Medium'),
(2, 'Sharp Turn', 'Hard'),
(3, 'Parallel Parking', 'Hard');

-- Insert Driving Sessions
INSERT INTO DrivingSession (sessionID, sessionDate, startTime, endTime, mileage, driverID, weatherID, trafficID, roadTypeID, visibilityID, maneuverID) VALUES
(101, '2025-05-01', '08:00:00', '09:00:00', 25.4, 1, 1, 1, 1, 1, 1),
(102, '2025-05-02', '21:00:00', '22:15:00', 15.8, 1, 2, 3, 3, 2, 2),
(103, '2025-05-03', '07:30:00', '08:30:00', 32.7, 2, 3, 2, 2, 3, 3);
