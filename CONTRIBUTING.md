# Contributing to eVote Simple

Thank you for your interest in contributing to eVote Simple! This project aims to provide a secure, transparent, and modular voting system for democratic processes.

## 🎯 Project Goals

Before contributing, please understand our core principles:
- **Security First**: All changes must maintain or enhance security
- **Transparency**: Code should be clear, documented, and auditable
- **Modularity**: Maintain separation between the four core modules
- **Accessibility**: Ensure usability for all types of users
- **Open Source**: Support the democratic ideals of transparency

## 🚀 Getting Started

### Development Setup

1. **Fork and Clone**
   ```bash
   git clone https://github.com/bogi99/eVote1-simple.git
   cd eVote1-simple
   ```

2. **Start Development Environment**
   ```bash
   ./vendor/bin/sail up -d
   ./vendor/bin/sail composer install
   ```

3. **Set Up Database**
   ```bash
   ./vendor/bin/sail mysql evote_simple < database/schema.sql
   ```

4. **Test Your Setup**
   ```bash
   ./scripts/test-php-extensions.sh
   curl http://localhost/setup-test.php
   ```

## 📋 Types of Contributions

### 🐛 Bug Reports
- Use the GitHub issue tracker
- Include steps to reproduce
- Provide system information
- Test with the latest version

### ✨ Feature Requests
- Check existing issues first
- Explain the use case clearly
- Consider security implications
- Discuss with maintainers

### 🔒 Security Issues
- **DO NOT** open public issues for security vulnerabilities
- Email security concerns privately
- Include detailed reproduction steps
- Allow time for investigation

### 📝 Documentation
- Keep documentation up-to-date
- Include code examples
- Focus on clarity and completeness
- Update relevant sections

### 🧪 Testing
- Add tests for new features
- Ensure existing tests pass
- Test security implications
- Include edge cases

## 🏗️ Code Guidelines

### PHP Standards
- Follow PSR-12 coding standards
- Use meaningful variable names
- Add PHPDoc comments
- Validate all inputs

### Security Requirements
- Use prepared SQL statements
- Sanitize all user inputs
- Implement proper access controls
- Log security-relevant actions

### Database Changes
- Create migration scripts
- Maintain referential integrity
- Include rollback procedures
- Test with sample data

### Frontend Guidelines
- Maintain responsive design
- Ensure accessibility
- Keep consistent styling
- Test across browsers

## 🔄 Development Workflow

### 1. Create Feature Branch
```bash
git checkout -b feature/your-feature-name
# or
git checkout -b bugfix/issue-description
```

### 2. Make Changes
- Follow coding standards
- Add appropriate tests
- Update documentation
- Test thoroughly

### 3. Commit Guidelines
```bash
# Good commit messages:
git commit -m "feat: add vote verification system"
git commit -m "fix: resolve SQL injection in voter lookup"
git commit -m "docs: update API documentation"
git commit -m "security: implement rate limiting for voting"

# Use prefixes:
# feat: new features
# fix: bug fixes
# docs: documentation
# security: security improvements
# test: testing changes
# refactor: code improvements
```

### 4. Submit Pull Request
- Provide clear description
- Link related issues
- Include testing steps
- Request appropriate reviewers

## 🧪 Testing Requirements

### Before Submitting
- [ ] All existing tests pass
- [ ] New tests added for new features
- [ ] Security implications considered
- [ ] Documentation updated
- [ ] Manual testing completed

### Security Testing
- [ ] Input validation tested
- [ ] SQL injection prevention verified
- [ ] Access controls working
- [ ] Audit logs functioning

### Integration Testing
- [ ] All modules work together
- [ ] Database integrity maintained
- [ ] API endpoints functional
- [ ] UI components responsive

## 📊 Module-Specific Guidelines

### Registration Module
- Validate all voter data
- Ensure unique voter IDs
- Maintain voter privacy
- Log registration actions

### Configurator Module  
- Validate election parameters
- Ensure data consistency
- Secure admin functions
- Audit configuration changes

### Voting Booth Module
- Maintain vote secrecy
- Prevent double voting
- Secure vote storage
- Audit voting actions

### Tabulator Module
- Ensure result accuracy
- Maintain vote privacy
- Secure API endpoints
- Cache results appropriately

## 🔍 Code Review Process

### What Reviewers Look For
- **Security**: No vulnerabilities introduced
- **Functionality**: Code works as intended
- **Standards**: Follows project guidelines
- **Testing**: Adequate test coverage
- **Documentation**: Clear and complete

### Review Timeline
- **Bug fixes**: 1-2 days
- **Features**: 3-5 days
- **Security**: Priority review
- **Documentation**: 1-2 days

## 🏷️ Release Process

### Version Numbering
- **Major** (1.0.0): Breaking changes
- **Minor** (1.1.0): New features
- **Patch** (1.1.1): Bug fixes

### Release Criteria
- All tests passing
- Security review completed
- Documentation updated
- Migration scripts tested

## 🤝 Community Guidelines

### Code of Conduct
- Be respectful and inclusive
- Focus on constructive feedback
- Help newcomers learn
- Maintain professional discourse

### Communication
- Use clear, technical language
- Provide context for decisions
- Ask questions when unclear
- Share knowledge openly

## 📞 Getting Help

### Resources
- 📖 **Documentation**: Check README and docs first
- 🐛 **Issues**: Search existing issues
- 💬 **Discussions**: Use GitHub Discussions for questions
- 📧 **Direct Contact**: For sensitive security matters

### Mentorship
New to open source? We're here to help!
- Ask questions in discussions
- Start with "good first issue" labels
- Request code review feedback
- Join community conversations

## 🎉 Recognition

### Contributors
All contributors are recognized in:
- GitHub contributors page
- Release notes
- Special recognition for security findings
- Community acknowledgments

### Types of Contributions
We value all contributions:
- Code improvements
- Documentation updates
- Bug reports
- Security research
- Testing assistance
- Community support

---

**Thank you for helping make democratic processes more transparent and secure!**

*"Democracy thrives when the tools that support it are open, transparent, and continuously improved by the community."*