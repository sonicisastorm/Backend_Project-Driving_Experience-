# Supervised Driving Experience - Backend Application

A PHP and MySQL web application for managing and tracking supervised driving experiences with comprehensive statistics, driver management, and enterprise-grade security features.

## 🚗 Live Project
**Visit the application**: [https://sonicstorm.alwaysdata.net/backend/driving_experience/](https://sonicstorm.alwaysdata.net/backend/driving_experience/)

<img width="1914" height="962" alt="image" src="https://github.com/user-attachments/assets/cc30e270-2244-4eb1-9836-959c0aa6dcd7" />

## Features

### Core Functionality
- **Add Driving Sessions**: Record date, time, mileage, weather, traffic conditions, road types, visibility, and maneuvers
- **View Summary**: Display all driving sessions in a detailed table with filters by driver
- **Statistics & Charts**: Visualize driving data with interactive bar charts, pie charts, and line graphs
- **Mobile Responsive**: Optimized for both desktop and mobile devices
- **W3C Compliant**: Valid HTML5 with semantic elements
- **Modern Design**: CSS Grid/Flexbox layout with smooth animations

### Driver Management
- **Add New Drivers**: Register new drivers with password protection
- **View All Drivers**: See all drivers with their session count and total distance
- **Delete Drivers**: Remove drivers and automatically delete all associated driving sessions
- **Auto-update**: Summary and form dropdowns automatically reflect changes
- **Transaction-safe**: Database operations are atomic and secure

### Analytics
- **Overall Statistics**: Combined data and charts for all drivers
- **Individual Driver Stats**: Dedicated statistics cards for each driver showing:
  - Total sessions and kilometers
  - Average distance per session
  - First and last session dates
- **Easy Navigation**: Header navigation bar across all pages

### 🔒 Advanced Security Features (NEW!)
- **Data Anonymization**: Custom HMAC-SHA256 tokenization system hides all database IDs from HTML forms
- **SQL Injection Prevention**: All queries use MySQLi prepared statements with parameterized inputs
- **Password Security**: BCrypt hashing with automatic salting (minimum 6 characters)
- **XSS Protection**: All user output sanitized with `htmlspecialchars()`
- **Token Validation**: Cryptographic verification of all form submissions
- **Database Verification**: Server-side validation ensures decoded IDs exist before operations
- **CSRF Protection**: Session-based validation prevents cross-site request forgery
- **Transaction Safety**: ACID-compliant operations with automatic rollback on failures

## Technical Stack

- **Backend**: PHP 7.4+ with MySQLi
- **Database**: MySQL/MariaDB
- **Security**: HMAC-SHA256 tokenization, BCrypt password hashing
- **Frontend**: HTML5, CSS3, JavaScript
- **Charts**: Chart.js with DataLabels plugin
- **Icons**: Font Awesome
- **Hosting**: Alwaysdata

## File Structure

```
/
├── config.php                 # Database configuration
├── security_helpers.php       # Token generation/decoding (NEW!)
├── index.php                  # Main driving experience form
├── add_session.php           # Form submission handler with token validation
├── manage_drivers.php        # Driver management page with secure delete
├── summary.php               # Summary and statistics page
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

3. Open `security_helpers.php`
4. **IMPORTANT**: Change the security token secret:
   ```php
   define('TOKEN_SECRET', 'your_unique_random_secret_key_here');
   ```
   Use a long random string (at least 32 characters). Example:
   ```php
   define('TOKEN_SECRET', 'xK9m2Pq7Rz5Lw8Fn3Yv6Jt4Hb1Gc0SaXy3Zm5Nq');
   ```

### 4. Upload Files to Alwaysdata

Upload all PHP files to your web directory:
- `config.php`
- `security_helpers.php` 
- `index.php`
- `add_session.php`
- `manage_drivers.php`
- `summary.php`

### 5. Test the Application

1. Access your site: `https://youraccount.alwaysdata.net/backend/driving_experience/`
2. Use the **Manage Drivers** page to add drivers
3. Test adding a driving session with the new drivers
4. View the summary page with statistics per driver
5. **Verify security**: View page source and confirm IDs are hashed (not raw numbers)

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

## Security Features Explained

### 1. Data Anonymization (Token System)
Instead of exposing raw database IDs in HTML forms:
```html
<!-- ❌ INSECURE (old way) -->
<option value="1">John Doe</option>

<!-- ✅ SECURE (new way) -->
<option value="3f7a9b2c4d5e6f7a8b9c0d1e2f3a4b5MTJ8ZHJpdmVy">John Doe</option>
```

**How it works:**
1. When displaying forms, database IDs are converted to cryptographic tokens using HMAC-SHA256
2. Tokens include the ID, entity type, and a secret key hash
3. When forms are submitted, tokens are validated and decoded server-side
4. Invalid or tampered tokens are rejected automatically

**Benefits:**
- Prevents ID enumeration attacks (can't guess valid IDs)
- Hides database structure from potential attackers
- Detects and blocks tampered form submissions
- Adds additional security layer beyond prepared statements

### 2. Multi-Layer Input Validation
- **Client-side**: HTML5 validation (required fields, formats)
- **Server-side**: PHP validation with type checking
- **Token validation**: Cryptographic verification of form data
- **Database verification**: Confirms decoded IDs exist before use
- **Business logic**: Validates constraints (end time after start time, etc.)

### 3. Database Security
- **Prepared Statements**: 100% protection against SQL injection
- **Parameterized Queries**: All user input properly escaped
- **Transaction Support**: ACID compliance with automatic rollback
- **Foreign Key Constraints**: Enforces referential integrity
- **Indexed Columns**: Optimized query performance

### 4. Password Security
- **BCrypt Algorithm**: Industry-standard password hashing
- **Automatic Salting**: Each password has unique salt
- **Minimum Length**: 6 character requirement enforced
- **Hash Storage**: Plain text passwords never stored

### 5. Session Security
- **PHP Sessions**: Secure server-side session management
- **Message System**: Success/error messages with automatic cleanup
- **Session Validation**: Prevents unauthorized access

## Advanced Features

### Dynamic Form Population
All dropdown menus are dynamically populated from the MySQL database:
- Drivers list updates in real-time when new drivers are added
- All condition options (weather, traffic, road type, visibility, maneuvers) are database-driven
- **Tokenized Values**: All form values use cryptographic tokens for security

### Comprehensive Data Validation
- Server-side validation ensures end time is after start time
- Driver name uniqueness checking prevents duplicates
- Password strength requirements (minimum 6 characters)
- Token cryptographic validation prevents tampering
- Database existence verification for all foreign keys
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

## Technical Implementation Highlights

### Security Architecture
- **Defense in Depth**: Multiple security layers protect against attacks
- **HMAC-SHA256 Tokenization**: Cryptographic ID obfuscation in forms
- **Prepared Statements**: Complete SQL injection prevention
- **Password Hashing**: BCrypt with automatic salting
- **XSS Protection**: All output properly escaped
- **Token Validation**: Cryptographic verification of form submissions
- **Database Verification**: Server-side ID existence checking

### Code Quality
- **Separation of Concerns**: Configuration, security, logic, and presentation separated
- **DRY Principle**: Reusable functions throughout
- **Error Handling**: Try-catch blocks with proper exception handling
- **Comprehensive Comments**: Inline documentation for complex logic
- **Consistent Naming**: camelCase for variables, snake_case for database
- **Transaction Safety**: ACID compliance for critical operations

### Performance
- **Efficient Queries**: Optimized JOINs minimize database calls
- **Connection Reuse**: Single connection per page lifecycle
- **Strategic Indexing**: Fast lookups on foreign keys
- **Lazy Loading**: Charts render only when data exists
- **CDN Resources**: External libraries loaded from CDN

## Troubleshooting

### Common Issues

**1. "Invalid form data detected" error**
- Ensure `security_helpers.php` is uploaded and accessible
- Verify `TOKEN_SECRET` is set in `security_helpers.php`
- Check that `config.php` includes `security_helpers.php`

**2. Tokens not working**
- Clear browser cache and reload the page
- Verify all files are uploaded with correct permissions
- Check PHP error log for specific error messages

**3. Driver deletion fails**
- Ensure database user has DELETE privileges
- Check that transactions are supported (InnoDB engine)
- Verify foreign key relationships are intact

**4. Password hashing issues**
- Ensure PHP version is 7.4 or higher
- Verify BCrypt is available (it is by default in PHP 7.4+)

## Support & Resources

For issues or questions, refer to:
- PHP Documentation: [https://www.php.net/docs.php](https://www.php.net/docs.php)
- MySQL Documentation: [https://dev.mysql.com/doc/](https://dev.mysql.com/doc/)
- Alwaysdata Support: [https://help.alwaysdata.com/](https://help.alwaysdata.com/)
- Chart.js Documentation: [https://www.chartjs.org/](https://www.chartjs.org/)
- OWASP Security Guide: [https://owasp.org/](https://owasp.org/)

## Version History

### v3.0 (Latest - Security Enhanced)
- **NEW**: HMAC-SHA256 token-based ID anonymization system
- **NEW**: `security_helpers.php` with token generation/decoding functions
- Enhanced form security with cryptographic token validation
- Added database verification for all decoded IDs
- Improved error handling for invalid/tampered tokens
- Updated all forms to use tokenized values instead of raw IDs
- Enhanced delete functionality with token-based validation
- Comprehensive security documentation added
- Multi-layer validation system implemented

### v2.0
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

## Security Notice

**IMPORTANT**: This application implements professional-grade security features. To maintain security:

1. **Always change `TOKEN_SECRET`** in `security_helpers.php` to a unique random value
2. Use strong database passwords
3. Keep PHP and MySQL updated to latest stable versions
4. Use HTTPS in production environments
5. Regularly review and update security measures
6. Never commit `config.php` or `security_helpers.php` with real credentials to public repositories

---

**Built with security, performance, and user experience in mind.**
