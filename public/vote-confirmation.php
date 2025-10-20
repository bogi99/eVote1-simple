<?php
require_once __DIR__ . '/../config/bootstrap.php';

// Check if we have vote result (from previous session)
if (!isset($_GET['receipt']) && !isset($_SESSION['vote_result'])) {
    header("Location: vote.php");
    exit;
}

// Get vote result either from session or URL parameter
if (isset($_SESSION['vote_result'])) {
    $voteResult = $_SESSION['vote_result'];
    $receiptHash = $voteResult['receipt_hash'];
    unset($_SESSION['vote_result']); // Clear it after use
} else {
    $receiptHash = $_GET['receipt'] ?? 'unknown';
    $voteResult = [
        'success' => true,
        'message' => 'Vote verified',
        'votes_count' => 'N/A',
        'receipt_hash' => $receiptHash
    ];
}

session_destroy(); // Ensure clean logout
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote Confirmation - eVote Simple</title>

    <!-- JetBrains Mono Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="css/styles.css">

    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            text-align: center;
        }

        .success-banner {
            background: #d4edda;
            border: 3px solid #28a745;
            border-radius: 10px;
            padding: 30px;
            margin: 30px 0;
        }

        .receipt-box {
            background: #f8f9fa;
            border: 2px dashed #6c757d;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
        }

        .receipt-hash {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #007acc;
            margin: 10px 0;
        }

        .security-info {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .print-button {
            background: #17a2b8;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 16px;
            cursor: pointer;
            margin: 10px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                font-size: 12pt;
            }

            .confirmation-container {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="confirmation-container">
        <div class="success-banner">
            <h1>✅ VOTE CAST SUCCESSFULLY</h1>
            <h2>Thank you for voting!</h2>
            <p style="font-size: 18px; margin: 20px 0;">
                Your ballot has been securely recorded and your voice has been heard.
            </p>
        </div>

        <div class="receipt-box">
            <h3>📋 VOTING RECEIPT</h3>
            <p><strong>Date:</strong> <?= date('Y-m-d') ?></p>
            <p><strong>Time:</strong> <?= date('H:i:s') ?></p>
            <p><strong>Votes Cast:</strong> <?= $voteResult['votes_count'] ?></p>

            <div class="receipt-hash">
                Receipt: <?= htmlspecialchars($receiptHash) ?>
            </div>

            <p><small>Keep this receipt for your records</small></p>
        </div>

        <div class="security-info">
            <h3>🔒 Security Confirmation</h3>
            <ul style="text-align: left; display: inline-block;">
                <li>✅ Your vote has been encrypted and stored</li>
                <li>✅ Your identity remains completely anonymous</li>
                <li>✅ All voting actions have been logged for audit</li>
                <li>✅ Your voter record has been marked as voted</li>
                <li>✅ You cannot vote again in this election</li>
            </ul>
        </div>

        <div class="info-box">
            <h3>ℹ️ What Happens Next?</h3>
            <ul style="text-align: left; display: inline-block;">
                <li>Votes will be tabulated after polls close</li>
                <li>Results will be available when counting is complete</li>
                <li>All votes undergo security verification</li>
                <li>Audit trails ensure election integrity</li>
                <li>Official results will be certified by election officials</li>
            </ul>
        </div>

        <div class="no-print" style="margin: 30px 0;">
            <button onclick="window.print()" class="print-button">
                🖨️ Print Receipt
            </button>

            <button onclick="copyReceipt()" class="print-button" style="background: #28a745;">
                📋 Copy Receipt Number
            </button>
        </div>

        <div class="no-print" style="margin-top: 40px;">
            <a href="index.php" class="submit-button" style="text-decoration: none;">
                🏠 Return to Home Page
            </a>
        </div>

        <div style="margin-top: 30px; font-size: 14px; color: #6c757d;">
            <p>This voting session has been securely logged out.</p>
            <p>eVote Simple - Secure Open Source Voting System</p>
        </div>
    </div>

    <script>
        function copyReceipt() {
            const receiptText = "Vote Receipt: <?= htmlspecialchars($receiptHash) ?> - Date: <?= date('Y-m-d H:i:s') ?>";

            if (navigator.clipboard) {
                navigator.clipboard.writeText(receiptText).then(function() {
                    alert('Receipt number copied to clipboard!');
                }).catch(function() {
                    fallbackCopy(receiptText);
                });
            } else {
                fallbackCopy(receiptText);
            }
        }

        function fallbackCopy(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                alert('Receipt number copied!');
            } catch (err) {
                alert('Could not copy receipt. Please write it down manually.');
            }
            document.body.removeChild(textArea);
        }

        // Prevent back button after voting
        history.pushState(null, null, window.location.href);
        window.addEventListener('popstate', function() {
            history.pushState(null, null, window.location.href);
            alert('You have already completed voting. Please use the Home button to navigate.');
        });

        // Auto-focus print button for accessibility
        window.addEventListener('load', function() {
            document.querySelector('.print-button').focus();
        });
    </script>
</body>

</html>