<?php
/**
 * NPC Tracker Setup - Quick Link Page
 * Run this to set up the NPC tracker database table
 */

session_start();
require_once '../includes/connect.php';

// Require admin access
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

define('LOTN_VERSION', '0.2.0');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NPC Tracker Setup - Valley by Night</title>
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/header.css">
    <style>
        .setup-container {
            max-width: 800px;
            margin: 60px auto;
            padding: 40px;
            background: rgba(26, 15, 15, 0.9);
            border: 2px solid var(--gold);
            border-radius: 8px;
            text-align: center;
        }

        .setup-container h1 {
            color: var(--gold);
            font-family: 'Cinzel', serif;
            margin-bottom: 20px;
        }

        .setup-container p {
            color: var(--light-text);
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .setup-button {
            display: inline-block;
            background: var(--gold);
            color: var(--blood-red);
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 1.1em;
            margin: 10px;
            transition: all 0.3s;
        }

        .setup-button:hover {
            background: var(--light-gold);
            transform: translateY(-2px);
        }

        .secondary-button {
            background: rgba(139, 115, 85, 0.3);
            color: var(--gold);
            border: 1px solid var(--gold);
        }

        .secondary-button:hover {
            background: rgba(139, 115, 85, 0.5);
        }

        .status-box {
            background: rgba(139, 115, 85, 0.2);
            padding: 20px;
            border-radius: 4px;
            margin: 30px 0;
        }

        .status-box h3 {
            color: var(--gold);
            margin-top: 0;
        }

        .button-group {
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="setup-container">
        <h1>📚 NPC Tracker Setup</h1>
        
        <p>
            Welcome to the NPC Tracker setup. This tool helps you track characters mentioned in backstories 
            that need full character sheets created.
        </p>

        <div class="status-box">
            <h3>⚙️ Step 1: Create Database Table</h3>
            <p>Click below to create the necessary database table (only needs to be done once):</p>
            <a href="../database/create_npc_tracker_table.php" class="setup-button">
                🔧 Run Database Setup
            </a>
        </div>

        <div class="status-box">
            <h3>✅ Step 2: Access the Tracker</h3>
            <p>Once the database is set up, you can access the tracker pages:</p>
            
            <div class="button-group">
                <a href="npc_tracker.php" class="setup-button secondary-button">
                    👁️ View NPCs
                </a>
                <a href="npc_tracker_submit.php" class="setup-button secondary-button">
                    ➕ Add New NPC
                </a>
            </div>
        </div>

        <div class="button-group">
            <a href="admin_panel.php" class="setup-button secondary-button">
                ← Back to Admin Panel
            </a>
        </div>
    </div>
</body>
</html>

