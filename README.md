# dispatchingsystem

## 🚛 Project Overview
A comprehensive web-based truck dispatching management system built with PHP, MySQL, and modern web technologies. This system helps logistics companies efficiently manage their fleet operations, dispatches, drivers, clients, and billing processes.

## ✨ Key Features

### 🔐 Authentication & Security
- Secure login system with session management
- Role-based access control
- Password-protected dashboard

### 📋 Dispatch Management
- Create, edit, and track dispatches
- Real-time status updates (dispatched → at pickup → loaded → in transit → at delivery → delivered)
- Assign drivers and trucks to dispatches
- Load details management (type, weight, quantity)
- Route planning with pickup/delivery locations

### 🚚 Fleet Management
- Complete truck inventory management
- Track truck specifications (model, capacity, fuel type, year)
- Automatic status updates (available → on route → maintenance)
- Maintenance scheduling and notes

### 👨‍💼 Driver Management
- Driver profiles with contact information
- License number tracking
- Automatic availability status based on assignments
- Performance tracking

### 🏢 Client Management
- Client company profiles
- Contact person details
- Billing information and payment terms
- Tax ID management
- Dispatch history per client

### 💰 Billing & Invoicing
- Automated invoice generation
- Professional invoice templates
- Cost calculation based on load details
- Tax management
- Payment tracking

### 📧 Email Notification System
- **Safe Mode**: Logs emails without sending (for testing)
- **Live Mode**: Sends actual emails to drivers and clients
- Configurable SMTP settings
- Status update notifications
- Email activity logging
- Template-based email system

### 📊 Reporting & Analytics
- Comprehensive dispatch reports
- Revenue tracking
- Performance metrics
- Filterable by date range and status
- Printable reports
- Success rate calculations

### 🛠️ Technical Features
- Responsive design for all devices
- Mobile-friendly sidebar navigation
- Print-friendly invoice layouts
- Database-driven architecture
- Session-based user management
- Form validation and error handling

## 🗂️ File Structure
```
truck-dispatching-system/
├── 📄 index.php                 # Redirects to login
├── 🔐 login.php                 # User authentication
├── 📊 dashboard.php             # Main dashboard with stats
├── 📋 dispatches.php            # Dispatch management
├── 🚚 trucks.php                # Truck management
├── 👨‍💼 drivers.php               # Driver management
├── 🏢 clients.php               # Client management
├── 💰 generate_invoice.php      # Invoice generation
├── 📊 reports.php               # Reporting system
├── 📧 email-log.php             # Email activity log
├── ⚙️ email-settings.php        # Email configuration
├── 📧 email_functions.php       # Email functionality
├── 📧 email-notifications.php   # Email notification class
├── 🗃️ database.php              # Database setup and connection
├── 🎨 css/style.css             # Main stylesheet
├── 📧 email_log.txt             # Email activity log file
├── ⚙️ email_settings.json       # Email configuration storage
└── 📁 includes/                 # Additional includes
```

## 🛠️ Technology Stack
- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling**: Custom CSS with responsive design
- **Icons**: Font Awesome 6.4.0
- **Email**: SMTP with PHPMailer compatibility

## 📋 System Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- SMTP server (for email functionality)

## 🚀 Getting Started
1. Upload files to your web server
2. Configure database connection in `database.php`
3. Access the system through `login.php`
4. Use default credentials: admin / admin123
5. Configure email settings for notifications

## 💡 Use Cases
- Logistics companies
- Freight brokers
- Trucking companies
- Supply chain management
- Transportation services

## 🔧 Customization
The system is designed to be easily customizable:
- Modify email templates
- Add new dispatch statuses
- Customize invoice layouts
- Extend reporting functionality
- Add new user roles

This system provides a complete solution for managing truck dispatching operations with professional invoicing, real-time tracking, and automated notifications.
