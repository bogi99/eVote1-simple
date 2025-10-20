<?php
require_once __DIR__ . '/../config/bootstrap.php';

use Bogi\EvoteSimple\VotingBooth\VotingBooth;

session_start();

// Check if voter is verified
if (!isset($_SESSION['voter_verified']) || !$_SESSION['voter_verified']) {
    header("Location: vote.php");
    exit;
}

$votingBooth = new VotingBooth();
$activeElections = $votingBooth->getActiveElections();
$message = '';
$messageType = '';

// Get the first active election (in a real system, might let user choose)
$currentElection = $activeElections[0] ?? null;

if (!$currentElection) {
    session_destroy();
    header("Location: vote.php");
    exit;
}

$ballot = $votingBooth->getElectionBallot($currentElection['id']);

if (!$ballot) {
    session_destroy();
    header("Location: vote.php");
    exit;
}

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cast_vote'])) {
    try {
        $votes = [
            'candidates' => $_POST['candidates'] ?? [],
            'questions' => $_POST['questions'] ?? []
        ];

        $result = $votingBooth->castVote(
            $_SESSION['voter_id'],
            $currentElection['id'],
            $votes,
            $_POST['booth_id'] ?? 'web-booth-' . session_id()
        );

        // Store result for confirmation page
        $_SESSION['vote_result'] = $result;
        session_destroy(); // Clear session after voting

        header("Location: vote-confirmation.php");
        exit;
    } catch (Exception $e) {
        $message = "Failed to cast vote: " . $e->getMessage();
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Ballot - <?= htmlspecialchars($ballot['name']) ?></title>

    <!-- JetBrains Mono Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="css/styles.css">

    <style>
        .ballot-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            border: 3px solid #000;
            background: white;
        }

        .ballot-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .voter-info {
            background: #f8f9fa;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .ballot-section {
            border: 2px solid #333;
            margin: 20px 0;
            padding: 20px;
            border-radius: 5px;
        }

        .candidate-option {
            display: flex;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .candidate-option:hover {
            background: #f0f8ff;
            border-color: #007acc;
        }

        .candidate-option.selected {
            background: #e8f5e8;
            border-color: #28a745;
            border-width: 2px;
        }

        .candidate-radio {
            margin-right: 15px;
            transform: scale(1.5);
        }

        .candidate-info {
            flex-grow: 1;
        }

        .question-option {
            display: flex;
            align-items: center;
            padding: 10px;
            margin: 5px 0;
        }

        .cast-vote-section {
            background: #fff3cd;
            border: 3px solid #ffc107;
            padding: 30px;
            margin: 30px 0;
            border-radius: 10px;
            text-align: center;
        }

        .cast-vote-button {
            background: #dc3545 !important;
            font-size: 24px !important;
            padding: 20px 50px !important;
            font-weight: bold;
        }

        .cast-vote-button:hover {
            background: #c82333 !important;
        }
    </style>
</head>

<body>
    <div class="ballot-container">
        <div class="ballot-header">
            <h1>🗳️ OFFICIAL BALLOT</h1>
            <h2><?= htmlspecialchars($ballot['name']) ?></h2>
            <p><strong>Election Date:</strong> <?= $ballot['election_date'] ?></p>
            <p><strong>Voting Period:</strong> <?= $ballot['start_time'] ?> - <?= $ballot['end_time'] ?></p>
        </div>

        <div class="voter-info">
            <strong>Voter:</strong> <?= htmlspecialchars($_SESSION['voter_name']) ?> |
            <strong>Precinct:</strong> <?= htmlspecialchars($_SESSION['precinct_name']) ?> |
            <strong>Time:</strong> <?= date('H:i:s') ?>
        </div>

        <?php if ($message): ?>
            <div class="<?= $messageType === 'success' ? 'success-box' : 'error-box' ?>">
                <p><?= htmlspecialchars($message) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="ballot-form">

            <!-- Candidate Sections -->
            <?php if (!empty($ballot['candidates_by_position'])): ?>
                <?php foreach ($ballot['candidates_by_position'] as $position => $candidates): ?>
                    <div class="ballot-section">
                        <h3>🏛️ <?= htmlspecialchars($position) ?></h3>
                        <p><em>Vote for ONE candidate</em></p>

                        <?php foreach ($candidates as $candidate): ?>
                            <div class="candidate-option" onclick="selectCandidate('<?= $position ?>', <?= $candidate['id'] ?>)">
                                <input type="radio"
                                    name="candidates[<?= htmlspecialchars($position) ?>]"
                                    value="<?= $candidate['id'] ?>"
                                    id="candidate_<?= $candidate['id'] ?>"
                                    class="candidate-radio">

                                <div class="candidate-info">
                                    <h4><?= htmlspecialchars($candidate['name']) ?></h4>
                                    <?php if ($candidate['party']): ?>
                                        <p><strong>Party:</strong> <?= htmlspecialchars($candidate['party']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($candidate['bio']): ?>
                                        <p><?= htmlspecialchars(substr($candidate['bio'], 0, 150)) ?><?= strlen($candidate['bio']) > 150 ? '...' : '' ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Ballot Questions -->
            <?php if (!empty($ballot['ballot_questions'])): ?>
                <?php foreach ($ballot['ballot_questions'] as $question): ?>
                    <div class="ballot-section">
                        <h3>❓ Ballot Question <?= $question['ballot_order'] + 1 ?></h3>
                        <p><strong><?= htmlspecialchars($question['question_text']) ?></strong></p>
                        <p><em>Choose one option</em></p>

                        <?php if ($question['question_type'] === 'yes_no'): ?>
                            <div class="question-option">
                                <input type="radio"
                                    name="questions[<?= $question['id'] ?>]"
                                    value="YES"
                                    id="question_<?= $question['id'] ?>_yes">
                                <label for="question_<?= $question['id'] ?>_yes">✅ YES</label>
                            </div>
                            <div class="question-option">
                                <input type="radio"
                                    name="questions[<?= $question['id'] ?>]"
                                    value="NO"
                                    id="question_<?= $question['id'] ?>_no">
                                <label for="question_<?= $question['id'] ?>_no">❌ NO</label>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="cast-vote-section">
                <h3>⚠️ IMPORTANT - REVIEW YOUR SELECTIONS</h3>
                <p>Once you cast your ballot, your choices cannot be changed.</p>
                <p>Please review all selections carefully before proceeding.</p>

                <div style="margin: 20px 0;">
                    <label>
                        <input type="checkbox" id="confirm-vote" required>
                        I have reviewed my selections and wish to cast my ballot
                    </label>
                </div>

                <button type="submit" name="cast_vote" class="cast-vote-button" id="cast-button" disabled>
                    🗳️ CAST BALLOT
                </button>
            </div>

            <input type="hidden" name="booth_id" value="web-booth-<?= session_id() ?>">
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="vote.php" style="color: #dc3545;">
                ← Exit Voting Booth (Your vote will NOT be saved)
            </a>
        </div>
    </div>

    <script>
        function selectCandidate(position, candidateId) {
            // Remove selected class from all candidates in this position
            document.querySelectorAll(`input[name="candidates[${position}]"]`).forEach(input => {
                input.closest('.candidate-option').classList.remove('selected');
            });

            // Select the clicked candidate
            const radio = document.getElementById(`candidate_${candidateId}`);
            radio.checked = true;
            radio.closest('.candidate-option').classList.add('selected');

            updateCastButton();
        }

        // Enable cast button when confirmation is checked
        document.getElementById('confirm-vote').addEventListener('change', function() {
            updateCastButton();
        });

        function updateCastButton() {
            const confirmChecked = document.getElementById('confirm-vote').checked;
            const castButton = document.getElementById('cast-button');

            castButton.disabled = !confirmChecked;
            castButton.style.opacity = confirmChecked ? '1' : '0.5';
        }

        // Prevent accidental navigation
        window.addEventListener('beforeunload', function(e) {
            if (!document.getElementById('cast-button').disabled) {
                e.preventDefault();
                e.returnValue = 'You have not cast your ballot yet. Are you sure you want to leave?';
            }
        });

        // Add selection styling to radio buttons
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.name.startsWith('candidates')) {
                    document.querySelectorAll(`input[name="${this.name}"]`).forEach(input => {
                        input.closest('.candidate-option').classList.remove('selected');
                    });
                    this.closest('.candidate-option').classList.add('selected');
                }
            });
        });
    </script>
</body>

</html>