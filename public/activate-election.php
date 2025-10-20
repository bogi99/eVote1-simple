<?php
require_once __DIR__ . '/../config/bootstrap.php';

use Bogi\EvoteSimple\Configurator\ElectionConfigurator;

$electionId = $_GET['id'] ?? null;
if (!$electionId) {
    header("Location: admin.php");
    exit;
}

$configurator = new ElectionConfigurator();

// Handle activation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = $configurator->updateElectionStatus($electionId, 'active');
        if ($result) {
            header("Location: admin.php?message=Election activated successfully");
        } else {
            header("Location: admin.php?error=Failed to activate election");
        }
        exit;
    } catch (Exception $e) {
        header("Location: admin.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}

$election = $configurator->getElectionWithDetails($electionId);
if (!$election) {
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activate Election - <?= htmlspecialchars($election['name']) ?></title>

    <!-- JetBrains Mono Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <h1>⚠️ Activate Election</h1>
    <h2><?= htmlspecialchars($election['name']) ?></h2>
    <p><a href="admin.php">← Back to Admin Dashboard</a></p>

    <div class="error-box" style="background: #fff3cd; color: #856404; border-color: #ffc107;">
        <h3>⚠️ Confirm Election Activation</h3>
        <p>You are about to activate this election. Once activated:</p>
        <ul>
            <li>Voters will be able to cast their votes</li>
            <li>Election settings cannot be easily changed</li>
            <li>Adding/removing candidates will be restricted</li>
            <li>The election will appear in the voting booth</li>
        </ul>
    </div>

    <div class="info-box">
        <h3>📋 Election Summary</h3>
        <p><strong>Name:</strong> <?= htmlspecialchars($election['name']) ?></p>
        <p><strong>Description:</strong> <?= htmlspecialchars($election['description']) ?></p>
        <p><strong>Date:</strong> <?= $election['election_date'] ?></p>
        <p><strong>Time:</strong> <?= $election['start_time'] ?> - <?= $election['end_time'] ?></p>
        <p><strong>Current Status:</strong> <?= strtoupper($election['status']) ?></p>
        <p><strong>Candidates:</strong> <?= count($election['candidates']) ?></p>
        <p><strong>Ballot Questions:</strong> <?= count($election['ballot_questions']) ?></p>
    </div>

    <?php if (count($election['candidates']) > 0): ?>
        <div class="success-box">
            <h3>✅ Ready to Activate</h3>
            <p>This election has candidates and can be activated.</p>

            <form method="POST" action="" style="margin-top: 20px;">
                <button type="submit" class="submit-button" style="background: #28a745;">
                    ▶️ Activate Election Now
                </button>
                <a href="admin.php" class="submit-button" style="background: #6c757d; text-decoration: none; margin-left: 10px;">
                    Cancel
                </a>
            </form>
        </div>
    <?php else: ?>
        <div class="error-box">
            <h3>❌ Cannot Activate</h3>
            <p>This election cannot be activated because it has no candidates.</p>
            <p><a href="manage-candidates.php?election_id=<?= $election['id'] ?>">Add candidates first</a></p>
        </div>
    <?php endif; ?>
</body>

</html>