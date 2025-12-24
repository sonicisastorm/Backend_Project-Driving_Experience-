# Supervised Driving Experience - Backend Application

A PHP and MySQL web application for managing and tracking supervised driving experiences with comprehensive statistics and driver management.

## 🚗 Live Project
**Visit the application**: [https://sonicstorm.alwaysdata.net/backend/driving_experience/](https://sonicstorm.alwaysdata.net/backend/driving_experience/)

## Features

### Core Functionality
- **Add Driving Sessions**: Record date, time, mileage, weather, traffic conditions, road types, visibility, and maneuvers
- **View Summary**: Display all driving sessions in a detailed table with filters by driver
- **Statistics & Charts**: Visualize driving data with interactive bar charts, pie charts, and line graphs
- **Mobile Responsive**: Optimized for both desktop and mobile devices
- **W3C Compliant**: Valid HTML5 with semantic elements
- **Modern Design**: CSS Grid/Flexbox layout with smooth animations

### Driver Management (NEW!)
- **Add New Drivers**: Register new drivers with password protection
- **View All Drivers**: See all drivers with their session count and total distance
- **Delete Drivers**: Remove drivers and automatically delete all associated driving sessions
- **Auto-update**: Summary and form dropdowns automatically reflect changes
- **Transaction-safe**: Database operations are atomic and secure

### Analytics (NEW!)
- **Overall Statistics**: Combined data and charts for all drivers
- **Individual Driver Stats**: Dedicated statistics cards for each driver showing:
  - Total sessions and kilometers
  - Average distance per session
  - First and last session dates
- **Easy Navigation**: Header navigation bar across all pages

## Technical Stack

- **Backend**: PHP 7.4+ with MySQLi
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Charts**: Chart.js with DataLabels plugin
- **Icons**: Font Awesome
- **Hosting**: Alwaysdata

## File Structure

```
/
├── config.php                 # Database configuration
├── index.php                  # Main driving experience form
├── add_session.php           # Form submission handler
├── manage_drivers.php        # Driver management page (NEW!)
├── summary.php               # Summary and statistics page (ENHANCED!)
├── database_setup.sql        # Database creation script
└── README.md                 # This file
```

## Installation Instructions

### 1. Database Setup on Alwaysdata

1. Log in to your Alwaysdata account
2. Go to **Databases** → **MySQL**
3. Create a new database (e.g., `youraccount_driving`)
4. Create a new database user:
   - Username: `youraccount_student`
   - Password: Choose a strong password
   - Grant all privileges on your database

### 2. Import Database Structure

1. In Alwaysdata, go to **Databases** → **phpMyAdmin**
2. Select your database
3. Click on **Import** tab
4. Upload the `database_setup.sql` file
5. Click **Go** to execute

### 3. Configure the Application

1. Open `config.php`
2. Update the database credentials:
   ```php
   define('DB_HOST', 'mysql-youraccount.alwaysdata.net');
   define('DB_NAME', 'youraccount_driving');
   define('DB_USER', 'youraccount_student');
   define('DB_PASS', 'your_password_here');
   ```

### 4. Upload Files to Alwaysdata

Upload all PHP files to your web directory:
- `config.php`
- `index.php`
- `add_session.php`
- `manage_drivers.php` (NEW!)
- `summary.php`

### 5. Test the Application

1. Access your site: `https://youraccount.alwaysdata.net/backend/driving_experience/`
2. Use the **Manage Drivers** page to add drivers
3. Test adding a driving session with the new drivers
4. View the summary page with statistics per driver

## How to Use

### Adding a New Driver
1. Click **Manage Drivers** in the navigation menu
2. Fill in the driver information (Name, Birthday, Password)
3. Click **Add Driver**
4. The new driver will appear in the dropdown on the main form

### Recording a Driving Session
1. Fill in all form fields on the home page
2. Select a driver from the dropdown
3. Enter date, time, mileage, and conditions
4. Click **Submit**
5. See success confirmation message

### Viewing Statistics
1. Click **Summary** in the navigation menu
2. View all recorded sessions in the table
3. Overall statistics show combined data for all drivers
4. Individual driver cards display personal statistics
5. Toggle between bar charts and pie charts for conditions data

### Deleting a Driver
1. Go to **Manage Drivers** page
2. Find the driver you want to delete
3. Click the **Delete** button
4. Confirm deletion in the modal (WARNING: This deletes all sessions for that driver)
5. The driver and their data are permanently removed

## Security Features

- **Prepared Statements**: All SQL queries use prepared statements to prevent SQL injection
- **Password Hashing**: Driver passwords use PHP's PASSWORD_BCRYPT algorithm
- **Input Validation**: Server-side validation of all form inputs
- **Session Management**: PHP sessions for success/error messages
- **Transaction Safety**: Database deletion operations use transactions for atomicity
- **Separate Database User**: Uses a dedicated database user with minimal privileges
- **XSS Protection**: All output is HTML-escaped using `htmlspecialchars()`

## Advanced Features

### Dynamic Form Population
All dropdown menus are dynamically populated from the MySQL database:
- Drivers list updates in real-time when new drivers are added
- All condition options (weather, traffic, road type, visibility, maneuvers) are database-driven

### Comprehensive Data Validation
- Server-side validation ensures end time is after start time
- Driver name uniqueness checking prevents duplicates
- Password strength requirements (minimum 6 characters)
- All fields required before submission
- Clear error messages displayed to users

### Advanced Statistics & Analytics
- Real-time calculation of total kilometers from database
- Individual driver performance tracking
- Interactive charts with multiple visualization types
- Chart.js integration with custom color schemes (green theme)
- Responsive chart rendering on all devices

### Responsive Design
- Mobile-first approach with media queries
- Optimized for screens from 320px to 1920px
- Touch-friendly interface elements
- Adaptive tables and forms for small screens

### User Experience Enhancements
- Default current date and time for quick entry
- Numeric keyboard on mobile for mileage input
- Smooth animations and transitions throughout
- Success/error messages with visual feedback
- Modal confirmation for destructive actions (delete)
- Navigation bar for easy page access

### Database Optimization
- Efficient JOIN queries to retrieve related data
- Indexed foreign keys for fast lookups
- Normalized database structure (3NF)
- Transaction-safe operations for data integrity
- Optimized views for common queries

## Data Deletion & Cascade

When a driver is deleted:
1. All DrivingSession records associated with the driver are deleted first
2. The Driver record is then deleted
3. The summary page automatically reflects these changes
4. The driver dropdown on the main form updates automatically
5. All statistics recalculate in real-time

This ensures:
- No orphaned records in the database
- Referential integrity is maintained
- All related data is properly cleaned up

## Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome Mobile)
- Mobile app browsers (Instagram, Facebook, etc.)

## Database Schema

### Tables
- **Driver**: Stores driver information with hashed passwords
- **WeatherCondition**: Weather condition options
- **TrafficCondition**: Traffic condition options
- **RoadType**: Road type options with difficulty levels
- **VisibilityRange**: Visibility range options
- **Maneuvers**: Driving maneuver options with difficulty
- **DrivingSession**: Main table storing all driving session records with foreign keys

### Views
- **vw_DrivingSessionDetails**: Complete session details with all related information
- **vw_DriverStatistics**: Driver performance summary statistics

## Support & Resources

For issues or questions, refer to:
- PHP Documentation: [https://www.php.net/docs.php](https://www.php.net/docs.php)
- MySQL Documentation: [https://dev.mysql.com/doc/](https://dev.mysql.com/doc/)
- Alwaysdata Support: [https://help.alwaysdata.com/](https://help.alwaysdata.com/)
- Chart.js Documentation: [https://www.chartjs.org/](https://www.chartjs.org/)

## Version History

### v2.0 (Latest)
- Added Driver Management page with CRUD operations
- Enhanced Summary page with per-driver statistics
- Added delete functionality with cascade delete
- Improved navigation with header menu
- Enhanced statistics with driver-specific cards
- Added transaction support for data integrity
- Improved error handling and validation

### v1.0 (Original)
- Basic driving session recording
- Summary view with overall statistics
- Chart visualization
- Mobile responsive design

## License

Educational project - © 2025

---
