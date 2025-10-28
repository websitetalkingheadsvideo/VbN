<?php
/**
 * Laws Agent Web Interface
 * AI-powered assistant for VTM/MET rules questions
 */

session_start();
require_once __DIR__ . '/../includes/connect.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Check email verification
$result = db_fetch_one($conn, "SELECT email_verified, username FROM users WHERE id = ?", "i", [$_SESSION['user_id']]);
if (!$result) {
    die("User not found");
}

if (!$result['email_verified']) {
    die("Email verification required. Please check your email and verify your account before using the Laws Agent.");
}

$username = $result['username'];
$conn->close();

require_once __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laws Agent - VbN</title>
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/header.css">
    <style>
        .laws-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
        }

        .chat-container {
            background: rgba(26, 15, 15, 0.8);
            border-radius: 8px;
            padding: 0;
            min-height: 600px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        .chat-header {
            text-align: center;
            padding: 30px 20px;
            border-bottom: 2px solid #8b0000;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px 8px 0 0;
        }

        .chat-header h1 {
            color: #8b0000;
            margin: 0 0 10px 0;
            font-size: 28px;
        }

        .chat-header p {
            color: #999;
            margin: 0;
            font-size: 14px;
        }

        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            min-height: 400px;
            max-height: 600px;
        }

        .messages-container::-webkit-scrollbar {
            width: 8px;
        }

        .messages-container::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.3);
        }

        .messages-container::-webkit-scrollbar-thumb {
            background: #8b0000;
            border-radius: 4px;
        }

        .message {
            margin-bottom: 20px;
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: translateY(10px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        .message.user {
            text-align: right;
        }

        .message-content {
            display: inline-block;
            max-width: 80%;
            padding: 15px 20px;
            border-radius: 12px;
            text-align: left;
            line-height: 1.6;
        }

        .message.user .message-content {
            background: rgba(139, 0, 0, 0.3);
            color: #fff;
            border: 1px solid #8b0000;
        }

        .message.assistant .message-content {
            background: rgba(0, 0, 0, 0.5);
            color: #ccc;
            border: 1px solid #444;
        }

        .message-content strong {
            color: #8b0000;
        }

        .sources {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #444;
            font-size: 13px;
        }

        .sources-title {
            color: #8b0000;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .source-item {
            color: #999;
            margin: 5px 0;
            padding: 8px 12px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            border-left: 3px solid #8b0000;
        }

        .source-item:hover {
            background: rgba(139, 0, 0, 0.2);
            transform: translateX(3px);
        }

        .source-item .book-name {
            color: #8b0000;
            font-weight: bold;
        }

        .source-item .source-meta {
            color: #666;
            font-size: 11px;
            margin-top: 2px;
        }

        .input-container {
            padding: 20px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 0 0 8px 8px;
            border-top: 1px solid #444;
        }

        .input-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .question-input {
            flex: 1;
            padding: 15px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #8b0000;
            color: #fff;
            border-radius: 4px;
            font-size: 16px;
            font-family: inherit;
        }

        .question-input:focus {
            outline: none;
            border-color: #a00000;
            box-shadow: 0 0 5px rgba(139, 0, 0, 0.5);
        }

        .send-button {
            padding: 15px 30px;
            background: #8b0000;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s;
        }

        .send-button:hover:not(:disabled) {
            background: #a00000;
        }

        .send-button:disabled {
            background: #555;
            cursor: not-allowed;
        }

        .filters-row {
            display: flex;
            gap: 10px;
            font-size: 14px;
        }

        .filter-select {
            padding: 8px 12px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #666;
            color: #ccc;
            border-radius: 4px;
            cursor: pointer;
        }

        .thinking {
            display: inline-block;
            padding: 15px 20px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #444;
            border-radius: 12px;
            color: #999;
        }

        .thinking::after {
            content: '...';
            animation: dots 1.5s infinite;
        }

        @keyframes dots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60%, 100% { content: '...'; }
        }

        .suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .suggestion-chip {
            padding: 8px 15px;
            background: rgba(139, 0, 0, 0.2);
            border: 1px solid #8b0000;
            border-radius: 20px;
            color: #ccc;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .suggestion-chip:hover {
            background: rgba(139, 0, 0, 0.4);
            transform: translateY(-2px);
        }

        .welcome-message {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .welcome-message h2 {
            color: #8b0000;
            margin-bottom: 20px;
        }

        .error-message {
            background: rgba(139, 0, 0, 0.2);
            border: 1px solid #8b0000;
            padding: 15px;
            border-radius: 8px;
            color: #ff6b6b;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<div class="laws-container">
    <div class="chat-container">
        <div class="chat-header">
            <h1>🧛 Laws Agent</h1>
            <p>Ask me anything about VTM/MET rules, disciplines, clans, mechanics, or lore</p>
            <p style="font-size: 12px; margin-top: 10px; color: #666;">Powered by AI with access to 31 official rulebooks</p>
        </div>

        <div class="messages-container" id="messages">
            <div class="welcome-message" id="welcome">
                <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
                <p>I'm your personal Laws Agent, powered by Anthropic Claude AI with knowledge from all VTM/MET rulebooks.</p>
                <p style="margin-top: 20px; color: #666;">I can help you understand:</p>
                <ul style="text-align: left; max-width: 500px; margin: 20px auto; color: #999;">
                    <li>Game mechanics and rules</li>
                    <li>Discipline powers and usage</li>
                    <li>Clan abilities and weaknesses</li>
                    <li>Combat and challenge systems</li>
                    <li>Character creation rules</li>
                    <li>Lore and setting information</li>
                </ul>
                
                <p style="margin-top: 30px; color: #666;">Try asking me something like:</p>
                
                <div class="suggestions" style="justify-content: center; margin-top: 20px;">
                    <div class="suggestion-chip" onclick="askQuestion('How does Celerity work in MET?')">How does Celerity work?</div>
                    <div class="suggestion-chip" onclick="askQuestion('What are the Six Traditions of the Camarilla?')">Camarilla Traditions</div>
                    <div class="suggestion-chip" onclick="askQuestion('Explain combat challenges and resolution')">Combat Challenges</div>
                    <div class="suggestion-chip" onclick="askQuestion('What disciplines do Toreador have access to?')">Toreador Disciplines</div>
                    <div class="suggestion-chip" onclick="askQuestion('How does Blood Bonding work?')">Blood Bonds</div>
                </div>
            </div>
        </div>

        <div class="input-container">
            <div class="filters-row">
                <select id="categoryFilter" class="filter-select">
                    <option value="">All Categories</option>
                    <option value="Core">Core Rules</option>
                    <option value="Faction">Faction Guides</option>
                    <option value="Supplement">Supplements</option>
                    <option value="Blood Magic">Blood Magic</option>
                    <option value="Journal">Journals</option>
                </select>
                
                <select id="systemFilter" class="filter-select">
                    <option value="">All Systems</option>
                    <option value="MET-VTM">MET-VTM</option>
                    <option value="MET">MET</option>
                    <option value="VTM">VTM</option>
                    <option value="MTA">MTA</option>
                </select>
            </div>
            
            <div class="input-row">
                <input 
                    type="text" 
                    id="questionInput" 
                    class="question-input" 
                    placeholder="Ask about rules, disciplines, clans, lore..."
                    onkeypress="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); askQuestion(); }"
                >
                <button class="send-button" id="sendButton" onclick="askQuestion()">
                    Ask
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let conversationHistory = [];

function askQuestion(predefinedQuestion = null) {
    const input = document.getElementById('questionInput');
    const question = predefinedQuestion || input.value.trim();
    
    if (!question) return;
    
    // Hide welcome message
    const welcome = document.getElementById('welcome');
    if (welcome) welcome.style.display = 'none';
    
    // Clear input if not using predefined question
    if (!predefinedQuestion) {
        input.value = '';
    }
    
    // Add user message
    addMessage('user', escapeHtml(question));
    
    // Show thinking indicator
    const thinkingId = addMessage('assistant', '<div class="thinking">Searching rulebooks and consulting AI</div>', true);
    
    // Disable input
    setInputEnabled(false);
    
    // Get filters
    const category = document.getElementById('categoryFilter').value;
    const system = document.getElementById('systemFilter').value;
    
    // Build URL
    let url = `api_laws_agent.php?action=ask&question=${encodeURIComponent(question)}`;
    if (category) url += `&category=${encodeURIComponent(category)}`;
    if (system) url += `&system=${encodeURIComponent(system)}`;
    
    // Make API call
    fetch(url)
        .then(response => response.json())
        .then(data => {
            // Remove thinking indicator
            removeMessage(thinkingId);
            
            if (data.success) {
                // Format answer with sources
                let answer = data.answer || 'I found some relevant information in the rulebooks.';
                
                // Convert markdown-style citations to HTML
                answer = formatAnswer(answer);
                
                if (data.sources && data.sources.length > 0) {
                    answer += '<div class="sources">';
                    answer += '<div class="sources-title">Sources:</div>';
                    data.sources.forEach((source, index) => {
                        answer += `
                            <div class="source-item" onclick="viewSource(${index}, ${JSON.stringify(source).replace(/"/g, '&quot;')})">
                                <div class="book-name">${escapeHtml(source.book)}</div>
                                <div class="source-meta">Page ${source.page} • ${source.category} • ${source.system}</div>
                            </div>
                        `;
                    });
                    answer += '</div>';
                }
                
                addMessage('assistant', answer);
                conversationHistory.push({ 
                    question, 
                    answer: data.answer,
                    sources: data.sources 
                });
            } else {
                addMessage('assistant', `<div class="error-message">Error: ${escapeHtml(data.error || 'Unknown error occurred')}</div>`);
            }
        })
        .catch(error => {
            removeMessage(thinkingId);
            addMessage('assistant', `<div class="error-message">Connection error: ${escapeHtml(error.message)}</div>`);
        })
        .finally(() => {
            setInputEnabled(true);
            input.focus();
        });
}

function addMessage(type, content, isTemporary = false) {
    const messagesContainer = document.getElementById('messages');
    const messageDiv = document.createElement('div');
    const messageId = 'msg-' + Date.now() + '-' + Math.random();
    
    messageDiv.id = messageId;
    messageDiv.className = `message ${type}`;
    messageDiv.innerHTML = `<div class="message-content">${content}</div>`;
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    return messageId;
}

function removeMessage(messageId) {
    const message = document.getElementById(messageId);
    if (message) {
        message.remove();
    }
}

function setInputEnabled(enabled) {
    const input = document.getElementById('questionInput');
    const button = document.getElementById('sendButton');
    
    input.disabled = !enabled;
    button.disabled = !enabled;
    
    button.textContent = enabled ? 'Ask' : 'Thinking...';
}

function viewSource(index, source) {
    alert(`Source ${index + 1}:\n\n${source.book}\nPage ${source.page}\n\n${source.excerpt || 'Click OK to search for this page.'}`);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatAnswer(text) {
    // Convert (Source X: ...) citations to styled spans
    text = text.replace(/\(Source \d+:([^)]+)\)/g, '<strong style="color: #8b0000;">(Source $&)</strong>');
    
    // Convert newlines to breaks
    text = text.replace(/\n/g, '<br>');
    
    return text;
}
</script>

</body>
</html>

