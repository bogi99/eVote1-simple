<?php
require_once __DIR__ . '/../config/bootstrap.php';

use Bogi\EvoteSimple\Tabulator\ElectionTabulator;

$tabulator = new ElectionTabulator();
$electionId = $_GET['election_id'] ?? null;
$results = null;
$error = null;

if ($electionId && is_numeric($electionId)) {
    try {
        $results = $tabulator->calculateElectionResults((int)$electionId);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get list of elections for dropdown
$db = \Bogi\EvoteSimple\Core\Database::getConnection();
$electionsStmt = $db->query("
    SELECT id, name, status, election_date,
           (SELECT COUNT(DISTINCT voter_id_hash) FROM votes WHERE election_id = elections.id) as votes_cast
    FROM elections 
    ORDER BY election_date DESC
");
$elections = $electionsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - eVote Simple</title>

    <!-- JetBrains Mono Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="css/styles.css">

    <style>
        .results-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .election-selector {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .position-results {
            background: white;
            margin-bottom: 30px;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .candidate-result {
            display: flex;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            border-radius: 6px;
            background: #f8f9fa;
        }

        .candidate-info {
            flex: 1;
        }

        .candidate-name {
            font-weight: bold;
            font-size: 18px;
            margin: 0;
        }

        .candidate-party {
            color: #666;
            font-size: 14px;
        }

        .vote-count {
            text-align: center;
            min-width: 100px;
        }

        .vote-number {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }

        .vote-percentage {
            font-size: 14px;
            color: #666;
        }

        .progress-bar {
            flex: 1;
            margin: 0 20px;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 0.3s ease;
        }

        .winner {
            background: #d4edda !important;
            border-left: 5px solid #28a745;
        }

        .statistics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #007bff;
        }

        .stat-label {
            color: #666;
            margin-top: 5px;
        }

        .question-results {
            background: #fff3cd;
            border-left: 5px solid #ffc107;
        }

        .response-result {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            margin: 5px 0;
            background: white;
            border-radius: 4px;
        }

        .export-buttons {
            text-align: center;
            margin: 30px 0;
        }

        .export-buttons a {
            margin: 0 10px;
        }

        .refresh-notice {
            background: #d1ecf1;
            color: #0c5460;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="results-container">
        <h1>📊 Election Results</h1>
        <p><a href="index.php">← Back to Home</a></p>

        <div class="election-selector">
            <h3>Select Election</h3>
            <form method="GET" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <select name="election_id" class="form-input" style="flex: 1; min-width: 200px;">
                    <option value="">-- Choose an Election --</option>
                    <?php foreach ($elections as $election): ?>
                        <option value="<?= $election['id'] ?>" <?= $electionId == $election['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($election['name']) ?>
                            (<?= $election['status'] ?>) -
                            <?= $election['votes_cast'] ?> votes
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="submit-button">View Results</button>

                <?php if ($electionId): ?>
                    <a href="?election_id=<?= $electionId ?>&refresh=1" class="submit-button" style="background: #17a2b8;">
                        🔄 Refresh
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($error): ?>
            <div class="error-box">
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($results): ?>
            <div class="refresh-notice">
                📅 Results calculated at: <?= $results['calculated_at'] ?> |
                🗳️ Election: <?= htmlspecialchars($results['election_info']['name']) ?> (<?= $results['election_info']['status'] ?>)
            </div>

            <!-- Export Options -->
            <div class="export-buttons">
                <a href="api/results.php?election_id=<?= $electionId ?>&format=json" target="_blank" class="submit-button">
                    📄 Export JSON
                </a>
                <a href="api/results.php?election_id=<?= $electionId ?>&format=csv" class="submit-button">
                    📊 Export CSV
                </a>
                <a href="api/results.php?election_id=<?= $electionId ?>&format=xml" class="submit-button">
                    🔖 Export XML
                </a>
            </div>

            <!-- Election Statistics -->
            <div class="statistics-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($results['statistics']['total_votes_cast']) ?></div>
                    <div class="stat-label">Total Votes Cast</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number"><?= number_format($results['statistics']['total_registered_voters']) ?></div>
                    <div class="stat-label">Registered Voters</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number"><?= $results['statistics']['turnout_percentage'] ?>%</div>
                    <div class="stat-label">Voter Turnout</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number"><?= count($results['candidates']) ?></div>
                    <div class="stat-label">Contested Positions</div>
                </div>
            </div>

            <!-- Candidate Results -->
            <?php foreach ($results['candidates'] as $position => $candidates): ?>
                <div class="position-results">
                    <h2>🏆 <?= htmlspecialchars($position) ?></h2>

                    <?php
                    $maxVotes = !empty($candidates) ? max(array_column($candidates, 'vote_count')) : 0;
                    foreach ($candidates as $index => $candidate):
                        $isWinner = $index === 0 && $candidate['vote_count'] > 0;
                        $barWidth = $maxVotes > 0 ? ($candidate['vote_count'] / $maxVotes) * 100 : 0;
                    ?>
                        <div class="candidate-result <?= $isWinner ? 'winner' : '' ?>">
                            <div class="candidate-info">
                                <div class="candidate-name">
                                    <?= $isWinner ? '👑 ' : '' ?><?= htmlspecialchars($candidate['name']) ?>
                                </div>
                                <div class="candidate-party">
                                    <?= htmlspecialchars($candidate['party'] ?? 'Independent') ?>
                                </div>
                            </div>

                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $barWidth ?>%"></div>
                            </div>

                            <div class="vote-count">
                                <div class="vote-number"><?= number_format($candidate['vote_count']) ?></div>
                                <div class="vote-percentage"><?= $candidate['percentage'] ?>%</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <!-- Ballot Question Results -->
            <?php if (!empty($results['ballot_questions'])): ?>
                <div class="position-results question-results">
                    <h2>❓ Ballot Questions</h2>

                    <?php foreach ($results['ballot_questions'] as $question): ?>
                        <h4><?= htmlspecialchars($question['question']['question_text']) ?></h4>

                        <?php foreach ($question['responses'] as $response): ?>
                            <div class="response-result">
                                <span><strong><?= htmlspecialchars($response['response_value']) ?></strong></span>
                                <span><?= number_format($response['response_count']) ?> votes (<?= $response['percentage'] ?>%)</span>
                            </div>
                        <?php endforeach; ?>

                        <?php if (count($results['ballot_questions']) > 1): ?>
                            <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Voting Timeline -->
            <?php if (!empty($results['statistics']['voting_timeline'])): ?>
                <div class="position-results">
                    <h3>📈 Voting Timeline</h3>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8f9fa;">
                                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Hour</th>
                                    <th style="padding: 10px; text-align: right; border-bottom: 2px solid #ddd;">Votes Cast</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results['statistics']['voting_timeline'] as $timeSlot): ?>
                                    <tr>
                                        <td style="padding: 8px; border-bottom: 1px solid #eee;">
                                            <?= date('g:i A', strtotime($timeSlot['hour_bucket'])) ?>
                                        </td>
                                        <td style="padding: 8px; text-align: right; border-bottom: 1px solid #eee;">
                                            <?= number_format($timeSlot['votes_in_hour']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        <?php elseif ($electionId): ?>
            <div class="info-box">
                <p>No results to display. Please select an election or ensure the election has votes.</p>
            </div>
        <?php else: ?>
            <div class="info-box">
                <h3>📊 Election Results System</h3>
                <p>Select an election from the dropdown above to view detailed results, statistics, and analytics.</p>

                <h4>Available Features:</h4>
                <ul>
                    <li><strong>Real-time Results:</strong> Live vote counts for active elections</li>
                    <li><strong>Final Results:</strong> Official results for closed elections</li>
                    <li><strong>Export Options:</strong> Download results in JSON, CSV, or XML format</li>
                    <li><strong>Statistics:</strong> Voter turnout, timeline analysis, and precinct breakdowns</li>
                    <li><strong>Winner Declarations:</strong> Automatic winner identification</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($results): ?>
        <script>
            // Auto-refresh for active elections
            <?php if ($results['election_info']['status'] === 'active'): ?>
                setTimeout(function() {
                    window.location.reload();
                }, 30000); // Refresh every 30 seconds for active elections
            <?php endif; ?>
        </script>
    <?php endif; ?>
</body>

</html>