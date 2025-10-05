<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>lotn_char_create</title>
</head>

<body>
	<?php
// LOTN Character Creator - Version 0.2.1
define('LOTN_VERSION', '0.2.1');

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include 'includes/connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Character - Laws of the Night</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Sidebar Tracker -->
    <div class="sidebar">
        <h3>Character Progress</h3>
        
        <div class="xp-summary">
            <div class="stat-line">
                <span class="stat-label">Starting XP:</span>
                <span class="stat-value">30</span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Spent:</span>
                <span class="stat-value negative" id="xpSpent">0</span>
            </div>
            <div class="stat-line" style="border-top: 2px solid #8b0000; margin-top: 10px; padding-top: 10px;">
                <span class="stat-label">Remaining:</span>
                <span class="total" id="xpRemaining">30</span>
            </div>
            <div class="stat-line" id="characterModeDisplay" style="display: none; border-top: 2px solid #ff8c00; margin-top: 10px; padding-top: 10px;">
                <span class="stat-label">Mode:</span>
                <span class="stat-value" style="color: #ff8c00;">Advancement</span>
            </div>
        </div>
        
        <div class="stat-group">
            <h4>XP Breakdown</h4>
            <div class="stat-line">
                <span class="stat-label">Traits:</span>
                <span class="stat-value" id="xpTraits">0</span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Abilities:</span>
                <span class="stat-value" id="xpAbilities">0</span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Disciplines:</span>
                <span class="stat-value" id="xpDisciplines">0</span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Backgrounds:</span>
                <span class="stat-value" id="xpBackgrounds">0</span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Virtues:</span>
                <span class="stat-value" id="xpVirtues">0</span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Willpower:</span>
                <span class="stat-value" id="xpWillpower">0</span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Humanity:</span>
                <span class="stat-value" id="xpHumanity">0</span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Merits:</span>
                <span class="stat-value" id="xpMerits">0</span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Flaws Bonus:</span>
                <span class="stat-value positive" id="xpFlaws">0</span>
            </div>
        </div>
        
        <div class="stat-group">
            <h4>Trait Counts</h4>
            <div class="stat-line">
                <span class="stat-label">Physical:</span>
                <span class="stat-value"><span id="physicalCount">0</span>/10</span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Social:</span>
                <span class="stat-value"><span id="socialCount">0</span>/10</span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Mental:</span>
                <span class="stat-value"><span id="mentalCount">0</span>/10</span>
            </div>
        </div>
        
        <div class="stat-group">
            <h4>Physical Trait Breakdown</h4>
            <div class="stat-line">
                <span class="stat-label">Agility & Speed:</span>
                <span class="stat-value"><span id="agilityCount">0</span></span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Strength & Endurance:</span>
                <span class="stat-value"><span id="strengthCount">0</span></span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Dexterity & Coordination:</span>
                <span class="stat-value"><span id="dexterityCount">0</span></span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Reflexes & Awareness:</span>
                <span class="stat-value"><span id="reflexesCount">0</span></span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Appearance:</span>
                <span class="stat-value"><span id="appearanceCount">0</span></span>
            </div>
        </div>
        
        <div class="stat-group">
            <h4>Current Stats</h4>
            <div class="stat-line">
                <span class="stat-label">Willpower:</span>
                <span class="stat-value" id="willpowerValue">5</span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Humanity:</span>
                <span class="stat-value" id="humanityValue">7</span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Conscience:</span>
                <span class="stat-value" id="conscienceValue">1</span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Self-Control:</span>
                <span class="stat-value" id="selfControlValue">1</span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Courage:</span>
                <span class="stat-value" id="courageValue">1</span>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="header">
            <h1>⚜ Laws of the Night: Character Creation ⚜</h1>
            <div class="header-right">
                <div class="user-info">
                    <span class="user-label">Logged in as:</span>
                    <span class="user-name"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest User'; ?></span>
                </div>
            <div class="version-info">
                <span class="version">v<?php echo LOTN_VERSION; ?></span>
                </div>
            </div>
            <div class="xp-tracker">
                <div class="label">Available XP</div>
                <div class="xp-display" id="xpDisplay">30</div>
                <div class="xp-label">Experience Points</div>
            </div>
        </div>
        
        <div class="tabs">
            <button class="tab active" onclick="showTab(0)">Basic Info</button>
            <button class="tab" onclick="showTab(1)">Traits</button>
            <button class="tab" onclick="showTab(2)">Abilities</button>
            <button class="tab" onclick="showTab(3)">Disciplines</button>
            <button class="tab" onclick="showTab(4)">Backgrounds</button>
            <button class="tab" onclick="showTab(5)">Morality</button>
            <button class="tab" onclick="showTab(6)">Merits & Flaws</button>
            <button class="tab" onclick="showTab(7)">Final Details</button>
        </div>
        
        <form id="characterForm">
            <!-- Tab 1: Basic Info -->
            <div class="tab-content active" id="tab0">
                <h2 style="color: #8b0000; margin-bottom: 25px;">Basic Information</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="characterName">Character Name *</label>
                        <input type="text" id="characterName" name="characterName" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="playerName">Player Name *</label>
                        <input type="text" id="playerName" name="playerName" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="chronicle">Chronicle</label>
                    <input type="text" id="chronicle" name="chronicle" value="Valley by Night">
                    <div class="helper-text">Name of the campaign/game</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nature">Nature *</label>
                        <select id="nature" name="nature" required>
                            <option value="">Select Nature...</option>
                            <option value="Architect">Architect</option>
                            <option value="Autist">Autist</option>
                            <option value="Bon Vivant">Bon Vivant</option>
                            <option value="Bravo">Bravo</option>
                            <option value="Caregiver">Caregiver</option>
                            <option value="Capitalist">Capitalist</option>
                            <option value="Competitor">Competitor</option>
                            <option value="Conformist">Conformist</option>
                            <option value="Conniver">Conniver</option>
                            <option value="Curmudgeon">Curmudgeon</option>
                            <option value="Deviant">Deviant</option>
                            <option value="Director">Director</option>
                            <option value="Fanatic">Fanatic</option>
                            <option value="Gallant">Gallant</option>
                            <option value="Judge">Judge</option>
                            <option value="Loner">Loner</option>
                            <option value="Martyr">Martyr</option>
                            <option value="Masochist">Masochist</option>
                            <option value="Monster">Monster</option>
                            <option value="Pedagogue">Pedagogue</option>
                            <option value="Penitent">Penitent</option>
                            <option value="Perfectionist">Perfectionist</option>
                            <option value="Rebel">Rebel</option>
                            <option value="Rogue">Rogue</option>
                            <option value="Survivor">Survivor</option>
                            <option value="Thrill-Seeker">Thrill-Seeker</option>
                            <option value="Traditionalist">Traditionalist</option>
                            <option value="Visionary">Visionary</option>
                        </select>
                        <div class="helper-text">True personality</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="demeanor">Demeanor *</label>
                        <select id="demeanor" name="demeanor" required>
                            <option value="">Select Demeanor...</option>
                            <option value="Architect">Architect</option>
                            <option value="Autist">Autist</option>
                            <option value="Bon Vivant">Bon Vivant</option>
                            <option value="Bravo">Bravo</option>
                            <option value="Caregiver">Caregiver</option>
                            <option value="Capitalist">Capitalist</option>
                            <option value="Competitor">Competitor</option>
                            <option value="Conformist">Conformist</option>
                            <option value="Conniver">Conniver</option>
                            <option value="Curmudgeon">Curmudgeon</option>
                            <option value="Deviant">Deviant</option>
                            <option value="Director">Director</option>
                            <option value="Fanatic">Fanatic</option>
                            <option value="Gallant">Gallant</option>
                            <option value="Judge">Judge</option>
                            <option value="Loner">Loner</option>
                            <option value="Martyr">Martyr</option>
                            <option value="Masochist">Masochist</option>
                            <option value="Monster">Monster</option>
                            <option value="Pedagogue">Pedagogue</option>
                            <option value="Penitent">Penitent</option>
                            <option value="Perfectionist">Perfectionist</option>
                            <option value="Rebel">Rebel</option>
                            <option value="Rogue">Rogue</option>
                            <option value="Survivor">Survivor</option>
                            <option value="Thrill-Seeker">Thrill-Seeker</option>
                            <option value="Traditionalist">Traditionalist</option>
                            <option value="Visionary">Visionary</option>
                        </select>
                        <div class="helper-text">Public personality</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="concept">Concept *</label>
                    <input type="text" id="concept" name="concept" required>
                    <div class="helper-text">Brief description of character concept (e.g., "Street Gang Leader", "Tortured Artist")</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <label for="clan" style="margin: 0;">Clan *</label>
                            <button type="button" class="help-btn" onclick="showClanGuide()" title="View Clan Guide">
                                <span>?</span>
                            </button>
                        </div>
                        <select id="clan" name="clan" required onchange="handleClanChange()">
                            <option value="">Select Clan...</option>
                            <option value="Assamite">⚔️ Assamite</option>
                            <option value="Brujah">✊ Brujah</option>
                            <option value="Followers of Set">🐍 Followers of Set</option>
                            <option value="Gangrel">🐺 Gangrel</option>
                            <option value="Giovanni">💀 Giovanni</option>
                            <option value="Lasombra">🌑 Lasombra</option>
                            <option value="Malkavian">🎭 Malkavian</option>
                            <option value="Nosferatu">🦇 Nosferatu</option>
                            <option value="Ravnos">🎪 Ravnos</option>
                            <option value="Toreador">🌹 Toreador</option>
                            <option value="Tremere">⭐ Tremere</option>
                            <option value="Tzimisce">🧬 Tzimisce</option>
                            <option value="Ventrue">👑 Ventrue</option>
                            <option value="Caitiff">❓ Caitiff</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="generation">Generation *</label>
                        <select id="generation" name="generation" required>
                            <option value="">Select Generation...</option>
                            <option value="13" selected>13th Generation</option>
                            <option value="12">12th Generation</option>
                            <option value="11">11th Generation</option>
                            <option value="10">10th Generation</option>
                            <option value="9">9th Generation</option>
                            <option value="8">8th Generation</option>
                            <option value="7">7th Generation</option>
                        </select>
                        <div class="helper-text">Distance from Caine</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="sire">Sire</label>
                    <input type="text" id="sire" name="sire">
                    <div class="helper-text">Name of vampire who embraced this character</div>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="pc" name="pc" checked>
                        <label for="pc" style="margin: 0;">Player Character (PC)</label>
                    </div>
                    <div class="helper-text">Uncheck if this is an NPC</div>
                </div>
                
                <div class="button-group">
                    <button type="button" disabled>← Previous</button>
                    <button type="button" class="save-btn" onclick="saveCharacter()">💾 Save Character</button>
                    <button type="button" onclick="showTab(1)">Next →</button>
                </div>
            </div>
            
            <!-- Tab 2: Traits -->
            <div class="tab-content" id="tab1">
                <h2 style="color: #8b0000; margin-bottom: 25px;">Traits</h2>
                
                <div class="info-box">
                    <strong>Trait Selection:</strong> Choose your traits from the lists below.
                    <ul>
                        <li><strong>First 7 traits</strong> in each category are <strong>FREE</strong></li>
                        <li>Traits 8-10 cost <strong>4 XP each</strong></li>
                        <li>Maximum 10 traits per category at character creation</li>
                        <li><strong>You can select the same trait multiple times</strong> - click the same trait button repeatedly</li>
                        <li><strong>Remove traits anytime</strong> - click the × button on any selected trait to remove it</li>
                        <li><strong>Negative traits give +4 XP each</strong> - select from the red negative trait sections below</li>
                        <li>Each selection counts toward your trait total and XP cost</li>
                    </ul>
                </div>
                
                <!-- Physical Traits -->
                <div class="trait-section">
                    <div class="trait-header">
                        <h3>Physical Traits</h3>
                        <div class="trait-progress">
                            <div class="trait-progress-label">
                                <span><span id="physicalCountDisplay">0</span> selected</span>
                                <span>7 required | 10 maximum</span>
                            </div>
                            <div class="trait-progress-bar">
                                <div class="trait-progress-fill incomplete" id="physicalProgressFill" style="width: 0%;">
                                    <div class="trait-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="trait-options" id="physicalOptions">
                        <!-- Agility & Speed -->
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Agile')">Agile</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Lithe')">Lithe</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Nimble')">Nimble</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Quick')">Quick</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Spry')">Spry</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Graceful')">Graceful</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Slender')">Slender</button>
                        
                        <!-- Strength & Endurance -->
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Strong')">Strong</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Hardy')">Hardy</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Tough')">Tough</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Resilient')">Resilient</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Sturdy')">Sturdy</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Vigorous')">Vigorous</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Burly')">Burly</button>
                        
                        <!-- Dexterity & Coordination -->
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Coordinated')">Coordinated</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Precise')">Precise</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Steady-handed')">Steady-handed</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Sleek')">Sleek</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Flexible')">Flexible</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Balanced')">Balanced</button>
                        
                        <!-- Reflexes & Awareness -->
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Alert')">Alert</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Sharp-eyed')">Sharp-eyed</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Quick-reflexed')">Quick-reflexed</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Perceptive')">Perceptive</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Reactive')">Reactive</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Observant')">Observant</button>
                        
                        <!-- Appearance / Presence of Body -->
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Athletic')">Athletic</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Well-built')">Well-built</button>
                        
                        <!-- Legacy traits -->
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Fast')">Fast</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Physical', 'Muscular')">Muscular</button>
                    </div>
                    
                    <div class="trait-list" id="physicalTraitList">
                    </div>
                    
                    <!-- Physical Negative Traits -->
                    <div class="negative-traits-section">
                        <h4>Physical Negative Traits (+4 XP each)</h4>
                        <div class="trait-options" id="physicalNegativeOptions">
                            <button type="button" class="trait-option-btn negative" onclick="selectNegativeTrait('Physical', 'Frail')">Frail</button>
                            <button type="button" class="trait-option-btn negative" onclick="selectNegativeTrait('Physical', 'Slow')">Slow</button>
                            <button type="button" class="trait-option-btn negative" onclick="selectNegativeTrait('Physical', 'Weak')">Weak</button>
                        </div>
                        <div class="trait-list" id="physicalNegativeTraitList">
                        </div>
                    </div>
                </div>
                
                <!-- Social Traits -->
                <div class="trait-section">
                    <div class="trait-header">
                        <h3>Social Traits</h3>
                        <div class="trait-progress">
                            <div class="trait-progress-label">
                                <span><span id="socialCountDisplay">0</span> selected</span>
                                <span>7 required | 10 maximum</span>
                            </div>
                            <div class="trait-progress-bar">
                                <div class="trait-progress-fill incomplete" id="socialProgressFill" style="width: 0%;">
                                    <div class="trait-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="trait-options" id="socialOptions">
                        <!-- Charm & Charisma -->
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Charming')">Charming</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Persuasive')">Persuasive</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Charismatic')">Charismatic</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Graceful')">Graceful</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Poised')">Poised</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Attractive')">Attractive</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Handsome')">Handsome</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Beautiful')">Beautiful</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Seductive')">Seductive</button>
                        
                        <!-- Manipulation & Deception -->
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Cunning')">Cunning</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Deceptive')">Deceptive</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Manipulative')">Manipulative</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Subtle')">Subtle</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Diplomatic')">Diplomatic</button>
                        
                        <!-- Personality / Presence -->
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Sociable')">Sociable</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Friendly')">Friendly</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Outgoing')">Outgoing</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Bold')">Bold</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Confident')">Confident</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Stubborn')">Stubborn</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Witty')">Witty</button>
                        
                        <!-- Leadership & Influence -->
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Commanding')">Commanding</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Inspiring')">Inspiring</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Assertive')">Assertive</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Authoritative')">Authoritative</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Motivating')">Motivating</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Loyal')">Loyal</button>
                        
                        <!-- Legacy traits -->
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Elegant')">Elegant</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Expressive')">Expressive</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Striking')">Striking</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Imposing')">Imposing</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Social', 'Intimidating')">Intimidating</button>
                    </div>
                    
                    <div class="trait-list" id="socialTraitList">
                    </div>
                    
                    <!-- Social Negative Traits -->
                    <div class="negative-traits-section">
                        <h4>Social Negative Traits (+4 XP each)</h4>
                        <div class="trait-options" id="socialNegativeOptions">
                            <button type="button" class="trait-option-btn negative" onclick="selectNegativeTrait('Social', 'Ugly')">Ugly</button>
                            <button type="button" class="trait-option-btn negative" onclick="selectNegativeTrait('Social', 'Awkward')">Awkward</button>
                            <button type="button" class="trait-option-btn negative" onclick="selectNegativeTrait('Social', 'Shy')">Shy</button>
                            <button type="button" class="trait-option-btn negative" onclick="selectNegativeTrait('Social', 'Rude')">Rude</button>
                        </div>
                        <div class="trait-list" id="socialNegativeTraitList">
                        </div>
                    </div>
                </div>
                
                <!-- Mental Traits -->
                <div class="trait-section">
                    <div class="trait-header">
                        <h3>Mental Traits</h3>
                        <div class="trait-progress">
                            <div class="trait-progress-label">
                                <span><span id="mentalCountDisplay">0</span> selected</span>
                                <span>7 required | 10 maximum</span>
                            </div>
                            <div class="trait-progress-bar">
                                <div class="trait-progress-fill incomplete" id="mentalProgressFill" style="width: 0%;">
                                    <div class="trait-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="trait-options" id="mentalOptions">
                        <!-- Intelligence & Knowledge -->
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Intelligent')">Intelligent</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Clever')">Clever</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Learned')">Learned</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Analytical')">Analytical</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Scholarly')">Scholarly</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Logical')">Logical</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Resourceful')">Resourceful</button>
                        
                        <!-- Perception & Awareness -->
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Observant')">Observant</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Alert')">Alert</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Sharp-eyed')">Sharp-eyed</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Attentive')">Attentive</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Quick-minded')">Quick-minded</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Insightful')">Insightful</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Perceptive')">Perceptive</button>
                        
                        <!-- Memory & Recall -->
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Remembering')">Remembering</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Studious')">Studious</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Focused')">Focused</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Methodical')">Methodical</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Precise')">Precise</button>
                        
                        <!-- Problem Solving & Planning -->
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Strategic')">Strategic</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Calculating')">Calculating</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Cunning')">Cunning</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Patient')">Patient</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Determined')">Determined</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Adaptive')">Adaptive</button>
                        
                        <!-- Personality / Mental Flavor -->
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Curious')">Curious</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Witty')">Witty</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Shrewd')">Shrewd</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Eccentric')">Eccentric</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Philosophical')">Philosophical</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Persistent')">Persistent</button>
                        
                        <!-- Legacy traits -->
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Calm')">Calm</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Creative')">Creative</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Dedicated')">Dedicated</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Intuitive')">Intuitive</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Rational')">Rational</button>
                        <button type="button" class="trait-option-btn" onclick="selectTrait('Mental', 'Wise')">Wise</button>
                    </div>
                    
                    <div class="trait-list" id="mentalTraitList">
                    </div>
                    
                    <!-- Mental Negative Traits -->
                    <div class="negative-traits-section">
                        <h4>Mental Negative Traits (+4 XP each)</h4>
                        <div class="trait-options" id="mentalNegativeOptions">
                            <button type="button" class="trait-option-btn negative" onclick="selectNegativeTrait('Mental', 'Dull')">Dull</button>
                            <button type="button" class="trait-option-btn negative" onclick="selectNegativeTrait('Mental', 'Scatterbrained')">Scatterbrained</button>
                            <button type="button" class="trait-option-btn negative" onclick="selectNegativeTrait('Mental', 'Absent-minded')">Absent-minded</button>
                            <button type="button" class="trait-option-btn negative" onclick="selectNegativeTrait('Mental', 'Distracted')">Distracted</button>
                            <button type="button" class="trait-option-btn negative" onclick="selectNegativeTrait('Mental', 'Forgetful')">Forgetful</button>
                            <button type="button" class="trait-option-btn negative" onclick="selectNegativeTrait('Mental', 'Rash')">Rash</button>
                        </div>
                        <div class="trait-list" id="mentalNegativeTraitList">
                        </div>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="button" onclick="showTab(0)">← Previous</button>
                    <button type="button" class="save-btn" onclick="saveCharacter()">💾 Save Character</button>
                    <button type="button" onclick="showTab(2)">Next →</button>
                </div>
            </div>
            
            <!-- Tab 3: Abilities -->
            <div class="tab-content" id="tab2">
                <h2 style="color: #8b0000; margin-bottom: 25px;">Abilities</h2>
                
                <div class="info-box">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div style="flex: 1;">
                            <strong>Ability Selection:</strong> Choose your abilities from the lists below.
                            <ul>
                                <li><strong>First 3 ability dots</strong> in each category are <strong>FREE</strong></li>
                                <li>Ability dots 4-5 cost <strong>2 XP each</strong></li>
                                <li><strong>Maximum 5 dots per individual ability</strong> (e.g., Athletics 5, Brawl 3, etc.)</li>
                                <li><strong>You can select the same ability multiple times</strong> - click the same ability button repeatedly to add dots</li>
                                <li><strong>Remove ability dots anytime</strong> - click the × button on any selected ability to remove dots</li>
                                <li>Each click adds 1 dot to that ability and counts toward your XP cost</li>
                            </ul>
                        </div>
                        <button type="button" class="help-btn" onclick="showDisciplineGuide()" title="View Discipline-Ability Guide">
                            <span>?</span>
                        </button>
                    </div>
                </div>
                
                <!-- Physical Abilities -->
                <div class="ability-section">
                    <div class="ability-header">
                        <h3>⚔️ Physical Abilities</h3>
                        <div class="ability-progress">
                            <div class="ability-progress-label">
                                <span><span id="physicalAbilitiesCountDisplay">0</span> dots</span>
                                <span>3 required | 5 max per ability</span>
                            </div>
                            <div class="ability-progress-bar">
                                <div class="ability-progress-fill incomplete" id="physicalAbilitiesProgressFill" style="width: 0%;">
                                    <div class="ability-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ability-options" id="physicalAbilitiesOptions">
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Physical', 'Athletics')">Athletics</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Physical', 'Brawl')">Brawl</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Physical', 'Dodge')">Dodge</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Physical', 'Firearms')">Firearms</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Physical', 'Melee')">Melee</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Physical', 'Security')">Security</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Physical', 'Stealth')">Stealth</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Physical', 'Survival')">Survival</button>
                    </div>
                    
                    <div class="ability-list" id="physicalAbilitiesList">
                    </div>
                </div>
                
                <!-- Social Abilities -->
                <div class="ability-section">
                    <div class="ability-header">
                        <h3>💬 Social Abilities</h3>
                        <div class="ability-progress">
                            <div class="ability-progress-label">
                                <span><span id="socialAbilitiesCountDisplay">0</span> dots</span>
                                <span>3 required | 5 max per ability</span>
                            </div>
                            <div class="ability-progress-bar">
                                <div class="ability-progress-fill incomplete" id="socialAbilitiesProgressFill" style="width: 0%;">
                                    <div class="ability-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ability-options" id="socialAbilitiesOptions">
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Social', 'Animal Ken')">Animal Ken</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Social', 'Empathy')">Empathy</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Social', 'Expression')">Expression</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Social', 'Intimidation')">Intimidation</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Social', 'Leadership')">Leadership</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Social', 'Subterfuge')">Subterfuge</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Social', 'Streetwise')">Streetwise</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Social', 'Etiquette')">Etiquette</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Social', 'Performance')">Performance</button>
                    </div>
                    
                    <div class="ability-list" id="socialAbilitiesList">
                    </div>
                </div>
                
                <!-- Mental Abilities -->
                <div class="ability-section">
                    <div class="ability-header">
                        <h3>🧠 Mental Abilities</h3>
                        <div class="ability-progress">
                            <div class="ability-progress-label">
                                <span><span id="mentalAbilitiesCountDisplay">0</span> dots</span>
                                <span>3 required | 5 max per ability</span>
                            </div>
                            <div class="ability-progress-bar">
                                <div class="ability-progress-fill incomplete" id="mentalAbilitiesProgressFill" style="width: 0%;">
                                    <div class="ability-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ability-options" id="mentalAbilitiesOptions">
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Mental', 'Academics')">Academics</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Mental', 'Computer')">Computer</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Mental', 'Finance')">Finance</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Mental', 'Investigation')">Investigation</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Mental', 'Law')">Law</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Mental', 'Linguistics')">Linguistics</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Mental', 'Medicine')">Medicine</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Mental', 'Occult')">Occult</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Mental', 'Politics')">Politics</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Mental', 'Science')">Science</button>
                    </div>
                    
                    <div class="ability-list" id="mentalAbilitiesList">
                    </div>
                </div>
                
                <!-- Optional Abilities -->
                <div class="ability-section">
                    <div class="ability-header">
                        <h3>🧩 Optional Abilities</h3>
                        <div class="ability-progress">
                            <div class="ability-progress-label">
                                <span><span id="optionalAbilitiesCountDisplay">0</span> dots</span>
                                <span>0 required | 5 max per ability</span>
                            </div>
                            <div class="ability-progress-bar">
                                <div class="ability-progress-fill incomplete" id="optionalAbilitiesProgressFill" style="width: 0%;">
                                    <div class="ability-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ability-options" id="optionalAbilitiesOptions">
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Optional', 'Alertness')">Alertness</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Optional', 'Awareness')">Awareness</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Optional', 'Drive')">Drive</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Optional', 'Crafts')">Crafts</button>
                        <button type="button" class="ability-option-btn" onclick="selectAbility('Optional', 'Firecraft')">Firecraft</button>
                    </div>
                    
                    <div class="ability-list" id="optionalAbilitiesList">
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="button" onclick="showTab(1)">← Previous</button>
                    <button type="button" class="save-btn" onclick="saveCharacter()">💾 Save Character</button>
                    <button type="button" onclick="showTab(3)">Next →</button>
                </div>
            </div>
            
            <!-- Tab 4: Disciplines -->
            <div class="tab-content" id="tab3">
                <h2 style="color: #8b0000; margin-bottom: 25px;">Disciplines</h2>
                
                <div class="info-box">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div style="flex: 1;">
                            <strong>Discipline Selection:</strong> Choose your disciplines from the lists below.
                            <ul>
                                <li><strong>First 3 discipline dots</strong> are <strong>FREE</strong></li>
                                <li>Discipline dots 4-5 cost <strong>3 XP each</strong></li>
                                <li><strong>Maximum 5 dots per individual discipline</strong> (e.g., Potence 5, Presence 3, etc.)</li>
                                <li><strong>You can select the same discipline multiple times</strong> - click the same discipline button repeatedly to add dots</li>
                                <li><strong>Remove discipline dots anytime</strong> - click the × button on any selected discipline to remove dots</li>
                                <li>Each click adds 1 dot to that discipline and counts toward your XP cost</li>
                                <li><strong>Clan disciplines</strong> are marked with a special indicator</li>
                            </ul>
                        </div>
                        <button type="button" class="help-btn" onclick="showDisciplineGuide()" title="View Discipline-Ability Guide">
                            <span>?</span>
                        </button>
                    </div>
                </div>
                
                <!-- Clan Disciplines -->
                <div class="discipline-section">
                    <div class="discipline-header">
                        <h3>🏛️ Clan Disciplines</h3>
                        <div class="discipline-progress">
                            <div class="discipline-progress-label">
                                <span><span id="clanDisciplinesCountDisplay">0</span> levels</span>
                                <span>3 free levels at creation</span>
                            </div>
                            <div class="discipline-progress-bar">
                                <div class="discipline-progress-fill incomplete" id="clanDisciplinesProgressFill" style="width: 0%;">
                                    <div class="discipline-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="discipline-options" id="clanDisciplinesOptions">
                        <button type="button" class="discipline-option-btn clan" data-discipline="Animalism" onmouseenter="showDisciplinePopover(event, 'Animalism')" onmouseleave="hideDisciplinePopover()">Animalism</button>
                        <button type="button" class="discipline-option-btn clan" data-discipline="Auspex" onmouseenter="showDisciplinePopover(event, 'Auspex')" onmouseleave="hideDisciplinePopover()">Auspex</button>
                        <button type="button" class="discipline-option-btn clan" data-discipline="Celerity" onmouseenter="showDisciplinePopover(event, 'Celerity')" onmouseleave="hideDisciplinePopover()">Celerity</button>
                        <button type="button" class="discipline-option-btn clan" data-discipline="Dominate" onmouseenter="showDisciplinePopover(event, 'Dominate')" onmouseleave="hideDisciplinePopover()">Dominate</button>
                        <button type="button" class="discipline-option-btn clan" data-discipline="Fortitude" onmouseenter="showDisciplinePopover(event, 'Fortitude')" onmouseleave="hideDisciplinePopover()">Fortitude</button>
                        <button type="button" class="discipline-option-btn clan" data-discipline="Obfuscate" onmouseenter="showDisciplinePopover(event, 'Obfuscate')" onmouseleave="hideDisciplinePopover()">Obfuscate</button>
                        <button type="button" class="discipline-option-btn clan" data-discipline="Potence" onmouseenter="showDisciplinePopover(event, 'Potence')" onmouseleave="hideDisciplinePopover()">Potence</button>
                        <button type="button" class="discipline-option-btn clan" data-discipline="Presence" onmouseenter="showDisciplinePopover(event, 'Presence')" onmouseleave="hideDisciplinePopover()">Presence</button>
                        <button type="button" class="discipline-option-btn clan" data-discipline="Protean" onmouseenter="showDisciplinePopover(event, 'Protean')" onmouseleave="hideDisciplinePopover()">Protean</button>
                    </div>
                    
                    <div class="discipline-list" id="clanDisciplinesList">
                    </div>
                </div>
                
                <!-- Blood Sorcery -->
                <div class="discipline-section" data-category="BloodSorcery">
                    <div class="discipline-header">
                        <h3>🩸 Blood Sorcery</h3>
                        <div class="discipline-progress">
                            <div class="discipline-progress-label">
                                <span><span id="bloodSorceryCountDisplay">0</span> levels</span>
                                <span>3 free levels at creation</span>
                            </div>
                            <div class="discipline-progress-bar">
                                <div class="discipline-progress-fill incomplete" id="bloodSorceryProgressFill" style="width: 0%;">
                                    <div class="discipline-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="discipline-options" id="bloodSorceryOptions">
                        <button type="button" class="discipline-option-btn" data-discipline="Thaumaturgy" onmouseenter="showDisciplinePopover(event, 'Thaumaturgy')" onmouseleave="hideDisciplinePopover()">Thaumaturgy</button>
                        <button type="button" class="discipline-option-btn" data-discipline="Necromancy" onmouseenter="showDisciplinePopover(event, 'Necromancy')" onmouseleave="hideDisciplinePopover()">Necromancy</button>
                        <button type="button" class="discipline-option-btn" data-discipline="Koldunic Sorcery" onmouseenter="showDisciplinePopover(event, 'Koldunic Sorcery')" onmouseleave="hideDisciplinePopover()">Koldunic Sorcery</button>
                    </div>
                    
                    <div class="discipline-list" id="bloodSorceryList">
                    </div>
                </div>
                
                <!-- Advanced Disciplines -->
                <div class="discipline-section" data-category="Advanced">
                    <div class="discipline-header">
                        <h3>⚡ Advanced Disciplines</h3>
                        <div class="discipline-progress">
                            <div class="discipline-progress-label">
                                <span><span id="advancedDisciplinesCountDisplay">0</span> levels</span>
                                <span>3 free levels at creation</span>
                            </div>
                            <div class="discipline-progress-bar">
                                <div class="discipline-progress-fill incomplete" id="advancedDisciplinesProgressFill" style="width: 0%;">
                                    <div class="discipline-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="discipline-options" id="advancedDisciplinesOptions">
                        <button type="button" class="discipline-option-btn" data-discipline="Obtenebration" onmouseenter="showDisciplinePopover(event, 'Obtenebration')" onmouseleave="hideDisciplinePopover()">Obtenebration</button>
                        <button type="button" class="discipline-option-btn" data-discipline="Chimerstry" onmouseenter="showDisciplinePopover(event, 'Chimerstry')" onmouseleave="hideDisciplinePopover()">Chimerstry</button>
                        <button type="button" class="discipline-option-btn" data-discipline="Dementation" onmouseenter="showDisciplinePopover(event, 'Dementation')" onmouseleave="hideDisciplinePopover()">Dementation</button>
                        <button type="button" class="discipline-option-btn" data-discipline="Quietus" onmouseenter="showDisciplinePopover(event, 'Quietus')" onmouseleave="hideDisciplinePopover()">Quietus</button>
                        <button type="button" class="discipline-option-btn" data-discipline="Vicissitude" onmouseenter="showDisciplinePopover(event, 'Vicissitude')" onmouseleave="hideDisciplinePopover()">Vicissitude</button>
                        <button type="button" class="discipline-option-btn" data-discipline="Serpentis" onmouseenter="showDisciplinePopover(event, 'Serpentis')" onmouseleave="hideDisciplinePopover()">Serpentis</button>
                        <button type="button" class="discipline-option-btn" data-discipline="Daimoinon" onmouseenter="showDisciplinePopover(event, 'Daimoinon')" onmouseleave="hideDisciplinePopover()">Daimoinon</button>
                        <button type="button" class="discipline-option-btn" data-discipline="Melpominee" onmouseenter="showDisciplinePopover(event, 'Melpominee')" onmouseleave="hideDisciplinePopover()">Melpominee</button>
                        <button type="button" class="discipline-option-btn" data-discipline="Valeren" onmouseenter="showDisciplinePopover(event, 'Valeren')" onmouseleave="hideDisciplinePopover()">Valeren</button>
                        <button type="button" class="discipline-option-btn" data-discipline="Mortis" onmouseenter="showDisciplinePopover(event, 'Mortis')" onmouseleave="hideDisciplinePopover()">Mortis</button>
                    </div>
                    
                    <div class="discipline-list" id="advancedDisciplinesList">
                    </div>
                </div>
                
                <!-- Discipline Power Popover -->
                <div id="disciplinePopover" class="discipline-popover" onmouseenter="clearPopoverTimeout()" onmouseleave="hideDisciplinePopover()">
                    <h4 id="popoverTitle">Discipline Powers</h4>
                    <div id="popoverPowers">
                        <!-- Power options will be dynamically generated here -->
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="button" onclick="showTab(2)">← Previous</button>
                    <button type="button" class="save-btn" onclick="saveCharacter()">💾 Save Character</button>
                    <button type="button" onclick="showTab(4)">Next →</button>
                </div>
            </div>
            
            <!-- Tab 5: Backgrounds -->
            <div class="tab-content" id="tab4">
                <h2 style="color: #8b0000; margin-bottom: 25px;">Backgrounds</h2>
                <p style="color: #666; margin-bottom: 20px;">Select your character's resources, connections, and social standing. Each background represents different types of influence and support available to your character.</p>
                
                <!-- Backgrounds Progress Summary -->
                <div class="backgrounds-summary">
                    <div class="summary-item">
                        <span class="summary-label">Total Background Points:</span>
                        <span class="summary-value" id="totalBackgroundsDisplay">0</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Free Points:</span>
                        <span class="summary-value" id="freeBackgroundsDisplay">5</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">XP Cost:</span>
                        <span class="summary-value" id="backgroundsXpDisplay">0</span>
                    </div>
                </div>
                
                <!-- Auto-calculated Generation Background -->
                <div class="background-section auto-calculated">
                    <div class="background-header">
                        <h3>🧬 Generation (Auto-calculated)</h3>
                        <div class="background-progress">
                            <div class="background-progress-label">
                                <span><span id="generationCountDisplay">0</span> points</span>
                            </div>
                            <div class="background-progress-bar">
                                <div class="background-progress-fill" id="generationProgressFill" style="width: 0%;">
                                    <div class="background-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="background-description">Your vampire generation background is automatically calculated from your Basic Info generation selection. Lower generation numbers (closer to Caine) provide more influence and status.</p>
                    <div class="background-list" id="generationList">
                        <div class="background-empty">Generation will be calculated from Basic Info</div>
                    </div>
                </div>
                
                <!-- Allies -->
                <div class="background-section">
                    <div class="background-header">
                        <h3>🤝 Allies</h3>
                        <div class="background-progress">
                            <div class="background-progress-label">
                                <span><span id="alliesCountDisplay">0</span> points</span>
                            </div>
                            <div class="background-progress-bar">
                                <div class="background-progress-fill" id="alliesProgressFill" style="width: 0%;">
                                    <div class="background-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="background-description">Friends, contacts, and people who will help you. Each point represents a significant ally or group of allies.</p>
                    <div class="background-options">
                        <button type="button" class="background-option-btn" data-background="Allies" data-level="1" onclick="selectBackground('Allies', 1)">1</button>
                        <button type="button" class="background-option-btn" data-background="Allies" data-level="2" onclick="selectBackground('Allies', 2)">2</button>
                        <button type="button" class="background-option-btn" data-background="Allies" data-level="3" onclick="selectBackground('Allies', 3)">3</button>
                        <button type="button" class="background-option-btn" data-background="Allies" data-level="4" onclick="selectBackground('Allies', 4)">4</button>
                        <button type="button" class="background-option-btn" data-background="Allies" data-level="5" onclick="selectBackground('Allies', 5)">5</button>
                    </div>
                    <div class="background-list" id="alliesList"></div>
                    <div class="background-details">
                        <label for="alliesDetails">Additional Information:</label>
                        <textarea id="alliesDetails" class="background-textarea" placeholder="Describe your allies (e.g., 'A D&D group that meets every Wednesday night', 'My old college friends who work in law enforcement')" rows="2"></textarea>
                    </div>
                </div>
                
                <!-- Contacts -->
                <div class="background-section">
                    <div class="background-header">
                        <h3>📞 Contacts</h3>
                        <div class="background-progress">
                            <div class="background-progress-label">
                                <span><span id="contactsCountDisplay">0</span> points</span>
                            </div>
                            <div class="background-progress-bar">
                                <div class="background-progress-fill" id="contactsProgressFill" style="width: 0%;">
                                    <div class="background-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="background-description">Information networks, informants, and people who provide you with knowledge and intelligence.</p>
                    <div class="background-options">
                        <button type="button" class="background-option-btn" data-background="Contacts" data-level="1" onclick="selectBackground('Contacts', 1)">1</button>
                        <button type="button" class="background-option-btn" data-background="Contacts" data-level="2" onclick="selectBackground('Contacts', 2)">2</button>
                        <button type="button" class="background-option-btn" data-background="Contacts" data-level="3" onclick="selectBackground('Contacts', 3)">3</button>
                        <button type="button" class="background-option-btn" data-background="Contacts" data-level="4" onclick="selectBackground('Contacts', 4)">4</button>
                        <button type="button" class="background-option-btn" data-background="Contacts" data-level="5" onclick="selectBackground('Contacts', 5)">5</button>
                    </div>
                    <div class="background-list" id="contactsList"></div>
                    <div class="background-details">
                        <label for="contactsDetails">Additional Information:</label>
                        <textarea id="contactsDetails" class="background-textarea" placeholder="Describe your contacts (e.g., 'Police informant in downtown precinct', 'Journalist at the local newspaper')" rows="2"></textarea>
                    </div>
                </div>
                
                <!-- Fame -->
                <div class="background-section">
                    <div class="background-header">
                        <h3>⭐ Fame</h3>
                        <div class="background-progress">
                            <div class="background-progress-label">
                                <span><span id="fameCountDisplay">0</span> points</span>
                            </div>
                            <div class="background-progress-bar">
                                <div class="background-progress-fill" id="fameProgressFill" style="width: 0%;">
                                    <div class="background-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="background-description">Public recognition, reputation, and celebrity status in mortal or Kindred society.</p>
                    <div class="background-options">
                        <button type="button" class="background-option-btn" data-background="Fame" data-level="1" onclick="selectBackground('Fame', 1)">1</button>
                        <button type="button" class="background-option-btn" data-background="Fame" data-level="2" onclick="selectBackground('Fame', 2)">2</button>
                        <button type="button" class="background-option-btn" data-background="Fame" data-level="3" onclick="selectBackground('Fame', 3)">3</button>
                        <button type="button" class="background-option-btn" data-background="Fame" data-level="4" onclick="selectBackground('Fame', 4)">4</button>
                        <button type="button" class="background-option-btn" data-background="Fame" data-level="5" onclick="selectBackground('Fame', 5)">5</button>
                    </div>
                    <div class="background-list" id="fameList"></div>
                    <div class="background-details">
                        <label for="fameDetails">Additional Information:</label>
                        <textarea id="fameDetails" class="background-textarea" placeholder="Describe your fame (e.g., 'Local TV news anchor', 'Famous musician in the underground scene')" rows="2"></textarea>
                    </div>
                </div>
                
                
                <!-- Herd -->
                <div class="background-section">
                    <div class="background-header">
                        <h3>🩸 Herd</h3>
                        <div class="background-progress">
                            <div class="background-progress-label">
                                <span><span id="herdCountDisplay">0</span> points</span>
                            </div>
                            <div class="background-progress-bar">
                                <div class="background-progress-fill" id="herdProgressFill" style="width: 0%;">
                                    <div class="background-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="background-description">Regular sources of blood - people who willingly or unknowingly provide sustenance.</p>
                    <div class="background-options">
                        <button type="button" class="background-option-btn" data-background="Herd" data-level="1" onclick="selectBackground('Herd', 1)">1</button>
                        <button type="button" class="background-option-btn" data-background="Herd" data-level="2" onclick="selectBackground('Herd', 2)">2</button>
                        <button type="button" class="background-option-btn" data-background="Herd" data-level="3" onclick="selectBackground('Herd', 3)">3</button>
                        <button type="button" class="background-option-btn" data-background="Herd" data-level="4" onclick="selectBackground('Herd', 4)">4</button>
                        <button type="button" class="background-option-btn" data-background="Herd" data-level="5" onclick="selectBackground('Herd', 5)">5</button>
                    </div>
                    <div class="background-list" id="herdList"></div>
                    <div class="background-details">
                        <label for="herdDetails">Additional Information:</label>
                        <textarea id="herdDetails" class="background-textarea" placeholder="Describe your herd (e.g., 'A D&D group that meets every Wednesday night', 'Regulars at the local coffee shop')" rows="2"></textarea>
                    </div>
                </div>
                
                <!-- Influence -->
                <div class="background-section">
                    <div class="background-header">
                        <h3>🏛️ Influence</h3>
                        <div class="background-progress">
                            <div class="background-progress-label">
                                <span><span id="influenceCountDisplay">0</span> points</span>
                            </div>
                            <div class="background-progress-bar">
                                <div class="background-progress-fill" id="influenceProgressFill" style="width: 0%;">
                                    <div class="background-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="background-description">Political power, social influence, and ability to affect change in mortal or Kindred society.</p>
                    <div class="background-options">
                        <button type="button" class="background-option-btn" data-background="Influence" data-level="1" onclick="selectBackground('Influence', 1)">1</button>
                        <button type="button" class="background-option-btn" data-background="Influence" data-level="2" onclick="selectBackground('Influence', 2)">2</button>
                        <button type="button" class="background-option-btn" data-background="Influence" data-level="3" onclick="selectBackground('Influence', 3)">3</button>
                        <button type="button" class="background-option-btn" data-background="Influence" data-level="4" onclick="selectBackground('Influence', 4)">4</button>
                        <button type="button" class="background-option-btn" data-background="Influence" data-level="5" onclick="selectBackground('Influence', 5)">5</button>
                    </div>
                    <div class="background-list" id="influenceList"></div>
                    <div class="background-details">
                        <label for="influenceDetails">Additional Information:</label>
                        <textarea id="influenceDetails" class="background-textarea" placeholder="Describe your influence (e.g., 'City council member', 'Union representative')" rows="2"></textarea>
                    </div>
                </div>
                
                <!-- Mentor -->
                <div class="background-section">
                    <div class="background-header">
                        <h3>👨‍🏫 Mentor</h3>
                        <div class="background-progress">
                            <div class="background-progress-label">
                                <span><span id="mentorCountDisplay">0</span> points</span>
                            </div>
                            <div class="background-progress-bar">
                                <div class="background-progress-fill" id="mentorProgressFill" style="width: 0%;">
                                    <div class="background-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="background-description">A teacher, guide, or patron who provides knowledge, protection, and guidance.</p>
                    <div class="background-options">
                        <button type="button" class="background-option-btn" data-background="Mentor" data-level="1" onclick="selectBackground('Mentor', 1)">1</button>
                        <button type="button" class="background-option-btn" data-background="Mentor" data-level="2" onclick="selectBackground('Mentor', 2)">2</button>
                        <button type="button" class="background-option-btn" data-background="Mentor" data-level="3" onclick="selectBackground('Mentor', 3)">3</button>
                        <button type="button" class="background-option-btn" data-background="Mentor" data-level="4" onclick="selectBackground('Mentor', 4)">4</button>
                        <button type="button" class="background-option-btn" data-background="Mentor" data-level="5" onclick="selectBackground('Mentor', 5)">5</button>
                    </div>
                    <div class="background-list" id="mentorList"></div>
                    <div class="background-details">
                        <label for="mentorDetails">Additional Information:</label>
                        <textarea id="mentorDetails" class="background-textarea" placeholder="Describe your mentor (e.g., 'Elder Ventrue who taught me politics', 'Former military officer')" rows="2"></textarea>
                    </div>
                </div>
                
                <!-- Resources -->
                <div class="background-section">
                    <div class="background-header">
                        <h3>💰 Resources</h3>
                        <div class="background-progress">
                            <div class="background-progress-label">
                                <span><span id="resourcesCountDisplay">0</span> points</span>
                            </div>
                            <div class="background-progress-bar">
                                <div class="background-progress-fill" id="resourcesProgressFill" style="width: 0%;">
                                    <div class="background-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="background-description">Money, property, equipment, and material wealth available to your character.</p>
                    <div class="background-options">
                        <button type="button" class="background-option-btn" data-background="Resources" data-level="1" onclick="selectBackground('Resources', 1)">1</button>
                        <button type="button" class="background-option-btn" data-background="Resources" data-level="2" onclick="selectBackground('Resources', 2)">2</button>
                        <button type="button" class="background-option-btn" data-background="Resources" data-level="3" onclick="selectBackground('Resources', 3)">3</button>
                        <button type="button" class="background-option-btn" data-background="Resources" data-level="4" onclick="selectBackground('Resources', 4)">4</button>
                        <button type="button" class="background-option-btn" data-background="Resources" data-level="5" onclick="selectBackground('Resources', 5)">5</button>
                    </div>
                    <div class="background-list" id="resourcesList"></div>
                    <div class="background-details">
                        <label for="resourcesDetails">Additional Information:</label>
                        <textarea id="resourcesDetails" class="background-textarea" placeholder="Describe your resources (e.g., 'Inherited family business', 'Tech startup shares')" rows="2"></textarea>
                    </div>
                </div>
                
                <!-- Retainers -->
                <div class="background-section">
                    <div class="background-header">
                        <h3>👥 Retainers</h3>
                        <div class="background-progress">
                            <div class="background-progress-label">
                                <span><span id="retainersCountDisplay">0</span> points</span>
                            </div>
                            <div class="background-progress-bar">
                                <div class="background-progress-fill" id="retainersProgressFill" style="width: 0%;">
                                    <div class="background-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="background-description">Servants, assistants, and loyal followers who serve your character.</p>
                    <div class="background-options">
                        <button type="button" class="background-option-btn" data-background="Retainers" data-level="1" onclick="selectBackground('Retainers', 1)">1</button>
                        <button type="button" class="background-option-btn" data-background="Retainers" data-level="2" onclick="selectBackground('Retainers', 2)">2</button>
                        <button type="button" class="background-option-btn" data-background="Retainers" data-level="3" onclick="selectBackground('Retainers', 3)">3</button>
                        <button type="button" class="background-option-btn" data-background="Retainers" data-level="4" onclick="selectBackground('Retainers', 4)">4</button>
                        <button type="button" class="background-option-btn" data-background="Retainers" data-level="5" onclick="selectBackground('Retainers', 5)">5</button>
                    </div>
                    <div class="background-list" id="retainersList"></div>
                    <div class="background-details">
                        <label for="retainersDetails">Additional Information:</label>
                        <textarea id="retainersDetails" class="background-textarea" placeholder="Describe your retainers (e.g., 'Personal assistant and bodyguard', 'Housekeeper and cook')" rows="2"></textarea>
                    </div>
                </div>
                
                <!-- Status -->
                <div class="background-section">
                    <div class="background-header">
                        <h3>👑 Status</h3>
                        <div class="background-progress">
                            <div class="background-progress-label">
                                <span><span id="statusCountDisplay">0</span> points</span>
                            </div>
                            <div class="background-progress-bar">
                                <div class="background-progress-fill" id="statusProgressFill" style="width: 0%;">
                                    <div class="background-progress-marker"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="background-description">Social standing, rank, and position within Kindred society or mortal organizations.</p>
                    <div class="background-options">
                        <button type="button" class="background-option-btn" data-background="Status" data-level="1" onclick="selectBackground('Status', 1)">1</button>
                        <button type="button" class="background-option-btn" data-background="Status" data-level="2" onclick="selectBackground('Status', 2)">2</button>
                        <button type="button" class="background-option-btn" data-background="Status" data-level="3" onclick="selectBackground('Status', 3)">3</button>
                        <button type="button" class="background-option-btn" data-background="Status" data-level="4" onclick="selectBackground('Status', 4)">4</button>
                        <button type="button" class="background-option-btn" data-background="Status" data-level="5" onclick="selectBackground('Status', 5)">5</button>
                    </div>
                    <div class="background-list" id="statusList"></div>
                    <div class="background-details">
                        <label for="statusDetails">Additional Information:</label>
                        <textarea id="statusDetails" class="background-textarea" placeholder="Describe your status (e.g., 'Prince of the city', 'Police lieutenant')" rows="2"></textarea>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="button" onclick="showTab(3)">← Previous</button>
                    <button type="button" class="save-btn" onclick="saveCharacter()">💾 Save Character</button>
                    <button type="button" onclick="showTab(5)">Next →</button>
                </div>
            </div>
            
            <!-- Tab 6: Morality -->
            <div class="tab-content" id="tab5">
                <h2 style="color: #8b0000; margin-bottom: 25px;">Morality</h2>
                <p>Morality & Stats section - Coming soon!</p>
                
                <div class="button-group">
                    <button type="button" onclick="showTab(4)">← Previous</button>
                    <button type="button" class="save-btn" onclick="saveCharacter()">💾 Save Character</button>
                    <button type="button" onclick="showTab(6)">Next →</button>
                </div>
            </div>
            
            <!-- Tab 7: Merits & Flaws -->
            <div class="tab-content" id="tab6">
                <h2 style="color: #8b0000; margin-bottom: 25px;">Merits & Flaws</h2>
                <p>Merits & Flaws section - Coming soon!</p>
                
                <div class="button-group">
                    <button type="button" onclick="showTab(5)">← Previous</button>
                    <button type="button" class="save-btn" onclick="saveCharacter()">💾 Save Character</button>
                    <button type="button" onclick="showTab(7)">Next →</button>
                </div>
            </div>
            
            <!-- Tab 8: Final Details -->
            <div class="tab-content" id="tab7">
                <h2 style="color: #8b0000; margin-bottom: 25px;">Final Details</h2>
                
                <div class="final-details-section">
                    <h3>Character Summary</h3>
                    <div id="characterSummary" class="character-summary">
                        <!-- Character summary will be populated by JavaScript -->
                    </div>
                    
                    <div class="finalization-options">
                        <h3>Finalize Your Character</h3>
                        <p>When you're ready to complete your character, click "Finalize Character" to save it permanently and generate your character sheet.</p>
                
                <div class="button-group">
                    <button type="button" onclick="showTab(6)">← Previous</button>
                            <button type="button" class="save-btn" onclick="saveCharacter()">💾 Save Draft</button>
                            <button type="button" class="finalize-btn" onclick="showFinalizePopup()">🎯 Finalize Character</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Clan Guide Modal -->
    <div id="clanGuideModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Clan Guide</h2>
                <button type="button" class="modal-close" onclick="closeClanGuide()">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>Complete guide to all vampire clans:</strong></p>
                <div class="clan-table-container">
                    <table class="clan-table">
                        <thead>
                            <tr>
                                <th>Clan</th>
                                <th>Disciplines</th>
                                <th>Weakness</th>
                                <th>Theme</th>
                                <th>Availability</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><strong>⚔️ Assamite</strong></td><td>Celerity, Obfuscate, Quietus</td><td>Blood addiction to other vampires</td><td>Middle Eastern assassins</td><td class="admin-approval">Admin Approval</td></tr>
                            <tr><td><strong>✊ Brujah</strong></td><td>Celerity, Potence, Presence</td><td>Prone to frenzy when insulted</td><td>Rebels and warriors</td><td class="pc-available">PC Available</td></tr>
                            <tr><td><strong>🐍 Followers of Set</strong></td><td>Obfuscate, Presence, Serpentis</td><td>Cannot enter holy ground</td><td>Egyptian cultists</td><td class="admin-approval">Admin Approval</td></tr>
                            <tr><td><strong>🐺 Gangrel</strong></td><td>Animalism, Fortitude, Protean</td><td>Prone to bestial traits over time</td><td>Shapeshifters and survivors</td><td class="pc-available">PC Available</td></tr>
                            <tr><td><strong>💀 Giovanni</strong></td><td>Dominate, Potence, Necromancy</td><td>Cannot create blood bonds</td><td>Necromancers and businessmen</td><td class="admin-approval">Admin Approval</td></tr>
                            <tr><td><strong>🌑 Lasombra</strong></td><td>Dominate, Obtenebration, Potence</td><td>No reflection in mirrors</td><td>Shadow manipulators</td><td class="admin-approval">Admin Approval</td></tr>
                            <tr><td><strong>🎭 Malkavian</strong></td><td>Auspex, Dementation, Obfuscate</td><td>All have some form of derangement</td><td>Seers and madmen</td><td class="pc-available">PC Available</td></tr>
                            <tr><td><strong>🦇 Nosferatu</strong></td><td>Animalism, Obfuscate, Potence</td><td>Hideously deformed</td><td>Information brokers</td><td class="pc-available">PC Available</td></tr>
                            <tr><td><strong>🎪 Ravnos</strong></td><td>Animalism, Chimerstry, Fortitude</td><td>Cannot resist challenges to honor</td><td>Illusionists and tricksters</td><td class="admin-approval">Admin Approval</td></tr>
                            <tr><td><strong>🌹 Toreador</strong></td><td>Auspex, Celerity, Presence</td><td>Prone to distraction by beauty</td><td>Artists and socialites</td><td class="pc-available">PC Available</td></tr>
                            <tr><td><strong>⭐ Tremere</strong></td><td>Auspex, Dominate, Thaumaturgy</td><td>Cannot create childer without permission</td><td>Blood sorcerers and scholars</td><td class="pc-available">PC Available</td></tr>
                            <tr><td><strong>🧬 Tzimisce</strong></td><td>Animalism, Auspex, Vicissitude</td><td>Must sleep in native soil</td><td>Flesh shapers</td><td class="admin-approval">Admin Approval</td></tr>
                            <tr><td><strong>👑 Ventrue</strong></td><td>Dominate, Fortitude, Presence</td><td>Cannot feed from animals or the poor</td><td>Leaders and rulers</td><td class="pc-available">PC Available</td></tr>
                            <tr><td><strong>❓ Caitiff</strong></td><td>Choose any 3 disciplines</td><td>No clan weakness (but no clan support)</td><td>Clanless vampires who can appear in any sect</td><td class="pc-available">PC Available</td></tr>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 20px;">
                    <h3>Character Creation Tips</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                        <div>
                            <h4>For New Players:</h4>
                            <ul>
                                <li><strong>PC Available clans:</strong> Brujah, Caitiff, Gangrel, Malkavian, Nosferatu, Toreador, Tremere, Ventrue</li>
                                <li>Focus on your character concept</li>
                                <li>Read the weakness carefully</li>
                                <li>Use the discipline guide for abilities</li>
                            </ul>
                        </div>
                        <div>
                            <h4>For Experienced Players:</h4>
                            <ul>
                                <li><strong>Admin Approval clans:</strong> Assamite, Setites, Giovanni, Lasombra, Ravnos, Tzimisce</li>
                                <li>Plan your discipline build</li>
                                <li>Embrace the clan weakness as roleplay</li>
                                <li>Consider cross-training disciplines</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn" onclick="closeClanGuide()">Close</button>
            </div>
        </div>
    </div>

    <!-- Discipline Guide Modal -->
    <div id="disciplineGuideModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Discipline-Ability Guide</h2>
                <button type="button" class="modal-close" onclick="closeDisciplineGuide()">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>Recommended abilities for each Discipline:</strong></p>
                <div class="discipline-table-container">
                    <table class="discipline-table">
                        <thead>
                            <tr>
                                <th>Discipline</th>
                                <th>Recommended Abilities</th>
                                <th>Backgrounds That Fit</th>
                                <th>Notes / Role Synergy</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><strong>Animalism</strong></td><td>Animal Ken, Empathy, Survival</td><td>Allies (animals), Herd</td><td>Animal handler, Beast whisperer, Gangrel archetype</td></tr>
                            <tr><td><strong>Auspex</strong></td><td>Awareness, Investigation, Occult, Empathy</td><td>Contacts, Mentor</td><td>Seer, investigator, Tremere or Malkavian vision user</td></tr>
                            <tr><td><strong>Celerity</strong></td><td>Athletics, Dodge, Melee, Firearms</td><td>Resources (gear), Retainers</td><td>Speed fighter, assassin, duelist</td></tr>
                            <tr><td><strong>Dominate</strong></td><td>Leadership, Intimidation, Subterfuge, Law</td><td>Status, Influence, Retainers</td><td>Commander, manipulator, Ventrue archetype</td></tr>
                            <tr><td><strong>Fortitude</strong></td><td>Survival, Medicine</td><td>Mentor, Herd</td><td>Stoic survivor, soldier, protector</td></tr>
                            <tr><td><strong>Obfuscate</strong></td><td>Stealth, Subterfuge, Security</td><td>Contacts, Allies</td><td>Spy, infiltrator, Nosferatu archetype</td></tr>
                            <tr><td><strong>Potence</strong></td><td>Athletics, Brawl, Melee</td><td>Allies, Retainers</td><td>Enforcer, bruiser, physical powerhouse</td></tr>
                            <tr><td><strong>Presence</strong></td><td>Leadership, Expression, Empathy, Subterfuge, Performance</td><td>Fame, Status, Herd</td><td>Social manipulator, performer, leader</td></tr>
                            <tr><td><strong>Protean</strong></td><td>Survival, Animal Ken, Brawl</td><td>Allies, Herd</td><td>Shapeshifter, feral predator, Gangrel</td></tr>
                            <tr><td><strong>Thaumaturgy</strong></td><td>Occult, Academics, Linguistics, Science</td><td>Mentor, Library (ST-ruled), Resources</td><td>Ritualist, scholar, Tremere archetype</td></tr>
                            <tr><td><strong>Necromancy</strong></td><td>Occult, Investigation, Intimidation</td><td>Mentor, Contacts</td><td>Death mage, Giovanni archetype</td></tr>
                            <tr><td><strong>Obtenebration</strong></td><td>Occult, Intimidation</td><td>Influence, Allies</td><td>Shadow wielder, Lasombra archetype</td></tr>
                            <tr><td><strong>Chimerstry</strong></td><td>Expression, Subterfuge, Occult</td><td>Fame, Resources</td><td>Illusionist, artist, trickster</td></tr>
                            <tr><td><strong>Dementation</strong></td><td>Empathy, Occult, Subterfuge</td><td>Mentor, Contacts</td><td>Manipulative seer, mind-breaker, Malkavian</td></tr>
                            <tr><td><strong>Quietus</strong></td><td>Stealth, Medicine, Subterfuge</td><td>Retainers, Allies</td><td>Assassin, poisoner, infiltrator</td></tr>
                            <tr><td><strong>Vicissitude</strong></td><td>Medicine, Crafts, Occult</td><td>Resources, Retainers</td><td>Flesh shaper, artisan, Tzimisce archetype</td></tr>
                            <tr><td><strong>Serpentis</strong></td><td>Subterfuge, Expression, Empathy</td><td>Herd, Status</td><td>Seductive manipulator, Setite archetype</td></tr>
                            <tr><td><strong>Koldunic Sorcery</strong></td><td>Occult, Survival, Science</td><td>Mentor, Resources</td><td>Elemental shaman or geomancer</td></tr>
                            <tr><td><strong>Daimoinon</strong></td><td>Occult, Intimidation, Expression</td><td>Influence, Status</td><td>Infernalist, fearmonger</td></tr>
                            <tr><td><strong>Melpominee</strong></td><td>Performance, Expression, Empathy</td><td>Fame, Herd</td><td>Toreador bard, social seducer</td></tr>
                            <tr><td><strong>Valeren / Obeah</strong></td><td>Medicine, Empathy, Occult</td><td>Mentor, Status</td><td>Salubri healer or judge</td></tr>
                            <tr><td><strong>Mortis</strong></td><td>Occult, Medicine, Investigation</td><td>Mentor, Contacts</td><td>Death-touched necromancer (Cappadocian lineage)</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn" onclick="closeDisciplineGuide()">Close</button>
            </div>
        </div>
    </div>

    <!-- Finalize Character Modal -->
    <div id="finalizeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🎯 Finalize Character</h2>
                <button type="button" class="modal-close" onclick="closeFinalizeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="finalize-warning">
                    <h3>⚠️ Important Notice</h3>
                    <p>Finalizing your character will:</p>
                    <ul>
                        <li>✅ Save your character permanently to the database</li>
                        <li>✅ Mark the character as complete</li>
                        <li>✅ Enable advancement mode for future XP spending</li>
                        <li>❌ Lock certain character creation options</li>
                    </ul>
                    <p><strong>Are you sure you want to finalize this character?</strong></p>
                </div>
                
                <div class="character-preview">
                    <h4>Character Preview:</h4>
                    <div id="finalizePreview" class="preview-content">
                        <!-- Character preview will be populated by JavaScript -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeFinalizeModal()">Cancel</button>
                <button type="button" class="btn-finalize" onclick="finalizeCharacter()">🎯 Finalize Character</button>
            </div>
        </div>
    </div>

    <!-- Character Sheet Modal -->
    <div id="characterSheetModal" class="modal">
        <div class="modal-content large-modal">
            <div class="modal-header">
                <h2>📄 Character Sheet</h2>
                <button type="button" class="modal-close" onclick="closeCharacterSheetModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="characterSheetContent" class="character-sheet">
                    <!-- Character sheet will be populated by JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeCharacterSheetModal()">Close</button>
                <button type="button" class="btn-download" onclick="downloadCharacterSheet()">📥 Download PDF</button>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
