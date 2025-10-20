<?php
require_once __DIR__ . '/../config/bootstrap.php';

use Bogi\EvoteSimple\Configurator\ElectionConfigurator;

$configurator = new ElectionConfigurator();
$message = '';
$messageType = '';

$electionId = $_GET['election_id'] ?? null;
if (!$electionId) {
    header("Location: admin.php");
    exit;
}

$election = $configurator->getElectionWithDetails($electionId);
if (!$election) {
    header("Location: admin.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $_POST['election_id'] = $electionId;
        $result = $configurator->addCandidate($_POST);
        $message = "Candidate added successfully!";
        $messageType = 'success';

        // Refresh election data
        $election = $configurator->getElectionWithDetails($electionId);
    } catch (Exception $e) {
        $message = "Failed to add candidate: " . $e->getMessage();
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates - <?= htmlspecialchars($election['name']) ?></title>

    <!-- JetBrains Mono Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <h1>👥 Manage Candidates</h1>
    <h2><?= htmlspecialchars($election['name']) ?></h2>
    <p><a href="admin.php">← Back to Admin Dashboard</a></p>

    <?php if ($message): ?>
        <div class="<?= $messageType === 'success' ? 'success-box' : 'error-box' ?>">
            <p><?= htmlspecialchars($message) ?></p>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="info-box">
            <h3>➕ Add New Candidate</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Candidate Name *</label>
                    <input type="text" name="name" id="name" required
                        placeholder="e.g., John Smith">
                </div>

                <div class="form-group">
                    <label for="position">Position *</label>
                    <select name="position" id="position" required>
                        <option value="">Select position</option>
                        <option value="President">President</option>
                        <option value="Vice President">Vice President</option>
                        <option value="Mayor">Mayor</option>
                        <option value="City Council">City Council</option>
                        <option value="School Board">School Board</option>
                        <option value="Judge">Judge</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="party">Political Party</label>
                    <input type="text" name="party" id="party"
                        placeholder="e.g., Democratic Party, Independent">
                </div>

                <div class="form-group">
                    <label for="bio">Biography</label>
                    <textarea name="bio" id="bio" rows="4"
                        placeholder="Brief candidate biography..."></textarea>
                </div>

                <div class="form-group">
                    <label for="ballot_order">Ballot Order</label>
                    <input type="number" name="ballot_order" id="ballot_order"
                        value="0" min="0" max="999"
                        title="Order on the ballot (0 = automatic)">
                </div>

                <button type="submit" class="submit-button">Add Candidate</button>
            </form>
        </div>

        <div class="info-box">
            <h3>📝 Current Candidates</h3>
            <?php if (empty($election['candidates'])): ?>
                <p>No candidates added yet. Add the first candidate using the form.</p>
            <?php else: ?>
                <div style="max-height: 500px; overflow-y: auto;">
                    <?php foreach ($election['candidates'] as $candidate): ?>
                        <div style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;">
                            <h4><?= htmlspecialchars($candidate['name']) ?></h4>
                            <p><strong>Position:</strong> <?= htmlspecialchars($candidate['position']) ?></p>
                            <?php if ($candidate['party']): ?>
                                <p><strong>Party:</strong> <?= htmlspecialchars($candidate['party']) ?></p>
                            <?php endif; ?>
                            <?php if ($candidate['bio']): ?>
                                <p><strong>Bio:</strong> <?= htmlspecialchars(substr($candidate['bio'], 0, 100)) ?><?= strlen($candidate['bio']) > 100 ? '...' : '' ?></p>
                            <?php endif; ?>
                            <p><strong>Ballot Order:</strong> <?= $candidate['ballot_order'] ?></p>
                            <p><small>Added: <?= $candidate['created_at'] ?></small></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: 15px; text-align: center;">
                    <p><strong>Total Candidates:</strong> <?= count($election['candidates']) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="success-box">
        <h3>🗳️ Election Status</h3>
        <p><strong>Current Status:</strong>
            <span style="background: <?= $election['status'] === 'active' ? '#28a745' : ($election['status'] === 'draft' ? '#ffc107' : '#6c757d') ?>; 
                  color: white; padding: 5px 10px; border-radius: 3px;">
                <?= strtoupper($election['status']) ?>
            </span>
        </p>
        <p><strong>Election Date:</strong> <?= $election['election_date'] ?>
            (<?= $election['start_time'] ?> - <?= $election['end_time'] ?>)</p>

        <?php if ($election['status'] === 'draft' && count($election['candidates']) > 0): ?>
            <div style="margin-top: 15px;">
                <a href="activate-election.php?id=<?= $election['id'] ?>"
                    class="submit-button"
                    style="background: #28a745; text-decoration: none;"
                    onclick="return confirm('Are you sure you want to activate this election? This will make it available for voting.')">
                    ▶️ Activate Election
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>