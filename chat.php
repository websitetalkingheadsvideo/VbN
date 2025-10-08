<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - VbN Character Creator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .user-info {
            font-size: 14px;
            opacity: 0.8;
            margin-top: 5px;
        }
        .content {
            padding: 20px;
            min-height: 400px;
        }
        .chat-placeholder {
            text-align: center;
            color: #666;
            font-size: 18px;
            margin-top: 100px;
        }
        .navigation {
            padding: 20px;
            background: #ecf0f1;
            text-align: center;
        }
        .nav-link {
            display: inline-block;
            margin: 0 10px;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .nav-link:hover {
            background: #2980b9;
        }
        .nav-link.dashboard {
            background: #27ae60;
        }
        .nav-link.dashboard:hover {
            background: #229954;
        }
        .character-selection {
            margin-bottom: 30px;
        }
        .character-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .character-card {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            background: #f9f9f9;
        }
        .character-card:hover {
            border-color: #3498db;
            background: #f0f8ff;
        }
        .character-card.selected {
            border-color: #27ae60;
            background: #e8f5e8;
        }
        .character-name {
            font-weight: bold;
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .character-details {
            font-size: 14px;
            color: #666;
        }
        .character-details span {
            display: block;
            margin: 2px 0;
        }
        .selected-character {
            background: #e8f5e8;
            border: 2px solid #27ae60;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .character-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        .info-item {
            background: white;
            padding: 8px;
            border-radius: 4px;
            font-size: 14px;
        }
        .info-label {
            font-weight: bold;
            color: #2c3e50;
        }
        .no-characters {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 40px;
        }
        .load-characters-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 10px 0;
        }
        .load-characters-btn:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💬 Chat</h1>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! 
                (<?php echo htmlspecialchars($_SESSION['role']); ?>)
            </div>
        </div>
        
        <div class="content">
            <div class="character-selection">
                <h3>Select Character for Chat</h3>
                <div class="character-list" id="characterList">
                    <p>Loading your characters...</p>
                </div>
                <div class="selected-character" id="selectedCharacter" style="display: none;">
                    <h4>Selected Character:</h4>
                    <div class="character-info" id="characterInfo"></div>
                </div>
            </div>
            
            <div class="chat-interface" id="chatInterface" style="display: none;">
                <div class="chat-placeholder">
                    <h2>Chat System</h2>
                    <p>Chat as: <span id="chatCharacterName"></span></p>
                    <p>This is a placeholder for the chat functionality.</p>
                    <p>Future features may include:</p>
                    <ul style="text-align: left; display: inline-block;">
                        <li>Real-time messaging</li>
                        <li>Character roleplay channels</li>
                        <li>Game master communications</li>
                        <li>Player discussions</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="navigation">
            <a href="dashboard.php" class="nav-link dashboard">🏠 Dashboard</a>
            <a href="lotn_char_create.php" class="nav-link">⚜ Character Creator</a>
            <a href="logout.php" class="nav-link">🚪 Logout</a>
        </div>
    </div>

    <script>
        let selectedCharacter = null;
        let userCharacters = [];

        // Load user's characters when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadUserCharacters();
        });

        async function loadUserCharacters() {
            try {
                const response = await fetch('api_get_characters.php');
                const data = await response.json();
                
                if (data.success) {
                    userCharacters = data.characters;
                    displayCharacters(userCharacters);
                } else {
                    document.getElementById('characterList').innerHTML = 
                        '<div class="no-characters">No characters found. <a href="lotn_char_create.php">Create your first character</a></div>';
                }
            } catch (error) {
                console.error('Error loading characters:', error);
                document.getElementById('characterList').innerHTML = 
                    '<div class="no-characters">Error loading characters. Please try again.</div>';
            }
        }

        function displayCharacters(characters) {
            const characterList = document.getElementById('characterList');
            
            if (characters.length === 0) {
                characterList.innerHTML = 
                    '<div class="no-characters">No characters found. <a href="lotn_char_create.php">Create your first character</a></div>';
                return;
            }

            characterList.innerHTML = characters.map(character => `
                <div class="character-card" onclick="selectCharacter(${character.id})">
                    <div class="character-name">${character.character_name}</div>
                    <div class="character-details">
                        <span><strong>Clan:</strong> ${character.clan}</span>
                        <span><strong>Generation:</strong> ${character.generation}</span>
                        <span><strong>Concept:</strong> ${character.concept}</span>
                        <span><strong>Nature:</strong> ${character.nature}</span>
                    </div>
                </div>
            `).join('');
        }

        function selectCharacter(characterId) {
            // Remove previous selection
            document.querySelectorAll('.character-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Add selection to clicked card
            event.target.closest('.character-card').classList.add('selected');

            // Find the selected character
            selectedCharacter = userCharacters.find(char => char.id == characterId);
            
            if (selectedCharacter) {
                // Show selected character info
                document.getElementById('selectedCharacter').style.display = 'block';
                document.getElementById('characterInfo').innerHTML = `
                    <div class="info-item">
                        <div class="info-label">Name:</div>
                        <div>${selectedCharacter.character_name}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Clan:</div>
                        <div>${selectedCharacter.clan}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Generation:</div>
                        <div>${selectedCharacter.generation}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Concept:</div>
                        <div>${selectedCharacter.concept}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nature:</div>
                        <div>${selectedCharacter.nature}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Demeanor:</div>
                        <div>${selectedCharacter.demeanor}</div>
                    </div>
                `;

                // Show chat interface
                document.getElementById('chatInterface').style.display = 'block';
                document.getElementById('chatCharacterName').textContent = selectedCharacter.character_name;

                // Scroll to chat interface
                document.getElementById('chatInterface').scrollIntoView({ behavior: 'smooth' });
            }
        }
    </script>
</body>
</html>
