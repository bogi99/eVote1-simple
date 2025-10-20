<?php
require_once __DIR__ . '/../config/bootstrap.php';

use Bogi\EvoteSimple\Tabulator\ElectionTabulator;

$tabulator = new ElectionTabulator();
$action = $_GET['action'] ?? 'dashboard';
$message = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($_POST['action']) {
            case 'calculate_results':
                $electionId = (int)$_POST['election_id'];
                $results = $tabulator->calculateElectionResults($electionId);
                $message = "Results calculated successfully for election ID {$electionId}";
                break;

            case 'export_results':
                $electionId = (int)$_POST['election_id'];
                $format = $_POST['format'];
                $exported = $tabulator->exportResults($electionId, $format);

                // Force download
                $filename = "election_{$electionId}_results.{$format}";
                header('Content-Type: application/octet-stream');
                header("Content-Disposition: attachment; filename=\"{$filename}\"");
                echo $exported;
                exit;
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get elections data
$db = \Bogi\EvoteSimple\Core\Database::getConnection();
$electionsStmt = $db->query("
    SELECT e.*, 
           COUNT(DISTINCT v.voter_id_hash) as votes_cast,
           COUNT(DISTINCT c.id) as candidate_count,
           COUNT(DISTINCT bq.id) as question_count
    FROM elections e
    LEFT JOIN votes v ON e.id = v.election_id
    LEFT JOIN candidates c ON e.id = c.election_id AND c.active = TRUE
    LEFT JOIN ballot_questions bq ON e.id = bq.election_id AND bq.active = TRUE
    GROUP BY e.id
    ORDER BY e.election_date DESC, e.created_at DESC
");
$elections = $electionsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabulator Admin - eVote Simple</title>

    <!-- JetBrains Mono Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="css/styles.css">

    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .admin-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .tab-button {
            padding: 10px 20px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px 5px 0 0;
            text-decoration: none;
            color: #333;
        }

        .tab-button.active {
            background: #007bff;
            color: white;
        }

        .elections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 20px;
        }

        .election-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #007bff;
        }

        .election-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .election-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            flex: 1;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-draft {
            background: #f8f9fa;
            color: #6c757d;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-closed {
            background: #fff3cd;
            color: #856404;
        }

        .status-finalized {
            background: #d1ecf1;
            color: #0c5460;
        }

        .election-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 15px 0;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }

        .election-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .action-form {
            display: inline-block;
        }

        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .quick-stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .quick-stat-number {
            font-size: 36px;
            font-weight: bold;
        }

        .quick-stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .api-docs {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .endpoint {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #28a745;
        }

        .endpoint-method {
            color: #28a745;
            font-weight: bold;
        }

        .endpoint-url {
            font-family: 'JetBrains Mono', monospace;
            background: #f1f1f1;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <h1>📊 Election Tabulator - Admin Panel</h1>
        <p><a href="admin.php">← Back to Main Admin</a> | <a href="index.php">Home</a></p>

        <?php if ($message): ?>
            <div class="success-box"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-box"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Navigation Tabs -->
        <div class="admin-tabs">
            <a href="?action=dashboard" class="tab-button <?= $action === 'dashboard' ? 'active' : '' ?>">
                📊 Dashboard
            </a>
            <a href="?action=elections" class="tab-button <?= $action === 'elections' ? 'active' : '' ?>">
                🗳️ Elections
            </a>
            <a href="?action=api" class="tab-button <?= $action === 'api' ? 'active' : '' ?>">
                🔌 API Documentation
            </a>
            <a href="results.php" class="tab-button">
                📈 View Results
            </a>
        </div>

        <?php if ($action === 'dashboard'): ?>
            <!-- Quick Statistics -->
            <div class="quick-stats">
                <div class="quick-stat-card">
                    <div class="quick-stat-number"><?= count($elections) ?></div>
                    <div class="quick-stat-label">Total Elections</div>
                </div>

                <div class="quick-stat-card">
                    <div class="quick-stat-number">
                        <?= count(array_filter($elections, fn($e) => $e['status'] === 'active')) ?>
                    </div>
                    <div class="quick-stat-label">Active Elections</div>
                </div>

                <div class="quick-stat-card">
                    <div class="quick-stat-number">
                        <?= array_sum(array_column($elections, 'votes_cast')) ?>
                    </div>
                    <div class="quick-stat-label">Total Votes Cast</div>
                </div>

                <div class="quick-stat-card">
                    <div class="quick-stat-number">
                        <?= count(array_filter($elections, fn($e) => in_array($e['status'], ['closed', 'finalized']))) ?>
                    </div>
                    <div class="quick-stat-label">Completed Elections</div>
                </div>
            </div>

            <!-- Recent Elections -->
            <div class="info-box">
                <h3>🔍 System Overview</h3>
                <p>The Tabulator module handles all result calculation, aggregation, and reporting for the voting system.</p>

                <h4>Key Features:</h4>
                <ul>
                    <li><strong>Real-time Results:</strong> Live calculation for active elections</li>
                    <li><strong>Final Results:</strong> Cached results for closed elections</li>
                    <li><strong>Export Capabilities:</strong> JSON, CSV, and XML export formats</li>
                    <li><strong>REST API:</strong> Programmatic access to results data</li>
                    <li><strong>Statistical Analysis:</strong> Turnout, timeline, and demographic breakdowns</li>
                    <li><strong>Winner Determination:</strong> Automatic winner identification</li>
                </ul>
            </div>

        <?php elseif ($action === 'elections'): ?>
            <!-- Elections Management -->
            <div class="elections-grid">
                <?php foreach ($elections as $election): ?>
                    <div class="election-card">
                        <div class="election-header">
                            <h3 class="election-title"><?= htmlspecialchars($election['name']) ?></h3>
                            <span class="status-badge status-<?= $election['status'] ?>">
                                <?= $election['status'] ?>
                            </span>
                        </div>

                        <p style="color: #666; margin: 10px 0;">
                            📅 <?= date('M j, Y', strtotime($election['election_date'])) ?>
                        </p>

                        <div class="election-stats">
                            <div class="stat">
                                <div class="stat-number"><?= $election['votes_cast'] ?></div>
                                <div class="stat-label">Votes Cast</div>
                            </div>

                            <div class="stat">
                                <div class="stat-number"><?= $election['candidate_count'] ?></div>
                                <div class="stat-label">Candidates</div>
                            </div>

                            <div class="stat">
                                <div class="stat-number"><?= $election['question_count'] ?></div>
                                <div class="stat-label">Questions</div>
                            </div>
                        </div>

                        <div class="election-actions">
                            <!-- Calculate Results -->
                            <form method="POST" class="action-form">
                                <input type="hidden" name="action" value="calculate_results">
                                <input type="hidden" name="election_id" value="<?= $election['id'] ?>">
                                <button type="submit" class="submit-button" style="font-size: 12px; padding: 6px 12px;">
                                    📊 Calculate
                                </button>
                            </form>

                            <!-- View Results -->
                            <a href="results.php?election_id=<?= $election['id'] ?>" class="submit-button" style="font-size: 12px; padding: 6px 12px; background: #28a745;">
                                👀 View Results
                            </a>

                            <!-- Export Dropdown -->
                            <div class="dropdown" style="display: inline-block; position: relative;">
                                <button onclick="toggleDropdown(<?= $election['id'] ?>)" class="submit-button" style="font-size: 12px; padding: 6px 12px; background: #17a2b8;">
                                    📤 Export ▼
                                </button>

                                <div id="dropdown-<?= $election['id'] ?>" style="display: none; position: absolute; top: 100%; left: 0; background: white; border: 1px solid #ddd; border-radius: 4px; min-width: 120px; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                    <a href="api/results.php?election_id=<?= $election['id'] ?>&format=json" target="_blank" style="display: block; padding: 8px 12px; text-decoration: none; color: #333; border-bottom: 1px solid #eee;">
                                        📄 JSON
                                    </a>
                                    <a href="api/results.php?election_id=<?= $election['id'] ?>&format=csv" style="display: block; padding: 8px 12px; text-decoration: none; color: #333; border-bottom: 1px solid #eee;">
                                        📊 CSV
                                    </a>
                                    <a href="api/results.php?election_id=<?= $election['id'] ?>&format=xml" style="display: block; padding: 8px 12px; text-decoration: none; color: #333;">
                                        🔖 XML
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($elections)): ?>
                    <div class="info-box">
                        <p>No elections found. <a href="admin.php">Create an election</a> to see tabulation options.</p>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($action === 'api'): ?>
            <!-- API Documentation -->
            <div class="api-docs">
                <h3>🔌 Results API Documentation</h3>
                <p>The Results API provides programmatic access to election results and statistics.</p>

                <div class="endpoint">
                    <div><span class="endpoint-method">GET</span> <code class="endpoint-url">/api/results.php?list=elections</code></div>
                    <p>Get list of all elections with basic statistics</p>
                </div>

                <div class="endpoint">
                    <div><span class="endpoint-method">GET</span> <code class="endpoint-url">/api/results.php?election_id={id}</code></div>
                    <p>Get complete results for a specific election</p>
                </div>

                <div class="endpoint">
                    <div><span class="endpoint-method">GET</span> <code class="endpoint-url">/api/results.php?election_id={id}&type=realtime</code></div>
                    <p>Get real-time results for active elections</p>
                </div>

                <div class="endpoint">
                    <div><span class="endpoint-method">GET</span> <code class="endpoint-url">/api/results.php?election_id={id}&type=final</code></div>
                    <p>Get final results for closed/finalized elections</p>
                </div>

                <div class="endpoint">
                    <div><span class="endpoint-method">GET</span> <code class="endpoint-url">/api/results.php?election_id={id}&format=csv</code></div>
                    <p>Export results in CSV format</p>
                </div>

                <div class="endpoint">
                    <div><span class="endpoint-method">GET</span> <code class="endpoint-url">/api/results.php?election_id={id}&format=xml</code></div>
                    <p>Export results in XML format</p>
                </div>

                <h4>Response Format</h4>
                <pre style="background: #f1f1f1; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>{
  "success": true,
  "data": {
    "election_id": 1,
    "election_info": { ... },
    "candidates": { ... },
    "ballot_questions": [ ... ],
    "statistics": { ... },
    "calculated_at": "2025-10-20 16:01:21"
  },
  "timestamp": "2025-10-20 16:01:21"
}</code></pre>

                <h4>Example Usage</h4>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 15px;">
                    <h5>JavaScript (Fetch API)</h5>
                    <pre style="background: #f1f1f1; padding: 10px; border-radius: 3px; overflow-x: auto;"><code>fetch('/api/results.php?election_id=1')
  .then(response => response.json())
  .then(data => {
    console.log('Results:', data);
  });</code></pre>

                    <h5>cURL</h5>
                    <pre style="background: #f1f1f1; padding: 10px; border-radius: 3px; overflow-x: auto;"><code>curl -X GET "<?= $_SERVER['HTTP_HOST'] ?>/api/results.php?election_id=1"</code></pre>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleDropdown(electionId) {
            const dropdown = document.getElementById('dropdown-' + electionId);
            const isVisible = dropdown.style.display === 'block';

            // Close all dropdowns
            document.querySelectorAll('[id^="dropdown-"]').forEach(d => d.style.display = 'none');

            // Toggle current dropdown
            dropdown.style.display = isVisible ? 'none' : 'block';
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('[id^="dropdown-"]').forEach(d => d.style.display = 'none');
            }
        });
    </script>
</body>

</html>