<?php

namespace Bogi\EvoteSimple\Tabulator;

use Bogi\EvoteSimple\Core\Database;
use PDO;

/**
 * Election Tabulator System
 * Handles vote counting, results calculation, and reporting
 */
class ElectionTabulator
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Calculate results for a specific election
     */
    public function calculateElectionResults(int $electionId): array
    {
        // Verify election exists
        if (!$this->electionExists($electionId)) {
            throw new \InvalidArgumentException("Election not found");
        }

        $results = [
            'election_id' => $electionId,
            'election_info' => $this->getElectionInfo($electionId),
            'candidates' => $this->calculateCandidateResults($electionId),
            'ballot_questions' => $this->calculateBallotQuestionResults($electionId),
            'statistics' => $this->calculateElectionStatistics($electionId),
            'calculated_at' => date('Y-m-d H:i:s')
        ];

        $this->logTabulatorAction('results_calculated', [
            'election_id' => $electionId,
            'total_votes' => $results['statistics']['total_votes_cast']
        ]);

        return $results;
    }

    /**
     * Calculate candidate vote totals
     */
    public function calculateCandidateResults(int $electionId): array
    {
        $sql = "
            SELECT 
                c.id,
                c.name,
                c.party,
                c.position,
                c.ballot_order,
                COUNT(v.id) as vote_count,
                ROUND(COUNT(v.id) * 100.0 / 
                    (SELECT COUNT(*) FROM votes 
                     WHERE election_id = ? 
                     AND candidate_id IS NOT NULL), 2) as percentage
            FROM candidates c
            LEFT JOIN votes v ON c.id = v.candidate_id AND v.election_id = ?
            WHERE c.election_id = ? AND c.active = TRUE
            GROUP BY c.id, c.name, c.party, c.position, c.ballot_order
            ORDER BY c.position, vote_count DESC, c.ballot_order
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$electionId, $electionId, $electionId]);
        $candidates = $stmt->fetchAll();

        // Group by position for better organization
        $grouped = [];
        foreach ($candidates as $candidate) {
            $position = $candidate['position'];
            if (!isset($grouped[$position])) {
                $grouped[$position] = [];
            }
            $grouped[$position][] = $candidate;
        }

        return $grouped;
    }

    /**
     * Calculate ballot question results
     */
    public function calculateBallotQuestionResults(int $electionId): array
    {
        $sql = "
            SELECT 
                bq.id,
                bq.question_text,
                bq.question_type,
                bq.ballot_order
            FROM ballot_questions bq
            WHERE bq.election_id = :election_id AND bq.active = TRUE
            ORDER BY bq.ballot_order
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':election_id' => $electionId]);
        $questions = $stmt->fetchAll();

        $results = [];
        foreach ($questions as $question) {
            $questionResults = [
                'question' => $question,
                'responses' => $this->getQuestionResponses($electionId, $question['id'])
            ];
            $results[] = $questionResults;
        }

        return $results;
    }

    /**
     * Get responses for a specific ballot question
     */
    private function getQuestionResponses(int $electionId, int $questionId): array
    {
        $sql = "
            SELECT 
                vote_value as response_value,
                COUNT(*) as response_count,
                ROUND(COUNT(*) * 100.0 / 
                    (SELECT COUNT(*) FROM votes 
                     WHERE election_id = ? 
                     AND ballot_question_id = ?), 2) as percentage
            FROM votes
            WHERE election_id = ? 
            AND ballot_question_id = ?
            AND vote_value IS NOT NULL
            GROUP BY vote_value
            ORDER BY response_count DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$electionId, $questionId, $electionId, $questionId]);

        return $stmt->fetchAll();
    }

    /**
     * Calculate election statistics
     */
    public function calculateElectionStatistics(int $electionId): array
    {
        $stats = [];

        // Total registered voters for this election
        $sql = "SELECT COUNT(*) FROM voters WHERE status = 'active'";
        $stmt = $this->db->query($sql);
        $stats['total_registered_voters'] = $stmt->fetchColumn();

        // Total votes cast
        $sql = "SELECT COUNT(DISTINCT voter_id_hash) FROM votes WHERE election_id = :election_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':election_id' => $electionId]);
        $stats['total_votes_cast'] = $stmt->fetchColumn();

        // Voter turnout percentage
        $stats['turnout_percentage'] = $stats['total_registered_voters'] > 0
            ? round(($stats['total_votes_cast'] / $stats['total_registered_voters']) * 100, 2)
            : 0;

        // Total candidate votes
        $sql = "SELECT COUNT(*) FROM votes WHERE election_id = :election_id AND candidate_id IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':election_id' => $electionId]);
        $stats['total_candidate_votes'] = $stmt->fetchColumn();

        // Total ballot question responses
        $sql = "SELECT COUNT(*) FROM votes WHERE election_id = :election_id AND ballot_question_id IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':election_id' => $electionId]);
        $stats['total_question_responses'] = $stmt->fetchColumn();

        // Votes by precinct
        $sql = "
            SELECT 
                p.name as precinct_name,
                COUNT(DISTINCT v.voter_id_hash) as votes_cast
            FROM votes v
            JOIN precincts p ON v.precinct_id = p.id
            WHERE v.election_id = :election_id
            GROUP BY p.id, p.name
            ORDER BY votes_cast DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':election_id' => $electionId]);
        $stats['votes_by_precinct'] = $stmt->fetchAll();

        // Voting timeline (votes per hour)
        $sql = "
            SELECT 
                DATE_FORMAT(cast_at, '%Y-%m-%d %H:00:00') as hour_bucket,
                COUNT(DISTINCT voter_id_hash) as votes_in_hour
            FROM votes
            WHERE election_id = :election_id
            GROUP BY hour_bucket
            ORDER BY hour_bucket
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':election_id' => $electionId]);
        $stats['voting_timeline'] = $stmt->fetchAll();

        return $stats;
    }

    /**
     * Get real-time results (for active elections)
     */
    public function getRealTimeResults(int $electionId): array
    {
        $election = $this->getElectionInfo($electionId);

        if ($election['status'] !== 'active') {
            throw new \InvalidArgumentException("Real-time results only available for active elections");
        }

        return $this->calculateElectionResults($electionId);
    }

    /**
     * Get final results (for closed elections)
     */
    public function getFinalResults(int $electionId): array
    {
        $election = $this->getElectionInfo($electionId);

        if (!in_array($election['status'], ['closed', 'finalized'])) {
            throw new \InvalidArgumentException("Final results only available for closed or finalized elections");
        }

        // Check if results are already cached
        $cached = $this->getCachedResults($electionId);
        if ($cached) {
            return $cached;
        }

        // Calculate and cache results
        $results = $this->calculateElectionResults($electionId);
        $this->cacheResults($electionId, $results);

        return $results;
    }

    /**
     * Export results to different formats
     */
    public function exportResults(int $electionId, string $format = 'json'): string
    {
        $results = $this->calculateElectionResults($electionId);

        switch (strtolower($format)) {
            case 'json':
                return json_encode($results, JSON_PRETTY_PRINT);

            case 'csv':
                return $this->resultsToCSV($results);

            case 'xml':
                return $this->resultsToXML($results);

            default:
                throw new \InvalidArgumentException("Unsupported export format: {$format}");
        }
    }

    /**
     * Get election winners
     */
    public function getElectionWinners(int $electionId): array
    {
        $candidateResults = $this->calculateCandidateResults($electionId);

        $winners = [];
        foreach ($candidateResults as $position => $candidates) {
            if (!empty($candidates)) {
                // Winner is the candidate with the most votes
                $winner = $candidates[0];
                $winners[$position] = $winner;
            }
        }

        return $winners;
    }

    /**
     * Cache results for finalized elections
     */
    private function cacheResults(int $electionId, array $results): void
    {
        $sql = "
            INSERT INTO election_results (election_id, results_data, calculated_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            results_data = VALUES(results_data),
            calculated_at = NOW()
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $electionId,
            json_encode($results)
        ]);
    }

    /**
     * Get cached results
     */
    private function getCachedResults(int $electionId): ?array
    {
        $sql = "SELECT results_data FROM election_results WHERE election_id = :election_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':election_id' => $electionId]);
        $cached = $stmt->fetchColumn();

        return $cached ? json_decode($cached, true) : null;
    }

    /**
     * Convert results to CSV format
     */
    private function resultsToCSV(array $results): string
    {
        $csv = "Position,Candidate,Party,Votes,Percentage\n";

        foreach ($results['candidates'] as $position => $candidates) {
            foreach ($candidates as $candidate) {
                $csv .= sprintf(
                    "%s,%s,%s,%d,%.2f%%\n",
                    $position,
                    $candidate['name'],
                    $candidate['party'] ?? 'Independent',
                    $candidate['vote_count'],
                    $candidate['percentage']
                );
            }
        }

        return $csv;
    }

    /**
     * Convert results to XML format
     */
    private function resultsToXML(array $results): string
    {
        $xml = new \SimpleXMLElement('<election_results/>');

        $xml->addAttribute('election_id', $results['election_id']);
        $xml->addAttribute('calculated_at', $results['calculated_at']);

        $candidatesNode = $xml->addChild('candidates');
        foreach ($results['candidates'] as $position => $candidates) {
            $positionNode = $candidatesNode->addChild('position');
            $positionNode->addAttribute('name', $position);

            foreach ($candidates as $candidate) {
                $candidateNode = $positionNode->addChild('candidate');
                $candidateNode->addAttribute('name', $candidate['name']);
                $candidateNode->addAttribute('party', $candidate['party'] ?? 'Independent');
                $candidateNode->addAttribute('votes', $candidate['vote_count']);
                $candidateNode->addAttribute('percentage', $candidate['percentage']);
            }
        }

        return $xml->asXML();
    }

    /**
     * Get election information
     */
    private function getElectionInfo(int $electionId): array
    {
        $sql = "SELECT * FROM elections WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $electionId]);
        $election = $stmt->fetch();

        if (!$election) {
            throw new \InvalidArgumentException("Election not found");
        }

        return $election;
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
     * Log tabulator actions
     */
    private function logTabulatorAction(string $action, array $details): void
    {
        $sql = "INSERT INTO system_audit_log (module, action, details, ip_address, timestamp) 
                VALUES ('tabulator', :action, :details, :ip, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':action' => $action,
            ':details' => json_encode($details),
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
}
