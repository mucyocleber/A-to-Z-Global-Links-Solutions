# Contributing to A to Z Global Link

Thank you for your interest in contributing to A to Z Global Link! This document provides guidelines and instructions for contributing to the project.

## Code of Conduct

- Be respectful and inclusive
- Provide constructive feedback
- Focus on the code, not the person
- Help others learn and grow

## Getting Started

### Prerequisites
- PHP 7.4+
- MySQL 8.0+
- Git
- Basic understanding of PHP and MySQL

### Development Setup

1. **Fork and clone the repository**
   ```bash
   git clone https://github.com/yourusername/atoz-global-link.git
   cd atoz-global-link
   ```

2. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Set up local development environment**
   - Configure `config/database.php` with local database
   - Import database schema
   - Set up file permissions

## Development Guidelines

### Code Style

- **PHP**: Follow PSR-12 coding standards
- **Naming**: Use camelCase for variables/functions, PascalCase for classes
- **Comments**: Add meaningful comments for complex logic
- **Indentation**: Use 4 spaces (not tabs)

### File Organization

```
Feature/Module/
├── ajax/
│   └── feature_action.php
├── includes/
│   └── feature_functions.php
├── feature_page.php
└── feature_edit.php
```

### Database Changes

- Create migration files for schema changes
- Document all new tables and columns
- Update `database/atozglob_al_link.sql`
- Test migrations thoroughly

### Security Best Practices

- Always use prepared statements (PDO)
- Validate and sanitize all user inputs
- Use password_hash() for passwords
- Implement proper access controls
- Log sensitive operations

## Commit Guidelines

### Commit Message Format

```
[TYPE] Brief description (50 chars max)

Detailed explanation if needed (wrap at 72 chars)

Fixes #123
```

### Types
- `[FEATURE]` - New feature
- `[FIX]` - Bug fix
- `[DOCS]` - Documentation
- `[STYLE]` - Code style changes
- `[REFACTOR]` - Code refactoring
- `[TEST]` - Test additions
- `[PERF]` - Performance improvements

### Examples
```
[FEATURE] Add multi-language support for visa services

[FIX] Resolve payment status update issue in admin panel

[DOCS] Update installation instructions

[REFACTOR] Simplify user authentication logic
```

## Pull Request Process

1. **Before submitting**
   - Test your changes thoroughly
   - Update documentation
   - Add/update tests if applicable
   - Ensure no conflicts with main branch

2. **Create pull request**
   - Use descriptive title
   - Reference related issues (#123)
   - Provide clear description of changes
   - Include screenshots for UI changes

3. **PR Template**
   ```markdown
   ## Description
   Brief description of changes

   ## Type of Change
   - [ ] Bug fix
   - [ ] New feature
   - [ ] Breaking change
   - [ ] Documentation update

   ## Testing
   Describe testing performed

   ## Screenshots (if applicable)
   Add screenshots here

   ## Checklist
   - [ ] Code follows style guidelines
   - [ ] Self-review completed
   - [ ] Comments added for complex logic
   - [ ] Documentation updated
   - [ ] No new warnings generated
   - [ ] Tests added/updated
   ```

## Testing

### Manual Testing Checklist

- [ ] Feature works as intended
- [ ] No console errors
- [ ] Responsive on mobile/tablet
- [ ] Database operations work correctly
- [ ] File uploads function properly
- [ ] Payment processing works
- [ ] Admin functions work correctly

### Test Coverage Areas

- User registration and login
- Application submission
- Document upload
- Payment processing
- Admin operations
- Permission checks
- Error handling

## Documentation

### Update These Files When Needed

- `README.md` - Project overview
- `CONTRIBUTING.md` - Contribution guidelines
- Code comments - Inline documentation
- Feature documentation - New features

### Documentation Standards

- Use clear, concise language
- Include code examples
- Add screenshots for UI changes
- Update table of contents
- Link to related documentation

## Reporting Issues

### Bug Report Template

```markdown
## Description
Clear description of the bug

## Steps to Reproduce
1. Step 1
2. Step 2
3. Step 3

## Expected Behavior
What should happen

## Actual Behavior
What actually happens

## Environment
- PHP Version: 
- MySQL Version:
- Browser:
- OS:

## Screenshots
Add screenshots if applicable

## Additional Context
Any other relevant information
```

### Feature Request Template

```markdown
## Description
Clear description of the feature

## Use Case
Why this feature is needed

## Proposed Solution
How it should work

## Alternatives Considered
Other possible approaches

## Additional Context
Any other relevant information
```

## Code Review Process

### What Reviewers Look For

- Code quality and style
- Security vulnerabilities
- Performance implications
- Test coverage
- Documentation completeness
- Backward compatibility

### Review Feedback

- Be constructive and helpful
- Suggest improvements, don't demand
- Explain the reasoning
- Provide resources/links when helpful

## Performance Considerations

- Optimize database queries
- Minimize file operations
- Cache when appropriate
- Lazy load resources
- Monitor memory usage

## Security Checklist

- [ ] Input validation implemented
- [ ] SQL injection prevention (prepared statements)
- [ ] XSS prevention (output escaping)
- [ ] CSRF protection
- [ ] Authentication checks
- [ ] Authorization checks
- [ ] Sensitive data not logged
- [ ] File upload validation

## Release Process

1. Update version number
2. Update CHANGELOG
3. Create release notes
4. Tag release in Git
5. Deploy to production

## Questions or Need Help?

- Check existing documentation
- Search closed issues
- Ask in discussions
- Contact maintainers

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to A to Z Global Link! 🙏
