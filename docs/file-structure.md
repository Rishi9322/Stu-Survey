# Project File Structure

This document outlines the organized file structure of the Student Satisfaction Survey System.

## Main Directory Structure

```
stu/
├── admin/                       # Admin panel files
│   ├── dashboard.php
│   ├── user_management.php
│   ├── survey_management.php
│   ├── ai_insights.php
│   └── AdvancedAIProvider.php
├── assets/                      # Static assets
│   ├── css/
│   │   ├── style.css
│   │   └── animations.css
│   ├── js/
│   │   └── advanced-animations.js
│   └── images/
├── includes/                    # Shared includes
│   ├── config.php
│   ├── header.php
│   ├── footer.php
│   ├── secure_config.php
│   └── logout.php
├── student/                     # Student portal
│   ├── dashboard.php
│   ├── profile.php
│   ├── survey.php
│   └── analytics.php
├── teacher/                     # Teacher portal
│   ├── dashboard.php
│   ├── profile.php
│   ├── survey.php
│   └── analytics.php
├── temp/                        # Temporary development files
│   ├── README.md
│   ├── dashboard-visualizer.php
│   ├── ai-testing/
│   ├── components/
│   └── assets/
├── docs/                        # Documentation
│   ├── file-structure.md
│   ├── api-documentation.md
│   └── deployment-guide.md
├── index.php                    # Main landing page
├── login.php                    # Login page
├── register.php                 # Registration page
├── .gitignore                   # Git ignore rules
└── README.md                    # Project README
```

## Folder Descriptions

### `/admin`
Contains all administrative functionality including user management, survey creation, and AI-powered analytics.

### `/assets`
Static assets including stylesheets, JavaScript files, and images used across the application.

### `/includes`
Shared PHP files including configuration, headers, footers, and common utilities.

### `/student` & `/teacher`
Role-specific portals with dashboards, profiles, and feature access.

### `/temp`
Temporary development files, prototypes, and testing components. Not included in production deployments.

### `/docs`
Project documentation including API docs, deployment guides, and architectural decisions.

## File Naming Conventions

- **PHP Files**: Use lowercase with underscores (e.g., `user_management.php`)
- **CSS Files**: Use lowercase with hyphens (e.g., `advanced-animations.css`)
- **JavaScript Files**: Use lowercase with hyphens (e.g., `chart-utilities.js`)
- **Documentation**: Use lowercase with hyphens (e.g., `api-documentation.md`)

## Clean Up Guidelines

1. **Regular Reviews**: Weekly cleanup of temp files
2. **Documentation**: Always document purpose of new files
3. **Dependencies**: Keep external dependencies organized
4. **Version Control**: Use .gitignore to exclude unnecessary files
5. **Production Ready**: Separate development and production code

## Migration Notes

- Old test files moved to `/temp` folder
- Legacy components archived in `/temp/components`
- Development assets isolated in `/temp/assets`
- Clear separation between development and production code
