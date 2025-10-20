<?php
require_once __DIR__ . '/../config/bootstrap.php';

use Bogi\EvoteSimple\Configurator\ElectionConfigurator;

$configurator = new ElectionConfigurator();
$stats = $configurator->getSystemStats();
$elections = $configurator->getAllElections();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - eVote Simple</title>

    <!-- JetBrains Mono Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <h1>🛠️ Admin Dashboard</h1>
    <p><a href="index.php">← Back to Home</a></p>

    <div class="success-box">
        <h3>📊 System Statistics</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div><strong>Total Elections:</strong> <?= $stats['total_elections'] ?></div>
            <div><strong>Active Elections:</strong> <?= $stats['active_elections'] ?></div>
            <div><strong>Registered Voters:</strong> <?= $stats['total_voters'] ?></div>
            <div><strong>Votes Cast:</strong> <?= $stats['total_votes'] ?></div>
            <div><strong>Total Candidates:</strong> <?= $stats['total_candidates'] ?></div>
            <div><strong>Total Precincts:</strong> <?= $stats['total_precincts'] ?></div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <div class="info-box">
            <h3>🗳️ Quick Actions</h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <a href="create-election.php" class="submit-button" style="text-decoration: none; text-align: center;">
                    ➕ Create New Election
                </a>
                <a href="manage-precincts.php" class="submit-button" style="text-decoration: none; text-align: center; background: #17a2b8;">
                    🏢 Manage Precincts
                </a>
                <a href="tabulator.php" class="submit-button" style="text-decoration: none; text-align: center; background: #28a745;">
                    📊 Results & Tabulation
                </a>
                <a href="results.php" class="submit-button" style="text-decoration: none; text-align: center; background: #dc3545;">
                    📈 View Election Results
                </a>
                <a href="system-logs.php" class="submit-button" style="text-decoration: none; text-align: center; background: #ffc107; color: #212529;">
                    📋 View System Logs
                </a>
            </div>
        </div>

        <div class="info-box">
            <h3>🗳️ Elections</h3>
            <?php if (empty($elections)): ?>
                <p>No elections found. <a href="create-election.php">Create the first election</a></p>
            <?php else: ?>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php foreach ($elections as $election): ?>
                        <div style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;">
                            <h4><?= htmlspecialchars($election['name']) ?></h4>
                            <p><strong>Date:</strong> <?= $election['election_date'] ?>
                                (<?= $election['start_time'] ?> - <?= $election['end_time'] ?>)</p>
                            <p><strong>Status:</strong>
                                <span style="background: <?= $election['status'] === 'active' ? '#28a745' : ($election['status'] === 'draft' ? '#ffc107' : '#6c757d') ?>; 
                                      color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px;">
                                    <?= strtoupper($election['status']) ?>
                                </span>
                            </p>
                            <p><strong>Candidates:</strong> <?= $election['candidate_count'] ?></p>
                            <div style="margin-top: 10px;">
                                <a href="edit-election.php?id=<?= $election['id'] ?>"
                                    style="background: #007bff; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px; margin-right: 5px;">
                                    ✏️ Edit
                                </a>
                                <a href="manage-candidates.php?election_id=<?= $election['id'] ?>"
                                    style="background: #17a2b8; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px; margin-right: 5px;">
                                    👥 Candidates
                                </a>
                                <?php if ($election['status'] === 'draft'): ?>
                                    <a href="activate-election.php?id=<?= $election['id'] ?>"
                                        style="background: #28a745; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px;">
                                        ▶️ Activate
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="info-box">
        <h3>ℹ️ Admin Information</h3>
        <ul>
            <li><strong>Configurator Module:</strong> Manage elections, candidates, and system settings</li>
            <li><strong>Election Status:</strong> Draft → Active → Closed → Finalized</li>
            <li><strong>Security:</strong> All actions are logged in the audit trail</li>
            <li><strong>Access:</strong> Admin functions require proper authentication (to be implemented)</li>
        </ul>
    </div>
</body>

</html>