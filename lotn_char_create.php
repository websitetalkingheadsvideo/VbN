<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>lotn_char_create</title>
</head>

<body>
	<?php
// LOTN Character Creator - Version 0.2.0
define('LOTN_VERSION', '0.3.0');

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
            <div class="version-info">
                <span class="version">v<?php echo LOTN_VERSION; ?></span>
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
                        <label for="clan">Clan *</label>
                        <select id="clan" name="clan" required>
                            <option value="">Select Clan...</option>
                            <option value="Assamite">Assamite</option>
                            <option value="Brujah">Brujah</option>
                            <option value="Followers of Set">Followers of Set</option>
                            <option value="Gangrel">Gangrel</option>
                            <option value="Giovanni">Giovanni</option>
                            <option value="Lasombra">Lasombra</option>
                            <option value="Malkavian">Malkavian</option>
                            <option value="Nosferatu">Nosferatu</option>
                            <option value="Ravnos">Ravnos</option>
                            <option value="Toreador">Toreador</option>
                            <option value="Tremere">Tremere</option>
                            <option value="Tzimisce">Tzimisce</option>
                            <option value="Ventrue">Ventrue</option>
                            <option value="Caitiff">Caitiff</option>
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
                <p>Abilities section - Coming soon!</p>
                
                <div class="button-group">
                    <button type="button" onclick="showTab(1)">← Previous</button>
                    <button type="button" class="save-btn" onclick="saveCharacter()">💾 Save Character</button>
                    <button type="button" onclick="showTab(3)">Next →</button>
                </div>
            </div>
            
            <!-- Tab 4: Disciplines -->
            <div class="tab-content" id="tab3">
                <h2 style="color: #8b0000; margin-bottom: 25px;">Disciplines</h2>
                <p>Disciplines section - Coming soon!</p>
                
                <div class="button-group">
                    <button type="button" onclick="showTab(2)">← Previous</button>
                    <button type="button" class="save-btn" onclick="saveCharacter()">💾 Save Character</button>
                    <button type="button" onclick="showTab(4)">Next →</button>
                </div>
            </div>
            
            <!-- Tab 5: Backgrounds -->
            <div class="tab-content" id="tab4">
                <h2 style="color: #8b0000; margin-bottom: 25px;">Backgrounds</h2>
                <p>Backgrounds section - Coming soon!</p>
                
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
                <p>Final Details section - Coming soon!</p>
                
                <div class="button-group">
                    <button type="button" onclick="showTab(6)">← Previous</button>
                    <button type="button" class="save-btn" onclick="saveCharacter()">💾 Save Character</button>
                    <button type="button" disabled>Next →</button>
                </div>
            </div>
        </form>
    </div>

    <script src="js/script.js"></script>
</body>
</html>