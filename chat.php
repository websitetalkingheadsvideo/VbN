<?php
/**
 * Chat Room - Valley by Night
 * Character selection and chat interface
 */
define('LOTN_VERSION', '0.6.0');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include header
include 'includes/header.php';
?>

<style>
        .chat-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        .chat-content {
            padding: 20px;
            min-height: 400px;
        }
        .chat-placeholder {
            text-align: center;
            color: #b8a090;
            font-size: 18px;
            margin-top: 100px;
            padding: 40px;
            background: rgba(26, 15, 15, 0.3);
            border: 2px dashed rgba(139, 0, 0, 0.3);
            border-radius: 8px;
        }
        .chat-placeholder h2 {
            color: #f5e6d3;
            font-family: var(--font-title), 'Libre Baskerville', serif;
        }
        .chat-placeholder ul {
            color: #d4c4b0;
        }
        .character-selection {
            margin-bottom: 30px;
        }
        .character-selection h3 {
            font-family: var(--font-title), 'Libre Baskerville', serif;
            color: #f5e6d3;
            font-size: 1.6em;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #8B0000;
        }
        .character-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .character-card {
            background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%);
            border: 2px solid #8B0000;
            border-radius: 8px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(139, 0, 0, 0.3);
        }
        .character-card:hover {
            border-color: #b30000;
            box-shadow: 0 6px 25px rgba(139, 0, 0, 0.5);
            transform: translateY(-2px);
        }
        .character-card.selected {
            border-color: #27ae60;
            background: linear-gradient(135deg, #1a3a1a 0%, #0f1a0f 100%);
            box-shadow: 0 6px 25px rgba(39, 174, 96, 0.5);
        }
        .character-name {
            font-family: var(--font-title), 'Libre Baskerville', serif;
            font-weight: bold;
            font-size: 1.3em;
            color: #f5e6d3;
            margin-bottom: 10px;
        }
        .character-details {
            font-family: var(--font-body), 'Source Serif Pro', serif;
            font-size: 0.95em;
            color: #d4c4b0;
        }
        .character-details span {
            display: block;
            margin: 5px 0;
        }
        .character-details strong {
            color: #b8a090;
        }
        .selected-character {
            background: linear-gradient(135deg, #1a3a1a 0%, #0f1a0f 100%);
            border: 2px solid #27ae60;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }
        .selected-character h4 {
            font-family: var(--font-title), 'Libre Baskerville', serif;
            color: #f5e6d3;
            margin-bottom: 15px;
        }
        .character-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        .info-item {
            background: rgba(26, 15, 15, 0.5);
            padding: 12px;
            border-radius: 5px;
            font-size: 14px;
            border: 1px solid rgba(139, 0, 0, 0.3);
        }
        .info-label {
            font-family: var(--font-body), 'Source Serif Pro', serif;
            font-weight: bold;
            color: #b8a090;
        }
        .info-item div:not(.info-label) {
            color: #d4c4b0;
            margin-top: 5px;
        }
        .no-characters {
            text-align: center;
            color: #b8a090;
            font-style: italic;
            padding: 40px;
            background: rgba(26, 15, 15, 0.3);
            border: 2px dashed rgba(139, 0, 0, 0.3);
            border-radius: 8px;
        }
        .no-characters a {
            color: #8B0000;
            text-decoration: underline;
        }
</style>

<div class="chat-container">
    <h2 class="section-heading">💬 Chat Room</h2>
    <p class="welcome-text">Select a character to enter the chat.</p>
    
    <div class="chat-content">
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

<?php
// Include footer
include 'includes/footer.php';
?>
