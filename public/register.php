<?php
require_once __DIR__ . '/../config/bootstrap.php';

use Bogi\EvoteSimple\Registration\VoterRegistration;

$registration = new VoterRegistration();
$precincts = $registration->getPrecincts();
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = $registration->registerVoter($_POST);
        $message = "Registration successful! Your Voter ID is: " . $result['voter_id'];
        $messageType = 'success';
    } catch (Exception $e) {
        $message = "Registration failed: " . $e->getMessage();
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Registration - eVote Simple</title>

    <!-- JetBrains Mono Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <h1>🗳️ Voter Registration</h1>
    <p><a href="index.php">← Back to Home</a></p>

    <?php if ($message): ?>
        <div class="<?= $messageType === 'success' ? 'success-box' : 'error-box' ?>">
            <p><?= htmlspecialchars($message) ?></p>
        </div>
    <?php endif; ?>

    <div class="info-box">
        <h3>📝 Register to Vote</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" name="first_name" id="first_name" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" name="last_name" id="last_name" required>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" name="phone" id="phone">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea name="address" id="address" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label for="date_of_birth">Date of Birth *</label>
                <input type="date" name="date_of_birth" id="date_of_birth" required>
            </div>

            <div class="form-group">
                <label for="precinct_id">Precinct *</label>
                <select name="precinct_id" id="precinct_id" required>
                    <option value="">Select a precinct</option>
                    <?php foreach ($precincts as $precinct): ?>
                        <option value="<?= $precinct['id'] ?>">
                            <?= htmlspecialchars($precinct['name']) ?> - <?= htmlspecialchars($precinct['address']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="submit-button">Register to Vote</button>
        </form>
    </div>

    <div class="info-box">
        <h3>ℹ️ Registration Information</h3>
        <ul>
            <li>You must be 18 years or older to register</li>
            <li>Your Voter ID will be generated automatically</li>
            <li>Keep your Voter ID safe - you'll need it to vote</li>
            <li>Registration is required before each election</li>
        </ul>
    </div>
</body>

</html>