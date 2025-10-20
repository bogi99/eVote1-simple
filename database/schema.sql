-- eVote Simple - Database Schema
-- Open Source Voting System with 4 Modules
-- Created: 2025-10-20

-- ============================================================================
-- CORE TABLES
-- ============================================================================

-- Elections table (managed by Configurator module)
CREATE TABLE elections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    election_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('draft', 'active', 'closed', 'finalized') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Precincts table (managed by Configurator module)
CREATE TABLE precincts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    capacity INT DEFAULT 1000,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- REGISTRATION MODULE TABLES
-- ============================================================================

-- Voters table (managed by Registration module)
CREATE TABLE voters (
    id INT PRIMARY KEY AUTO_INCREMENT,
    voter_id VARCHAR(50) UNIQUE NOT NULL, -- Generated unique ID for voting
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    precinct_id INT,
    date_of_birth DATE,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    has_voted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (precinct_id) REFERENCES precincts(id)
);

-- ============================================================================
-- CONFIGURATOR MODULE TABLES
-- ============================================================================

-- Candidates table (managed by Configurator module)
CREATE TABLE candidates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    election_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    party VARCHAR(255),
    position VARCHAR(255) NOT NULL, -- President, Mayor, Council, etc.
    bio TEXT,
    photo_url VARCHAR(500),
    ballot_order INT DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE
);

-- Ballot configuration (managed by Configurator module)
CREATE TABLE ballot_questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    election_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('single_choice', 'multiple_choice', 'yes_no') DEFAULT 'single_choice',
    max_selections INT DEFAULT 1,
    ballot_order INT DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE
);

-- ============================================================================
-- VOTING BOOTH MODULE TABLES
-- ============================================================================

-- Votes table (managed by Voting Booth module)
CREATE TABLE votes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    election_id INT NOT NULL,
    voter_id_hash VARCHAR(255) NOT NULL, -- Hashed voter ID for privacy
    candidate_id INT,
    ballot_question_id INT,
    vote_value VARCHAR(255), -- For yes/no or custom responses
    precinct_id INT NOT NULL,
    booth_id VARCHAR(50), -- Identifier for voting booth/terminal
    cast_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    vote_hash VARCHAR(255) NOT NULL, -- Hash for vote verification
    INDEX idx_election_precinct (election_id, precinct_id),
    INDEX idx_cast_at (cast_at),
    FOREIGN KEY (election_id) REFERENCES elections(id),
    FOREIGN KEY (candidate_id) REFERENCES candidates(id),
    FOREIGN KEY (ballot_question_id) REFERENCES ballot_questions(id),
    FOREIGN KEY (precinct_id) REFERENCES precincts(id)
);

-- Vote audit trail (security feature)
CREATE TABLE vote_audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vote_id INT,
    action ENUM('cast', 'verified', 'flagged', 'deleted') NOT NULL,
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vote_id) REFERENCES votes(id)
);

-- ============================================================================
-- TABULATOR MODULE TABLES
-- ============================================================================

-- Results summary (managed by Tabulator module)
CREATE TABLE results_summary (
    id INT PRIMARY KEY AUTO_INCREMENT,
    election_id INT NOT NULL,
    candidate_id INT,
    ballot_question_id INT,
    precinct_id INT,
    vote_count INT DEFAULT 0,
    percentage DECIMAL(5,2) DEFAULT 0.00,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_election_candidate (election_id, candidate_id),
    INDEX idx_election_precinct (election_id, precinct_id),
    FOREIGN KEY (election_id) REFERENCES elections(id),
    FOREIGN KEY (candidate_id) REFERENCES candidates(id),
    FOREIGN KEY (ballot_question_id) REFERENCES ballot_questions(id),
    FOREIGN KEY (precinct_id) REFERENCES precincts(id)
);

-- Real-time results cache (for API performance)
CREATE TABLE results_cache (
    id INT PRIMARY KEY AUTO_INCREMENT,
    election_id INT NOT NULL,
    cache_key VARCHAR(255) NOT NULL,
    cache_data JSON NOT NULL,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cache (election_id, cache_key),
    FOREIGN KEY (election_id) REFERENCES elections(id)
);

-- ============================================================================
-- SECURITY & AUDIT TABLES
-- ============================================================================

-- System audit log
CREATE TABLE system_audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    module ENUM('registration', 'configurator', 'voting_booth', 'tabulator') NOT NULL,
    action VARCHAR(255) NOT NULL,
    user_id VARCHAR(255),
    details JSON,
    ip_address VARCHAR(45),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_module_action (module, action),
    INDEX idx_timestamp (timestamp)
);

-- Session management for voting booths
CREATE TABLE voting_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    voter_id_hash VARCHAR(255),
    election_id INT NOT NULL,
    booth_id VARCHAR(50),
    status ENUM('active', 'completed', 'expired', 'terminated') DEFAULT 'active',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (election_id) REFERENCES elections(id)
);

-- ============================================================================
-- INDEXES FOR PERFORMANCE
-- ============================================================================

-- Additional performance indexes
CREATE INDEX idx_voters_voter_id ON voters(voter_id);
CREATE INDEX idx_voters_precinct ON voters(precinct_id);
CREATE INDEX idx_votes_election_date ON votes(election_id, cast_at);
CREATE INDEX idx_candidates_election_position ON candidates(election_id, position);
CREATE INDEX idx_results_election_updated ON results_summary(election_id, last_updated);

-- ============================================================================
-- INITIAL DATA
-- ============================================================================

-- Default precinct
INSERT INTO precincts (name, address, capacity) VALUES 
('Main Precinct', '123 Democracy Street, Voterville', 5000);

-- Sample election (draft status)
INSERT INTO elections (name, description, election_date, start_time, end_time) VALUES 
('2025 General Election', 'Annual municipal elections', '2025-11-05', '08:00:00', '20:00:00');