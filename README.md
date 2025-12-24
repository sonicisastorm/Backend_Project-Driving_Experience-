# Supervised Driving Experience - Backend Application

A PHP and MySQL web application for managing and tracking supervised driving experiences.

## Features

- **Add Driving Sessions**: Record date, time, mileage, weather, traffic conditions, road types, visibility, and maneuvers
- **View Summary**: Display all driving sessions in a detailed table
- **Statistics & Charts**: Visualize driving data with interactive bar charts, pie charts, and line graphs
- **Mobile Responsive**: Optimized for both desktop and mobile devices
- **W3C Compliant**: Valid HTML5 with semantic elements
- **Modern Design**: CSS Grid/Flexbox layout with smooth animations

## Technical Stack

- **Backend**: PHP 7.4+ with MySQLi
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Charts**: Chart.js with DataLabels plugin
- **Icons**: Font Awesome

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
- `summary.php`

### 5. Test the Application

1. Access your site: `https://youraccount.alwaysdata.net/`
2. Test adding a driving session
3. View the summary page with statistics

## File Structure

```
/
├── config.php           # Database configuration
├── index.php            # Main entry form
├── add_session.php      # Form submission handler
├── summary.php          # Summary and statistics page
└── database_setup.sql   # Database creation script
```

## Security Features

- **Prepared Statements**: All SQL queries use prepared statements to prevent SQL injection
- **Input Validation**: Server-side validation of all form inputs
- **Session Management**: PHP sessions for success/error messages
- **Separate Database User**: Uses a dedicated database user with minimal privileges

## Original Features Highlighted

### Technical Implementations:

1. **Dynamic Form Population**: All dropdown menus are dynamically populated from MySQL database, eliminating the need for static JSON files

2. **Comprehensive Data Validation**: 
   - Server-side validation ensures end time is after start time
   - All fields are validated before database insertion
   - Clear error messages displayed to users

3. **Advanced Statistics**:
   - Real-time calculation of total kilometers from database
   - Interactive charts with multiple visualization types
   - Chart.js integration with custom color schemes

4. **Responsive Design**:
   - Mobile-first approach with media queries
   - Optimized for screens from 320px to 1920px
   - Touch-friendly interface elements

5. **User Experience Enhancements**:
   - Default current date and time for quick entry
   - Numeric keyboard on mobile for mileage input
   - Smooth animations and transitions
   - Success messages after form submission

6. **Database Optimization**:
   - Efficient JOIN queries to retrieve related data
   - Indexed foreign keys for fast lookups
   - Normalized database structure (3NF)

## Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Support

For issues or questions, contact your instructor or refer to:
- PHP Documentation: https://www.php.net/docs.php
- MySQL Documentation: https://dev.mysql.com/doc/
- Alwaysdata Support: https://help.alwaysdata.com/

## License

Educational project - © 2025

---
