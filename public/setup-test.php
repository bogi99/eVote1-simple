<?php
require_once __DIR__ . '/../config/bootstrap.php';

use Bogi\EvoteSimple\Configurator\ElectionConfigurator;
use Bogi\EvoteSimple\Registration\VoterRegistration;

try {
    $configurator = new ElectionConfigurator();
    $registration = new VoterRegistration();

    echo "<h1>🧪 Setting up Test Election & Voter</h1>";

    // Create a test election for today
    $electionData = [
        'name' => 'Test Election - ' . date('Y-m-d'),
        'description' => 'Test election for voting booth demonstration',
        'election_date' => date('Y-m-d'), // Today
        'start_time' => '00:00:00',
        'end_time' => '23:59:59'
    ];

    $election = $configurator->createElection($electionData);
    $electionId = $election['election_id'];

    echo "<p>✅ Election created with ID: {$electionId}</p>";

    // Add test candidates
    $candidates = [
        [
            'election_id' => $electionId,
            'name' => 'Alice Johnson',
            'position' => 'Mayor',
            'party' => 'Progressive Party',
            'bio' => 'Community leader with 15 years of public service experience.',
            'ballot_order' => 1
        ],
        [
            'election_id' => $electionId,
            'name' => 'Bob Smith',
            'position' => 'Mayor',
            'party' => 'Citizens Alliance',
            'bio' => 'Local business owner focused on economic development.',
            'ballot_order' => 2
        ],
        [
            'election_id' => $electionId,
            'name' => 'Carol Davis',
            'position' => 'City Council',
            'party' => 'Independent',
            'bio' => 'Environmental advocate and urban planning expert.',
            'ballot_order' => 1
        ]
    ];

    foreach ($candidates as $candidate) {
        $result = $configurator->addCandidate($candidate);
        echo "<p>✅ Added candidate: {$candidate['name']} for {$candidate['position']}</p>";
    }

    // Add a ballot question
    $questionData = [
        'election_id' => $electionId,
        'question_text' => 'Should the city invest in renewable energy infrastructure?',
        'question_type' => 'yes_no',
        'ballot_order' => 1
    ];

    $configurator->addBallotQuestion($questionData);
    echo "<p>✅ Added ballot question</p>";

    // Activate the election
    $configurator->updateElectionStatus($electionId, 'active');
    echo "<p>✅ Election activated</p>";

    // Create a test voter
    $voterData = [
        'first_name' => 'Test',
        'last_name' => 'Voter',
        'email' => 'test.voter@example.com',
        'date_of_birth' => '1990-01-01',
        'precinct_id' => 1,
        'address' => '123 Test Street'
    ];

    $voter = $registration->registerVoter($voterData);
    $testVoterId = $voter['voter_id'];

    echo "<p>✅ Test voter created with ID: <strong>{$testVoterId}</strong></p>";

    echo "<div style='background: #d4edda; padding: 20px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>🎉 Test Setup Complete!</h3>";
    echo "<p>You can now test the voting booth with:</p>";
    echo "<ul>";
    echo "<li><strong>Voter ID:</strong> {$testVoterId}</li>";
    echo "<li><strong>Election:</strong> {$electionData['name']}</li>";
    echo "<li><strong>Candidates:</strong> 3 candidates across 2 positions</li>";
    echo "<li><strong>Ballot Question:</strong> 1 yes/no question</li>";
    echo "</ul>";
    echo "<p><a href='vote.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🗳️ Go to Voting Booth</a></p>";
    echo "</div>";

    echo "<p><a href='index.php'>← Back to Home</a></p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p><a href='index.php'>← Back to Home</a></p>";
}
