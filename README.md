# Schedule Tracker

Schedule Tracker is a PHP-based web application designed to manage and track schedules for tournaments or events. It allows users to create, view, edit, and delete schedules, as well as download schedules as PDF reports for a specified date range. The application features a user-friendly interface with Bootstrap styling, responsive design, and secure user authentication.

## Features
- **User Authentication**: Secure login system with session management and inactivity timeout (2 minutes).
- **Schedule Management**:
  - Create new schedules with details like date, time, tour name, match count, status, booking price, and sell/earn price.
  - View schedules in a tabular format, grouped by date.
  - Edit or delete existing schedules.
- **PDF Download**: Generate and download PDF reports of schedules for a selected date range, including a profit summary.
- **Responsive Design**: Mobile-friendly interface with horizontal scrolling for tables on smaller screens.
- **DataTables Integration**: Enhanced table functionality for better user experience.
- **SweetAlert2 Notifications**: User-friendly alerts for success, error, and confirmation messages.

## Prerequisites
To run this application, you need:
- **PHP** (>= 7.4)
- **MySQL** or **MariaDB**
- **Web Server** (e.g., Apache, Nginx)
- **Composer** (for installing TCPDF, if not already included)
- A modern web browser (e.g., Chrome, Firefox)

## Installation

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/schedule-tracker.git
cd schedule-tracker
```

### 2. Set Up the Database
1. Create a MySQL database named `schedule_tracker`.
2. Import the following SQL to create the `schedule` table:
   ```sql
   CREATE TABLE schedule (
       id INT AUTO_INCREMENT PRIMARY KEY,
       schedule_date DATE NOT NULL,
       time_slot DATETIME NOT NULL,
       tour_name VARCHAR(255) NOT NULL,
       match_info INT NOT NULL,
       status ENUM('No Update', 'Qualified', 'Disqualified') NOT NULL,
       booking_price DECIMAL(10,2) DEFAULT NULL,
       sell_earn_price DECIMAL(10,2) DEFAULT NULL
   );
   ```
3. Update the database configuration in `api/config.php` with your database credentials:
   ```php
   $host = "localhost";
   $user = "your_username";
   $password = "your_password";
   $db = "schedule_tracker";
   ```

### 3. Install Dependencies
The application uses TCPDF for PDF generation. If not already included, install it via Composer:
```bash
composer require tecnickcom/tcpdf
```
Ensure the `tcpdf` folder is placed in the `api` directory.

### 4. Configure the Web Server
- Place the project files in your web server's root directory (e.g., `htdocs` for Apache).
- Ensure the `api` directory is accessible and writable for temporary files (if needed for TCPDF).
- Point your web server to the project directory.

### 5. Set File Permissions
Ensure the web server has read/write permissions for the project directory:
```bash
chmod -R 755 api/
```

### 6. Access the Application
- Open your browser and navigate to `http://localhost/schedule-tracker/`.
- Log in with the default credentials:
  - **Username**: Dorin
  - **Password**: 829798

## File Structure
```
schedule-tracker/
├── api/
│   ├── config.php              # Database configuration
│   ├── download_pdf.php        # Generates PDF reports
│   ├── edit.php               # Edit schedule form
│   ├── submit.php             # Handles schedule creation
│   ├── update.php             # Handles schedule updates
│   ├── script.js              # Frontend JavaScript logic
│   ├── style.css              # Custom CSS styling
│   └── tcpdf/                 # TCPDF library (for PDF generation)
├── delete.php                 # Deletes schedules
├── index.php                  # Create schedule page
├── login.php                  # Login page
├── logout.php                 # Logout script
├── overview.php               # Schedule overview with edit/delete options
├── viewer.php                 # View schedules and download PDFs
└── README.md                  # Project documentation
```

## Usage
1. **Login**: Access the login page and use the default credentials to log in.
2. **Create Schedule** (`index.php`):
   - Fill out the form with schedule details (date, time, tour name, etc.).
   - Submit to save the schedule to the database.
3. **View Schedules**:
   - **Overview** (`overview.php`): Displays schedules with options to edit or delete.
   - **Viewer** (`viewer.php`): Displays schedules without edit/delete options and includes a form to download PDFs.
4. **Edit Schedule** (`api/edit.php`): Modify existing schedule details.
5. **Delete Schedule** (`delete.php`): Permanently remove a schedule after confirmation.
6. **Download PDF** (`api/download_pdf.php`):
   - Select a date range in the Viewer page.
   - Download a PDF report summarizing schedules and profit (sell/earn price - booking price).
7. **Logout** (`logout.php`): Ends the session and redirects to the login page.

## Dependencies
- **Bootstrap 5.3.3**: Frontend framework for styling and components.
- **jQuery 3.7.1**: JavaScript library for DOM manipulation.
- **DataTables 1.13.7**: Table enhancement plugin for sorting and responsive tables.
- **SweetAlert2 11**: Alert library for user notifications.
- **TCPDF**: PHP library for generating PDF reports.

CDN links are used for Bootstrap, jQuery, DataTables, and SweetAlert2. Ensure internet access for these to load, or host them locally.

## Security Notes
- **Default Credentials**: Change the default username and password in `login.php` for production use.
- **Session Timeout**: The application logs out users after 2 minutes of inactivity (configurable in `api/script.js`).
- **Input Sanitization**: All user inputs are sanitized using `filter_input` to prevent SQL injection and XSS.
- **Database Security**: Use a dedicated database user with limited permissions and update credentials in `config.php`.
- **HTTPS**: Deploy the application over HTTPS to secure data transmission.

## Troubleshooting
- **Database Connection Error**: Verify `config.php` credentials and ensure the MySQL server is running.
- **PDF Generation Fails**: Ensure the `tcpdf` folder is present and writable. Check PHP error logs for details.
- **Styles/Scripts Not Loading**: Confirm CDN links are accessible or host dependencies locally.
- **Table Scroll Issues on Mobile**: Ensure `style.css` has proper `min-width` settings for tables.

## Contributing
1. Fork the repository.
2. Create a new branch (`git checkout -b feature/your-feature`).
3. Make changes and commit (`git commit -m "Add your feature"`).
4. Push to the branch (`git push origin feature/your-feature`).
5. Open a pull request.

## License
This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contact
For questions or feedback, contact [Your Name] at [your.email@example.com] or open an issue on GitHub.
