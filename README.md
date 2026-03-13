# A to Z Global Link - Professional Visa & Immigration Services Platform

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?logo=mysql)](https://www.mysql.com/)
[![Status](https://img.shields.io/badge/status-active-success.svg)](#)

## 🌍 Overview

A to Z Global Link is a comprehensive web-based platform designed to streamline visa and immigration services for Rwandan citizens. The platform connects applicants with professional immigration consultants, enabling seamless application processing, document management, and payment tracking across multiple destination countries.

## ✨ Key Features

### For Applicants
- **Easy Application Process**: Intuitive 3-step visa application workflow
- **Multi-Service Support**: Support for various visa types (tourist, business, student, work, etc.)
- **Document Management**: Secure upload and tracking of required documents
- **Payment Processing**: Flexible payment options with proof tracking
- **Application Tracking**: Real-time status updates on visa applications
- **Refund Management**: Transparent refund request and processing system
- **User Dashboard**: Personalized profile and application history
- **Live Chat Support**: Direct communication with support team

### For Administrators
- **Comprehensive Dashboard**: Real-time analytics and application metrics
- **Application Management**: Review, approve, and manage visa applications
- **User Management**: Complete user administration with role-based access
- **Payment Tracking**: Monitor and verify payment transactions
- **Document Verification**: Secure document upload and verification system
- **Service Configuration**: Manage visa services, categories, and pricing
- **Country Management**: Configure destination countries and requirements
- **Reporting & Export**: Generate detailed reports and export data
- **Activity Logging**: Complete audit trail of all system activities
- **Role-Based Access Control (RBAC)**: Granular permission management

## 🏗️ Architecture

### Technology Stack
- **Backend**: PHP 7.4+
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript
- **Server**: Apache with .htaccess support
- **Security**: PDO prepared statements, password hashing, session management

### Project Structure
```
atoz/
├── admin/                    # Admin panel
│   ├── ajax/                # AJAX endpoints for dynamic operations
│   ├── includes/            # Admin-specific includes
│   ├── gd/                  # Graph/chart generation
│   └── *.php                # Admin pages
├── config/                  # Configuration files
│   └── database.php         # Database connection
├── database/                # Database schema
│   └── atozglob_al_link.sql # SQL dump
├── includes/                # Shared includes
│   ├── header.php
│   ├── footer.php
│   └── payment-notifications.php
├── uploads/                 # User uploads
│   ├── documents/           # Application documents
│   ├── payments/            # Payment proofs
│   ├── profiles/            # User profile pictures
│   └── chat/                # Chat attachments
├── logo/                    # Brand assets
├── images/                  # Static images
└── *.php                    # Public pages
```

## 🚀 Getting Started

### Prerequisites
- PHP 7.4 or higher
- MySQL 8.0 or higher
- Apache web server with mod_rewrite enabled
- Composer (optional, for dependency management)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/atoz-global-link.git
   cd atoz-global-link
   ```

2. **Configure database connection**
   ```bash
   # Edit config/database.php with your database credentials
   nano config/database.php
   ```

3. **Import database schema**
   ```bash
   mysql -u your_user -p your_database < database/atozglob_al_link.sql
   ```

4. **Set up file permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/documents/
   chmod 755 uploads/payments/
   chmod 755 uploads/profiles/
   chmod 755 uploads/chat/
   ```

5. **Configure web server**
   - Point your domain to the project root
   - Ensure `.htaccess` files are processed (AllowOverride All)

6. **Access the application**
   - User portal: `http://yourdomain.com`
   - Admin panel: `http://yourdomain.com/admin`

## 📋 Core Modules

### User Management
- User registration and authentication
- Profile management with photo upload
- Application history tracking
- Payment history and refund requests

### Application Management
- Multi-step application wizard
- Service selection with pricing
- Document upload and verification
- Application status tracking
- Refund processing

### Payment System
- Multiple payment method support
- Payment proof upload and verification
- Payment status tracking
- Refund management and processing
- Payment notifications

### Document Management
- Secure document upload
- File type validation
- Document verification workflow
- Secure document retrieval

### Communication
- Admin-to-user messaging system
- Chat notifications
- Message history tracking
- Support ticket system

### Reporting
- User analytics and statistics
- Application statistics
- Payment reports
- Export functionality (PDF, CSV)

## 🔐 Security Features

- **Password Security**: Bcrypt password hashing
- **SQL Injection Prevention**: PDO prepared statements
- **Session Management**: Secure session handling
- **File Upload Validation**: Type and size validation
- **CSRF Protection**: Token-based protection
- **Activity Logging**: Complete audit trail
- **Role-Based Access Control**: Granular permissions

## 📊 Database Schema

Key tables:
- `users` - User accounts and profiles
- `admins` - Administrator accounts
- `applications` - Visa applications
- `services` - Available visa services
- `service_categories` - Service categorization
- `destination_countries` - Destination country information
- `payments` - Payment transactions
- `documents` - Application documents
- `messages` - User-admin communication
- `activity_logs` - System activity tracking

## 🔧 Configuration

### Environment Variables
Create a `.env` file in the project root:
```
DB_HOST=localhost
DB_USER=your_db_user
DB_PASS=your_db_password
DB_NAME=atozglob_al_link
```

### Admin Setup
1. Access `/admin/rbac-setup.php` to initialize RBAC system
2. Create initial admin user through setup wizard
3. Configure admin roles and permissions

## 📝 API Endpoints

### AJAX Endpoints (Admin)
- `admin/ajax/get_users.php` - Fetch user list
- `admin/ajax/get_applications.php` - Fetch applications
- `admin/ajax/update_application_status.php` - Update application status
- `admin/ajax/process_payment.php` - Process payments
- `admin/ajax/send_message.php` - Send messages
- And many more...

## 🎨 Frontend Features

- **Responsive Design**: Mobile-first approach
- **Modern UI**: Gradient backgrounds, smooth animations
- **Interactive Components**: Dynamic forms and modals
- **WhatsApp Integration**: Direct WhatsApp contact button
- **Accessibility**: WCAG compliant markup

## 📱 User Workflows

### Application Workflow
1. User registers and creates profile
2. Selects visa service and destination
3. Fills application form
4. Uploads required documents
5. Selects payment plan
6. Completes payment
7. Admin reviews and processes
8. User receives visa documents

### Admin Workflow
1. Login to admin panel
2. Review pending applications
3. Verify uploaded documents
4. Approve/reject applications
5. Process payments
6. Communicate with users
7. Generate reports

## 🐛 Known Issues & Fixes

See documentation files:
- `REFUND_ISSUES_FIXED.md` - Refund system fixes
- `PAYMENT_FIXES_SUMMARY.md` - Payment processing fixes
- `FOREIGN_KEY_FIX.md` - Database relationship fixes
- `RBAC_INSTALLATION.md` - RBAC setup guide

## 📚 Documentation

- `PERMISSION_IMPLEMENTATION_GUIDE.md` - RBAC implementation details
- `QUICK_PERMISSION_GUIDE.md` - Quick permission reference
- `REFUND_DEBUG_GUIDE.md` - Refund system debugging

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👥 Support

For support, email: support@atozgloballink.com
WhatsApp: +250 796 597 936

## 🌟 Acknowledgments

- Built with PHP and MySQL
- Inspired by modern SaaS platforms
- Designed for Rwandan immigration services

## 📈 Roadmap

- [ ] Mobile app (iOS/Android)
- [ ] Multi-language support
- [ ] Advanced analytics dashboard
- [ ] API for third-party integrations
- [ ] Automated email notifications
- [ ] SMS notifications
- [ ] Video call support
- [ ] Blockchain document verification

---

**A to Z Global Link** - Your Gateway to Global Opportunities 🌍
