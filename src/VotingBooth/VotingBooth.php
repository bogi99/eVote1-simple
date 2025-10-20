<?php

namespace Bogi\EvoteSimple\VotingBooth;

use Bogi\EvoteSimple\Core\Database;
use PDO;

/**
 * Voting Booth System
 * Handles secure vote casting, ballot display, and voter authentication
 */
class VotingBooth
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Verify voter eligibility and get voter info
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
            $this->logAction('voter_verification_failed', ['voter_id' => $voterId, 'reason' => 'not_found']);
            return null;
        }

        // Check if voter has already voted
        if ($voter['has_voted']) {
            $this->logAction('voter_verification_failed', ['voter_id' => $voterId, 'reason' => 'already_voted']);
            $voter['can_vote'] = false;
            $voter['reason'] = 'You have already voted in this election';
        } else {
            $voter['can_vote'] = true;
            $voter['reason'] = 'Eligible to vote';
        }

        $this->logAction('voter_verification', [
            'voter_id' => $voterId,
            'can_vote' => $voter['can_vote'],
            'precinct_id' => $voter['precinct_id']
        ]);

        return $voter;
    }

    /**
     * Get active elections available for voting
     */
    public function getActiveElections(): array
    {
        $sql = "SELECT * FROM elections 
                WHERE status = 'active' 
                AND election_date = CURDATE()
                AND CURTIME() BETWEEN start_time AND end_time
                ORDER BY name";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get election ballot with candidates and questions
     */
    public function getElectionBallot(int $electionId): ?array
    {
        // Get election details
        $sql = "SELECT * FROM elections WHERE id = :id AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $electionId]);
        $election = $stmt->fetch();

        if (!$election) {
            return null;
        }

        // Check if election is currently active (time-wise)
        $now = date('H:i:s');
        $today = date('Y-m-d');

        if (
            $election['election_date'] !== $today ||
            $now < $election['start_time'] ||
            $now > $election['end_time']
        ) {
            return null;
        }

        // Get candidates grouped by position
        $sql = "SELECT * FROM candidates 
                WHERE election_id = :election_id AND active = TRUE 
                ORDER BY position, ballot_order, name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':election_id' => $electionId]);
        $candidates = $stmt->fetchAll();

        // Group candidates by position
        $candidatesByPosition = [];
        foreach ($candidates as $candidate) {
            $candidatesByPosition[$candidate['position']][] = $candidate;
        }

        // Get ballot questions
        $sql = "SELECT * FROM ballot_questions 
                WHERE election_id = :election_id AND active = TRUE 
                ORDER BY ballot_order, id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':election_id' => $electionId]);
        $questions = $stmt->fetchAll();

        $election['candidates_by_position'] = $candidatesByPosition;
        $election['ballot_questions'] = $questions;

        return $election;
    }

    /**
     * Cast vote securely
     */
    public function castVote(string $voterId, int $electionId, array $votes, string $boothId = 'default'): array
    {
        // Verify voter can vote
        $voter = $this->verifyVoter($voterId);
        if (!$voter || !$voter['can_vote']) {
            throw new \InvalidArgumentException("Voter not eligible to vote");
        }

        // Get election ballot to validate votes
        $ballot = $this->getElectionBallot($electionId);
        if (!$ballot) {
            throw new \InvalidArgumentException("Election not available for voting");
        }

        // Validate votes against ballot
        $this->validateVotes($votes, $ballot);

        try {
            // Start transaction
            $this->db->beginTransaction();

            // Hash voter ID for privacy
            $voterIdHash = hash('sha256', $voterId . $electionId . date('Y-m-d'));

            $votesInserted = 0;

            // Cast candidate votes
            if (isset($votes['candidates'])) {
                foreach ($votes['candidates'] as $position => $candidateId) {
                    if (!empty($candidateId)) {
                        $voteHash = $this->generateVoteHash($voterIdHash, $candidateId, $position);

                        $sql = "INSERT INTO votes (election_id, voter_id_hash, candidate_id, 
                                precinct_id, booth_id, vote_hash, cast_at) 
                                VALUES (:election_id, :voter_id_hash, :candidate_id, 
                                :precinct_id, :booth_id, :vote_hash, NOW())";

                        $stmt = $this->db->prepare($sql);
                        $stmt->execute([
                            ':election_id' => $electionId,
                            ':voter_id_hash' => $voterIdHash,
                            ':candidate_id' => $candidateId,
                            ':precinct_id' => $voter['precinct_id'],
                            ':booth_id' => $boothId,
                            ':vote_hash' => $voteHash
                        ]);

                        $voteId = $this->db->lastInsertId();

                        // Log vote cast
                        $this->logVoteAction($voteId, 'cast', [
                            'position' => $position,
                            'candidate_id' => $candidateId,
                            'booth_id' => $boothId
                        ]);

                        $votesInserted++;
                    }
                }
            }

            // Cast ballot question votes
            if (isset($votes['questions'])) {
                foreach ($votes['questions'] as $questionId => $answer) {
                    if (!empty($answer)) {
                        $voteHash = $this->generateVoteHash($voterIdHash, $answer, "question_$questionId");

                        $sql = "INSERT INTO votes (election_id, voter_id_hash, ballot_question_id, 
                                vote_value, precinct_id, booth_id, vote_hash, cast_at) 
                                VALUES (:election_id, :voter_id_hash, :ballot_question_id, 
                                :vote_value, :precinct_id, :booth_id, :vote_hash, NOW())";

                        $stmt = $this->db->prepare($sql);
                        $stmt->execute([
                            ':election_id' => $electionId,
                            ':voter_id_hash' => $voterIdHash,
                            ':ballot_question_id' => $questionId,
                            ':vote_value' => $answer,
                            ':precinct_id' => $voter['precinct_id'],
                            ':booth_id' => $boothId,
                            ':vote_hash' => $voteHash
                        ]);

                        $voteId = $this->db->lastInsertId();

                        // Log vote cast
                        $this->logVoteAction($voteId, 'cast', [
                            'question_id' => $questionId,
                            'answer' => $answer,
                            'booth_id' => $boothId
                        ]);

                        $votesInserted++;
                    }
                }
            }

            // Mark voter as having voted
            $sql = "UPDATE voters SET has_voted = TRUE, updated_at = CURRENT_TIMESTAMP 
                    WHERE voter_id = :voter_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':voter_id' => $voterId]);

            // Commit transaction
            $this->db->commit();

            $this->logAction('vote_cast_success', [
                'voter_id_hash' => $voterIdHash,
                'election_id' => $electionId,
                'votes_count' => $votesInserted,
                'booth_id' => $boothId
            ]);

            return [
                'success' => true,
                'message' => 'Vote cast successfully',
                'votes_count' => $votesInserted,
                'receipt_hash' => substr($voterIdHash, 0, 8) // Short receipt for voter
            ];
        } catch (\Exception $e) {
            $this->db->rollback();

            $this->logAction('vote_cast_failed', [
                'voter_id' => $voterId,
                'election_id' => $electionId,
                'error' => $e->getMessage(),
                'booth_id' => $boothId
            ]);

            throw new \RuntimeException("Failed to cast vote: " . $e->getMessage());
        }
    }

    /**
     * Validate votes against ballot structure
     */
    private function validateVotes(array $votes, array $ballot): void
    {
        // Validate candidate votes
        if (isset($votes['candidates'])) {
            $validCandidates = [];
            foreach ($ballot['candidates_by_position'] as $position => $candidates) {
                foreach ($candidates as $candidate) {
                    $validCandidates[$candidate['id']] = $position;
                }
            }

            foreach ($votes['candidates'] as $position => $candidateId) {
                if (!empty($candidateId)) {
                    if (!isset($validCandidates[$candidateId]) || $validCandidates[$candidateId] !== $position) {
                        throw new \InvalidArgumentException("Invalid candidate selection");
                    }
                }
            }
        }

        // Validate question votes
        if (isset($votes['questions'])) {
            $validQuestions = [];
            foreach ($ballot['ballot_questions'] as $question) {
                $validQuestions[$question['id']] = $question;
            }

            foreach ($votes['questions'] as $questionId => $answer) {
                if (!empty($answer) && !isset($validQuestions[$questionId])) {
                    throw new \InvalidArgumentException("Invalid question selection");
                }
            }
        }
    }

    /**
     * Generate vote hash for verification
     */
    private function generateVoteHash(string $voterIdHash, string $choice, string $category): string
    {
        return hash('sha256', $voterIdHash . $choice . $category . microtime(true));
    }

    /**
     * Log voting booth actions
     */
    private function logAction(string $action, array $details): void
    {
        $sql = "INSERT INTO system_audit_log (module, action, details, ip_address, timestamp) 
                VALUES ('voting_booth', :action, :details, :ip, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':action' => $action,
            ':details' => json_encode($details),
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }

    /**
     * Log vote-specific actions
     */
    private function logVoteAction(int $voteId, string $action, array $details): void
    {
        $sql = "INSERT INTO vote_audit_log (vote_id, action, details, ip_address, timestamp) 
                VALUES (:vote_id, :action, :details, :ip, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':vote_id' => $voteId,
            ':action' => $action,
            ':details' => json_encode($details),
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
}
