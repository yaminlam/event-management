# Event Management System

This is a Event Management System built using PHP and MySQL. It allows users to register for events, manage events, and view attendee reports. The system includes authentication, CRUD operations for events, and the ability to track event attendees.

## Project Features

- **User Authentication**: Users can register, log in, and log out.
- **Event Management**: Admin users can create, update, and delete events.
- **Attendee Registration**: Admin users can register for events as attendees.
- **Attendee Report**: Admins can view the list of attendees for each event.
- **CRUD Operations**: Admins can perform CRUD operations on events and view reports.
  
## Technologies Used

- **PHP**: Server-side scripting for application logic.
- **MySQL**: Database management system for storing event and user data.
- **HTML/CSS/Bootstrap**: Front-end styling and responsive design.
- **AJAX**: For smooth dynamic interactions (optional if implemented).
- **GitHub**: Version control and collaboration.

## Installation Instructions

### Prerequisites
Make sure you have the following installed on your system:

- PHP 7.x or higher
- MySQL 5.x or higher
- A web server (Apache)
  
### Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yaminlam/event-management.git

## Set up the Database:

- Create a MySQL database, e.g., event_management.
- Import the database file into your phpmyadmin.

## Configure Database Connection:

In db.php, update the database connection credentials with your own:

- define('DB_SERVER', 'localhost');
- define('DB_USERNAME', 'root');
- define('DB_PASSWORD', '');
- define('DB_DATABASE', 'event_management');
- define('PORT', '3307'); //example for my case.

## Set up the Web Server:

- Place the project files in the root directory of your web server.
- Ensure your server is configured to support PHP.
- Visit index.php in your browser to see the application in action.

## Test the System:
- Visit the login page (login.php) to register a new user or log in with existing credentials.
- Access the event management features after logging in as an admin user.

## File Descriptions
- add_event.php: Form to add new events to the system.
- attendee_report.php: View the list of attendees for each event.
- check_login.php: Verifies user login status.
- db.php: Contains database connection details.
- delete_event.php: Used to delete events from the system.
- footer.php: Contains footer HTML code.
- header.php: Contains header HTML code.
- index.php: Dashboard of the application.
- login.php: Login page for users.
- logout.php: Logs the user out of the system.
- register.php: Registration for new users.
- register_attendee.php: Allows users to register as attendees for events.
- update_event.php: Used to update event details.

## Future Improvements
- Email Notifications: Send confirmation emails for event registrations.
- User Roles: Implement additional user roles like "Organizer" with more permissions.
- Event Search: Add search functionality to find events easily.
- Event Calendar: Implement a calendar view for events.
- **REST API: Expose a REST API for integration with other applications.

License
This project is licensed under the MIT License - see the LICENSE file for details.

Contact
If you have any questions or suggestions, feel free to open an issue or contact me directly.

GitHub: https://github.com/yaminlam/event-management





