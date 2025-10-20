# 🗳️ eVote Simple - Open Source Voting System

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://www.php.net/)
[![Docker](https://img.shields.io/badge/Docker-Supported-blue.svg)](https://www.docker.com/)
[![Security](https://img.shields.io/badge/Security-Hardened-green.svg)](./SECURITY.md)

A secure, transparent, and modular voting platform built with PHP, designed for democratic processes with complete audit trails and real-time results.

## 🎯 Project Vision

**eVote Simple** is an open-source voting system that prioritizes:
- **🔐 Security**: Multi-layered security with vote encryption and audit trails
- **🔍 Transparency**: Open-source code for public verification
- **🧩 Modularity**: Independent modules that work together seamlessly
- **📊 Verifiability**: Complete audit capabilities and result verification
- **🌐 Accessibility**: User-friendly interfaces for all stakeholders

## ✨ Features

### 🏗️ **Modular Architecture**
Four independent modules working in harmony:

| Module | Purpose | Status |
|--------|---------|--------|
| 📝 **Registration** | Voter registration and ID management | ✅ Complete |
| 🔧 **Configurator** | Election setup, candidates, precincts | ✅ Complete |
| 🗳️ **Voting Booth** | Secure voting interface with confirmation | ✅ Complete |
| 📊 **Tabulator** | Results aggregation, API & reporting | ✅ Complete |

### 🔒 **Security Features**
- **Vote Privacy**: Voter IDs hashed for anonymity
- **Vote Integrity**: Cryptographic vote hashing
- **Audit Trails**: Complete action logging
- **Container Security**: Non-root execution, dropped privileges
- **Input Validation**: SQL injection prevention
- **Session Security**: Secure voter authentication

### 📊 **Results & Analytics**
- **Real-time Results**: Live vote counting for active elections
- **Export Capabilities**: JSON, CSV, XML export formats
- **REST API**: Programmatic access to results
- **Statistical Analysis**: Turnout, timeline, precinct breakdowns
- **Winner Determination**: Automatic result calculation

### 🎨 **User Experience**
- **Responsive Design**: Works on desktop and mobile
- **JetBrains Mono Font**: Developer-friendly typography
- **Intuitive Navigation**: Clear workflow for all user types
- **Visual Feedback**: Progress indicators and confirmations

## 🚀 Quick Start

### Prerequisites
- **Docker** and **Docker Compose**
- **Git**
- **PHP 8.2+** (for local development)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/bogi99/eVote1-simple.git
   cd eVote1-simple
   ```

2. **Set up environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

3. **Start with Docker (Recommended)**
   ```bash
   # Build and start containers
   ./vendor/bin/sail up -d
   
   # Install dependencies
   ./vendor/bin/sail composer install
   
   # Set up database
   ./vendor/bin/sail mysql evote_simple < database/schema.sql
   ```

4. **Access the application**
   - **Main Interface**: http://localhost
   - **Admin Panel**: http://localhost/admin.php
   - **Results API**: http://localhost/api/results.php
   - **Database**: localhost:3306 (user: sail, password: password)

### Quick Demo Setup

```bash
# Create a test election with candidates and sample voter
curl http://localhost/setup-test.php

# This creates:
# - Test election for today
# - 3 candidates across 2 positions  
# - 1 ballot question
# - Sample voter ID for testing
```

## 📖 Usage Guide

### 👥 **For Election Administrators**

1. **Create Election**
   - Go to **Admin Panel** → **Create New Election**
   - Set election dates, times, and description
   - Add candidates and their details
   - Configure ballot questions

2. **Manage Precincts**
   - Set up voting locations
   - Assign capacity limits
   - Configure precinct-specific settings

3. **Monitor Results**
   - **Real-time**: View live results during voting
   - **Export**: Download results in multiple formats
   - **API Access**: Integrate with external systems

### 🗳️ **For Voters**

1. **Register to Vote**
   - Provide required information
   - Receive unique voter ID
   - Verify registration status

2. **Cast Your Vote**
   - Enter voter ID at voting booth
   - Review ballot and candidates
   - Make selections and confirm
   - Receive vote confirmation receipt

### 📊 **For Observers**

1. **View Results**
   - Access public results page
   - Monitor real-time vote counts
   - Download election data
   - Verify result integrity

## 🏛️ System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    eVote Simple Architecture                 │
├─────────────────────────────────────────────────────────────┤
│  Frontend (Web UI)                                          │
│  ├── Registration Interface                                 │
│  ├── Admin Dashboard                                        │
│  ├── Voting Booth                                          │
│  └── Results Display                                        │
├─────────────────────────────────────────────────────────────┤
│  Backend (PHP 8.2)                                         │
│  ├── 📝 Registration Module                                │
│  ├── 🔧 Configurator Module                               │
│  ├── 🗳️ Voting Booth Module                              │
│  └── 📊 Tabulator Module                                  │
├─────────────────────────────────────────────────────────────┤
│  API Layer                                                  │
│  ├── REST API (JSON)                                       │
│  ├── Export API (CSV/XML)                                  │
│  └── Real-time Results                                     │
├─────────────────────────────────────────────────────────────┤
│  Database (MySQL 8.0)                                      │
│  ├── Elections & Candidates                                │
│  ├── Voters & Precincts                                    │
│  ├── Votes & Results                                       │
│  └── Audit Logs                                            │
├─────────────────────────────────────────────────────────────┤
│  Infrastructure (Docker)                                    │
│  ├── 🐳 Application Container (Ubuntu 22.04 + PHP 8.2)    │
│  ├── 🗄️ MySQL Container                                   │
│  ├── 🔄 Redis Container                                    │
│  └── 📧 Mailpit Container                                 │
└─────────────────────────────────────────────────────────────┘
```

## 📊 Database Schema

The system uses **11 interconnected tables** with proper relationships:

### Core Tables
- **`elections`** - Election metadata and scheduling
- **`candidates`** - Candidate information and ballot positions
- **`voters`** - Registered voter information
- **`precincts`** - Voting location management

### Voting Tables  
- **`votes`** - Anonymous vote records with hashing
- **`ballot_questions`** - Referendum and ballot questions
- **`election_results`** - Cached results for performance

### Security Tables
- **`system_audit_log`** - All system actions
- **`vote_audit_log`** - Voting-specific audit trail
- **`voter_check_ins`** - Vote tracking and prevention of double-voting

[View Complete Schema](database/schema.sql)

## 🔌 API Reference

### Elections API

```bash
# Get all elections
GET /api/results.php?list=elections

# Get election results  
GET /api/results.php?election_id=1

# Real-time results (active elections)
GET /api/results.php?election_id=1&type=realtime

# Final results (closed elections)
GET /api/results.php?election_id=1&type=final
```

### Export API

```bash
# Export as JSON
GET /api/results.php?election_id=1&format=json

# Export as CSV
GET /api/results.php?election_id=1&format=csv

# Export as XML
GET /api/results.php?election_id=1&format=xml
```

### Response Format

```json
{
  "success": true,
  "data": {
    "election_id": 1,
    "election_info": {
      "name": "Test Election",
      "status": "active",
      "election_date": "2025-10-20"
    },
    "candidates": {
      "Mayor": [
        {
          "name": "Alice Johnson",
          "party": "Progressive Party", 
          "vote_count": 127,
          "percentage": 45.2
        }
      ]
    },
    "statistics": {
      "total_votes_cast": 281,
      "turnout_percentage": 67.8
    }
  },
  "timestamp": "2025-10-20 16:30:00"
}
```

## 🔒 Security

**Security is paramount in a voting system.** We implement multiple layers:

### Container Security
- ✅ **Non-root execution**: App runs as user `appuser` (UID 1001)
- ✅ **Minimal privileges**: All capabilities dropped except essential
- ✅ **Security hardening**: `no-new-privileges`, read-only filesystem options
- ✅ **Updated base**: Ubuntu 22.04 LTS with latest security patches

### Application Security  
- ✅ **Input validation**: All inputs sanitized and validated
- ✅ **SQL injection prevention**: Prepared statements throughout
- ✅ **Vote anonymity**: Voter IDs hashed before storage
- ✅ **Audit trails**: Complete logging of all actions
- ✅ **Session security**: Secure voting workflow management

### Data Security
- ✅ **Vote encryption**: Cryptographic vote hashing
- ✅ **Database constraints**: Foreign keys and data integrity
- ✅ **Access controls**: Role-based permissions
- ✅ **Backup security**: Secure data export capabilities

[Read Full Security Guidelines](SECURITY.md)

## 🧪 Testing

### Manual Testing

```bash
# Test PHP extensions
./scripts/test-php-extensions.sh

# Create test election
curl http://localhost/setup-test.php

# Test voting workflow
curl http://localhost/vote.php
```

### Automated Testing

```bash
# Run PHP unit tests (when implemented)
./vendor/bin/sail test

# Run security scans
./vendor/bin/sail exec app php -l src/
```

## 📦 Development

### Local Development Setup

```bash
# Clone and setup
git clone https://github.com/bogi99/eVote1-simple.git
cd eVote1-simple

# Start development environment
./vendor/bin/sail up -d

# Watch for changes (if using file watchers)
./vendor/bin/sail exec app php -S 0.0.0.0:8000 -t public
```

### File Structure

```
eVote1-simple/
├── 📁 config/           # Configuration files
├── 📁 database/         # Schema and migrations
├── 📁 docker/           # Docker configuration
├── 📁 public/           # Web-accessible files
│   ├── 🏠 index.php     # Homepage
│   ├── 📝 register.php  # Voter registration
│   ├── 🗳️ vote.php      # Voting interface
│   ├── 📊 results.php   # Results viewer
│   └── 🔧 admin.php     # Admin panel
├── 📁 src/              # Application logic
│   ├── 📁 Core/         # Database and utilities
│   ├── 📁 Registration/ # Voter registration
│   ├── 📁 Configurator/ # Election management
│   ├── 📁 VotingBooth/  # Voting system
│   └── 📁 Tabulator/    # Results processing
├── 📁 view/             # UI templates and CSS
├── 📁 scripts/          # Utility scripts
├── 🐳 docker-compose.yml
├── 🔒 SECURITY.md
└── 📖 README.md
```

### Contributing

1. **Fork the repository**
2. **Create feature branch**: `git checkout -b feature/amazing-feature`
3. **Commit changes**: `git commit -m 'Add amazing feature'`
4. **Push to branch**: `git push origin feature/amazing-feature`
5. **Open Pull Request**

## 🗺️ Roadmap

### Phase 1: Core System ✅ **COMPLETE**
- [x] Database schema design
- [x] Registration module
- [x] Configurator module  
- [x] Voting booth module
- [x] Tabulator module
- [x] Results API
- [x] Security hardening

### Phase 2: Enhanced Security 🔄 **IN PROGRESS**
- [ ] End-to-end vote encryption
- [ ] Multi-factor authentication
- [ ] Advanced audit capabilities
- [ ] Tamper detection mechanisms
- [ ] Vote verification systems

### Phase 3: Advanced Features 📋 **PLANNED**
- [ ] Mobile-first PWA interface
- [ ] Blockchain integration for transparency
- [ ] Multi-language support
- [ ] Advanced analytics dashboard
- [ ] Real-time monitoring alerts

### Phase 4: Production Ready 🎯 **FUTURE**
- [ ] Load balancing and scaling
- [ ] Automated security scanning
- [ ] Compliance certifications
- [ ] Professional audit trail
- [ ] High-availability deployment

## 🆘 Support

### Documentation
- 📖 **README**: This comprehensive guide
- 🔒 **Security**: [SECURITY.md](SECURITY.md) 
- 📊 **API Docs**: Available at `/api/results.php` with examples
- 🏗️ **Architecture**: Database schema and system design

### Community
- 🐛 **Issues**: [GitHub Issues](https://github.com/bogi99/eVote1-simple/issues)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/bogi99/eVote1-simple/discussions)
- 📧 **Security**: Report security issues privately to [security@yourdomain.com]

### Getting Help
- 📚 Check the documentation first
- 🔍 Search existing issues
- 🆕 Create detailed issue reports
- 🤝 Join community discussions

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

### Why Open Source?
Democratic processes should be transparent and verifiable. By open-sourcing this voting system:
- 🔍 **Transparency**: Anyone can audit the code
- 🔒 **Security**: Community-driven security improvements
- 🌍 **Accessibility**: Free for democratic organizations worldwide
- 🤝 **Collaboration**: Collective improvement of election technology

## 🙏 Acknowledgments

- **Security**: Thanks to the open-source security community
- **Design**: Inspired by modern democratic principles
- **Technology**: Built on robust open-source foundations
- **Community**: Grateful for all contributors and feedback

---

**Built with ❤️ for democracy and transparency**

*"The best way to ensure election integrity is to make the process completely transparent and auditable."*

---

### 🔗 Quick Links
- 🏠 [Homepage](http://localhost) 
- 🗳️ [Vote Now](http://localhost/vote.php)
- 🔧 [Admin Panel](http://localhost/admin.php)
- 📊 [View Results](http://localhost/results.php)
- 🔌 [API Documentation](http://localhost/api/results.php)
- 🧪 [Test Setup](http://localhost/setup-test.php)