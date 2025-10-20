<?php
require_once __DIR__ . '/../config/bootstrap.php'; // Include your Twig bootstrap file
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Voting System</title>

    <!-- JetBrains Mono Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <h1>🗳️ eVote Simple - Open Source Voting System</h1>
    <p>A secure, transparent, and modular voting platform</p>

    <div style="text-align: center; margin: 20px 0; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <a href="vote.php" class="submit-button" style="text-decoration: none; display: inline-block; background: #dc3545;">
            🗳️ Vote Now
        </a>
        <a href="register.php" class="submit-button" style="text-decoration: none; display: inline-block;">
            📝 Register to Vote
        </a>
        <a href="admin.php" class="submit-button" style="text-decoration: none; display: inline-block; background: #17a2b8;">
            🛠️ Admin Dashboard
        </a>
        <a href="results.php" class="submit-button" style="text-decoration: none; display: inline-block; background: #28a745;">
            📊 View Results
        </a>
    </div>

    <div class="info-box">
        <h3>🏗️ System Architecture</h3>
        <p>Four independent modules working together:</p>
        <ul>
            <li><strong>Registration:</strong> Voter registration and ID management</li>
            <li><strong>Configurator:</strong> Election setup, candidates, precincts</li>
            <li><strong>Voting Booth:</strong> Secure voting interface</li>
            <li><strong>Tabulator:</strong> Results aggregation and reporting</li>
        </ul>
    </div>

    <div class="success-box">
        <h3>🎉 System Complete!</h3>
        <p>All four core modules successfully implemented:</p>
        <ul>
            <li>✅ <strong>Database Schema:</strong> 11 tables with security & audit features</li>
            <li>✅ <strong>Registration Module:</strong> Voter registration with unique IDs</li>
            <li>✅ <strong>Configurator Module:</strong> Election setup, candidate management</li>
            <li>✅ <strong>Voting Booth Module:</strong> Complete - secure voting interface with confirmation</li>
            <li>✅ <strong>Tabulator Module:</strong> Complete - results aggregation, API & reporting</li>
        </ul>

        <div style="margin-top: 15px; text-align: center;">
            <a href="setup-test.php" class="submit-button" style="text-decoration: none; display: inline-block; background: #28a745; font-size: 14px; padding: 8px 16px;">
                🧪 Setup Test Election
            </a>
        </div>
    </div>

    <div class="info-box">
        <h3>💻 Technical Stack</h3>
        <p><strong>PHP Version:</strong> <code><?php echo PHP_VERSION; ?></code></p>
        <p><strong>Database:</strong> <code>MySQL 8.0</code></p>
        <p><strong>Environment:</strong> <code>Docker + Laravel Sail</code></p>
        <p><strong>Font:</strong> <code>JetBrains Mono</code> 🎨</p>
        <p><strong>Server Time:</strong> <code><?php echo date('Y-m-d H:i:s'); ?></code></p>
    </div>
</body>

</html>