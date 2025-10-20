<?php
require_once __DIR__ . '/../config/bootstrap.php';

use Bogi\EvoteSimple\VotingBooth\VotingBooth;

$votingBooth = new VotingBooth();
$activeElections = $votingBooth->getActiveElections();
$message = '';
$messageType = '';

// Handle voter ID verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voter_id'])) {
    $voterId = trim($_POST['voter_id']);

    if (empty($voterId)) {
        $message = "Please enter your Voter ID";
        $messageType = 'error';
    } else {
        $voter = $votingBooth->verifyVoter($voterId);

        if ($voter && $voter['can_vote']) {
            // Store voter ID in session and redirect to ballot
            session_start();
            $_SESSION['voter_id'] = $voterId;
            $_SESSION['voter_verified'] = true;
            $_SESSION['voter_name'] = $voter['first_name'] . ' ' . $voter['last_name'];
            $_SESSION['precinct_name'] = $voter['precinct_name'];

            header("Location: ballot.php");
            exit;
        } else {
            $message = $voter ? $voter['reason'] : "Voter ID not found or invalid";
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Booth - eVote Simple</title>

    <!-- JetBrains Mono Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="css/styles.css">

    <style>
        .voting-booth-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            border: 3px solid #007acc;
            border-radius: 10px;
            background: #f8f9fa;
        }

        .election-status {
            text-align: center;
            margin-bottom: 30px;
        }

        .voter-id-input {
            font-size: 24px !important;
            text-align: center;
            letter-spacing: 2px;
            padding: 20px !important;
            border: 3px solid #28a745 !important;
        }

        .vote-button {
            font-size: 20px !important;
            padding: 20px 40px !important;
            background: #28a745 !important;
            width: 100%;
        }

        .security-notice {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="voting-booth-container">
        <h1 style="text-align: center;">🗳️ Secure Voting Booth</h1>

        <div class="election-status">
            <?php if (empty($activeElections)): ?>
                <div class="error-box">
                    <h3>❌ No Active Elections</h3>
                    <p>There are currently no elections available for voting.</p>
                    <p>Elections must be active and within voting hours.</p>
                </div>
            <?php else: ?>
                <div class="success-box">
                    <h3>✅ Elections Available</h3>
                    <?php foreach ($activeElections as $election): ?>
                        <div style="margin: 10px 0;">
                            <strong><?= htmlspecialchars($election['name']) ?></strong><br>
                            <small>Voting: <?= $election['start_time'] ?> - <?= $election['end_time'] ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($activeElections)): ?>
            <?php if ($message): ?>
                <div class="<?= $messageType === 'success' ? 'success-box' : 'error-box' ?>">
                    <p style="text-align: center;"><?= htmlspecialchars($message) ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="voter_id" style="text-align: center; display: block; font-size: 18px; margin-bottom: 15px;">
                        Enter Your Voter ID
                    </label>
                    <input type="text" name="voter_id" id="voter_id"
                        class="voter-id-input"
                        placeholder="V2025XXXXXX"
                        maxlength="11"
                        pattern="V[0-9]{10}"
                        title="Voter ID format: V followed by 10 digits"
                        required autofocus>
                </div>

                <button type="submit" class="vote-button">
                    🔐 Verify ID & Vote
                </button>
            </form>

            <div class="security-notice">
                <h4>🔒 Security & Privacy Notice</h4>
                <ul style="text-align: left; display: inline-block;">
                    <li>Your vote is secret and anonymous</li>
                    <li>Voter ID is hashed for privacy protection</li>
                    <li>All voting actions are logged for audit</li>
                    <li>You can only vote once per election</li>
                    <li>Report any irregularities immediately</li>
                </ul>
            </div>
        <?php else: ?>
            <div class="info-box">
                <h3>ℹ️ Voting Information</h3>
                <p>Voting is only available:</p>
                <ul>
                    <li>During active elections</li>
                    <li>Within the scheduled voting hours</li>
                    <li>On the designated election date</li>
                    <li>With a valid, registered Voter ID</li>
                </ul>
                <p><a href="index.php">← Return to Home</a></p>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" style="color: #007acc; text-decoration: none;">
                🏠 Return to Home Page
            </a>
        </div>
    </div>

    <script>
        // Auto-format Voter ID input
        document.getElementById('voter_id').addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase();

            // Remove any non-alphanumeric characters except V
            value = value.replace(/[^V0-9]/g, '');

            // Ensure it starts with V
            if (value.length > 0 && !value.startsWith('V')) {
                value = 'V' + value.replace(/V/g, '');
            }

            // Limit to V + 10 digits
            if (value.length > 11) {
                value = value.substring(0, 11);
            }

            e.target.value = value;
        });

        // Auto-submit on Enter
        document.getElementById('voter_id').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.target.closest('form').submit();
            }
        });
    </script>
</body>

</html>