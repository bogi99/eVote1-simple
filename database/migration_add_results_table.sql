-- Migration: Add election_results table for caching tabulated results
-- This table stores calculated results for finalized elections

CREATE TABLE election_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    election_id INT NOT NULL,
    results_data JSON NOT NULL,
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_election_results (election_id),
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    INDEX idx_election_results_calculated (calculated_at)
);