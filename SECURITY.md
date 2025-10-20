# Security Guidelines for eVote Simple

## 🔒 Security Overview

This voting system implements multiple layers of security to protect election integrity and voter privacy.

## 🛡️ Current Security Measures

### Database Security
- ✅ **Vote Privacy**: Voter IDs are hashed before storage (`voter_id_hash`)
- ✅ **Vote Integrity**: Each vote has a cryptographic hash (`vote_hash`)
- ✅ **Audit Trails**: All actions logged in `system_audit_log` and `vote_audit_log`
- ✅ **Prepared Statements**: All SQL queries use prepared statements to prevent injection
- ✅ **Foreign Key Constraints**: Database enforces referential integrity

### Container Security
- ✅ **Non-root User**: Application runs as user `appuser` (UID 1001)
- ✅ **Minimal Capabilities**: Dropped all capabilities except essential ones
- ✅ **No New Privileges**: Container cannot escalate privileges
- ✅ **Security Options**: Enhanced Docker security options enabled
- ✅ **Health Checks**: Container health monitoring implemented

### Application Security
- ✅ **Input Validation**: All user inputs validated and sanitized
- ✅ **Session Management**: Secure session handling for voting workflow
- ✅ **Environment Variables**: Sensitive config in environment files (.env)
- ✅ **Error Handling**: No sensitive information exposed in error messages

## 🚨 Security Vulnerabilities Fixed

### Docker Base Image
- **Issue**: `php:8.4-cli` had high severity vulnerabilities
- **Solution**: Migrated to Ubuntu 22.04 LTS with PHP 8.2 (stable LTS)
- **Additional Hardening**: Added security updates, minimal packages, non-root user

## 🔧 Production Security Checklist

### Before Deployment:
- [ ] **SSL/TLS**: Enable HTTPS with valid certificates
- [ ] **Database Encryption**: Enable MySQL encryption at rest
- [ ] **Network Security**: Configure firewall rules
- [ ] **Access Controls**: Implement admin authentication
- [ ] **Backup Security**: Encrypt database backups
- [ ] **Log Monitoring**: Set up security log monitoring
- [ ] **Vulnerability Scanning**: Regular container and dependency scans

### Environment Security:
- [ ] **Environment Files**: Secure .env files with proper permissions (600)
- [ ] **Database Credentials**: Use strong, unique passwords
- [ ] **API Keys**: Rotate keys regularly
- [ ] **File Permissions**: Restrict file system permissions

### Voting System Specific:
- [ ] **Vote Encryption**: Implement end-to-end vote encryption
- [ ] **Ballot Secrecy**: Ensure vote anonymity cannot be compromised
- [ ] **Tamper Detection**: Implement vote integrity verification
- [ ] **Audit Capabilities**: Enable comprehensive election auditing
- [ ] **Result Verification**: Implement result verification mechanisms

## 🔍 Security Monitoring

### Log Files to Monitor:
- `system_audit_log` - All system actions
- `vote_audit_log` - All voting actions
- Container logs - Application errors and access
- Database logs - Query patterns and access

### Key Metrics:
- Failed login attempts
- Unusual voting patterns
- Database access anomalies
- Container security events

## 📞 Security Reporting

If you discover a security vulnerability, please report it responsibly:

1. **DO NOT** open a public GitHub issue
2. Email security concerns to: [your-security-email]
3. Include detailed reproduction steps
4. Allow time for investigation and patching

## 🔄 Security Updates

- Regularly update base Docker images
- Monitor security advisories for PHP and dependencies
- Update dependencies using `composer update`
- Review and update security configurations

## 📚 Additional Resources

- [OWASP Web Application Security](https://owasp.org/www-project-top-ten/)
- [Docker Security Best Practices](https://docs.docker.com/engine/security/)
- [PHP Security Guidelines](https://www.php.net/manual/en/security.php)
- [Election Security Guidelines](https://www.cisa.gov/election-security)

---

**Remember**: Security is an ongoing process, not a one-time setup. Regular reviews and updates are essential for maintaining election security.