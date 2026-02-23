# Student Satisfaction Survey System

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple.svg)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Build Status](https://img.shields.io/badge/Build-Passing-success.svg)](https://github.com/your-username/your-repo/actions)

> A comprehensive web-based survey system for educational institutions with AI-powered analytics and role-based access control.

## 🌟 Features

### Core Functionality
- **🎯 Role-Based Access Control** - Separate interfaces for Students, Teachers, and Administrators
- **📊 AI-Powered Analytics** - Intelligent survey analysis using Grok and DeepSeek APIs
- **📱 Responsive Design** - Modern Bootstrap 5 interface that works on all devices
- **🔒 Secure Authentication** - Robust user management with secure session handling
- **📈 Real-time Dashboard** - Interactive charts and statistics for instant insights

### Advanced Features  
- **🤖 AI Chat Interface** - Natural language querying of survey data
- **📄 Document Management** - Built-in markdown viewer for system documentation
- **🔧 Admin Tools** - Comprehensive user and survey management
- **📊 Data Visualization** - Charts, graphs, and exportable reports
- **🌐 Multi-Role Support** - Tailored experiences for different user types

## 🚀 Quick Start

### Prerequisites
- PHP 8.1 or higher
- MySQL 8.0 or higher  
- Apache/Nginx web server
- Composer (for dependency management)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/student-satisfaction-survey.git
   cd student-satisfaction-survey
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Database setup**
   ```bash
   # Import the database schema
   mysql -u your_username -p your_database < database/final_database_setup.sql
   ```

4. **Configure environment**
   ```bash
   cp core/includes/config.example.php core/includes/config.php
   # Edit config.php with your database credentials
   ```

5. **Start development server**
   ```bash
   # For XAMPP users
   # Place project in htdocs folder and start Apache/MySQL
   
   # For standalone PHP development server
   php -S localhost:8000 -t public/
   ```

## 📁 Project Structure

```
stu/
├── 📂 app/                 # Application core
│   ├── 📂 admin/           # Admin panel components  
│   ├── 📂 api/             # REST API endpoints
│   ├── 📂 student/         # Student portal
│   └── 📂 teacher/         # Teacher dashboard
├── 📂 core/                # System core files
│   └── 📂 includes/        # Common includes & config
├── 📂 public/              # Public web files
├── 📂 assets/              # CSS, JS, images
├── 📂 ai/                  # AI integration modules
├── 📂 database/            # Database schemas & migrations
├── 📂 tests/               # Test suites
└── 📂 tools/               # Development utilities
```

## 🔧 Configuration

### Database Configuration
Edit `core/includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'student_survey_db');
```

### AI API Configuration
Edit `core/includes/secure_config.php`:
```php
define('GROK_API_KEY', 'your_grok_api_key');
define('DEEPSEEK_API_KEY', 'your_deepseek_api_key');
```

## 🧪 Testing

```bash
# Run PHP unit tests
./vendor/bin/phpunit tests/

# Run JavaScript tests
npm test

# Run integration tests
php tests/integration/run_tests.php
```

## 📚 Documentation

- [📖 User Guide](docs/user-guide.md) - Complete user manual
- [🔧 API Documentation](docs/api.md) - REST API reference
- [🏗️ Architecture](docs/architecture.md) - System architecture overview
- [🚀 Deployment Guide](docs/deployment.md) - Production deployment

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Bootstrap team for the UI framework
- Chart.js for data visualization
- PHP community for excellent documentation
- AI providers (Grok, DeepSeek) for analytics capabilities

## 📞 Support

If you encounter any issues or have questions:

- 📧 Email: support@yourdomain.com
- 🐛 Issues: [GitHub Issues](https://github.com/your-username/your-repo/issues)
- 📖 Docs: [Documentation Portal](docs/)

---

<div align="center">
  <strong>Built with ❤️ for educational institutions</strong>
</div>