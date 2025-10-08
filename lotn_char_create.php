<?php
// LOTN Character Creator - Version 0.2.9
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
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Source+Serif+Pro:ital,wght@0,400;0,600;1,400&family=IM+Fell+English:ital@0;1&family=Nosifer&display=swap" rel="stylesheet">
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
                <span class="stat-label">Merits & Flaws:</span>
                <span class="stat-value" id="xpMeritsFlaws">0</span>
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
                <span class="stat-value" id="conscienceValueOld">1</span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Self-Control:</span>
                <span class="stat-value" id="selfControlValueOld">1</span>
            </div>
            <div class="stat-line">
                <span class="stat-label">Courage:</span>
                <span class="stat-value" id="courageValue">1</span>
            </div>
        </div>
        
        <!-- Live Character Preview -->
        <div class="character-preview">
            <div class="preview-header">
                <h4>Character Preview</h4>
                <div class="sheet-mode-toggle">
                    <label class="toggle-label">
                        <input type="radio" name="sheetMode" value="full" checked>
                        <span class="toggle-text">Full</span>
                    </label>
                    <label class="toggle-label">
                        <input type="radio" name="sheetMode" value="compact">
                        <span class="toggle-text">Compact</span>
                    </label>
                </div>
            </div>
            <div class="preview-card" id="previewCard">
                <div class="preview-character-header">
                    <div class="preview-cash" id="previewCash">$100</div>
                    <div class="preview-name" id="previewName">Unknown Character</div>
                    <div class="preview-clan" id="previewClan">No Clan Selected</div>
                </div>
                
                <div class="preview-section">
                    <div class="preview-label">Physical Traits</div>
                    <div class="preview-traits" id="previewPhysical">
                        <span class="preview-trait">None selected</span>
                    </div>
                </div>
                
                <div class="preview-section">
                    <div class="preview-label">Social Traits</div>
                    <div class="preview-traits" id="previewSocial">
                        <span class="preview-trait">None selected</span>
                    </div>
                </div>
                
                <div class="preview-section">
                    <div class="preview-label">Mental Traits</div>
                    <div class="preview-traits" id="previewMental">
                        <span class="preview-trait">None selected</span>
                    </div>
                </div>
                
                <div class="preview-section">
                    <div class="preview-label">Abilities</div>
                    <div class="preview-traits" id="previewAbilities">
                        <span class="preview-trait">None selected</span>
                    </div>
                </div>
                
                <div class="preview-section">
                    <div class="preview-label">Disciplines</div>
                    <div class="preview-traits" id="previewDisciplines">
                        <span class="preview-trait">None selected</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container" id="sheet">
        <div class="header">
            <h1 class="brand">⚜ Laws of the Night: Character Creation ⚜</h1>
            <div class="header-center">
                <div class="user-info">
                    <span class="user-label">Logged in as:</span>
                    <span class="user-name"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest User'; ?></span>
                </div>
                <div class="version-info">
                    <span class="version">v<?php echo LOTN_VERSION; ?></span>
                </div>
            </div>
            <div class="header-right">
                <div class="xp-tracker">
                    <div class="label">Available XP</div>
                    <div class="xp-display" id="xpDisplay">30</div>
                    <div class="xp-label">Experience Points</div>
                </div>
            </div>
        </div>
        
        <div class="tabs">
            <button class="tab active tab-btn" data-tab="basic">Basic Info</button>
            <button class="tab tab-btn" data-tab="traits">Traits</button>
            <button class="tab tab-btn" data-tab="abilities">Abilities</button>
            <button class="tab tab-btn" data-tab="disciplines">Disciplines</button>
            <button class="tab tab-btn" data-tab="backgrounds">Backgrounds</button>
            <button class="tab tab-btn" data-tab="morality">Morality</button>
            <button class="tab tab-btn" data-tab="merits">Merits & Flaws</button>
            <button class="tab tab-btn" data-tab="review">Final Details</button>
        </div>
        
        <!-- Progress Indicator -->
        <div class="tab-progress">
            <div class="tab-progress-bar" id="tabProgressBar" style="width: 12.5%;"></div>
        </div>
        
        <form id="characterForm">
            <!-- Tab 1: Basic Info -->
            <div class="tab-content active" id="basicTab">
                <div class="tab-card">
                    <div class="card-header">
                        <h2 class="card-title">Basic Information</h2>
                        <p class="card-subtitle">Essential character details and background</p>
                    </div>
                
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
                            <button type="button" class="help-btn" data-action="show-clan-guide" title="View Clan Guide">
                                <span>?</span>
                            </button>
                        </div>
                        <select id="clan" name="clan" required>
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
                
                <!-- Health Levels & Willpower Display -->
                <div class="form-group">
                    <label>Health Levels & Willpower</label>
                    <div class="health-willpower-display">
                        <!-- Health Levels -->
                        <div class="virtue-section">
                            <div class="virtue-label">Health Levels</div>
                            <div class="virtue-bars">
                                <div class="virtue-progress-container">
                                    <div class="virtue-progress-fill" id="healthProgress"></div>
                                    <div class="virtue-level-markers" id="healthMarkers"></div>
                                </div>
                                <span class="virtue-value" id="healthValue">7</span>/7
                            </div>
                        </div>
                        
                        <!-- Willpower -->
                        <div class="virtue-section">
                            <div class="virtue-label">Willpower</div>
                            <div class="virtue-bars">
                                <div class="virtue-progress-container">
                                    <div class="virtue-progress-fill" id="willpowerProgress"></div>
                                    <div class="virtue-level-markers" id="willpowerMarkers"></div>
                                </div>
                                <span class="virtue-value" id="willpowerValue">5</span>/5
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="button" disabled>← Previous</button>
                    <button type="button" class="save-btn" data-action="save">💾 Save Character</button>
                    <button type="button" class="nav-btn" data-action="next">Next →</button>
                </div>
                </div>
            </div>
            
            <!-- Tab 2: Traits -->
            <div class="tab-content" id="traitsTab">
                <div class="tab-card">
                    <div class="card-header">
                        <h2 class="card-title">Traits</h2>
                        <p class="card-subtitle">Distribute points across Physical, Social, and Mental attributes</p>
                    </div>
                
                <div class="info-box">
                    <strong>Trait Point Distribution:</strong> You have 15 free trait points to distribute across three categories.
                    <ul>
                        <li><strong>One category gets 7 free points</strong></li>
                        <li><strong>Another category gets 5 free points</strong></li>
                        <li><strong>The third category gets 3 free points</strong></li>
                        <li>You can only select up to your allocated free points per category</li>
                        <li>No additional traits beyond your free points during character creation</li>
                        <li><strong>You can select the same trait multiple times</strong> - click the same trait button repeatedly</li>
                        <li><strong>Remove traits anytime</strong> - click the × button on any selected trait to remove it</li>
                        <li><strong>Negative traits give +4 XP each</strong> - select from the red negative trait sections below</li>
                    </ul>
                </div>
                
                <!-- Point Distribution Interface -->
                <div class="point-distribution">
                    <h3>Distribute Your 15 Free Trait Points</h3>
                    <div class="distribution-options">
                        <div class="quick-select">
                            <h4>Quick Select:</h4>
                            <button type="button" class="dist-btn" data-dist="physical-primary">Physical Primary (7/5/3)</button>
                            <button type="button" class="dist-btn" data-dist="social-primary">Social Primary (7/5/3)</button>
                            <button type="button" class="dist-btn" data-dist="mental-primary">Mental Primary (7/5/3)</button>
                        </div>
                        <div class="manual-distribution">
                            <h4>Manual Distribution:</h4>
                            <div class="dist-controls">
                                <div class="dist-control">
                                    <label>Physical:</label>
                                    <select id="physicalPoints" class="point-select">
                                        <option value="7">7 points</option>
                                        <option value="5">5 points</option>
                                        <option value="3">3 points</option>
                                    </select>
                                </div>
                                <div class="dist-control">
                                    <label>Social:</label>
                                    <select id="socialPoints" class="point-select">
                                        <option value="5">5 points</option>
                                        <option value="7">7 points</option>
                                        <option value="3">3 points</option>
                                    </select>
                                </div>
                                <div class="dist-control">
                                    <label>Mental:</label>
                                    <select id="mentalPoints" class="point-select">
                                        <option value="3">3 points</option>
                                        <option value="7">7 points</option>
                                        <option value="5">5 points</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="distribution-status">
                        <span id="distributionStatus">Current: Physical(7), Social(5), Mental(3)</span>
                    </div>
                </div>
                
                <!-- Physical Traits -->
                <div class="trait-section">
                    <div class="trait-header">
                        <h3>Physical Traits</h3>
                        <div class="trait-progress">
                            <div class="trait-progress-label">
                                <span><span id="physicalCountDisplay">0</span> selected</span>
                                <span><span id="physicalFreeDisplay">7</span> maximum</span>
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
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Agile">Agile</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Lithe">Lithe</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Nimble">Nimble</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Quick">Quick</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Spry">Spry</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Graceful">Graceful</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Slender">Slender</button>
                        
                        <!-- Strength & Endurance -->
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Strong">Strong</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Hardy">Hardy</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Tough">Tough</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Resilient">Resilient</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Sturdy">Sturdy</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Vigorous">Vigorous</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Burly">Burly</button>
                        
                        <!-- Dexterity & Coordination -->
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Coordinated">Coordinated</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Precise">Precise</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Steady-handed">Steady-handed</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Sleek">Sleek</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Flexible">Flexible</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Balanced">Balanced</button>
                        
                        <!-- Reflexes & Awareness -->
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Alert">Alert</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Sharp-eyed">Sharp-eyed</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Quick-reflexed">Quick-reflexed</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Perceptive">Perceptive</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Reactive">Reactive</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Observant">Observant</button>
                        
                        <!-- Appearance / Presence of Body -->
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Athletic">Athletic</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Well-built">Well-built</button>
                        
                        <!-- Legacy traits -->
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Fast">Fast</button>
                        <button type="button" class="trait-option-btn" data-category="Physical" data-trait="Muscular">Muscular</button>
                    </div>
                    
                    <div class="trait-list" id="physicalTraitList">
                    </div>
                    
                    <!-- Physical Negative Traits -->
                    <div class="negative-traits-section">
                        <h4>Physical Negative Traits (+4 XP each)</h4>
                        <div class="trait-options" id="physicalNegativeOptions">
                            <button type="button" class="trait-option-btn negative" data-category="Physical" data-trait="Frail">Frail</button>
                            <button type="button" class="trait-option-btn negative" data-category="Physical" data-trait="Slow">Slow</button>
                            <button type="button" class="trait-option-btn negative" data-category="Physical" data-trait="Weak">Weak</button>
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
                                <span><span id="socialFreeDisplay">5</span> maximum</span>
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
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Charming">Charming</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Persuasive">Persuasive</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Charismatic">Charismatic</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Graceful">Graceful</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Poised">Poised</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Attractive">Attractive</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Handsome">Handsome</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Beautiful">Beautiful</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Seductive">Seductive</button>
                        
                        <!-- Manipulation & Deception -->
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Cunning">Cunning</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Deceptive">Deceptive</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Manipulative">Manipulative</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Subtle">Subtle</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Diplomatic">Diplomatic</button>
                        
                        <!-- Personality / Presence -->
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Sociable">Sociable</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Friendly">Friendly</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Outgoing">Outgoing</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Bold">Bold</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Confident">Confident</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Stubborn">Stubborn</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Witty">Witty</button>
                        
                        <!-- Leadership & Influence -->
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Commanding">Commanding</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Inspiring">Inspiring</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Assertive">Assertive</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Authoritative">Authoritative</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Motivating">Motivating</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Loyal">Loyal</button>
                        
                        <!-- Legacy traits -->
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Elegant">Elegant</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Expressive">Expressive</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Striking">Striking</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Imposing">Imposing</button>
                        <button type="button" class="trait-option-btn" data-category="Social" data-trait="Intimidating">Intimidating</button>
                    </div>
                    
                    <div class="trait-list" id="socialTraitList">
                    </div>
                    
                    <!-- Social Negative Traits -->
                    <div class="negative-traits-section">
                        <h4>Social Negative Traits (+4 XP each)</h4>
                        <div class="trait-options" id="socialNegativeOptions">
                            <button type="button" class="trait-option-btn negative" data-category="Social" data-trait="Ugly">Ugly</button>
                            <button type="button" class="trait-option-btn negative" data-category="Social" data-trait="Awkward">Awkward</button>
                            <button type="button" class="trait-option-btn negative" data-category="Social" data-trait="Shy">Shy</button>
                            <button type="button" class="trait-option-btn negative" data-category="Social" data-trait="Rude">Rude</button>
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
                                <span><span id="mentalFreeDisplay">3</span> maximum</span>
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
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Intelligent">Intelligent</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Clever">Clever</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Learned">Learned</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Analytical">Analytical</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Scholarly">Scholarly</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Logical">Logical</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Resourceful">Resourceful</button>
                        
                        <!-- Perception & Awareness -->
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Observant">Observant</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Alert">Alert</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Sharp-eyed">Sharp-eyed</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Attentive">Attentive</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Quick-minded">Quick-minded</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Insightful">Insightful</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Perceptive">Perceptive</button>
                        
                        <!-- Memory & Recall -->
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Remembering">Remembering</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Studious">Studious</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Focused">Focused</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Methodical">Methodical</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Precise">Precise</button>
                        
                        <!-- Problem Solving & Planning -->
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Strategic">Strategic</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Calculating">Calculating</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Cunning">Cunning</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Patient">Patient</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Determined">Determined</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Adaptive">Adaptive</button>
                        
                        <!-- Personality / Mental Flavor -->
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Curious">Curious</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Witty">Witty</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Shrewd">Shrewd</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Eccentric">Eccentric</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Philosophical">Philosophical</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Persistent">Persistent</button>
                        
                        <!-- Legacy traits -->
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Calm">Calm</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Creative">Creative</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Dedicated">Dedicated</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Intuitive">Intuitive</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Rational">Rational</button>
                        <button type="button" class="trait-option-btn" data-category="Mental" data-trait="Wise">Wise</button>
                    </div>
                    
                    <div class="trait-list" id="mentalTraitList">
                    </div>
                    
                    <!-- Mental Negative Traits -->
                    <div class="negative-traits-section">
                        <h4>Mental Negative Traits (+4 XP each)</h4>
                        <div class="trait-options" id="mentalNegativeOptions">
                            <button type="button" class="trait-option-btn negative" data-category="Mental" data-trait="Dull">Dull</button>
                            <button type="button" class="trait-option-btn negative" data-category="Mental" data-trait="Scatterbrained">Scatterbrained</button>
                            <button type="button" class="trait-option-btn negative" data-category="Mental" data-trait="Absent-minded">Absent-minded</button>
                            <button type="button" class="trait-option-btn negative" data-category="Mental" data-trait="Distracted">Distracted</button>
                            <button type="button" class="trait-option-btn negative" data-category="Mental" data-trait="Forgetful">Forgetful</button>
                            <button type="button" class="trait-option-btn negative" data-category="Mental" data-trait="Rash">Rash</button>
                        </div>
                        <div class="trait-list" id="mentalNegativeTraitList">
                        </div>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="button" class="nav-btn" data-action="previous">← Previous</button>
                    <button type="button" class="save-btn" data-action="save">💾 Save Character</button>
                    <button type="button" class="nav-btn" data-action="next">Next →</button>
                </div>
                </div>
            </div>
            
            <!-- Tab 3: Abilities -->
            <div class="tab-content" id="abilitiesTab">
                <div class="tab-card">
                    <div class="card-header">
                        <h2 class="card-title">Abilities</h2>
                        <p class="card-subtitle">Choose your character's skills and talents</p>
                    </div>
                
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
                        <button type="button" class="help-btn" data-action="show-discipline-guide" title="View Discipline-Ability Guide">
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
                        <button type="button" class="ability-option-btn" data-category="Physical" data-ability="Athletics">Athletics</button>
                        <button type="button" class="ability-option-btn" data-category="Physical" data-ability="Brawl">Brawl</button>
                        <button type="button" class="ability-option-btn" data-category="Physical" data-ability="Dodge">Dodge</button>
                        <button type="button" class="ability-option-btn" data-category="Physical" data-ability="Firearms">Firearms</button>
                        <button type="button" class="ability-option-btn" data-category="Physical" data-ability="Melee">Melee</button>
                        <button type="button" class="ability-option-btn" data-category="Physical" data-ability="Security">Security</button>
                        <button type="button" class="ability-option-btn" data-category="Physical" data-ability="Stealth">Stealth</button>
                        <button type="button" class="ability-option-btn" data-category="Physical" data-ability="Survival">Survival</button>
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
                        <button type="button" class="ability-option-btn" data-category="Social" data-ability="Animal Ken">Animal Ken</button>
                        <button type="button" class="ability-option-btn" data-category="Social" data-ability="Empathy">Empathy</button>
                        <button type="button" class="ability-option-btn" data-category="Social" data-ability="Expression">Expression</button>
                        <button type="button" class="ability-option-btn" data-category="Social" data-ability="Intimidation">Intimidation</button>
                        <button type="button" class="ability-option-btn" data-category="Social" data-ability="Leadership">Leadership</button>
                        <button type="button" class="ability-option-btn" data-category="Social" data-ability="Subterfuge">Subterfuge</button>
                        <button type="button" class="ability-option-btn" data-category="Social" data-ability="Streetwise">Streetwise</button>
                        <button type="button" class="ability-option-btn" data-category="Social" data-ability="Etiquette">Etiquette</button>
                        <button type="button" class="ability-option-btn" data-category="Social" data-ability="Performance">Performance</button>
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
                        <button type="button" class="ability-option-btn" data-category="Mental" data-ability="Academics">Academics</button>
                        <button type="button" class="ability-option-btn" data-category="Mental" data-ability="Computer">Computer</button>
                        <button type="button" class="ability-option-btn" data-category="Mental" data-ability="Finance">Finance</button>
                        <button type="button" class="ability-option-btn" data-category="Mental" data-ability="Investigation">Investigation</button>
                        <button type="button" class="ability-option-btn" data-category="Mental" data-ability="Law">Law</button>
                        <button type="button" class="ability-option-btn" data-category="Mental" data-ability="Linguistics">Linguistics</button>
                        <button type="button" class="ability-option-btn" data-category="Mental" data-ability="Medicine">Medicine</button>
                        <button type="button" class="ability-option-btn" data-category="Mental" data-ability="Occult">Occult</button>
                        <button type="button" class="ability-option-btn" data-category="Mental" data-ability="Politics">Politics</button>
                        <button type="button" class="ability-option-btn" data-category="Mental" data-ability="Science">Science</button>
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
                        <button type="button" class="ability-option-btn" data-category="Optional" data-ability="Alertness">Alertness</button>
                        <button type="button" class="ability-option-btn" data-category="Optional" data-ability="Awareness">Awareness</button>
                        <button type="button" class="ability-option-btn" data-category="Optional" data-ability="Drive">Drive</button>
                        <button type="button" class="ability-option-btn" data-category="Optional" data-ability="Crafts">Crafts</button>
                        <button type="button" class="ability-option-btn" data-category="Optional" data-ability="Firecraft">Firecraft</button>
                    </div>
                    
                    <div class="ability-list" id="optionalAbilitiesList">
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="button" class="nav-btn" data-action="previous">← Previous</button>
                    <button type="button" class="save-btn" data-action="save">💾 Save Character</button>
                    <button type="button" class="nav-btn" data-action="next">Next →</button>
                </div>
                </div>
            </div>
            
            <!-- Tab 4: Disciplines -->
            <div class="tab-content" id="disciplinesTab">
                <div class="tab-card">
                    <div class="card-header">
                        <h2 class="card-title">Disciplines</h2>
                        <p class="card-subtitle">Supernatural powers unique to your clan</p>
                    </div>
                
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
                        <button type="button" class="help-btn" data-action="show-discipline-guide" title="View Discipline-Ability Guide">
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
                        <button type="button" class="discipline-option-btn" data-discipline="Thaumaturgy">Thaumaturgy</button>
                        <button type="button" class="discipline-option-btn" data-discipline="Necromancy">Necromancy</button>
                        <button type="button" class="discipline-option-btn" data-discipline="Koldunic Sorcery">Koldunic Sorcery</button>
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
                    <button type="button" class="nav-btn" data-action="previous">← Previous</button>
                    <button type="button" class="save-btn" data-action="save">💾 Save Character</button>
                    <button type="button" class="nav-btn" data-action="next">Next →</button>
                </div>
                </div>
            </div>
            
            <!-- Tab 5: Backgrounds -->
            <div class="tab-content" id="backgroundsTab">
                <div class="tab-card">
                    <div class="card-header">
                        <h2 class="card-title">Backgrounds</h2>
                        <p class="card-subtitle">Resources, allies, and connections</p>
                    </div>
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
                        <button type="button" class="background-option-btn" data-background="Allies" data-level="1">1</button>
                        <button type="button" class="background-option-btn" data-background="Allies" data-level="2">2</button>
                        <button type="button" class="background-option-btn" data-background="Allies" data-level="3">3</button>
                        <button type="button" class="background-option-btn" data-background="Allies" data-level="4">4</button>
                        <button type="button" class="background-option-btn" data-background="Allies" data-level="5">5</button>
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
                        <button type="button" class="background-option-btn" data-background="Contacts" data-level="1">1</button>
                        <button type="button" class="background-option-btn" data-background="Contacts" data-level="2">2</button>
                        <button type="button" class="background-option-btn" data-background="Contacts" data-level="3">3</button>
                        <button type="button" class="background-option-btn" data-background="Contacts" data-level="4">4</button>
                        <button type="button" class="background-option-btn" data-background="Contacts" data-level="5">5</button>
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
                        <button type="button" class="background-option-btn" data-background="Fame" data-level="1">1</button>
                        <button type="button" class="background-option-btn" data-background="Fame" data-level="2">2</button>
                        <button type="button" class="background-option-btn" data-background="Fame" data-level="3">3</button>
                        <button type="button" class="background-option-btn" data-background="Fame" data-level="4">4</button>
                        <button type="button" class="background-option-btn" data-background="Fame" data-level="5">5</button>
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
                        <button type="button" class="background-option-btn" data-background="Herd" data-level="1">1</button>
                        <button type="button" class="background-option-btn" data-background="Herd" data-level="2">2</button>
                        <button type="button" class="background-option-btn" data-background="Herd" data-level="3">3</button>
                        <button type="button" class="background-option-btn" data-background="Herd" data-level="4">4</button>
                        <button type="button" class="background-option-btn" data-background="Herd" data-level="5">5</button>
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
                        <button type="button" class="background-option-btn" data-background="Influence" data-level="1">1</button>
                        <button type="button" class="background-option-btn" data-background="Influence" data-level="2">2</button>
                        <button type="button" class="background-option-btn" data-background="Influence" data-level="3">3</button>
                        <button type="button" class="background-option-btn" data-background="Influence" data-level="4">4</button>
                        <button type="button" class="background-option-btn" data-background="Influence" data-level="5">5</button>
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
                        <button type="button" class="background-option-btn" data-background="Mentor" data-level="1">1</button>
                        <button type="button" class="background-option-btn" data-background="Mentor" data-level="2">2</button>
                        <button type="button" class="background-option-btn" data-background="Mentor" data-level="3">3</button>
                        <button type="button" class="background-option-btn" data-background="Mentor" data-level="4">4</button>
                        <button type="button" class="background-option-btn" data-background="Mentor" data-level="5">5</button>
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
                        <button type="button" class="background-option-btn" data-background="Resources" data-level="1">1</button>
                        <button type="button" class="background-option-btn" data-background="Resources" data-level="2">2</button>
                        <button type="button" class="background-option-btn" data-background="Resources" data-level="3">3</button>
                        <button type="button" class="background-option-btn" data-background="Resources" data-level="4">4</button>
                        <button type="button" class="background-option-btn" data-background="Resources" data-level="5">5</button>
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
                        <button type="button" class="background-option-btn" data-background="Retainers" data-level="1">1</button>
                        <button type="button" class="background-option-btn" data-background="Retainers" data-level="2">2</button>
                        <button type="button" class="background-option-btn" data-background="Retainers" data-level="3">3</button>
                        <button type="button" class="background-option-btn" data-background="Retainers" data-level="4">4</button>
                        <button type="button" class="background-option-btn" data-background="Retainers" data-level="5">5</button>
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
                        <button type="button" class="background-option-btn" data-background="Status" data-level="1">1</button>
                        <button type="button" class="background-option-btn" data-background="Status" data-level="2">2</button>
                        <button type="button" class="background-option-btn" data-background="Status" data-level="3">3</button>
                        <button type="button" class="background-option-btn" data-background="Status" data-level="4">4</button>
                        <button type="button" class="background-option-btn" data-background="Status" data-level="5">5</button>
                    </div>
                    <div class="background-list" id="statusList"></div>
                    <div class="background-details">
                        <label for="statusDetails">Additional Information:</label>
                        <textarea id="statusDetails" class="background-textarea" placeholder="Describe your status (e.g., 'Prince of the city', 'Police lieutenant')" rows="2"></textarea>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="button" class="nav-btn" data-action="previous">← Previous</button>
                    <button type="button" class="save-btn" data-action="save">💾 Save Character</button>
                    <button type="button" class="nav-btn" data-action="next">Next →</button>
                </div>
                </div>
            </div>
            
            <!-- Tab 6: Morality -->
            <div class="tab-content" id="moralityTab">
                <div class="tab-card">
                    <div class="card-header">
                        <h2 class="card-title">Path of Humanity</h2>
                        <p class="card-subtitle">Virtues, willpower, and moral compass</p>
                    </div>
                    
                    <div class="morality-section">
                        <!-- Humanity Display -->
                        <div class="morality-stat">
                            <div class="stat-header">
                                <h3>Humanity</h3>
                                <span class="moral-state" id="moralStateDisplay">Conflicted</span>
                            </div>
                            <div class="humanity-bar">
                                <div class="humanity-track">
                                    <div class="humanity-fill" id="humanityFill" style="width: 80%;"></div>
                                </div>
                                <div class="humanity-value">
                                    <span id="humanityValue">8</span>/10
                                </div>
                            </div>
                        </div>
                        
                        <!-- Virtues Section -->
                        <div class="virtues-section">
                            <h3>Virtues</h3>
                            <p class="virtue-instructions">Distribute 7 points between your two Virtues (minimum 1 each)</p>
                            <div class="virtue-allocation">
                                <div class="virtue-points-remaining">
                                    <span class="points-label">Points Remaining:</span>
                                    <span class="points-value" id="virtuePointsRemaining">7</span>
                                </div>
                            </div>
                            <div class="virtue-grid">
                                <div class="virtue-stat">
                                    <label for="conscience">Conscience</label>
                                    <div class="virtue-display">
                                        <span class="virtue-label">Resists degeneration</span>
                                        <div class="virtue-controls">
                                            <button type="button" class="virtue-btn" onclick="adjustVirtue('conscience', -1)" id="conscienceMinus">-</button>
                                            <div class="virtue-bars">
                                                <div class="virtue-progress-container">
                                                    <div class="virtue-progress-fill" id="conscienceProgress"></div>
                                                    <div class="virtue-level-markers" id="conscienceMarkers"></div>
                                                </div>
                                                <span class="virtue-value" id="conscienceValue">1</span>/5
                                            </div>
                                            <button type="button" class="virtue-btn" onclick="adjustVirtue('conscience', 1)" id="consciencePlus">+</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="virtue-stat">
                                    <label for="selfControl">Self-Control</label>
                                    <div class="virtue-display">
                                        <span class="virtue-label">Resists frenzy</span>
                                        <div class="virtue-controls">
                                            <button type="button" class="virtue-btn" onclick="adjustVirtue('selfControl', -1)" id="selfControlMinus">-</button>
                                            <div class="virtue-bars">
                                                <div class="virtue-progress-container">
                                                    <div class="virtue-progress-fill" id="selfControlProgress"></div>
                                                    <div class="virtue-level-markers" id="selfControlMarkers"></div>
                                                </div>
                                                <span class="virtue-value" id="selfControlValue">1</span>/5
                                            </div>
                                            <button type="button" class="virtue-btn" onclick="adjustVirtue('selfControl', 1)" id="selfControlPlus">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Humanity Display -->
                        <div class="humanity-info">
                            <h3>Starting Humanity</h3>
                            <p class="help-text">Your starting Humanity equals Conscience + Self-Control. You can raise it with Freebie Points (2 points per dot).</p>
                            <div class="humanity-calculation">
                                <div class="calculation-display">
                                    <span class="calculation-text">Conscience + Self-Control = Humanity</span>
                                    <span class="calculation-formula" id="humanityCalculation">1 + 1 = 2</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hierarchy of Sins Reference -->
                        <div class="sins-reference">
                            <h3>Hierarchy of Sins</h3>
                            <div class="sins-list">
                                <div class="sin-level" data-level="10">
                                    <span class="sin-number">10</span>
                                    <span class="sin-description">Selfish thoughts</span>
                                </div>
                                <div class="sin-level" data-level="9">
                                    <span class="sin-number">9</span>
                                    <span class="sin-description">Minor selfish acts</span>
                                </div>
                                <div class="sin-level" data-level="8">
                                    <span class="sin-number">8</span>
                                    <span class="sin-description">Injury to another</span>
                                </div>
                                <div class="sin-level" data-level="7">
                                    <span class="sin-number">7</span>
                                    <span class="sin-description">Theft, petty crime</span>
                                </div>
                                <div class="sin-level" data-level="6">
                                    <span class="sin-number">6</span>
                                    <span class="sin-description">Destruction of property</span>
                                </div>
                                <div class="sin-level" data-level="5">
                                    <span class="sin-number">5</span>
                                    <span class="sin-description">Intentional injury</span>
                                </div>
                                <div class="sin-level" data-level="4">
                                    <span class="sin-number">4</span>
                                    <span class="sin-description">Impassioned killing</span>
                                </div>
                                <div class="sin-level" data-level="3">
                                    <span class="sin-number">3</span>
                                    <span class="sin-description">Planned killing, torture</span>
                                </div>
                                <div class="sin-level" data-level="2">
                                    <span class="sin-number">2</span>
                                    <span class="sin-description">Casual killing</span>
                                </div>
                                <div class="sin-level" data-level="1">
                                    <span class="sin-number">1</span>
                                    <span class="sin-description">Utterly depraved acts</span>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <div class="button-group">
                        <button type="button" class="nav-btn" data-action="previous">← Previous</button>
                        <button type="button" class="save-btn" data-action="save">💾 Save Character</button>
                        <button type="button" class="nav-btn" data-action="next">Next →</button>
                    </div>
                </div>
            </div>
            
            <!-- Tab 7: Merits & Flaws -->
            <div class="tab-content" id="meritsTab">
                <div class="tab-card">
                    <div class="card-header">
                        <h2 class="card-title">Merits & Flaws</h2>
                        <p class="card-subtitle">Special advantages and disadvantages</p>
                    </div>
                    
                    <!-- Merits & Flaws Summary -->
                    <div class="merits-flaws-summary">
                        <div class="summary-item">
                            <span class="label">Merits Cost:</span>
                            <span class="value" id="meritsCost">0</span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Flaws Points:</span>
                            <span class="value" id="flawsPoints">0</span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Net Cost:</span>
                            <span class="value" id="netCost">0</span>
                        </div>
                    </div>
                    
                    <!-- Filter and Search Controls -->
                    <div class="filter-controls">
                        <div class="filter-group">
                            <label>Filter by Category:</label>
                            <select id="categoryFilter" data-action="filter-merits-flaws">
                                <option value="all">All Categories</option>
                                <option value="Physical">💪 Physical</option>
                                <option value="Mental">🧠 Mental</option>
                                <option value="Social">👥 Social</option>
                                <option value="Supernatural">✨ Supernatural</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Show:</label>
                            <select id="typeFilter" data-action="filter-merits-flaws">
                                <option value="both">Merits & Flaws</option>
                                <option value="merits">Merits Only</option>
                                <option value="flaws">Flaws Only</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Sort by:</label>
                            <select id="sortFilter" data-action="filter-merits-flaws">
                                <option value="cost">Cost (Low to High)</option>
                                <option value="cost-desc">Cost (High to Low)</option>
                                <option value="name">Name (A-Z)</option>
                                <option value="name-desc">Name (Z-A)</option>
                                <option value="category">Category</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Search:</label>
                            <input type="text" id="searchFilter" placeholder="Search merits and flaws..." onkeyup="filterMeritsFlaws()">
                        </div>
                        <div class="filter-group">
                            <button type="button" class="reset-filters-btn" onclick="resetMeritsFlawsFilters()" title="Reset all filters">
                                🔄 Reset
                            </button>
                        </div>
                    </div>
                    
                    <!-- Available Merits & Flaws -->
                    <div class="merits-flaws-container">
                        <div class="available-section">
                            <h3>Available</h3>
                            <div class="merits-flaws-list" id="availableList">
                                <!-- Will be populated by JavaScript -->
                            </div>
                        </div>
                        
                        <div class="selected-section">
                            <h3>Selected</h3>
                            <div class="merits-flaws-list" id="selectedList">
                                <!-- Will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Conflict Warning -->
                    <div class="conflict-warning" id="conflictWarning" style="display: none;">
                        <span class="warning-icon">⚠️</span>
                        <span class="warning-text" id="conflictText"></span>
                    </div>
                    
                    <div class="button-group">
                        <button type="button" class="nav-btn" data-action="previous">← Previous</button>
                        <button type="button" class="save-btn" data-action="save">💾 Save Character</button>
                        <button type="button" class="nav-btn" data-action="next">Next →</button>
                    </div>
                </div>
            </div>
            
            <!-- Tab 7: Final Details -->
            <div class="tab-content" id="reviewTab">
                <div class="tab-card">
                    <div class="card-header">
                        <h2 class="card-title">Final Details</h2>
                        <p class="card-subtitle">Complete your character and review</p>
                    </div>
                
                    <div class="form-group">
                        <label>Character Summary</label>
                        <div id="characterSummary" class="character-summary">
                            <!-- Character summary will be generated here -->
                        </div>
                    </div>
                    
                    
                    <div class="button-group">
                        <button type="button" class="nav-btn" data-action="previous">← Previous</button>
                        <button type="button" class="save-btn" data-action="save">💾 Save Draft</button>
                        <button type="button" class="finalize-btn" data-action="finalize-character">🎯 Finalize Character</button>
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
                <button type="button" class="modal-close" data-action="close-discipline-guide">&times;</button>
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
                <button type="button" class="modal-btn" data-action="close-discipline-guide">Close</button>
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

    <!-- Merit/Flaw Description Modal -->
    <div id="meritFlawDescriptionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="meritFlawModalTitle">Merit/Flaw Description</h2>
                <button type="button" class="modal-close" onclick="closeMeritFlawDescription()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="merit-flaw-detail">
                    <div class="detail-columns">
                        <div class="detail-column">
                            <span class="detail-label">Type:</span>
                            <span class="detail-value" id="meritFlawType"></span>
                        </div>
                        <div class="detail-column">
                            <span class="detail-label">Category:</span>
                            <span class="detail-value" id="meritFlawCategory"></span>
                        </div>
                        <div class="detail-column">
                            <span class="detail-label">Cost:</span>
                            <span class="detail-value" id="meritFlawCost"></span>
                        </div>
                    </div>
                    <div class="detail-description">
                        <h4>Description:</h4>
                        <p id="meritFlawDescription"></p>
                    </div>
                    <div class="detail-effects" id="meritFlawEffects" style="display: none;">
                        <h4>Effects:</h4>
                        <ul id="meritFlawEffectsList"></ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn" onclick="closeMeritFlawDescription()">Close</button>
            </div>
        </div>
    </div>

    <!-- Modular JavaScript Architecture -->
    <!-- Core Modules -->
    <script src="js/modules/core/StateManager.js"></script>
    <script src="js/modules/core/UIManager.js"></script>
    <script src="js/modules/core/EventManager.js"></script>
    <script src="js/modules/core/DataManager.js"></script>
    <script src="js/modules/core/NotificationManager.js"></script>
    <script src="js/modules/core/ValidationManager.js"></script>
    
    <!-- UI Modules -->
    <script src="js/modules/ui/TabManager.js"></script>
    <script src="js/modules/ui/PreviewManager.js"></script>
    
    <!-- System Modules -->
    <script src="js/modules/systems/TraitSystem.js"></script>
    <script src="js/modules/systems/AbilitySystem.js"></script>
    <!-- <script src="js/modules/systems/DisciplineSystem.js"></script> -->
    <script src="js/modules/systems/MeritsFlawsSystem.js"></script>
    <script src="js/modules/systems/BackgroundSystem.js"></script>
    <script src="js/modules/systems/MoralitySystem.js"></script>
    <script src="js/modules/systems/CashSystem.js"></script>
    <script src="js/modules/systems/HealthWillpowerSystem.js"></script>
    
    <!-- Main Application -->
    <script src="js/modules/main.js"></script>
    
    <!-- Working Discipline System from Yesterday -->
    <script>
        // Discipline system variables
        let currentPopoverTimeout = null;
        let currentPopoverButton = null;
        
        // Discipline powers data
        const disciplinePowers = {
            'Animalism': [
                { level: 1, name: 'Feral Whispers', description: 'Communicate with animals' },
                { level: 2, name: 'Animal Succulence', description: 'Feed from animals' },
                { level: 3, name: 'Quell the Beast', description: 'Calm frenzied vampires' },
                { level: 4, name: 'Subsume the Spirit', description: 'Possess animals' },
                { level: 5, name: 'Animal Dominion', description: 'Command all animals in area' }
            ],
            'Auspex': [
                { level: 1, name: 'Heightened Senses', description: 'Enhanced perception' },
                { level: 2, name: 'Aura Perception', description: 'See emotional auras' },
                { level: 3, name: 'The Spirit\'s Touch', description: 'Read objects\' history' },
                { level: 4, name: 'Telepathy', description: 'Read minds' },
                { level: 5, name: 'Psychic Projection', description: 'Astral projection' }
            ],
            'Celerity': [
                { level: 1, name: 'Cat\'s Grace', description: 'Enhanced speed and reflexes' },
                { level: 2, name: 'Rapid Reflexes', description: 'Extra actions in combat' },
                { level: 3, name: 'Lightning Strike', description: 'Devastating speed attacks' },
                { level: 4, name: 'Blink', description: 'Teleport short distances' },
                { level: 5, name: 'Time Stop', description: 'Stop time briefly' }
            ],
            'Dominate': [
                { level: 1, name: 'Cloud Memory', description: 'Erase recent memories' },
                { level: 2, name: 'Mesmerize', description: 'Compel simple actions' },
                { level: 3, name: 'The Forgetful Mind', description: 'Implant false memories' },
                { level: 4, name: 'Mass Manipulation', description: 'Affect multiple targets' },
                { level: 5, name: 'Possession', description: 'Take control of body' }
            ],
            'Fortitude': [
                { level: 1, name: 'Resilience', description: 'Resist physical damage' },
                { level: 2, name: 'Unswayable Mind', description: 'Resist mental influence' },
                { level: 3, name: 'Toughness', description: 'Ignore wound penalties' },
                { level: 4, name: 'Defy Bane', description: 'Resist supernatural effects' },
                { level: 5, name: 'Fortify the Inner Facade', description: 'Become immune to damage' }
            ],
            'Obfuscate': [
                { level: 1, name: 'Cloak of Shadows', description: 'Hide in darkness' },
                { level: 2, name: 'Silence of Death', description: 'Move without sound' },
                { level: 3, name: 'Mask of a Thousand Faces', description: 'Change appearance' },
                { level: 4, name: 'Vanish', description: 'Become completely invisible' },
                { level: 5, name: 'Cloak the Gathering', description: 'Hide groups of people' }
            ],
            'Potence': [
                { level: 1, name: 'Lethal Body', description: 'Enhanced physical strength' },
                { level: 2, name: 'Prowess', description: 'Devastating physical attacks' },
                { level: 3, name: 'Brutal Feed', description: 'Feed through violence' },
                { level: 4, name: 'Spark of Rage', description: 'Cause frenzy in others' },
                { level: 5, name: 'Earthshock', description: 'Create earthquakes' }
            ],
            'Presence': [
                { level: 1, name: 'Awe', description: 'Inspire admiration' },
                { level: 2, name: 'Dread Gaze', description: 'Cause fear' },
                { level: 3, name: 'Entrancement', description: 'Create devoted followers' },
                { level: 4, name: 'Summon', description: 'Compel others to come' },
                { level: 5, name: 'Majesty', description: 'Become untouchable' }
            ],
            'Protean': [
                { level: 1, name: 'Eyes of the Beast', description: 'Enhanced night vision' },
                { level: 2, name: 'Shape of the Beast', description: 'Transform into animal' },
                { level: 3, name: 'Mist Form', description: 'Become mist' },
                { level: 4, name: 'Form of the Ancient', description: 'Become giant bat' },
                { level: 5, name: 'Earth Meld', description: 'Merge with earth' }
            ],
            'Vicissitude': [
                { level: 1, name: 'Malleable Visage', description: 'Change facial features' },
                { level: 2, name: 'Fleshcraft', description: 'Modify body structure' },
                { level: 3, name: 'Bonecraft', description: 'Manipulate bones' },
                { level: 4, name: 'Horrid Form', description: 'Take monstrous shape' },
                { level: 5, name: 'Metamorphosis', description: 'Complete body transformation' }
            ],
            'Dementation': [
                { level: 1, name: 'Confusion', description: 'Cause mental disorientation' },
                { level: 2, name: 'The Haunting', description: 'Create hallucinations' },
                { level: 3, name: 'Nightmare', description: 'Induce terrifying dreams' },
                { level: 4, name: 'Total Insanity', description: 'Drive target completely mad' },
                { level: 5, name: 'The Beast Within', description: 'Unleash inner monster' }
            ],
            'Thaumaturgy': [
                { level: 1, name: 'A Taste for Blood', description: 'Sense blood and vitae' },
                { level: 2, name: 'Blood Rage', description: 'Cause frenzy in others' },
                { level: 3, name: 'The Blood Bond', description: 'Create blood bonds' },
                { level: 4, name: 'Blood of Acid', description: 'Corrupt blood' },
                { level: 5, name: 'Cauldron of Blood', description: 'Mass blood manipulation' }
            ],
            'Necromancy': [
                { level: 1, name: 'Speak with the Dead', description: 'Communicate with spirits' },
                { level: 2, name: 'Summon Soul', description: 'Call forth spirits' },
                { level: 3, name: 'Compel Soul', description: 'Force spirit obedience' },
                { level: 4, name: 'Reanimate Corpse', description: 'Raise the dead' },
                { level: 5, name: 'Soul Stealing', description: 'Capture souls' }
            ],
            'Quietus': [
                { level: 1, name: 'Silence of Death', description: 'Move without sound' },
                { level: 2, name: 'Touch of Death', description: 'Poisonous touch' },
                { level: 3, name: 'Baal\'s Caress', description: 'Lethal blood attack' },
                { level: 4, name: 'Blood of the Lamb', description: 'Corrupt blood' },
                { level: 5, name: 'The Killing Word', description: 'Death by command' }
            ],
            'Serpentis': [
                { level: 1, name: 'Eyes of the Serpent', description: 'Hypnotic gaze' },
                { level: 2, name: 'Tongue of the Asp', description: 'Venomous bite' },
                { level: 3, name: 'Form of the Cobra', description: 'Transform into snake' },
                { level: 4, name: 'The Serpent\'s Kiss', description: 'Paralyzing venom' },
                { level: 5, name: 'The Serpent\'s Embrace', description: 'Complete serpent form' }
            ],
            'Obtenebration': [
                { level: 1, name: 'Shroud of Night', description: 'Create darkness' },
                { level: 2, name: 'Arms of the Abyss', description: 'Shadow tentacles' },
                { level: 3, name: 'Shadow Form', description: 'Become living shadow' },
                { level: 4, name: 'Summon the Abyss', description: 'Call forth darkness' },
                { level: 5, name: 'Black Metamorphosis', description: 'Become shadow demon' }
            ],
            'Chimerstry': [
                { level: 1, name: 'Ignis Fatuus', description: 'Create false lights' },
                { level: 2, name: 'Fata Morgana', description: 'Create illusions' },
                { level: 3, name: 'Permanency', description: 'Make illusions real' },
                { level: 4, name: 'Horrid Reality', description: 'Create nightmare illusions' },
                { level: 5, name: 'Reality\'s Curtain', description: 'Alter reality itself' }
            ],
            'Daimoinon': [
                { level: 1, name: 'Summon Demon', description: 'Call forth minor demons' },
                { level: 2, name: 'Bind Demon', description: 'Control summoned demons' },
                { level: 3, name: 'Demon\'s Kiss', description: 'Gain demonic powers' },
                { level: 4, name: 'Hell\'s Gate', description: 'Open portal to Hell' },
                { level: 5, name: 'Infernal Mastery', description: 'Command all demons' }
            ],
            'Melpominee': [
                { level: 1, name: 'The Tragic Muse', description: 'Inspire artistic genius' },
                { level: 2, name: 'The Tragic Flaw', description: 'Reveal fatal weaknesses' },
                { level: 3, name: 'The Tragic Hero', description: 'Create doomed champions' },
                { level: 4, name: 'The Tragic End', description: 'Ensure dramatic deaths' },
                { level: 5, name: 'The Tragic Cycle', description: 'Control fate itself' }
            ],
            'Valeren': [
                { level: 1, name: 'The Healing Touch', description: 'Heal others' },
                { level: 2, name: 'The Warrior\'s Resolve', description: 'Enhance combat abilities' },
                { level: 3, name: 'The Martyr\'s Blessing', description: 'Absorb others\' pain' },
                { level: 4, name: 'The Saint\'s Grace', description: 'Become immune to harm' },
                { level: 5, name: 'The Messiah\'s Return', description: 'Resurrect the dead' }
            ],
            'Mortis': [
                { level: 1, name: 'Speak with the Dead', description: 'Communicate with corpses' },
                { level: 2, name: 'Animate Corpse', description: 'Raise the dead' },
                { level: 3, name: 'Bone Craft', description: 'Manipulate bones' },
                { level: 4, name: 'Soul Stealing', description: 'Capture souls' },
                { level: 5, name: 'Death\'s Embrace', description: 'Become death itself' }
            ]
        };
        
        // Show discipline power popover
        function showDisciplinePopover(event, disciplineName) {
            // Don't show popover if the discipline button is disabled
            if (event.target.disabled) {
                return;
            }
            
            // Clear any existing timeout
            if (currentPopoverTimeout) {
                clearTimeout(currentPopoverTimeout);
                currentPopoverTimeout = null;
            }
            
            const popover = document.getElementById('disciplinePopover');
            const popoverTitle = document.getElementById('popoverTitle');
            const popoverPowers = document.getElementById('popoverPowers');
            
            // Set title
            popoverTitle.textContent = `${disciplineName} Powers`;
            
            // Get available powers for this discipline
            const availablePowers = getAvailablePowers(disciplineName);
            
            // Clear existing content
            popoverPowers.innerHTML = '';
            
            // Generate power options
            availablePowers.forEach(power => {
                const powerOption = document.createElement('div');
                powerOption.className = 'power-option';
                powerOption.onclick = () => selectPower(disciplineName, power);
                powerOption.innerHTML = `
                    <strong>Level ${power.level}:</strong> ${power.name}
                    <br><small>${power.description}</small>
                `;
                popoverPowers.appendChild(powerOption);
            });
            
            // Position popover
            const button = event.target;
            const rect = button.getBoundingClientRect();
            
            popover.style.position = 'fixed';
            popover.style.left = (rect.right + 10) + 'px';
            popover.style.top = rect.top + 'px';
            popover.style.display = 'block';
            popover.style.zIndex = '1000';
            
            currentPopoverButton = button;
        }
        
        // Clear popover timeout
        function clearPopoverTimeout() {
            if (currentPopoverTimeout) {
                clearTimeout(currentPopoverTimeout);
                currentPopoverTimeout = null;
            }
        }
        
        // Hide discipline power popover
        function hideDisciplinePopover() {
            currentPopoverTimeout = setTimeout(() => {
                const popover = document.getElementById('disciplinePopover');
                popover.style.display = 'none';
                currentPopoverButton = null; // Clear button reference
            }, 500); // Longer delay to allow moving to popover
        }
        
        // Get available powers for a discipline (not yet selected)
        function getAvailablePowers(disciplineName) {
            const allPowers = disciplinePowers[disciplineName] || [];
            const selectedPowers = getSelectedPowers(disciplineName);
            
            return allPowers.filter(power => 
                !selectedPowers.some(selected => selected.level === power.level)
            );
        }
        
        // Get selected powers for a discipline
        function getSelectedPowers(disciplineName) {
            // For now, return empty array since we don't have discipline selection implemented yet
            // This will show all powers as available
            return [];
        }
        
        // Select a power and add to discipline list
        function selectPower(disciplineName, power) {
            console.log(`Selected ${disciplineName} Level ${power.level}: ${power.name}`);
            
            // Check if this power is already selected
            const disciplineList = document.getElementById('clanDisciplinesList');
            if (disciplineList) {
                const existingItems = disciplineList.querySelectorAll('.discipline-item');
                const powerAlreadySelected = Array.from(existingItems).some(item => {
                    const nameSpan = item.querySelector('.discipline-name');
                    const levelSpan = item.querySelector('.discipline-level');
                    return nameSpan && levelSpan && 
                           nameSpan.textContent === `${disciplineName}: ${power.name}` &&
                           levelSpan.textContent === power.level.toString();
                });
                
                if (powerAlreadySelected) {
                    alert(`${power.name} (Level ${power.level}) is already selected.`);
                    return;
                }
                
                // Create new discipline item
                const disciplineItem = document.createElement('div');
                disciplineItem.className = 'discipline-item';
                disciplineItem.innerHTML = `
                    <span class="discipline-name">${disciplineName}: ${power.name}</span>
                    <span class="discipline-level">${power.level}</span>
                    <button type="button" class="remove-discipline-btn" onclick="removeDiscipline('${disciplineName}: ${power.name}', ${power.level})">×</button>
                `;
                disciplineList.appendChild(disciplineItem);
                
                // Update count
                const countDisplay = document.getElementById('clanDisciplinesCountDisplay');
                if (countDisplay) {
                    const items = disciplineList.querySelectorAll('.discipline-item');
                    countDisplay.textContent = items.length;
                }
            }
        }
        
        // Remove discipline from list
        function removeDiscipline(disciplinePowerName, level) {
            const disciplineList = document.getElementById('clanDisciplinesList');
            if (disciplineList) {
                const items = disciplineList.querySelectorAll('.discipline-item');
                items.forEach(item => {
                    const nameSpan = item.querySelector('.discipline-name');
                    const levelSpan = item.querySelector('.discipline-level');
                    if (nameSpan && levelSpan && 
                        nameSpan.textContent === disciplinePowerName && 
                        levelSpan.textContent === level.toString()) {
                        item.remove();
                        
                        // Update count
                        const countDisplay = document.getElementById('clanDisciplinesCountDisplay');
                        if (countDisplay) {
                            const remainingItems = disciplineList.querySelectorAll('.discipline-item');
                            countDisplay.textContent = remainingItems.length;
                        }
                    }
                });
            }
        }
        
        // Update discipline button availability based on clan
        function updateDisciplineAvailability() {
            const clan = document.getElementById('clan').value;
            const clanDisciplines = {
                'Toreador': ['Auspex', 'Celerity', 'Presence'],
                'Brujah': ['Celerity', 'Potence', 'Presence'],
                'Ventrue': ['Dominate', 'Fortitude', 'Presence'],
                'Gangrel': ['Animalism', 'Fortitude', 'Protean'],
                'Nosferatu': ['Animalism', 'Obfuscate', 'Potence'],
                'Malkavian': ['Auspex', 'Dementation', 'Obfuscate'],
                'Tremere': ['Auspex', 'Dominate', 'Thaumaturgy'],
                'Assamite': ['Celerity', 'Obfuscate', 'Quietus'],
                'Followers of Set': ['Obfuscate', 'Presence', 'Serpentis'],
                'Giovanni': ['Dominate', 'Potence', 'Necromancy'],
                'Lasombra': ['Dominate', 'Obfuscate', 'Obtenebration'],
                'Ravnos': ['Animalism', 'Chimerstry', 'Fortitude'],
                'Tzimisce': ['Animalism', 'Auspex', 'Vicissitude'],
                'Caitiff': [] // Caitiff can learn any discipline
            };
            
            // Get all discipline buttons
            const disciplineButtons = document.querySelectorAll('.discipline-option-btn');
            
            disciplineButtons.forEach(button => {
                const disciplineName = button.getAttribute('data-discipline');
                
                if (!clan) {
                    // No clan selected - disable all
                    button.disabled = true;
                    button.classList.add('disabled');
                } else if (clan === 'Caitiff') {
                    // Caitiff can learn any discipline - enable all
                    button.disabled = false;
                    button.classList.remove('disabled');
                    button.classList.add('caitiff-available');
                } else if (!clanDisciplines[clan] || !clanDisciplines[clan].includes(disciplineName)) {
                    // Discipline not available to clan - disable
                    button.disabled = true;
                    button.classList.add('disabled');
                    button.classList.remove('caitiff-available');
                } else {
                    // Discipline available to clan - enable
                    button.disabled = false;
                    button.classList.remove('disabled');
                    button.classList.remove('caitiff-available');
                }
            });
        }
        
        // Call updateDisciplineAvailability when clan changes
        document.addEventListener('DOMContentLoaded', function() {
            const clanSelect = document.getElementById('clan');
            if (clanSelect) {
                clanSelect.addEventListener('change', updateDisciplineAvailability);
                // Initial update
                updateDisciplineAvailability();
            }
        });
    </script>
</body>
</html>
