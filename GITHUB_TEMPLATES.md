# GitHub Templates

## Issue Templates

### Bug Report Template
```markdown
---
name: Bug Report
about: Report a bug to help us improve
title: "[BUG] "
labels: bug
assignees: ''

---

## Description
A clear and concise description of what the bug is.

## Steps to Reproduce
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

## Expected Behavior
A clear and concise description of what you expected to happen.

## Actual Behavior
What actually happens instead.

## Screenshots
If applicable, add screenshots to help explain your problem.

## Environment
- PHP Version: [e.g. 7.4, 8.0]
- MySQL Version: [e.g. 8.0]
- Browser: [e.g. Chrome, Firefox]
- OS: [e.g. Windows, Linux, macOS]

## Additional Context
Add any other context about the problem here.
```

### Feature Request Template
```markdown
---
name: Feature Request
about: Suggest an idea for this project
title: "[FEATURE] "
labels: enhancement
assignees: ''

---

## Description
A clear and concise description of what you want to happen.

## Use Case
Describe the use case or problem this feature would solve.

## Proposed Solution
Describe how you think this feature should work.

## Alternatives Considered
A clear and concise description of any alternative solutions or features you've considered.

## Additional Context
Add any other context or screenshots about the feature request here.
```

### Documentation Issue Template
```markdown
---
name: Documentation Issue
about: Report missing or unclear documentation
title: "[DOCS] "
labels: documentation
assignees: ''

---

## Description
What documentation is missing or unclear?

## Location
Where in the documentation is this issue? (file path or URL)

## Suggested Improvement
How should this be documented?

## Additional Context
Any additional information that would help.
```

### Security Issue Template
```markdown
---
name: Security Issue
about: Report a security vulnerability
title: "[SECURITY] "
labels: security
assignees: ''

---

## ⚠️ IMPORTANT
Do NOT open a public issue for security vulnerabilities!
Please email security@atozgloballink.com instead.

This template is for non-sensitive security discussions only.
```

---

## Pull Request Template

```markdown
---
name: Pull Request
about: Submit code changes
title: "[TYPE] Brief description"
labels: ''
assignees: ''

---

## Description
Please include a summary of the changes and related context.

## Type of Change
- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to change)
- [ ] Documentation update

## Related Issue
Fixes #(issue number)

## Testing
Describe the tests you ran and how to reproduce them:
- [ ] Test A
- [ ] Test B

## Screenshots (if applicable)
Add screenshots for UI changes.

## Checklist
- [ ] My code follows the style guidelines of this project
- [ ] I have performed a self-review of my own code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] I have made corresponding changes to the documentation
- [ ] My changes generate no new warnings
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests passed locally with my changes
- [ ] Any dependent changes have been merged and published

## Breaking Changes
Describe any breaking changes here.

## Additional Context
Add any other context about the PR here.
```

---

## Discussion Templates

### General Discussion
```markdown
# [DISCUSSION] Topic Title

## Context
Provide context for the discussion.

## Question/Topic
What would you like to discuss?

## Relevant Information
- Point 1
- Point 2
- Point 3

## Looking Forward
What are you hoping to achieve with this discussion?
```

### Ideas & Suggestions
```markdown
# [IDEA] Suggestion Title

## Overview
Brief overview of the idea.

## Benefits
- Benefit 1
- Benefit 2
- Benefit 3

## Implementation
How could this be implemented?

## Feedback
What feedback or thoughts do you have?
```

---

## GitHub Actions Workflow Template

```yaml
name: CI/CD Pipeline

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: test_db
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
        ports:
          - 3306:3306

    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '7.4'
        extensions: mysql, pdo_mysql
    
    - name: Validate composer.json
      run: composer validate
    
    - name: Install dependencies
      run: composer install --prefer-dist --no-progress
    
    - name: Run tests
      run: composer test
    
    - name: Run PHP CodeSniffer
      run: composer phpcs
    
    - name: Run PHPStan
      run: composer phpstan
```

---

## GitHub Pages Configuration

