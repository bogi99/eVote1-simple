<?php

namespace Bogi\EvoteSimple\Registration;

use Bogi\EvoteSimple\Core\Database;
use PDO;

/**
 * Voter Registration System
 * Handles voter registration, ID generation, and precinct assignment
 */
class VoterRegistration
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Register a new voter
     */
    public function registerVoter(array $voterData): array
    {
        // Generate unique voter ID
        $voterId = $this->generateVoterId();

        // Validate required fields
        $required = ['first_name', 'last_name', 'email', 'date_of_birth', 'precinct_id'];
        foreach ($required as $field) {
            if (empty($voterData[$field])) {
                throw new \InvalidArgumentException("Required field missing: {$field}");
            }
        }

        // Check if email already exists
        if ($this->emailExists($voterData['email'])) {
            throw new \InvalidArgumentException("Email already registered");
        }

        try {
            $sql = "INSERT INTO voters (voter_id, first_name, last_name, email, phone, address, 
                    precinct_id, date_of_birth, status) 
                    VALUES (:voter_id, :first_name, :last_name, :email, :phone, :address, 
                    :precinct_id, :date_of_birth, 'active')";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':voter_id' => $voterId,
                ':first_name' => $voterData['first_name'],
                ':last_name' => $voterData['last_name'],
                ':email' => $voterData['email'],
                ':phone' => $voterData['phone'] ?? null,
                ':address' => $voterData['address'] ?? null,
                ':precinct_id' => $voterData['precinct_id'],
                ':date_of_birth' => $voterData['date_of_birth'],
            ]);

            // Log the registration
            $this->logAction('voter_registered', [
                'voter_id' => $voterId,
                'email' => $voterData['email'],
                'precinct_id' => $voterData['precinct_id']
            ]);

            return [
                'success' => true,
                'voter_id' => $voterId,
                'message' => 'Voter registered successfully'
            ];
        } catch (\PDOException $e) {
            throw new \RuntimeException("Registration failed: " . $e->getMessage());
        }
    }

    /**
     * Verify voter eligibility
     */
    public function verifyVoter(string $voterId): ?array
    {
        $sql = "SELECT v.*, p.name as precinct_name 
                FROM voters v 
                LEFT JOIN precincts p ON v.precinct_id = p.id 
                WHERE v.voter_id = :voter_id AND v.status = 'active'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':voter_id' => $voterId]);

        $voter = $stmt->fetch();

        if (!$voter) {
            return null;
        }

        // Check if voter has already voted
        if ($voter['has_voted']) {
            $voter['can_vote'] = false;
            $voter['reason'] = 'Already voted';
        } else {
            $voter['can_vote'] = true;
            $voter['reason'] = 'Eligible to vote';
        }

        return $voter;
    }

    /**
     * Mark voter as having voted
     */
    public function markAsVoted(string $voterId): bool
    {
        $sql = "UPDATE voters SET has_voted = TRUE, updated_at = CURRENT_TIMESTAMP 
                WHERE voter_id = :voter_id";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([':voter_id' => $voterId]);

        if ($result) {
            $this->logAction('voter_marked_voted', ['voter_id' => $voterId]);
        }

        return $result;
    }

    /**
     * Get all precincts for registration form
     */
    public function getPrecincts(): array
    {
        $sql = "SELECT id, name, address, capacity FROM precincts WHERE active = TRUE ORDER BY name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Generate unique voter ID
     */
    private function generateVoterId(): string
    {
        do {
            $voterId = 'V' . date('Y') . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            $exists = $this->voterIdExists($voterId);
        } while ($exists);

        return $voterId;
    }

    /**
     * Check if voter ID exists
     */
    private function voterIdExists(string $voterId): bool
    {
        $sql = "SELECT COUNT(*) FROM voters WHERE voter_id = :voter_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':voter_id' => $voterId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Check if email exists
     */
    private function emailExists(string $email): bool
    {
        $sql = "SELECT COUNT(*) FROM voters WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Log registration actions
     */
    private function logAction(string $action, array $details): void
    {
        $sql = "INSERT INTO system_audit_log (module, action, details, ip_address, timestamp) 
                VALUES ('registration', :action, :details, :ip, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':action' => $action,
            ':details' => json_encode($details),
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
}
