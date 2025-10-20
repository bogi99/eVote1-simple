<?php
require_once __DIR__ . '/../config/bootstrap.php';

use Bogi\EvoteSimple\Configurator\ElectionConfigurator;

$configurator = new ElectionConfigurator();
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = $configurator->createElection($_POST);
        $message = "Election created successfully! Election ID: " . $result['election_id'];
        $messageType = 'success';

        // Redirect to candidate management after creation
        header("Location: manage-candidates.php?election_id=" . $result['election_id']);
        exit;
    } catch (Exception $e) {
        $message = "Failed to create election: " . $e->getMessage();
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Election - eVote Simple</title>

    <!-- JetBrains Mono Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <h1>🗳️ Create New Election</h1>
    <p><a href="admin.php">← Back to Admin Dashboard</a></p>

    <?php if ($message): ?>
        <div class="<?= $messageType === 'success' ? 'success-box' : 'error-box' ?>">
            <p><?= htmlspecialchars($message) ?></p>
        </div>
    <?php endif; ?>

    <div class="info-box">
        <h3>📋 Election Details</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Election Name *</label>
                <input type="text" name="name" id="name" required
                    placeholder="e.g., 2025 General Election"
                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea name="description" id="description" rows="4" required
                    placeholder="Describe this election..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label for="election_date">Election Date *</label>
                    <input type="date" name="election_date" id="election_date" required
                        min="<?= date('Y-m-d') ?>"
                        value="<?= htmlspecialchars($_POST['election_date'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="start_time">Start Time *</label>
                    <input type="time" name="start_time" id="start_time" required
                        value="<?= htmlspecialchars($_POST['start_time'] ?? '08:00') ?>">
                </div>

                <div class="form-group">
                    <label for="end_time">End Time *</label>
                    <input type="time" name="end_time" id="end_time" required
                        value="<?= htmlspecialchars($_POST['end_time'] ?? '20:00') ?>">
                </div>
            </div>

            <button type="submit" class="submit-button">Create Election</button>
        </form>
    </div>

    <div class="info-box">
        <h3>ℹ️ Election Setup Process</h3>
        <ol>
            <li><strong>Create Election:</strong> Set basic details and schedule</li>
            <li><strong>Add Candidates:</strong> Register candidates for each position</li>
            <li><strong>Add Ballot Questions:</strong> Configure yes/no questions or referendums</li>
            <li><strong>Review & Test:</strong> Verify all settings are correct</li>
            <li><strong>Activate Election:</strong> Make it available for voting</li>
        </ol>

        <p><strong>Note:</strong> Elections start in "Draft" status and must be manually activated when ready.</p>
    </div>
</body>

</html>