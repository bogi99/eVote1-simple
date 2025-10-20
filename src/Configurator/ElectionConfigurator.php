<?php

namespace Bogi\EvoteSimple\Configurator;

use Bogi\EvoteSimple\Core\Database;
use PDO;

/**
 * Election Configuration System
 * Handles election setup, candidate management, and system configuration
 */
class ElectionConfigurator
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Create a new election
     */
    public function createElection(array $electionData): array
    {
        $required = ['name', 'description', 'election_date', 'start_time', 'end_time'];
        foreach ($required as $field) {
            if (empty($electionData[$field])) {
                throw new \InvalidArgumentException("Required field missing: {$field}");
            }
        }

        try {
            $sql = "INSERT INTO elections (name, description, election_date, start_time, end_time, status) 
                    VALUES (:name, :description, :election_date, :start_time, :end_time, 'draft')";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':name' => $electionData['name'],
                ':description' => $electionData['description'],
                ':election_date' => $electionData['election_date'],
                ':start_time' => $electionData['start_time'],
                ':end_time' => $electionData['end_time']
            ]);

            $electionId = $this->db->lastInsertId();

            $this->logAction('election_created', [
                'election_id' => $electionId,
                'name' => $electionData['name']
            ]);

            return [
                'success' => true,
                'election_id' => $electionId,
                'message' => 'Election created successfully'
            ];
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to create election: " . $e->getMessage());
        }
    }

    /**
     * Add candidate to an election
     */
    public function addCandidate(array $candidateData): array
    {
        $required = ['election_id', 'name', 'position'];
        foreach ($required as $field) {
            if (empty($candidateData[$field])) {
                throw new \InvalidArgumentException("Required field missing: {$field}");
            }
        }

        // Verify election exists
        if (!$this->electionExists($candidateData['election_id'])) {
            throw new \InvalidArgumentException("Election not found");
        }

        try {
            $sql = "INSERT INTO candidates (election_id, name, party, position, bio, ballot_order, active) 
                    VALUES (:election_id, :name, :party, :position, :bio, :ballot_order, TRUE)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':election_id' => $candidateData['election_id'],
                ':name' => $candidateData['name'],
                ':party' => $candidateData['party'] ?? null,
                ':position' => $candidateData['position'],
                ':bio' => $candidateData['bio'] ?? null,
                ':ballot_order' => $candidateData['ballot_order'] ?? 0
            ]);

            $candidateId = $this->db->lastInsertId();

            $this->logAction('candidate_added', [
                'candidate_id' => $candidateId,
                'election_id' => $candidateData['election_id'],
                'name' => $candidateData['name'],
                'position' => $candidateData['position']
            ]);

            return [
                'success' => true,
                'candidate_id' => $candidateId,
                'message' => 'Candidate added successfully'
            ];
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to add candidate: " . $e->getMessage());
        }
    }

    /**
     * Add ballot question to an election
     */
    public function addBallotQuestion(array $questionData): array
    {
        $required = ['election_id', 'question_text', 'question_type'];
        foreach ($required as $field) {
            if (empty($questionData[$field])) {
                throw new \InvalidArgumentException("Required field missing: {$field}");
            }
        }

        if (!$this->electionExists($questionData['election_id'])) {
            throw new \InvalidArgumentException("Election not found");
        }

        try {
            $sql = "INSERT INTO ballot_questions (election_id, question_text, question_type, max_selections, ballot_order, active) 
                    VALUES (:election_id, :question_text, :question_type, :max_selections, :ballot_order, TRUE)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':election_id' => $questionData['election_id'],
                ':question_text' => $questionData['question_text'],
                ':question_type' => $questionData['question_type'],
                ':max_selections' => $questionData['max_selections'] ?? 1,
                ':ballot_order' => $questionData['ballot_order'] ?? 0
            ]);

            $questionId = $this->db->lastInsertId();

            $this->logAction('ballot_question_added', [
                'question_id' => $questionId,
                'election_id' => $questionData['election_id'],
                'question_type' => $questionData['question_type']
            ]);

            return [
                'success' => true,
                'question_id' => $questionId,
                'message' => 'Ballot question added successfully'
            ];
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to add ballot question: " . $e->getMessage());
        }
    }

    /**
     * Get all elections
     */
    public function getAllElections(): array
    {
        $sql = "SELECT *, 
                (SELECT COUNT(*) FROM candidates WHERE election_id = elections.id) as candidate_count,
                (SELECT COUNT(*) FROM voters WHERE has_voted = TRUE) as total_votes
                FROM elections 
                ORDER BY election_date DESC, created_at DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get election by ID with candidates
     */
    public function getElectionWithDetails(int $electionId): ?array
    {
        $sql = "SELECT * FROM elections WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $electionId]);
        $election = $stmt->fetch();

        if (!$election) {
            return null;
        }

        // Get candidates
        $sql = "SELECT * FROM candidates WHERE election_id = :election_id AND active = TRUE ORDER BY ballot_order, name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':election_id' => $electionId]);
        $election['candidates'] = $stmt->fetchAll();

        // Get ballot questions
        $sql = "SELECT * FROM ballot_questions WHERE election_id = :election_id AND active = TRUE ORDER BY ballot_order";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':election_id' => $electionId]);
        $election['ballot_questions'] = $stmt->fetchAll();

        return $election;
    }

    /**
     * Update election status
     */
    public function updateElectionStatus(int $electionId, string $status): bool
    {
        $validStatuses = ['draft', 'active', 'closed', 'finalized'];
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }

        $sql = "UPDATE elections SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([':status' => $status, ':id' => $electionId]);

        if ($result) {
            $this->logAction('election_status_changed', [
                'election_id' => $electionId,
                'new_status' => $status
            ]);
        }

        return $result;
    }

    /**
     * Create new precinct
     */
    public function createPrecinct(array $precinctData): array
    {
        $required = ['name', 'address'];
        foreach ($required as $field) {
            if (empty($precinctData[$field])) {
                throw new \InvalidArgumentException("Required field missing: {$field}");
            }
        }

        try {
            $sql = "INSERT INTO precincts (name, address, capacity, active) 
                    VALUES (:name, :address, :capacity, TRUE)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':name' => $precinctData['name'],
                ':address' => $precinctData['address'],
                ':capacity' => $precinctData['capacity'] ?? 1000
            ]);

            $precinctId = $this->db->lastInsertId();

            $this->logAction('precinct_created', [
                'precinct_id' => $precinctId,
                'name' => $precinctData['name']
            ]);

            return [
                'success' => true,
                'precinct_id' => $precinctId,
                'message' => 'Precinct created successfully'
            ];
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to create precinct: " . $e->getMessage());
        }
    }

    /**
     * Get system statistics
     */
    public function getSystemStats(): array
    {
        $stats = [];

        // Elections count
        $stmt = $this->db->query("SELECT COUNT(*) FROM elections");
        $stats['total_elections'] = $stmt->fetchColumn();

        // Active elections count
        $stmt = $this->db->query("SELECT COUNT(*) FROM elections WHERE status = 'active'");
        $stats['active_elections'] = $stmt->fetchColumn();

        // Total registered voters
        $stmt = $this->db->query("SELECT COUNT(*) FROM voters WHERE status = 'active'");
        $stats['total_voters'] = $stmt->fetchColumn();

        // Total votes cast
        $stmt = $this->db->query("SELECT COUNT(*) FROM votes");
        $stats['total_votes'] = $stmt->fetchColumn();

        // Total candidates
        $stmt = $this->db->query("SELECT COUNT(*) FROM candidates WHERE active = TRUE");
        $stats['total_candidates'] = $stmt->fetchColumn();

        // Total precincts
        $stmt = $this->db->query("SELECT COUNT(*) FROM precincts WHERE active = TRUE");
        $stats['total_precincts'] = $stmt->fetchColumn();

        return $stats;
    }

    /**
     * Check if election exists
     */
    private function electionExists(int $electionId): bool
    {
        $sql = "SELECT COUNT(*) FROM elections WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $electionId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Log configurator actions
     */
    private function logAction(string $action, array $details): void
    {
        $sql = "INSERT INTO system_audit_log (module, action, details, ip_address, timestamp) 
                VALUES ('configurator', :action, :details, :ip, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':action' => $action,
            ':details' => json_encode($details),
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
}
