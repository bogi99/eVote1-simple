<?php
require_once __DIR__ . '/../config/bootstrap.php';

use Bogi\EvoteSimple\Tabulator\ElectionTabulator;

/**
 * Results API - REST endpoint for election results
 * 
 * Endpoints:
 * GET /api/results.php?election_id=1 - Get results for specific election
 * GET /api/results.php?election_id=1&format=json|csv|xml - Export results
 * GET /api/results.php?election_id=1&type=realtime|final - Get specific result type
 * GET /api/results.php?list=elections - List all elections with status
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS requests for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $tabulator = new ElectionTabulator();

    // Handle election list request
    if (isset($_GET['list']) && $_GET['list'] === 'elections') {
        $elections = getElectionsList();
        echo json_encode([
            'success' => true,
            'data' => $elections,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    // Require election_id for result requests
    if (!isset($_GET['election_id']) || !is_numeric($_GET['election_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'election_id parameter is required']);
        exit;
    }

    $electionId = (int)$_GET['election_id'];
    $format = $_GET['format'] ?? 'json';
    $type = $_GET['type'] ?? 'auto';

    // Handle export formats
    if ($format !== 'json') {
        handleExport($tabulator, $electionId, $format);
        exit;
    }

    // Get results based on type
    $results = getResultsByType($tabulator, $electionId, $type);

    echo json_encode([
        'success' => true,
        'data' => $results,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    error_log("Results API Error: " . $e->getMessage());
}

/**
 * Get list of all elections with their status
 */
function getElectionsList(): array
{
    $db = \Bogi\EvoteSimple\Core\Database::getConnection();

    $sql = "
        SELECT 
            id,
            name,
            description,
            election_date,
            start_time,
            end_time,
            status,
            (SELECT COUNT(DISTINCT voter_id_hash) FROM votes WHERE election_id = elections.id) as votes_cast,
            (SELECT COUNT(*) FROM candidates WHERE election_id = elections.id AND active = TRUE) as candidate_count
        FROM elections
        ORDER BY election_date DESC, created_at DESC
    ";

    $stmt = $db->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get results based on type (realtime, final, or auto)
 */
function getResultsByType($tabulator, int $electionId, string $type): array
{
    switch ($type) {
        case 'realtime':
            return $tabulator->getRealTimeResults($electionId);

        case 'final':
            return $tabulator->getFinalResults($electionId);

        case 'auto':
        default:
            // Auto-determine based on election status
            $db = \Bogi\EvoteSimple\Core\Database::getConnection();
            $sql = "SELECT status FROM elections WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $electionId]);
            $status = $stmt->fetchColumn();

            if ($status === 'active') {
                return $tabulator->getRealTimeResults($electionId);
            } elseif (in_array($status, ['closed', 'finalized'])) {
                return $tabulator->getFinalResults($electionId);
            } else {
                return $tabulator->calculateElectionResults($electionId);
            }
    }
}

/**
 * Handle export requests (CSV, XML)
 */
function handleExport($tabulator, int $electionId, string $format): void
{
    $exported = $tabulator->exportResults($electionId, $format);

    switch ($format) {
        case 'csv':
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="election_' . $electionId . '_results.csv"');
            break;

        case 'xml':
            header('Content-Type: application/xml');
            header('Content-Disposition: attachment; filename="election_' . $electionId . '_results.xml"');
            break;

        default:
            header('Content-Type: application/json');
    }

    echo $exported;
}
