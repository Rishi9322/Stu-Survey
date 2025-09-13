# 🚀 Advanced AI-Powered Student Feedback System

A comprehensive educational feedback system with advanced AI capabilities, secure API integration, and intelligent data analysis.

## ✨ Features

### 🤖 Advanced AI Integration
- **Multi-Provider Support**: Grok Compound, DeepSeek R1 (via OpenRouter), Local Python AI
- **Secure API Management**: Environment-based configuration with GitHub-safe practices
- **Database-Connected AI**: Real-time analysis of actual system data
- **Model Selection**: Dynamic switching between AI providers

### 📊 Intelligent Analytics
- **Sentiment Analysis**: Multi-dimensional emotion and satisfaction scoring
- **Predictive Analytics**: Trend forecasting and pattern recognition  
- **Topic Extraction**: Automatic categorization and priority assessment
- **Performance Metrics**: Resolution rates, response times, implementation success

### 🔒 Security & Privacy
- **Environment Variables**: API keys secured in `.env` file
- **Key Masking**: Sensitive data protection in logs and displays
- **GitIgnore Configuration**: Automatic exclusion of sensitive files
- **Access Controls**: Role-based permissions and data protection

### 📚 Training Data Integration
- **CSV/Excel Import**: Automated data processing and validation
- **Google Sheets**: Direct integration with shared spreadsheets
- **Smart Parsing**: Flexible column detection and data mapping
- **Export Capabilities**: JSON export for AI model training

## 🛠️ Setup Instructions

### 1. Environment Configuration

Create a `.env` file in the root directory:

```env
# AI API Keys - DO NOT COMMIT TO GITHUB
GROK_API_KEY=your_grok_api_key_here
OPENROUTER_API_KEY=your_openrouter_api_key_here

# Site Information
SITE_URL=http://localhost/stu
SITE_NAME=Student Feedback System

# AI Configuration
AI_DEFAULT_MODEL=grok-compound
AI_FALLBACK_MODEL=local-python
AI_MAX_TOKENS=1000
AI_TEMPERATURE=0.7
```

### 2. Database Setup

Run the database setup scripts:
- `database_setup.sql` - Main database structure
- `final_database_setup.sql` - Additional tables and indexes

### 3. Python Environment

Ensure Python 3.13+ is installed and accessible via:
- `py` command (Windows)
- `python` or `python3` command (Linux/Mac)

### 4. File Permissions

Ensure the `uploads/training/` directory is writable:
```bash
chmod 755 uploads/training/
```

## 📋 API Keys Setup

### Grok API (Groq)
1. Visit [Groq Console](https://console.groq.com)
2. Create an account and generate an API key
3. Add to `.env` file as `GROK_API_KEY`

### OpenRouter (for DeepSeek)
1. Visit [OpenRouter](https://openrouter.ai)
2. Create account and get API key
3. Add to `.env` file as `OPENROUTER_API_KEY`

## 🎯 Access Points

### Admin Dashboard
- **Main AI Insights**: `/admin/ai_insights.php`
- **Complete Test Suite**: `/admin/complete_test.html`
- **Training Manager**: `/admin/training_manager.html`

### Testing & Development
- **API Test Interface**: `/admin/ai_test_interface.html`
- **Command Line Test**: `/admin/test_advanced_ai.php`
- **System Validation**: `/admin/system_validation.php`

## 🧪 Testing the System

### 1. Security Test
```bash
php admin/complete_test_endpoint.php
# POST action=test_security
```

### 2. Database Connection
```bash
php admin/test_advanced_ai.php
```

### 3. AI Models Test
Access `/admin/complete_test.html` and run all tests

### 4. Training Data Import
1. Go to `/admin/training_manager.html`
2. Upload CSV or connect Google Sheets
3. Preview and export training data

## 📁 File Structure

```
/
├── admin/
│   ├── AdvancedAIProvider.php      # Main AI provider with API integrations
│   ├── DatabaseAI.php              # Database-connected AI analysis
│   ├── TrainingDataIntegrator.php  # Training data import system
│   ├── ai_insights.php             # Enhanced dashboard with model selection
│   ├── complete_test.html          # Comprehensive test interface
│   └── training_manager.html       # Training data management
├── includes/
│   ├── secure_config.php           # Secure configuration loader
│   ├── config.php                  # Database configuration
│   └── functions.php               # Helper functions
├── uploads/training/               # Training data upload directory
├── .env                           # Environment variables (create this)
├── .gitignore                     # Git ignore rules
└── advanced_ai_engine.py          # Enhanced Python AI engine
```

## 🔧 Configuration Options

### AI Model Selection
- **grok-compound**: Advanced reasoning (30 RPM, 250 RPD, 70K TPM)
- **deepseek-r1**: Unlimited reasoning via OpenRouter
- **local-python**: Fast local processing (unlimited)

### Training Data Formats
Supported columns (flexible detection):
- `category` / `type` / `classification`
- `subject` / `title` / `topic`
- `content` / `description` / `feedback`
- `sentiment` / `mood` / `emotion`
- `priority` / `urgency` / `importance`
- `tags` / `keywords` / `labels`

### Google Sheets Integration
1. Make sheet public: Share → Anyone with link can view
2. Expected URL format: `https://docs.google.com/spreadsheets/d/{SHEET_ID}/edit`
3. System automatically converts to CSV export URL

## 🚨 Troubleshooting

### API Key Issues
- Check `.env` file exists and contains valid keys
- Verify keys are not exposed in public repositories
- Test individual APIs using the test interface

### Database Connection
- Verify MySQL/MariaDB is running
- Check database credentials in `includes/config.php`
- Ensure all required tables exist

### Python Integration
- Confirm Python 3.13+ is installed
- Test Python accessibility: `py --version`
- Check file permissions on Python scripts

### File Upload Issues
- Verify `uploads/training/` directory exists and is writable
- Check PHP `upload_max_filesize` and `post_max_size`
- Ensure file formats are supported (CSV, XLSX, XLS)

## 📊 Performance Optimization

### Caching
- API responses cached for similar queries
- Database query optimization with proper indexing
- Local Python processing for fast responses

### Rate Limiting
- Grok API: 30 requests/minute, 250 requests/day
- OpenRouter: Varies by model
- Local AI: No limits

### Monitoring
- Response time tracking
- Token usage monitoring  
- Error rate analysis
- Database performance metrics

## 🔄 Maintenance

### Regular Tasks
1. Monitor API usage and costs
2. Update training data regularly
3. Review and clean old logs
4. Backup database and configurations

### Security Updates
1. Rotate API keys periodically
2. Update dependencies
3. Review access logs
4. Monitor for security vulnerabilities

## 🤝 Contributing

1. Fork the repository
2. Create feature branch
3. Make changes
4. Test thoroughly using `/admin/complete_test.html`
5. Submit pull request

## 📞 Support

For issues and questions:
1. Check the comprehensive test interface first
2. Review error logs in browser console
3. Verify API key configuration
4. Test database connectivity

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

🌟 **System Status**: Production Ready with Advanced AI Integration
🔐 **Security**: GitHub-Safe Configuration
🧪 **Testing**: Comprehensive Test Suite Available
📚 **Training**: Google Sheets & Excel Integration