### _config.yml
```yaml
title: A to Z Global Link
description: Professional Visa & Immigration Services Platform
theme: jekyll-theme-minimal
logo: logo/logo.jpg
show_downloads: true
google_analytics: UA-XXXXXXXX-X

navigation:
  - title: Home
    url: /
  - title: Documentation
    url: /docs
  - title: Contributing
    url: /CONTRIBUTING.md
  - title: GitHub
    url: https://github.com/yourusername/atoz-global-link
```

---

## GitHub Community Files Checklist

- [x] README.md
- [x] CONTRIBUTING.md
- [x] CODE_OF_CONDUCT.md
- [x] LICENSE
- [x] SECURITY.md
- [x] .gitignore
- [x] CHANGELOG.md
- [x] Issue templates
- [x] Pull request template
- [x] GitHub Actions workflows
- [ ] GitHub Pages (optional)
- [ ] Discussions enabled
- [ ] Wiki pages (optional)

---

## Repository Settings Recommendations

### General
- ✅ Enable Issues
- ✅ Enable Discussions
- ✅ Enable Projects
- ✅ Enable Wiki (optional)
- ✅ Enable Sponsorships (optional)

### Branch Protection Rules
- Require pull request reviews before merging
- Require status checks to pass before merging
- Require branches to be up to date before merging
- Require code reviews from code owners
- Dismiss stale pull request approvals

### Secrets & Variables
- `DB_HOST` - Database host
- `DB_USER` - Database user
- `DB_PASS` - Database password
- `DB_NAME` - Database name

### Webhooks
- Configure for CI/CD pipeline
- Set up deployment notifications
- Configure security scanning

---

## GitHub Labels Recommendations

### Type
- `bug` - Something isn't working
- `enhancement` - New feature or request
- `documentation` - Improvements or additions to documentation
- `question` - Further information is requested

### Priority
- `priority: critical` - Must be fixed immediately
- `priority: high` - Should be fixed soon
- `priority: medium` - Can be fixed in next sprint
- `priority: low` - Nice to have

### Status
- `status: in-progress` - Currently being worked on
- `status: blocked` - Blocked by another issue
- `status: review` - Waiting for review
- `status: ready` - Ready to be worked on

### Area
- `area: admin` - Admin panel related
- `area: user` - User portal related
- `area: payment` - Payment system
- `area: documents` - Document management
- `area: security` - Security related

### Other
- `good first issue` - Good for newcomers
- `help wanted` - Extra attention is needed
- `security` - Security vulnerability
- `wontfix` - This will not be worked on

---

## GitHub Milestones

### Version 1.0.0
- [x] Core platform features
- [x] Admin dashboard
- [x] Payment processing
- [x] Document management

### Version 1.1.0
- [ ] Mobile app API
- [ ] Multi-language support
- [ ] Advanced analytics

### Version 2.0.0
- [ ] Third-party integrations
- [ ] AI-powered features
- [ ] Blockchain verification

---

## GitHub Releases Template

```markdown
# Version X.Y.Z - Release Date

## 🎉 What's New

### Features
- Feature 1
- Feature 2
- Feature 3

### Improvements
- Improvement 1
- Improvement 2

### Bug Fixes
- Bug fix 1
- Bug fix 2

### Security
- Security fix 1
- Security fix 2

## 📦 Installation

```bash
git clone https://github.com/yourusername/atoz-global-link.git
cd atoz-global-link
git checkout vX.Y.Z
```

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for full details.

## 🙏 Contributors

Thanks to all contributors who made this release possible!

## 📞 Support

- Report issues: [GitHub Issues](https://github.com/yourusername/atoz-global-link/issues)
- Ask questions: [GitHub Discussions](https://github.com/yourusername/atoz-global-link/discussions)
- Security: security@atozgloballink.com
```

---

## GitHub Discussions Categories

### 📢 Announcements
- Project updates
- New releases
- Important notices

### 💡 Ideas
- Feature suggestions
- Improvement ideas
- Architecture discussions

### 🤔 Q&A
- How-to questions
- Troubleshooting
- Best practices

### 🎉 Show & Tell
- Success stories
- Use cases
- Community projects

### 🐛 Troubleshooting
- Common issues
- Error solutions
- Debugging help

---

*These templates are ready to be added to your GitHub repository!*
