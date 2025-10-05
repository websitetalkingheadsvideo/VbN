// Laws of the Night Character Creation - JavaScript Functions
// Version 0.2.0

// API Configuration
const API_BASE_URL = 'http://localhost:5000/api';
const PHP_BASE_URL = ''; // Relative path for PHP scripts

// Tab functionality
function showTab(tabIndex) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => content.classList.remove('active'));
    
    // Remove active class from all tabs
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Show selected tab content
    const selectedTab = document.getElementById('tab' + tabIndex);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
    
    // Add active class to selected tab button
    const selectedTabButton = tabs[tabIndex];
    if (selectedTabButton) {
        selectedTabButton.classList.add('active');
    }
}

// Notification system
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = message;
    
    // Style the notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
        color: white;
        padding: 15px 20px;
        border-radius: 5px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 10000;
        font-weight: bold;
        max-width: 300px;
        word-wrap: break-word;
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

// Character saving function
function saveCharacter(isFinalization = false) {
    // Collect all form data
    const formData = collectFormData();
    
    // Validate required fields
    if (!validateFormData(formData)) {
        return;
    }
    
    // Show loading state
    const saveButtons = document.querySelectorAll('.save-btn');
    saveButtons.forEach(btn => {
        btn.disabled = true;
        btn.innerHTML = isFinalization ? '🎯 Finalizing...' : '💾 Saving...';
    });
    
    // Send data to PHP save script
    console.log('Sending data:', formData);
    fetch('test_simple_insert.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text().then(text => {
            console.log('Raw response:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON response:', text);
                console.error('JSON parse error:', e);
                throw new Error('Invalid response from server: ' + text.substring(0, 200));
            }
        });
    })
    .then(data => {
        if (data.success) {
            if (isFinalization) {
                showNotification('🎉 Character finalized successfully!', 'success');
            } else {
                showNotification('✅ Character saved successfully!', 'success');
            }
            console.log('Full response data:', data);
            console.log('Character ID:', data.character_id);
        } else {
            showNotification('❌ Error saving character: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('❌ Error saving character: ' + error.message, 'error');
    })
    .finally(() => {
        // Reset button state
        saveButtons.forEach(btn => {
            btn.disabled = false;
            btn.innerHTML = isFinalization ? '🎯 Finalize Character' : '💾 Save Draft';
        });
    });
}

// Collect all form data
function collectFormData() {
    const form = document.getElementById('characterForm');
    const formData = new FormData(form);
    
    // Basic character info
    const data = {
        character_name: formData.get('characterName'),
        player_name: formData.get('playerName'),
        chronicle: formData.get('chronicle'),
        nature: formData.get('nature'),
        demeanor: formData.get('demeanor'),
        concept: formData.get('concept'),
        clan: formData.get('clan'),
        generation: parseInt(formData.get('generation')),
        sire: formData.get('sire'),
        pc: formData.get('pc') === 'on',
        biography: formData.get('biography') || '',
        equipment: formData.get('equipment') || '',
        total_xp: 30,
        spent_xp: characterData.xpSpent,
        traits: characterData.traits,
        negativeTraits: characterData.negativeTraits,
        abilities: characterData.abilities,
        disciplines: characterData.disciplines,
        backgrounds: characterData.backgrounds,
        backgroundDetails: characterData.backgroundDetails,
        merits_flaws: [], // Will be populated when merits & flaws tab is implemented
        morality: {
            path_name: 'Humanity',
            path_rating: 7,
            conscience: 1,
            self_control: 1,
            courage: 1,
            willpower_permanent: 5,
            willpower_current: 5,
            humanity: 7
        },
        status: {
            sect_status: '',
            clan_status: '',
            city_status: '',
            health_levels: 'Healthy',
            blood_pool_current: 10,
            blood_pool_maximum: 10
        }
    };
    
    return data;
}

// Validate form data - Only require character name
function validateFormData(data) {
    // Only require character name
    if (!data.character_name || data.character_name.toString().trim() === '') {
        alert('Please enter a character name.');
        return false;
    }
    
    return true;
}

// Discipline powers data structure (loaded from database)
let disciplinePowers = {};
let clanDisciplineAccess = {};

// Load discipline data from database
async function loadDisciplineData() {
    try {
        const response = await fetch('api_disciplines.php?action=all');
        const result = await response.json();
        
        if (result.success) {
            // Set discipline powers
            disciplinePowers = result.data.disciplinePowers;
            
            // Set clan discipline access
            clanDisciplineAccess = result.data.clanDisciplineAccess;
            
            console.log('✅ Discipline data loaded from database');
            console.log('Loaded disciplines:', Object.keys(disciplinePowers).length);
            console.log('Loaded clans:', Object.keys(clanDisciplineAccess).length);
        } else {
            console.error('❌ Failed to load discipline data:', result.error);
            // Fallback to hardcoded data if database fails
            loadFallbackData();
        }
    } catch (error) {
        console.error('❌ Error loading discipline data:', error);
        // Fallback to hardcoded data if database fails
        loadFallbackData();
    }
}

// Fallback hardcoded data (in case database is unavailable)
function loadFallbackData() {
    console.log('⚠️ Using fallback hardcoded discipline data');
    
    disciplinePowers = {
    'Animalism': [
        { level: 1, name: 'Sense the Beast', description: 'The vampire can sense the presence of animals within a certain radius and understand their basic emotional state.' },
        { level: 2, name: 'Feral Whispers', description: 'The vampire can communicate directly with animals, understanding their thoughts and conveying complex messages.' },
        { level: 3, name: 'Quell the Beast', description: 'The vampire can calm and control the Beast within themselves or other vampires, reducing frenzy.' },
        { level: 4, name: 'Beckoning', description: 'The vampire can call animals to their location from a considerable distance.' },
        { level: 5, name: 'Animal Control', description: 'The vampire gains complete control over animals, able to command them to perform any action.' }
    ],
    'Auspex': [
        { level: 1, name: 'Aura Perception', description: 'The vampire can see the emotional and spiritual auras surrounding living beings.' },
        { level: 2, name: 'Telepathy', description: 'The vampire can read surface thoughts and emotions from other beings.' },
        { level: 3, name: 'Psychometry', description: 'The vampire can read the history and emotional resonance of objects by touching them.' },
        { level: 4, name: 'Premonition', description: 'The vampire gains glimpses of future events through dreams, visions, or sudden insights.' },
        { level: 5, name: 'Sense the Unseen', description: 'The vampire can perceive supernatural phenomena, spirits, and otherworldly entities.' }
    ],
    'Celerity': [
        { level: 1, name: 'Quickness', description: 'The vampire can move and react at superhuman speeds, allowing them to perform actions much faster than normal.' },
        { level: 2, name: 'Sprint', description: 'The vampire can achieve incredible bursts of speed over short distances.' },
        { level: 3, name: 'Enhanced Reflexes', description: 'The vampire\'s reaction time becomes so fast they can dodge bullets and catch arrows in flight.' },
        { level: 4, name: 'Blur', description: 'The vampire moves so fast they become a blur, making them nearly impossible to target.' },
        { level: 5, name: 'Accelerated Movement', description: 'The vampire can maintain superhuman speed for extended periods.' }
    ],
    'Dominate': [
        { level: 1, name: 'Command', description: 'The vampire can issue simple, direct commands that mortals and weaker vampires must obey.' },
        { level: 2, name: 'Mesmerize', description: 'The vampire can place a target in a trance-like state, making them highly suggestible.' },
        { level: 3, name: 'Memory Alteration', description: 'The vampire can modify, erase, or implant false memories in a target\'s mind.' },
        { level: 4, name: 'Suggestion', description: 'The vampire can plant subtle suggestions in a target\'s mind that they will act upon later.' },
        { level: 5, name: 'Mental Domination', description: 'The vampire gains complete control over a target\'s mind, able to command them to perform any action.' }
    ],
    'Fortitude': [
        { level: 1, name: 'Resistance', description: 'The vampire can resist physical damage and environmental hazards better than normal.' },
        { level: 2, name: 'Endurance', description: 'The vampire can maintain physical activity and resist fatigue for extended periods.' },
        { level: 3, name: 'Pain Tolerance', description: 'The vampire can ignore pain and continue functioning normally even when severely injured.' },
        { level: 4, name: 'Damage Reduction', description: 'The vampire can reduce the damage taken from physical attacks.' },
        { level: 5, name: 'Supernatural Stamina', description: 'The vampire gains almost supernatural levels of physical resilience.' }
    ],
    'Obfuscate': [
        { level: 1, name: 'Cloak of Shadows', description: 'The vampire can blend into shadows and darkness, becoming difficult to see and track.' },
        { level: 2, name: 'Vanish', description: 'The vampire can become completely invisible for short periods.' },
        { level: 3, name: 'Mask of a Thousand Faces', description: 'The vampire can change their appearance to look like anyone they have seen.' },
        { level: 4, name: 'Silent Movement', description: 'The vampire can move without making any sound, becoming completely silent.' },
        { level: 5, name: 'Unseen Presence', description: 'The vampire can make others forget they ever saw them.' }
    ],
    'Potence': [
        { level: 1, name: 'Prowess', description: 'The vampire gains superhuman physical strength, allowing them to perform feats far beyond mortal capabilities.' },
        { level: 2, name: 'Shove', description: 'The vampire can deliver powerful shoves and pushes that can knock down or throw opponents great distances.' },
        { level: 3, name: 'Knockdown', description: 'The vampire can deliver devastating blows that can knock down even the strongest opponents.' },
        { level: 4, name: 'Crushing Blow', description: 'The vampire can deliver attacks so powerful they can crush through armor and break weapons.' },
        { level: 5, name: 'Leap', description: 'The vampire can jump incredible distances and heights, covering great distances with a single bound.' }
    ],
    'Presence': [
        { level: 1, name: 'Awe', description: 'The vampire can project an aura of majesty and power that makes others feel small and insignificant.' },
        { level: 2, name: 'Dread Gaze', description: 'The vampire can project an aura of fear and intimidation that can cause others to flee or submit.' },
        { level: 3, name: 'Entrancement', description: 'The vampire can charm and captivate others, making them highly susceptible to influence.' },
        { level: 4, name: 'Majesty', description: 'The vampire can project an aura of divine authority that makes others feel compelled to worship them.' },
        { level: 5, name: 'Inspire', description: 'The vampire can use their presence to inspire others to greatness, enhancing their abilities.' }
    ],
    'Protean': [
        { level: 1, name: 'Shape of the Beast', description: 'The vampire can transform into a wolf or bat, gaining the abilities and instincts of the chosen animal form.' },
        { level: 2, name: 'Claws', description: 'The vampire can extend razor-sharp claws from their fingers, making their hands into deadly weapons.' },
        { level: 3, name: 'Feral Leap', description: 'The vampire can leap incredible distances and heights, covering great distances with a single bound.' },
        { level: 4, name: 'Flight (Bat Form)', description: 'The vampire can transform into a bat and gain the ability to fly.' },
        { level: 5, name: 'Natural Armor', description: 'The vampire can harden their skin to create natural armor that provides protection against physical attacks.' }
    ],
    'Thaumaturgy': [
        { level: 1, name: 'Lure of Flames', description: 'The vampire can create and control fire, using their blood magic to summon flames.' },
        { level: 2, name: 'Shield of Thorns', description: 'The vampire can create protective barriers using their blood magic.' },
        { level: 3, name: 'Rite of Blood', description: 'The vampire can use their blood to power magical rituals and create mystical effects.' },
        { level: 4, name: 'Circle of Protection', description: 'The vampire can create magical circles that provide protection against supernatural threats.' },
        { level: 5, name: 'Blood Bond', description: 'The vampire can create mystical bonds between themselves and others using their blood.' }
    ],
    'Necromancy': [
        { level: 1, name: 'Sense Death', description: 'The vampire can sense the presence of death, decay, and the recently deceased.' },
        { level: 2, name: 'Command Dead', description: 'The vampire can command and control undead creatures, forcing them to obey their will.' },
        { level: 3, name: 'Drain Life', description: 'The vampire can drain the life force from living beings, using their necromantic powers.' },
        { level: 4, name: 'Haunt', description: 'The vampire can create ghostly manifestations and supernatural phenomena.' },
        { level: 5, name: 'Animate Corpse', description: 'The vampire can raise the dead as undead servants.' }
    ],
    'Obtenebration': [
        { level: 1, name: 'Shadow Cloak', description: 'The vampire can wrap themselves in shadows, becoming difficult to see.' },
        { level: 2, name: 'Dark Tendrils', description: 'The vampire can create shadowy tendrils that can grab, constrict, and harm opponents.' },
        { level: 3, name: 'Shroud of Night', description: 'The vampire can create areas of supernatural darkness that can block light.' },
        { level: 4, name: 'Shadow Walk', description: 'The vampire can merge with shadows, becoming one with darkness.' },
        { level: 5, name: 'Nightmarish Strike', description: 'The vampire can use their control over darkness to create attacks that cause both physical and psychological damage.' }
    ],
    'Chimerstry': [
        { level: 1, name: 'Minor Illusion', description: 'The vampire can create small, simple illusions that can fool the senses.' },
        { level: 2, name: 'Disguise', description: 'The vampire can create complex illusions that can change their appearance or the appearance of others.' },
        { level: 3, name: 'Confusion', description: 'The vampire can create illusions that can confuse and disorient opponents.' },
        { level: 4, name: 'Hallucinatory Image', description: 'The vampire can create complex, detailed illusions that can fool multiple senses.' },
        { level: 5, name: 'Invisibility Illusion', description: 'The vampire can create illusions that can make themselves or others completely invisible.' }
    ],
    'Dementation': [
        { level: 1, name: 'Awe of Madness', description: 'The vampire can project an aura of madness that can cause others to become confused and disoriented.' },
        { level: 2, name: 'Fear Projection', description: 'The vampire can project intense fear into the minds of others.' },
        { level: 3, name: 'Confusion', description: 'The vampire can create mental confusion in others, making them unable to distinguish between reality and illusion.' },
        { level: 4, name: 'Irrational Fear', description: 'The vampire can create specific, irrational fears in others.' },
        { level: 5, name: 'Frenzy Inducement', description: 'The vampire can cause others to enter a state of frenzy, making them lose control.' }
    ],
    'Quietus': [
        { level: 1, name: 'Poison Glands', description: 'The vampire can produce and secrete various poisons from their body.' },
        { level: 2, name: 'Silent Kill', description: 'The vampire can kill others silently and without leaving obvious signs of violence.' },
        { level: 3, name: 'Respiratory Poison', description: 'The vampire can create poisons that can be delivered through the air.' },
        { level: 4, name: 'Hemorrhage', description: 'The vampire can cause internal bleeding in others, creating wounds that can be fatal.' },
        { level: 5, name: 'Lethal Strike', description: 'The vampire can deliver attacks that can cause instant death.' }
    ],
    'Vicissitude': [
        { level: 1, name: 'Fleshcraft', description: 'The vampire can reshape and modify living flesh, changing the appearance and structure of themselves and others.' },
        { level: 2, name: 'Alter Form', description: 'The vampire can make more dramatic changes to their own body, altering their shape and structure.' },
        { level: 3, name: 'Skin Hardening', description: 'The vampire can harden their skin to create natural armor that provides protection against physical attacks.' },
        { level: 4, name: 'Stretch Limb', description: 'The vampire can extend and stretch their limbs to reach distant objects or attack from unexpected angles.' },
        { level: 5, name: 'Weaponize Flesh', description: 'The vampire can transform parts of their body into weapons.' }
    ],
    'Serpentis': [
        { level: 1, name: 'Hypnotic Gaze', description: 'The vampire can use their eyes to hypnotize others, making them highly suggestible.' },
        { level: 2, name: 'Venomous Bite', description: 'The vampire can produce and deliver venom through their bite.' },
        { level: 3, name: 'Serpent\'s Strike', description: 'The vampire can attack with incredible speed and precision, striking like a snake.' },
        { level: 4, name: 'Mesmerize', description: 'The vampire can create powerful hypnotic effects that can control the behavior of others.' },
        { level: 5, name: 'Shape Serpent', description: 'The vampire can transform into a large serpent, gaining the abilities and instincts of a snake.' }
    ],
    'Koldunic Sorcery': [
        { level: 1, name: 'Elemental Bolt', description: 'The vampire can create and project bolts of elemental energy.' },
        { level: 2, name: 'Minor Ward', description: 'The vampire can create small protective barriers using elemental energy.' },
        { level: 3, name: 'Fire Blast', description: 'The vampire can create powerful blasts of fire that can burn opponents and cause massive damage.' },
        { level: 4, name: 'Ice Shard', description: 'The vampire can create and project shards of ice that can pierce through armor.' },
        { level: 5, name: 'Earth Spike', description: 'The vampire can cause spikes of earth to erupt from the ground.' }
    ],
    'Daimoinon': [
        { level: 1, name: 'Fear Aura', description: 'The vampire can project an aura of fear that can cause others to become terrified and potentially flee.' },
        { level: 2, name: 'Infernal Grasp', description: 'The vampire can create shadowy hands that can grab, constrict, and harm opponents from a distance.' },
        { level: 3, name: 'Summon Demon', description: 'The vampire can call upon infernal entities to aid them.' },
        { level: 4, name: 'Curse', description: 'The vampire can place curses on others that can cause various negative effects over time.' },
        { level: 5, name: 'Dark Inspiration', description: 'The vampire can use their connection to the infernal to inspire others to commit acts of evil or violence.' }
    ],
    'Melpominee': [
        { level: 1, name: 'Captivating Song', description: 'The vampire can use their voice to create musical effects that can charm and captivate others.' },
        { level: 2, name: 'Charm', description: 'The vampire can use their voice to create effects that can make others more susceptible to their influence.' },
        { level: 3, name: 'Enthrall Audience', description: 'The vampire can use their voice to create effects that can captivate large groups of people.' },
        { level: 4, name: 'Inspire Emotion', description: 'The vampire can use their voice to create specific emotional effects in others.' },
        { level: 5, name: 'Hypnotic Performance', description: 'The vampire can use their voice to create powerful hypnotic effects that can control the behavior of others.' }
    ],
    'Valeren': [
        { level: 1, name: 'Healing Touch', description: 'The vampire can use their supernatural abilities to heal wounds and injuries in others.' },
        { level: 2, name: 'Restore Vitality', description: 'The vampire can use their supernatural abilities to restore energy and vitality to others.' },
        { level: 3, name: 'Detox', description: 'The vampire can use their supernatural abilities to remove poisons and toxins from others.' },
        { level: 4, name: 'Protective Ward', description: 'The vampire can use their supernatural abilities to create protective effects that can shield others from harm.' },
        { level: 5, name: 'Ritual Aid', description: 'The vampire can use their supernatural abilities to enhance the effectiveness of rituals and ceremonies.' }
    ],
    'Mortis': [
        { level: 1, name: 'Sense Death', description: 'The vampire can sense the presence of death, decay, and the recently deceased.' },
        { level: 2, name: 'Drain Life', description: 'The vampire can drain the life force from living beings, using their connection to death.' },
        { level: 3, name: 'Haunting Presence', description: 'The vampire can create ghostly manifestations and supernatural phenomena.' },
        { level: 4, name: 'Wither', description: 'The vampire can cause living things to wither and decay, using their connection to death.' },
        { level: 5, name: 'Deathly Chill', description: 'The vampire can create effects that can cause extreme cold and death-like conditions.' }
    ]
    };
    
    clanDisciplineAccess = {
        'Assamite': ['Animalism', 'Celerity', 'Obfuscate', 'Quietus'],
        'Brujah': ['Celerity', 'Potence', 'Presence'],
        'Caitiff': ['Animalism', 'Auspex', 'Celerity', 'Dominate', 'Fortitude', 'Obfuscate', 'Potence', 'Presence', 'Protean', 'Thaumaturgy', 'Necromancy', 'Koldunic Sorcery', 'Obtenebration', 'Chimerstry', 'Dementation', 'Quietus', 'Vicissitude', 'Serpentis', 'Daimoinon', 'Melpominee', 'Valeren', 'Mortis'],
        'Followers of Set': ['Animalism', 'Obfuscate', 'Presence', 'Serpentis'],
        'Gangrel': ['Animalism', 'Fortitude', 'Protean'],
        'Giovanni': ['Dominate', 'Fortitude', 'Necromancy', 'Mortis'],
        'Lasombra': ['Dominate', 'Obfuscate', 'Obtenebration'],
        'Malkavian': ['Auspex', 'Dementation', 'Obfuscate'],
        'Nosferatu': ['Animalism', 'Fortitude', 'Obfuscate'],
        'Ravnos': ['Animalism', 'Chimerstry', 'Fortitude'],
        'Toreador': ['Auspex', 'Celerity', 'Presence'],
        'Tremere': ['Auspex', 'Dominate', 'Thaumaturgy'],
        'Tzimisce': ['Animalism', 'Auspex', 'Dominate', 'Vicissitude'],
        'Ventrue': ['Dominate', 'Fortitude', 'Presence']
    };
}

// Popover management
let currentPopoverTimeout = null;
let currentPopoverButton = null;

// Update popover position on scroll
function updatePopoverPosition() {
    const popover = document.getElementById('disciplinePopover');
    if (popover.style.display === 'block' && currentPopoverButton) {
        const rect = currentPopoverButton.getBoundingClientRect();
        const scrollX = window.pageXOffset || document.documentElement.scrollLeft;
        const scrollY = window.pageYOffset || document.documentElement.scrollTop;
        
        // Position to the right and up using page coordinates
        popover.style.left = (rect.right + scrollX + 10) + 'px';
        popover.style.top = (rect.top + scrollY - 10) + 'px';
        
        // Ensure popover stays within viewport
        const popoverRect = popover.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        // Adjust if popover goes off the right edge
        if (rect.right + 10 + popoverRect.width > viewportWidth) {
            popover.style.left = (rect.left + scrollX - popoverRect.width - 10) + 'px';
        }
        
        // Adjust if popover goes off the top edge
        if (rect.top + scrollY - 10 < scrollY) {
            popover.style.top = (rect.bottom + scrollY + 10) + 'px';
        }
    }
}

// Add scroll event listener
window.addEventListener('scroll', updatePopoverPosition);

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
        
        // Calculate XP cost for this power first
        const selectedClan = document.getElementById('clan').value;
        const clanDisciplineAccess = getClanDisciplineAccess();
        const isInClan = clanDisciplineAccess[selectedClan] && 
                        clanDisciplineAccess[selectedClan].includes(disciplineName);
        
        // Count current total discipline levels (highest level per discipline)
        let currentTotalLevels = 0;
        const disciplineLevels = {}; // Track highest level per discipline
        
        ['Clan', 'BloodSorcery', 'Advanced'].forEach(category => {
            if (characterData.disciplines[category]) {
                characterData.disciplines[category].forEach(existingPower => {
                    const disciplineName = existingPower.name;
                    if (!disciplineLevels[disciplineName] || existingPower.level > disciplineLevels[disciplineName]) {
                        disciplineLevels[disciplineName] = existingPower.level;
                    }
                });
            }
        });
        
        // Sum up the highest level of each discipline
        Object.values(disciplineLevels).forEach(level => {
            currentTotalLevels += level;
        });
        
        // Calculate cost based on mode
        let costText;
        let costClass;
        
        if (isAdvancementMode()) {
            // In advancement mode: all powers cost XP
            const xpCost = isInClan ? power.level * 3 : power.level * 4;
            costText = isInClan ? `${xpCost} XP (in-clan)` : `${xpCost} XP (out-of-clan)`;
            costClass = "paid";
        } else {
            // In character creation mode: first 3 discipline levels are free
            // Calculate what the new total would be if we add this power
            const newDisciplineLevels = { ...disciplineLevels };
            if (!newDisciplineLevels[disciplineName] || power.level > newDisciplineLevels[disciplineName]) {
                newDisciplineLevels[disciplineName] = power.level;
            }
            
            const newTotalLevels = Object.values(newDisciplineLevels).reduce((sum, level) => sum + level, 0);
            
            if (newTotalLevels <= 3) {
                costText = "Free (character creation)";
                costClass = "free";
            } else {
                const paidLevels = newTotalLevels - 3;
                const xpCost = paidLevels * 3;
                
                // Check if player has enough XP
                if (characterData.xpRemaining < xpCost) {
                    costText = `${xpCost} XP (insufficient XP)`;
                    costClass = "insufficient";
                } else {
                    costText = `${xpCost} XP (spend XP)`;
                    costClass = "paid";
                }
            }
        }
        
        // Check if power is available (prerequisites met)
        const isAvailable = isPowerAvailable(disciplineName, power.level);
        if (!isAvailable) {
            powerOption.classList.add('disabled');
        }
        
        // Check if insufficient XP (only in character creation mode)
        if (!isAdvancementMode() && costClass === 'insufficient') {
            powerOption.classList.add('disabled');
        }
        
        powerOption.innerHTML = `
            <span class="power-level">${power.level}.</span>
            <span class="power-name">${power.name}</span>
            <div class="power-cost ${costClass}">${costText}</div>
            <div class="power-description">${power.description}</div>
        `;
        
        popoverPowers.appendChild(powerOption);
    });
    
    // Store current button reference for scroll updates
    currentPopoverButton = event.target;
    
    // Position popover
    const button = event.target;
    const rect = button.getBoundingClientRect();
    const scrollX = window.pageXOffset || document.documentElement.scrollLeft;
    const scrollY = window.pageYOffset || document.documentElement.scrollTop;
    
    // Position to the right and up using page coordinates
    popover.style.left = (rect.right + scrollX + 10) + 'px';
    popover.style.top = (rect.top + scrollY - 10) + 'px';
    
    // Ensure popover stays within viewport
    const popoverRect = popover.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    
    // Adjust if popover goes off the right edge
    if (rect.right + 10 + popoverRect.width > viewportWidth) {
        popover.style.left = (rect.left + scrollX - popoverRect.width - 10) + 'px';
    }
    
    // Adjust if popover goes off the top edge
    if (rect.top + scrollY - 10 < scrollY) {
        popover.style.top = (rect.bottom + scrollY + 10) + 'px';
    }
    
    // Add hover events to popover to keep it open
    popover.onmouseenter = () => {
        if (currentPopoverTimeout) {
            clearTimeout(currentPopoverTimeout);
            currentPopoverTimeout = null;
        }
    };
    
    popover.onmouseleave = () => {
        hideDisciplinePopover();
    };
    
    // Show popover
    popover.style.display = 'block';
}

// Clear popover timeout (called when hovering over popover)
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
    const selectedPowers = [];
    
    // Check all categories for this discipline
    ['Clan', 'BloodSorcery', 'Advanced'].forEach(category => {
        if (characterData.disciplines[category]) {
            characterData.disciplines[category].forEach(discipline => {
                if (discipline.name === disciplineName) {
                    selectedPowers.push(discipline);
                }
            });
        }
    });
    
    return selectedPowers;
}

// Check if a power is available (prerequisites met)
function isPowerAvailable(disciplineName, powerLevel) {
    const selectedPowers = getSelectedPowers(disciplineName);
    
    // Check if all lower level powers are selected
    for (let i = 1; i < powerLevel; i++) {
        const hasLowerPower = selectedPowers.some(power => power.level === i);
        if (!hasLowerPower) {
            return false;
        }
    }
    
    return true;
}

// Select a power
function selectPower(disciplineName, power) {
    // Check if power is available
    if (!isPowerAvailable(disciplineName, power.level)) {
        return;
    }
    
    // Count current total discipline levels (highest level per discipline)
    let currentTotalLevels = 0;
    const disciplineLevels = {}; // Track highest level per discipline
    
    ['Clan', 'BloodSorcery', 'Advanced'].forEach(category => {
        if (characterData.disciplines[category]) {
            characterData.disciplines[category].forEach(existingPower => {
                const disciplineName = existingPower.name;
                if (!disciplineLevels[disciplineName] || existingPower.level > disciplineLevels[disciplineName]) {
                    disciplineLevels[disciplineName] = existingPower.level;
                }
            });
        }
    });
    
    // Sum up the highest level of each discipline
    Object.values(disciplineLevels).forEach(level => {
        currentTotalLevels += level;
    });
    
    // Check if adding this power would exceed the 3-level limit (only in character creation mode)
    if (!isAdvancementMode()) {
        const newDisciplineLevels = { ...disciplineLevels };
        if (!newDisciplineLevels[disciplineName] || power.level > newDisciplineLevels[disciplineName]) {
            newDisciplineLevels[disciplineName] = power.level;
        }
        
        const newTotalLevels = Object.values(newDisciplineLevels).reduce((sum, level) => sum + level, 0);
        
        if (newTotalLevels > 3) {
            // Check if player has enough XP to spend
            const additionalLevels = newTotalLevels - 3;
            const xpCost = additionalLevels * 3;
            
            if (characterData.xpRemaining < xpCost) {
                alert(`Not enough XP! You need ${xpCost} XP but only have ${characterData.xpRemaining} remaining.\n\nThis would make ${disciplineName} level ${power.level}\nNew total would be: ${newTotalLevels} levels`);
                return;
            }
            
            // Ask if player wants to spend XP
            const confirmed = confirm(
                `You've used your 3 free discipline levels!\n\n` +
                `This power would cost ${xpCost} XP (${additionalLevels} additional level(s)).\n\n` +
                `Do you want to spend XP to continue?`
            );
            
            if (!confirmed) {
                return;
            }
        }
    }
    
    // Determine category based on discipline
    let category = 'Clan';
    if (['Thaumaturgy', 'Necromancy', 'Koldunic Sorcery'].includes(disciplineName)) {
        category = 'BloodSorcery';
    } else if (['Obtenebration', 'Chimerstry', 'Dementation', 'Quietus', 'Vicissitude', 'Serpentis', 'Daimoinon', 'Melpominee', 'Valeren', 'Mortis'].includes(disciplineName)) {
        category = 'Advanced';
    }
    
    // Add power to character data
    if (!characterData.disciplines[category]) {
        characterData.disciplines[category] = [];
    }
    
    characterData.disciplines[category].push({
        name: disciplineName,
        level: power.level,
        powerName: power.name,
        description: power.description
    });
    
    // Update display
    refreshDisciplineDisplay(category);
    
    // Hide popover
    hideDisciplinePopover();
    
    // Update XP
    updateXPDisplay();
}

// Character data storage
let characterData = {
    traits: {
        Physical: [],
        Social: [],
        Mental: []
    },
    negativeTraits: {
        Physical: [],
        Social: [],
        Mental: []
    },
    abilities: {
        Physical: [],
        Social: [],
        Mental: [],
        Optional: []
    },
    disciplines: {
        Clan: [],
        BloodSorcery: [],
        Advanced: []
    },
    physicalTraitCategories: {
        agility: ['Agile', 'Lithe', 'Nimble', 'Quick', 'Spry', 'Graceful', 'Slender'],
        strength: ['Strong', 'Hardy', 'Tough', 'Resilient', 'Sturdy', 'Vigorous', 'Burly'],
        dexterity: ['Coordinated', 'Precise', 'Steady-handed', 'Sleek', 'Flexible', 'Balanced'],
        reflexes: ['Alert', 'Sharp-eyed', 'Quick-reflexed', 'Perceptive', 'Reactive', 'Observant'],
        appearance: ['Athletic', 'Well-built'],
        legacy: ['Fast', 'Muscular']
    },
    backgrounds: {
        Allies: 0,
        Contacts: 0,
        Fame: 0,
        Generation: 0,
        Herd: 0,
        Influence: 0,
        Mentor: 0,
        Resources: 0,
        Retainers: 0,
        Status: 0
    },
    backgroundDetails: {
        Allies: '',
        Contacts: '',
        Fame: '',
        Generation: '',
        Herd: '',
        Influence: '',
        Mentor: '',
        Resources: '',
        Retainers: '',
        Status: ''
    },
    xpSpent: 0,
    xpRemaining: 30,
    isCharacterComplete: false // Track if character creation is finished
};

// Trait selection function
function selectTrait(category, traitName) {
    const traitList = characterData.traits[category];
    const traitListElement = document.getElementById(category.toLowerCase() + 'TraitList');
    
    // Add trait to character data
    traitList.push(traitName);
    
    // Refresh the trait display
    refreshTraitDisplay(category);
    
    // Update count and progress
    updateTraitCount(category);
    updateXPDisplay();
    
    // Update trait button appearance (but don't disable - allow multiple selections)
    const traitButton = Array.from(document.querySelectorAll('.trait-option-btn')).find(btn => 
        btn.onclick.toString().includes(category) && btn.textContent.includes(traitName.split(' (')[0])
    );
    if (traitButton) {
        traitButton.classList.add('selected');
        // Update button text to show count
        const count = characterData.traits[category].filter(t => t === traitName).length;
        if (count > 1) {
            traitButton.textContent = `${traitName} (${count})`;
        }
    }
}

// Negative trait selection function
function selectNegativeTrait(category, traitName) {
    const traitList = characterData.negativeTraits[category];
    const traitListElement = document.getElementById(category.toLowerCase() + 'NegativeTraitList');
    
    // Add trait to character data
    traitList.push(traitName);
    
    // Refresh the trait display
    refreshNegativeTraitDisplay(category);
    
    // Update count and progress
    updateTraitCount(category);
    updateXPDisplay();
    
    // Update trait button appearance (but don't disable - allow multiple selections)
    const traitButton = Array.from(document.querySelectorAll('.trait-option-btn.negative')).find(btn => 
        btn.onclick.toString().includes(category) && btn.textContent.includes(traitName.split(' (')[0])
    );
    if (traitButton) {
        traitButton.classList.add('selected');
        // Update button text to show count
        const count = characterData.negativeTraits[category].filter(t => t === traitName).length;
        if (count > 1) {
            traitButton.textContent = `${traitName} (${count})`;
        }
    }
}

// Remove negative trait function
function removeNegativeTrait(category, traitName, element) {
    const traitList = characterData.negativeTraits[category];
    const index = traitList.lastIndexOf(traitName); // Remove the last instance
    if (index > -1) {
        traitList.splice(index, 1);
    }
    
    // Refresh the trait display
    refreshNegativeTraitDisplay(category);
    
    // Update count and progress
    updateTraitCount(category);
    updateXPDisplay();
    
    // Update trait button appearance
    const traitButton = Array.from(document.querySelectorAll('.trait-option-btn.negative')).find(btn => 
        btn.onclick.toString().includes(category) && btn.textContent.includes(traitName.split(' (')[0])
    );
    if (traitButton) {
        const remainingCount = characterData.negativeTraits[category].filter(t => t === traitName).length;
        if (remainingCount === 0) {
            traitButton.classList.remove('selected');
            traitButton.textContent = traitName;
        } else if (remainingCount === 1) {
            traitButton.textContent = traitName;
        } else {
            traitButton.textContent = `${traitName} (${remainingCount})`;
        }
    }
}

// Refresh trait display for a category
function refreshTraitDisplay(category) {
    const traitListElement = document.getElementById(category.toLowerCase() + 'TraitList');
    traitListElement.innerHTML = '';
    
    // Group traits by name and count them
    const traitCounts = {};
    characterData.traits[category].forEach(trait => {
        traitCounts[trait] = (traitCounts[trait] || 0) + 1;
    });
    
    // Create display elements for each unique trait
    Object.keys(traitCounts).forEach(traitName => {
        const count = traitCounts[traitName];
        const traitElement = document.createElement('div');
        traitElement.className = 'selected-trait';
        const displayName = count > 1 ? `${traitName} (${count})` : traitName;
        traitElement.innerHTML = `
            <span class="trait-name">${displayName}</span>
            <button type="button" class="remove-trait-btn" onclick="removeTrait('${category}', '${traitName}', this)">×</button>
        `;
        traitListElement.appendChild(traitElement);
    });
}

// Refresh negative trait display for a category
function refreshNegativeTraitDisplay(category) {
    const traitListElement = document.getElementById(category.toLowerCase() + 'NegativeTraitList');
    traitListElement.innerHTML = '';
    
    // Group traits by name and count them
    const traitCounts = {};
    characterData.negativeTraits[category].forEach(trait => {
        traitCounts[trait] = (traitCounts[trait] || 0) + 1;
    });
    
    // Create display elements for each unique trait
    Object.keys(traitCounts).forEach(traitName => {
        const count = traitCounts[traitName];
        const traitElement = document.createElement('div');
        traitElement.className = 'selected-trait negative';
        const displayName = count > 1 ? `${traitName} (${count})` : traitName;
        traitElement.innerHTML = `
            <span class="trait-name">${displayName}</span>
            <button type="button" class="remove-trait-btn" onclick="removeNegativeTrait('${category}', '${traitName}', this)">×</button>
        `;
        traitListElement.appendChild(traitElement);
    });
}

// Remove trait function
function removeTrait(category, traitName, element) {
    const traitList = characterData.traits[category];
    const index = traitList.lastIndexOf(traitName); // Remove the last instance
    if (index > -1) {
        traitList.splice(index, 1);
    }
    
    // Refresh the trait display
    refreshTraitDisplay(category);
    
    // Update count and progress
    updateTraitCount(category);
    updateXPDisplay();
    
    // Update trait button appearance
    const traitButton = Array.from(document.querySelectorAll('.trait-option-btn')).find(btn => 
        btn.onclick.toString().includes(category) && btn.textContent.includes(traitName.split(' (')[0])
    );
    if (traitButton) {
        const remainingCount = characterData.traits[category].filter(t => t === traitName).length;
        if (remainingCount === 0) {
            traitButton.classList.remove('selected');
            traitButton.textContent = traitName;
        } else if (remainingCount === 1) {
            traitButton.textContent = traitName;
        } else {
            traitButton.textContent = `${traitName} (${remainingCount})`;
        }
    }
}

// Update trait count and progress bar
function updateTraitCount(category) {
    const count = characterData.traits[category].length;
    const countDisplay = document.getElementById(category.toLowerCase() + 'CountDisplay');
    const progressFill = document.getElementById(category.toLowerCase() + 'ProgressFill');
    const sidebarCount = document.getElementById(category.toLowerCase() + 'Count');
    
    // Update displays
    countDisplay.textContent = count;
    if (sidebarCount) sidebarCount.textContent = count;
    
    // Update progress bar
    const percentage = Math.min((count / 10) * 100, 100);
    progressFill.style.width = percentage + '%';
    
    // Update progress bar class
    if (count >= 7) {
        progressFill.classList.remove('incomplete');
        progressFill.classList.add('complete');
    } else {
        progressFill.classList.remove('complete');
        progressFill.classList.add('incomplete');
    }
    
    // Update physical trait category counts if this is Physical
    if (category === 'Physical') {
        updatePhysicalTraitCategories();
    }
}

// Update physical trait category counts
function updatePhysicalTraitCategories() {
    const categories = ['agility', 'strength', 'dexterity', 'reflexes', 'appearance'];
    
    categories.forEach(category => {
        const traitNames = characterData.physicalTraitCategories[category];
        const count = characterData.traits.Physical.filter(trait => traitNames.includes(trait)).length;
        const countElement = document.getElementById(category + 'Count');
        if (countElement) {
            countElement.textContent = count;
        }
    });
}

// Ability selection function
function selectAbility(category, abilityName) {
    const abilityList = characterData.abilities[category];
    const abilityListElement = document.getElementById(category.toLowerCase() + 'AbilitiesList');
    
    // Check if this ability is already at maximum (5 dots)
    const currentCount = abilityList.filter(a => a === abilityName).length;
    if (currentCount >= 5) {
        alert(`${abilityName} is already at maximum level (5 dots).`);
        return;
    }
    
    // Add ability to character data
    abilityList.push(abilityName);
    
    // Refresh the ability display
    refreshAbilityDisplay(category);
    
    // Update count and progress
    updateAbilityCount(category);
    updateXPDisplay();
    
    // Update ability button appearance (but don't disable - allow multiple selections)
    const abilityButton = Array.from(document.querySelectorAll('.ability-option-btn')).find(btn => 
        btn.onclick.toString().includes(category) && btn.textContent.includes(abilityName.split(' (')[0])
    );
    if (abilityButton) {
        abilityButton.classList.add('selected');
        // Update button text to show count
        const count = characterData.abilities[category].filter(a => a === abilityName).length;
        if (count > 1) {
            abilityButton.textContent = `${abilityName} (${count})`;
        }
        
        // Disable button if at maximum
        if (count >= 5) {
            abilityButton.disabled = true;
            abilityButton.style.opacity = '0.6';
            abilityButton.title = `${abilityName} is at maximum level (5 dots)`;
        }
    }
}

// Remove ability function
function removeAbility(category, abilityName, element) {
    const abilityList = characterData.abilities[category];
    const index = abilityList.lastIndexOf(abilityName); // Remove the last instance
    if (index > -1) {
        abilityList.splice(index, 1);
    }
    
    // Refresh the ability display
    refreshAbilityDisplay(category);
    
    // Update count and progress
    updateAbilityCount(category);
    updateXPDisplay();
    
    // Update ability button appearance
    const abilityButton = Array.from(document.querySelectorAll('.ability-option-btn')).find(btn => 
        btn.onclick.toString().includes(category) && btn.textContent.includes(abilityName.split(' (')[0])
    );
    if (abilityButton) {
        const remainingCount = characterData.abilities[category].filter(a => a === abilityName).length;
        if (remainingCount === 0) {
            abilityButton.classList.remove('selected');
            abilityButton.textContent = abilityName;
            abilityButton.disabled = false;
            abilityButton.style.opacity = '1';
            abilityButton.title = '';
        } else if (remainingCount === 1) {
            abilityButton.textContent = abilityName;
            abilityButton.disabled = false;
            abilityButton.style.opacity = '1';
            abilityButton.title = '';
        } else {
            abilityButton.textContent = `${abilityName} (${remainingCount})`;
            abilityButton.disabled = false;
            abilityButton.style.opacity = '1';
            abilityButton.title = '';
        }
    }
}

// Refresh ability display for a category
function refreshAbilityDisplay(category) {
    const abilityListElement = document.getElementById(category.toLowerCase() + 'AbilitiesList');
    abilityListElement.innerHTML = '';
    
    // Group abilities by name and count them
    const abilityCounts = {};
    characterData.abilities[category].forEach(ability => {
        abilityCounts[ability] = (abilityCounts[ability] || 0) + 1;
    });
    
    // Create display elements for each unique ability
    Object.keys(abilityCounts).forEach(abilityName => {
        const count = abilityCounts[abilityName];
        const abilityElement = document.createElement('div');
        abilityElement.className = 'selected-ability';
        const displayName = count > 1 ? `${abilityName} (${count})` : abilityName;
        abilityElement.innerHTML = `
            <span class="ability-name">${displayName}</span>
            <button type="button" class="remove-ability-btn" onclick="removeAbility('${category}', '${abilityName}', this)">×</button>
        `;
        abilityListElement.appendChild(abilityElement);
    });
}

// Update ability count and progress bar
function updateAbilityCount(category) {
    const count = characterData.abilities[category].length;
    const countDisplay = document.getElementById(category.toLowerCase() + 'AbilitiesCountDisplay');
    const progressFill = document.getElementById(category.toLowerCase() + 'AbilitiesProgressFill');
    
    // Update displays
    countDisplay.textContent = count;
    
    // Update progress bar
    const percentage = Math.min((count / 5) * 100, 100);
    progressFill.style.width = percentage + '%';
    
    // Update progress bar class
    if (count >= 3) {
        progressFill.classList.remove('incomplete');
        progressFill.classList.add('complete');
    } else {
        progressFill.classList.remove('complete');
        progressFill.classList.add('incomplete');
    }
}

// Discipline selection function
function selectDiscipline(category, disciplineName) {
    const disciplineList = characterData.disciplines[category];
    const disciplineListElement = document.getElementById(category.toLowerCase() + 'DisciplinesList');
    
    // Check if this discipline is already at maximum (5 dots)
    const currentCount = disciplineList.filter(d => d === disciplineName).length;
    if (currentCount >= 5) {
        alert(`${disciplineName} is already at maximum level (5 dots).`);
        return;
    }
    
    // Add discipline to character data
    disciplineList.push(disciplineName);
    
    // Refresh the discipline display
    refreshDisciplineDisplay(category);
    
    // Update count and progress
    updateDisciplineCount(category);
    updateXPDisplay();
    
    // Update discipline button appearance (but don't disable - allow multiple selections)
    const disciplineButton = Array.from(document.querySelectorAll('.discipline-option-btn')).find(btn => 
        btn.onclick.toString().includes(category) && btn.textContent.includes(disciplineName.split(' (')[0])
    );
    if (disciplineButton) {
        disciplineButton.classList.add('selected');
        // Update button text to show count
        const count = characterData.disciplines[category].filter(d => d === disciplineName).length;
        if (count > 1) {
            disciplineButton.textContent = `${disciplineName} (${count})`;
        }
        
        // Disable button if at maximum
        if (count >= 5) {
            disciplineButton.disabled = true;
            disciplineButton.style.opacity = '0.6';
            disciplineButton.title = `${disciplineName} is at maximum level (5 dots)`;
        }
    }
}

// Remove discipline function
// Remove power from character
function removePower(category, powerIndex, buttonElement) {
    // Remove from character data
    if (characterData.disciplines[category] && characterData.disciplines[category][powerIndex]) {
        characterData.disciplines[category].splice(powerIndex, 1);
    }
    
    // Refresh the entire display to update indices
    refreshDisciplineDisplay(category);
    
    // Update count and progress bar
    updateDisciplineCount(category);
    
    // Update XP
    updateXPDisplay();
}

// Refresh discipline display for a category
function refreshDisciplineDisplay(category) {
    // Map category names to their corresponding element IDs
    const elementIdMap = {
        'Clan': 'clanDisciplinesList',
        'BloodSorcery': 'bloodSorceryList',
        'Advanced': 'advancedDisciplinesList'
    };
    
    const disciplineListElement = document.getElementById(elementIdMap[category]);
    if (!disciplineListElement) {
        console.warn(`Discipline list element not found for category: ${category}`);
        return;
    }
    disciplineListElement.innerHTML = '';
    
    // Display each selected power individually
    if (characterData.disciplines[category]) {
        characterData.disciplines[category].forEach((power, index) => {
            const disciplineElement = document.createElement('div');
            disciplineElement.className = 'selected-discipline';
            
            // Add clan class for clan disciplines
            if (category === 'Clan') {
                disciplineElement.classList.add('clan');
            }
            
            // Format: "Discipline: Power Name"
            const displayName = `${power.name}: ${power.powerName}`;
            disciplineElement.innerHTML = `
                <span class="discipline-name">${displayName}</span>
                <button type="button" class="remove-discipline-btn" onclick="removePower('${category}', ${index}, this)">×</button>
            `;
            disciplineListElement.appendChild(disciplineElement);
        });
    }
}

// Update discipline count and progress bar
function updateDisciplineCount(category) {
    // Count total discipline levels in this category (highest level per discipline)
    const disciplineLevels = {}; // Track highest level per discipline
    
    if (characterData.disciplines[category]) {
        characterData.disciplines[category].forEach(power => {
            const disciplineName = power.name;
            if (!disciplineLevels[disciplineName] || power.level > disciplineLevels[disciplineName]) {
                disciplineLevels[disciplineName] = power.level;
            }
        });
    }
    
    // Sum up the highest level of each discipline
    const totalLevels = Object.values(disciplineLevels).reduce((sum, level) => sum + level, 0);
    
    // Get the correct display element ID based on category
    let countDisplayId;
    let progressFillId;
    
    switch(category) {
        case 'Clan':
            countDisplayId = 'clanDisciplinesCountDisplay';
            progressFillId = 'clanDisciplinesProgressFill';
            break;
        case 'BloodSorcery':
            countDisplayId = 'bloodSorceryCountDisplay';
            progressFillId = 'bloodSorceryProgressFill';
            break;
        case 'Advanced':
            countDisplayId = 'advancedDisciplinesCountDisplay';
            progressFillId = 'advancedDisciplinesProgressFill';
            break;
    }
    
    const countDisplay = document.getElementById(countDisplayId);
    const progressFill = document.getElementById(progressFillId);
    
    if (countDisplay) {
        countDisplay.textContent = totalLevels;
    }
    
    if (progressFill) {
        // Update progress bar (3 levels max for character creation)
        const percentage = Math.min((totalLevels / 3) * 100, 100);
        progressFill.style.width = percentage + '%';
        
        // Update progress bar class
        if (totalLevels >= 3) {
            progressFill.classList.remove('incomplete');
            progressFill.classList.add('complete');
        } else {
            progressFill.classList.remove('complete');
            progressFill.classList.add('incomplete');
        }
    }
}

// Mark character as complete (called when character creation is finished)
function markCharacterComplete() {
    characterData.isCharacterComplete = true;
    
    // Show advancement mode indicator
    const modeDisplay = document.getElementById('characterModeDisplay');
    if (modeDisplay) {
        modeDisplay.style.display = 'block';
    }
    
    // Update all displays
    updateXPDisplay(); // Recalculate XP with advancement rules
    updateDisciplineCount('Clan');
    updateDisciplineCount('BloodSorcery');
    updateDisciplineCount('Advanced');
    
    // Show confirmation
    alert('Character creation completed! You are now in advancement mode.\n\nYou can now:\n- Remove any discipline powers\n- Add new powers (all cost XP)\n- No 3-level limit restrictions');
}

// Check if character is in advancement mode
function isAdvancementMode() {
    return characterData.isCharacterComplete;
}

// Get clan discipline access mapping (now loaded from database)
function getClanDisciplineAccess() {
    return clanDisciplineAccess;
}

// XP tracking and validation functions
function updateXPDisplay() {
    let totalXP = 0;
    let traitsXP = 0;
    let abilitiesXP = 0;
    let disciplinesXP = 0;
    let negativeTraitsXP = 0;
    
    // Calculate XP spent on traits (first 7 are free, 8-10 cost 4 XP each)
    ['Physical', 'Social', 'Mental'].forEach(category => {
        const count = characterData.traits[category].length;
        if (count > 7) {
            const paidTraits = count - 7;
            traitsXP += paidTraits * 4;
        }
    });
    
    // Calculate XP spent on abilities (first 3 are free, 4-5 cost 2 XP each)
    ['Physical', 'Social', 'Mental', 'Optional'].forEach(category => {
        const count = characterData.abilities[category].length;
        if (count > 3) {
            const paidAbilities = count - 3;
            abilitiesXP += paidAbilities * 2;
        }
    });
    
    // Calculate XP spent on discipline powers
    const selectedClan = document.getElementById('clan').value;
    const clanDisciplineAccess = getClanDisciplineAccess();
    
    if (isAdvancementMode()) {
        // In advancement mode: all powers cost XP based on in-clan/out-of-clan
        ['Clan', 'BloodSorcery', 'Advanced'].forEach(category => {
            if (characterData.disciplines[category]) {
                characterData.disciplines[category].forEach(power => {
                    const isInClan = clanDisciplineAccess[selectedClan] && 
                                    clanDisciplineAccess[selectedClan].includes(power.name);
                    const xpCost = isInClan ? power.level * 3 : power.level * 4;
                    disciplinesXP += xpCost;
                });
            }
        });
    } else {
        // In character creation mode: first 3 discipline levels are free
        const disciplineLevels = {}; // Track highest level per discipline
        
        ['Clan', 'BloodSorcery', 'Advanced'].forEach(category => {
            if (characterData.disciplines[category]) {
                characterData.disciplines[category].forEach(power => {
                    const disciplineName = power.name;
                    if (!disciplineLevels[disciplineName] || power.level > disciplineLevels[disciplineName]) {
                        disciplineLevels[disciplineName] = power.level;
                    }
                });
            }
        });
        
        // Sum up the highest level of each discipline
        const totalDisciplineLevels = Object.values(disciplineLevels).reduce((sum, level) => sum + level, 0);
        
        // First 3 discipline levels are free at character creation
        if (totalDisciplineLevels > 3) {
            const paidLevels = totalDisciplineLevels - 3;
            disciplinesXP += paidLevels * 3; // Each additional level costs 3 XP
        }
    }
    
    // Calculate XP gained from negative traits (+4 XP each)
    ['Physical', 'Social', 'Mental'].forEach(category => {
        const negativeCount = characterData.negativeTraits[category].length;
        negativeTraitsXP += negativeCount * 4;
    });
    
    // Calculate XP spent on backgrounds (first 5 points are free, additional points cost 2 XP each)
    const totalBackgroundPoints = Object.values(characterData.backgrounds).reduce((sum, level) => sum + level, 0);
    const backgroundsXP = Math.max(0, totalBackgroundPoints - 5) * 2;
    
    totalXP = traitsXP + abilitiesXP + disciplinesXP + backgroundsXP - negativeTraitsXP; // Negative traits reduce XP cost
    
    // Update character data
    characterData.xpSpent = totalXP;
    characterData.xpRemaining = 30 - totalXP;
    
    // Update displays
    document.getElementById('xpSpent').textContent = totalXP;
    document.getElementById('xpRemaining').textContent = characterData.xpRemaining;
    document.getElementById('xpDisplay').textContent = characterData.xpRemaining;
    document.getElementById('xpTraits').textContent = traitsXP;
    document.getElementById('xpAbilities').textContent = abilitiesXP;
    document.getElementById('xpDisciplines').textContent = disciplinesXP;
    document.getElementById('xpBackgrounds').textContent = backgroundsXP;
    document.getElementById('xpFlaws').textContent = negativeTraitsXP;
    
    // Update XP remaining color
    const xpRemainingElement = document.getElementById('xpRemaining');
    if (characterData.xpRemaining < 0) {
        xpRemainingElement.classList.add('negative');
    } else {
        xpRemainingElement.classList.remove('negative');
    }
}

function validateCharacter() {
    // Validate that character meets LOTN requirements
    // Check trait counts, XP spending, etc.
}

// Form validation functions (to be implemented)
function validateForm() {
    // Validate all form fields before saving
    // Return true if valid, false if not
}

// Character data management (to be implemented)
function loadCharacter(characterId) {
    // Load existing character data into the form
}

function saveCharacterData() {
    // Collect all form data and save to database
}

// Clan Guide Modal Functions
function showClanGuide() {
    document.getElementById('clanGuideModal').style.display = 'block';
}

function closeClanGuide() {
    document.getElementById('clanGuideModal').style.display = 'none';
}

// Discipline Guide Modal Functions
function showDisciplineGuide() {
    document.getElementById('disciplineGuideModal').style.display = 'block';
}

function closeDisciplineGuide() {
    document.getElementById('disciplineGuideModal').style.display = 'none';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const clanModal = document.getElementById('clanGuideModal');
    const disciplineModal = document.getElementById('disciplineGuideModal');
    if (event.target === clanModal) {
        clanModal.style.display = 'none';
    }
    if (event.target === disciplineModal) {
        disciplineModal.style.display = 'none';
    }
}

// Discipline Section Visibility Functions
function handleClanChange() {
    const clanSelect = document.getElementById('clan');
    const selectedClan = clanSelect.value;
    
    // Check if player has selected disciplines and warn about potential loss
    const hasSelectedDisciplines = Object.values(characterData.disciplines).some(category => 
        category && category.length > 0
    );
    
    if (hasSelectedDisciplines) {
        const confirmed = confirm(
            `⚠️ Warning: Changing your clan may remove some of your selected disciplines.\n\n` +
            `Are you sure you want to switch to ${selectedClan}?\n\n` +
            `Any disciplines not available to ${selectedClan} will be automatically removed.`
        );
        
        if (!confirmed) {
            // Revert the clan selection
            const previousClan = clanSelect.dataset.previousValue || 'Brujah';
            clanSelect.value = previousClan;
            return;
        }
    }
    
    // Store current selection as previous value
    clanSelect.dataset.previousValue = selectedClan;
    
    // Define which clans have access to which discipline categories
    const bloodSorceryClans = ['Giovanni', 'Tremere', 'Caitiff'];
    const advancedClans = ['Assamite', 'Followers of Set', 'Lasombra', 'Malkavian', 'Ravnos', 'Tzimisce', 'Caitiff'];
    
    // Get clan-specific discipline access
    const clanDisciplineAccess = getClanDisciplineAccess();
    
    // Get discipline sections
    const bloodSorcerySection = document.querySelector('[data-category="BloodSorcery"]');
    const advancedSection = document.querySelector('[data-category="Advanced"]');
    
    // Show/hide Blood Sorcery section
    if (bloodSorcerySection) {
        if (bloodSorceryClans.includes(selectedClan)) {
            bloodSorcerySection.style.display = 'block';
        } else {
            bloodSorcerySection.style.display = 'none';
            // Clear any selected Blood Sorcery disciplines
            clearDisciplinesByCategory('BloodSorcery');
        }
    }
    
    // Show/hide Advanced Disciplines section
    if (advancedSection) {
        if (advancedClans.includes(selectedClan)) {
            advancedSection.style.display = 'block';
        } else {
            advancedSection.style.display = 'none';
            // Clear any selected Advanced disciplines
            clearDisciplinesByCategory('Advanced');
        }
    }
    
    // Clear any invalid disciplines and update button states
    clearInvalidDisciplines(selectedClan, clanDisciplineAccess);
    updateDisciplineButtonStates(selectedClan, clanDisciplineAccess);
}

function clearDisciplinesByCategory(category) {
    // Remove disciplines from the specified category from characterData
    if (characterData.disciplines && characterData.disciplines[category]) {
        characterData.disciplines[category] = [];
        refreshDisciplineDisplay(category);
        updateXPDisplay();
    }
}

function clearInvalidDisciplines(selectedClan, clanDisciplineAccess) {
    // Clear any selected disciplines that the clan can't access
    const allowedDisciplines = clanDisciplineAccess[selectedClan] || [];
    let removedPowers = [];
    
    if (characterData.disciplines) {
        Object.keys(characterData.disciplines).forEach(category => {
            if (characterData.disciplines[category]) {
                const originalCount = characterData.disciplines[category].length;
                
                // Filter out powers from disciplines the clan can't access
                characterData.disciplines[category] = characterData.disciplines[category].filter(power => {
                    const isValid = allowedDisciplines.includes(power.name);
                    if (!isValid) {
                        removedPowers.push(`${power.name}: ${power.powerName}`);
                    }
                    return isValid;
                });
                
                refreshDisciplineDisplay(category);
            }
        });
        updateXPDisplay();
        
        // Show notification if any powers were removed
        if (removedPowers.length > 0) {
            showClanChangeNotification(removedPowers, selectedClan);
        }
    }
}

// Show notification when disciplines are removed due to clan change
function showClanChangeNotification(removedPowers, newClan) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #8b0000;
        color: #fff;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        z-index: 10000;
        max-width: 300px;
        font-size: 14px;
        border-left: 4px solid #ff8c00;
    `;
    
    const powerList = removedPowers.slice(0, 3).join('<br>');
    const moreText = removedPowers.length > 3 ? `<br>...and ${removedPowers.length - 3} more` : '';
    
    notification.innerHTML = `
        <strong>⚠️ Clan Change Notice</strong><br>
        <small>Switched to ${newClan}</small><br><br>
        <strong>Removed powers:</strong><br>
        ${powerList}${moreText}
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
    
    // Allow manual close
    notification.onclick = () => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    };
}

function updateDisciplineButtonStates(selectedClan, clanDisciplineAccess) {
    // Get all discipline option buttons
    const allDisciplineButtons = document.querySelectorAll('.discipline-option-btn');
    
    // Get allowed disciplines for the selected clan
    const allowedDisciplines = clanDisciplineAccess[selectedClan] || [];
    
    // Update each discipline button
    allDisciplineButtons.forEach(button => {
        const disciplineName = button.textContent.trim();
        const isAllowed = allowedDisciplines.includes(disciplineName);
        
        if (isAllowed) {
            // Enable the button
            button.disabled = false;
            button.style.opacity = '1';
            button.style.cursor = 'pointer';
            button.title = ''; // Clear any tooltip
        } else {
            // Disable the button
            button.disabled = true;
            button.style.opacity = '0.4';
            button.style.cursor = 'not-allowed';
            button.title = `${disciplineName} is not available to ${selectedClan}`;
        }
    });
}

function initializeDisciplineSections() {
    // Set initial visibility based on current clan selection
    handleClanChange();
}

// Finalize Character Functions
function showFinalizePopup() {
    // Generate character summary
    generateCharacterSummary();
    
    // Show the finalize modal
    document.getElementById('finalizeModal').style.display = 'block';
}

function closeFinalizeModal() {
    document.getElementById('finalizeModal').style.display = 'none';
}

function generateCharacterSummary() {
    const summaryDiv = document.getElementById('characterSummary');
    const previewDiv = document.getElementById('finalizePreview');
    
    // Get character data
    const characterName = document.getElementById('characterName').value || 'Unnamed Character';
    const playerName = document.getElementById('playerName').value || 'Unknown Player';
    const clan = document.getElementById('clan').value || 'No Clan Selected';
    const concept = document.getElementById('concept').value || 'No Concept';
    const nature = document.getElementById('nature').value || 'No Nature';
    const demeanor = document.getElementById('demeanor').value || 'No Demeanor';
    
    // Count traits
    const physicalTraits = characterData.traits.Physical.length;
    const socialTraits = characterData.traits.Social.length;
    const mentalTraits = characterData.traits.Mental.length;
    
    // Count abilities
    const physical = characterData.abilities.Physical ? characterData.abilities.Physical.length : 0;
    const social = characterData.abilities.Social ? characterData.abilities.Social.length : 0;
    const mental = characterData.abilities.Mental ? characterData.abilities.Mental.length : 0;
    
    // Count disciplines
    const totalDisciplines = Object.keys(characterData.disciplines).length;
    const totalDisciplineLevels = Object.values(characterData.disciplines).reduce((sum, levels) => sum + levels.length, 0);
    
    // Count backgrounds
    const totalBackgroundPoints = Object.values(characterData.backgrounds).reduce((sum, level) => sum + level, 0);
    const selectedBackgrounds = Object.entries(characterData.backgrounds)
        .filter(([name, level]) => level > 0)
        .map(([name, level]) => {
            const details = characterData.backgroundDetails[name] ? ` (${characterData.backgroundDetails[name]})` : '';
            return `${name} ${level}${details}`;
        })
        .join(', ');
    
    // XP summary
    const totalXP = characterData.xpRemaining + characterData.xpSpent;
    const spentXP = characterData.xpSpent;
    const remainingXP = characterData.xpRemaining;
    
    const summaryHTML = `
        <div class="character-summary-content">
            <h4>📋 Character Summary</h4>
            <div class="summary-grid">
                <div class="summary-section">
                    <h5>Basic Info</h5>
                    <p><strong>Name:</strong> ${characterName}</p>
                    <p><strong>Player:</strong> ${playerName}</p>
                    <p><strong>Clan:</strong> ${clan}</p>
                    <p><strong>Concept:</strong> ${concept}</p>
                    <p><strong>Nature:</strong> ${nature}</p>
                    <p><strong>Demeanor:</strong> ${demeanor}</p>
                </div>
                <div class="summary-section">
                    <h5>Traits</h5>
                    <p><strong>Physical:</strong> ${physicalTraits}/7</p>
                    <p><strong>Social:</strong> ${socialTraits}/5</p>
                    <p><strong>Mental:</strong> ${mentalTraits}/3</p>
                </div>
                <div class="summary-section">
                    <h5>Abilities</h5>
                    <p><strong>Physical:</strong> ${physical}/3</p>
                    <p><strong>Social:</strong> ${social}/3</p>
                    <p><strong>Mental:</strong> ${mental}/3</p>
                </div>
                <div class="summary-section">
                    <h5>Disciplines</h5>
                    <p><strong>Total Disciplines:</strong> ${totalDisciplines}</p>
                    <p><strong>Total Levels:</strong> ${totalDisciplineLevels}</p>
                </div>
                <div class="summary-section">
                    <h5>Backgrounds</h5>
                    <p><strong>Total Points:</strong> ${totalBackgroundPoints}/5</p>
                    <p><strong>Selected:</strong> ${selectedBackgrounds || 'None'}</p>
                </div>
                <div class="summary-section">
                    <h5>Experience</h5>
                    <p><strong>Total XP:</strong> ${totalXP}</p>
                    <p><strong>Spent XP:</strong> ${spentXP}</p>
                    <p><strong>Remaining XP:</strong> ${remainingXP}</p>
                </div>
            </div>
        </div>
    `;
    
    summaryDiv.innerHTML = summaryHTML;
    previewDiv.innerHTML = summaryHTML;
}

function finalizeCharacter() {
    // Mark character as complete
    characterData.isCharacterComplete = true;
    
    // Save character to database
    saveCharacter(true); // true indicates finalization
    
    // Close the modal
    closeFinalizeModal();
    
    // Show success message
    showNotification('🎉 Character finalized successfully!', 'success');
    
    // Show character sheet
    setTimeout(() => {
        showCharacterSheet();
    }, 1000);
}

function showCharacterSheet() {
    // Generate character sheet content
    generateCharacterSheet();
    
    // Show the character sheet modal
    document.getElementById('characterSheetModal').style.display = 'block';
}

function closeCharacterSheetModal() {
    document.getElementById('characterSheetModal').style.display = 'none';
}

function generateCharacterSheet() {
    const sheetDiv = document.getElementById('characterSheetContent');
    
    // Get character data
    const characterName = document.getElementById('characterName').value || 'Unnamed Character';
    const playerName = document.getElementById('playerName').value || 'Unknown Player';
    const clan = document.getElementById('clan').value || 'No Clan Selected';
    const concept = document.getElementById('concept').value || 'No Concept';
    const nature = document.getElementById('nature').value || 'No Nature';
    const demeanor = document.getElementById('demeanor').value || 'No Demeanor';
    const chronicle = document.getElementById('chronicle').value || 'Valley by Night';
    const generation = document.getElementById('generation').value || '13th';
    const sire = document.getElementById('sire').value || 'Unknown';
    
    const sheetHTML = `
        <div class="character-sheet-content">
            <div class="sheet-header">
                <h1>Laws of the Night - Character Sheet</h1>
                <div class="character-info">
                    <h2>${characterName}</h2>
                    <p><strong>Player:</strong> ${playerName} | <strong>Chronicle:</strong> ${chronicle}</p>
                </div>
            </div>
            
            <div class="sheet-section">
                <h3>Basic Information</h3>
                <div class="info-grid">
                    <p><strong>Clan:</strong> ${clan}</p>
                    <p><strong>Concept:</strong> ${concept}</p>
                    <p><strong>Nature:</strong> ${nature}</p>
                    <p><strong>Demeanor:</strong> ${demeanor}</p>
                    <p><strong>Generation:</strong> ${generation}</p>
                    <p><strong>Sire:</strong> ${sire}</p>
                </div>
            </div>
            
            <div class="sheet-section">
                <h3>Attributes</h3>
                <div class="attributes-grid">
                    <div class="attribute-category">
                        <h4>Physical</h4>
                        <div class="attribute-list">
                            ${characterData.traits.Physical.map(trait => `<div class="attribute-item">${trait}</div>`).join('')}
                        </div>
                    </div>
                    <div class="attribute-category">
                        <h4>Social</h4>
                        <div class="attribute-list">
                            ${characterData.traits.Social.map(trait => `<div class="attribute-item">${trait}</div>`).join('')}
                        </div>
                    </div>
                    <div class="attribute-category">
                        <h4>Mental</h4>
                        <div class="attribute-list">
                            ${characterData.traits.Mental.map(trait => `<div class="attribute-item">${trait}</div>`).join('')}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="sheet-section">
                <h3>Abilities</h3>
                <div class="abilities-grid">
                    <div class="ability-category">
                        <h4>Physical</h4>
                        <div class="ability-list">
                            ${characterData.abilities.Physical ? characterData.abilities.Physical.map(ability => `<div class="ability-item">${ability}</div>`).join('') : ''}
                        </div>
                    </div>
                    <div class="ability-category">
                        <h4>Social</h4>
                        <div class="ability-list">
                            ${characterData.abilities.Social ? characterData.abilities.Social.map(ability => `<div class="ability-item">${ability}</div>`).join('') : ''}
                        </div>
                    </div>
                    <div class="ability-category">
                        <h4>Mental</h4>
                        <div class="ability-list">
                            ${characterData.abilities.Mental ? characterData.abilities.Mental.map(ability => `<div class="ability-item">${ability}</div>`).join('') : ''}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="sheet-section">
                <h3>Disciplines</h3>
                <div class="disciplines-list">
                    ${Object.entries(characterData.disciplines).map(([discipline, levels]) => 
                        `<div class="discipline-item">
                            <strong>${discipline}:</strong> ${levels.map(level => level.name).join(', ')}
                        </div>`
                    ).join('')}
                </div>
            </div>
            
            <div class="sheet-section">
                <h3>Backgrounds</h3>
                <div class="backgrounds-list">
                    ${Object.entries(characterData.backgrounds)
                        .filter(([name, level]) => level > 0)
                        .map(([name, level]) => {
                            const details = characterData.backgroundDetails[name] ? ` - ${characterData.backgroundDetails[name]}` : '';
                            return `<div class="background-item">
                                <strong>${name}:</strong> ${level}${details}
                            </div>`;
                        }).join('')}
                    ${Object.values(characterData.backgrounds).every(level => level === 0) ? 
                        '<div class="background-item">No backgrounds selected</div>' : ''}
                </div>
            </div>
            
            <div class="sheet-section">
                <h3>Experience</h3>
                <div class="xp-info">
                    <p><strong>Total XP:</strong> ${characterData.xpRemaining + characterData.xpSpent}</p>
                    <p><strong>Spent XP:</strong> ${characterData.xpSpent}</p>
                    <p><strong>Remaining XP:</strong> ${characterData.xpRemaining}</p>
                </div>
            </div>
        </div>
    `;
    
    sheetDiv.innerHTML = sheetHTML;
}

function downloadCharacterSheet() {
    // This would implement PDF generation
    // For now, we'll show a placeholder message
    showNotification('📥 PDF download feature coming soon!', 'info');
}

// Initialize the character creation form
// Backgrounds System Functions
function calculateGenerationBackground() {
    const generationSelect = document.getElementById('generation');
    if (!generationSelect) return 0;
    
    const generationValue = parseInt(generationSelect.value);
    if (!generationValue) return 0;
    
    // Convert generation to background points (lower generation = higher background)
    // 13th generation = 1 point, 12th = 2 points, 11th = 3 points, 10th = 4 points, 9th = 5 points
    // Higher generations (14th, 15th) = 0 points
    if (generationValue >= 9 && generationValue <= 13) {
        return 14 - generationValue; // 13th=1, 12th=2, 11th=3, 10th=4, 9th=5
    }
    return 0; // 14th generation and higher get 0 background points
}

function updateGenerationBackground() {
    const generationLevel = calculateGenerationBackground();
    characterData.backgrounds.Generation = generationLevel;
    updateBackgroundDisplay('Generation');
    updateBackgroundsSummary();
    updateXPDisplay();
}

function selectBackground(backgroundName, level) {
    // Check if this level is already selected
    if (characterData.backgrounds[backgroundName] === level) {
        // Deselect if already selected
        characterData.backgrounds[backgroundName] = 0;
    } else {
        // Select new level
        characterData.backgrounds[backgroundName] = level;
    }
    
    // Update display
    updateBackgroundDisplay(backgroundName);
    updateBackgroundsSummary();
    updateXPDisplay();
}

function updateBackgroundDisplay(backgroundName) {
    const level = characterData.backgrounds[backgroundName];
    const countDisplay = document.getElementById(backgroundName.toLowerCase() + 'CountDisplay');
    const progressFill = document.getElementById(backgroundName.toLowerCase() + 'ProgressFill');
    const backgroundList = document.getElementById(backgroundName.toLowerCase() + 'List');
    
    // Update count display
    if (countDisplay) {
        countDisplay.textContent = level;
    }
    
    // Update progress bar
    if (progressFill) {
        const percentage = (level / 5) * 100;
        progressFill.style.width = percentage + '%';
        
        // Update progress bar class
        if (level > 0) {
            progressFill.classList.add('complete');
        } else {
            progressFill.classList.remove('complete');
        }
    }
    
    // Update background list
    if (backgroundList) {
        backgroundList.innerHTML = '';
        
        if (level > 0) {
            const backgroundItem = document.createElement('div');
            backgroundItem.className = 'background-item';
            if (backgroundName === 'Generation') {
                // Auto-calculated background - no remove button
                backgroundItem.innerHTML = `<span>${backgroundName} ${level} (Auto-calculated)</span>`;
            } else {
                // Manual background - with remove button
                backgroundItem.innerHTML = `
                    <span>${backgroundName} ${level}</span>
                    <button type="button" class="remove-btn" onclick="selectBackground('${backgroundName}', ${level})" title="Remove">×</button>
                `;
            }
            backgroundList.appendChild(backgroundItem);
        } else {
            const emptyMessage = document.createElement('div');
            emptyMessage.className = 'background-empty';
            if (backgroundName === 'Generation') {
                emptyMessage.textContent = 'Generation will be calculated from Basic Info';
            } else {
                emptyMessage.textContent = 'No background selected';
            }
            backgroundList.appendChild(emptyMessage);
        }
    }
    
    // Update button states (skip for auto-calculated Generation)
    if (backgroundName !== 'Generation') {
        updateBackgroundButtons(backgroundName);
    }
}

function updateBackgroundButtons(backgroundName) {
    const level = characterData.backgrounds[backgroundName];
    const buttons = document.querySelectorAll(`[data-background="${backgroundName}"]`);
    
    buttons.forEach(button => {
        const buttonLevel = parseInt(button.dataset.level);
        if (buttonLevel === level) {
            button.classList.add('selected');
        } else {
            button.classList.remove('selected');
        }
    });
}

function updateBackgroundsSummary() {
    const totalPoints = Object.values(characterData.backgrounds).reduce((sum, level) => sum + level, 0);
    const freePoints = 5; // Characters get 5 free background points
    const usedFreePoints = Math.min(totalPoints, freePoints);
    const xpCost = Math.max(0, totalPoints - freePoints) * 2; // Each point over 5 costs 2 XP
    
    // Update displays
    const totalDisplay = document.getElementById('totalBackgroundsDisplay');
    const freeDisplay = document.getElementById('freeBackgroundsDisplay');
    const xpDisplay = document.getElementById('backgroundsXpDisplay');
    
    if (totalDisplay) totalDisplay.textContent = totalPoints;
    if (freeDisplay) freeDisplay.textContent = `${usedFreePoints}/${freePoints}`;
    if (xpDisplay) xpDisplay.textContent = xpCost;
    
    // Update XP tracker
    updateXPDisplay();
}

function initializeBackgrounds() {
    // Initialize manual background displays (excluding Generation which is auto-calculated)
    const manualBackgroundNames = ['Allies', 'Contacts', 'Fame', 'Herd', 'Influence', 'Mentor', 'Resources', 'Retainers', 'Status'];
    
    manualBackgroundNames.forEach(backgroundName => {
        updateBackgroundDisplay(backgroundName);
    });
    
    // Initialize auto-calculated generation background
    updateGenerationBackground();
    
    // Add event listener to generation dropdown
    const generationSelect = document.getElementById('generation');
    if (generationSelect) {
        generationSelect.addEventListener('change', updateGenerationBackground);
    }
    
    // Add event listeners to background detail textareas
    const backgroundNames = ['Allies', 'Contacts', 'Fame', 'Herd', 'Influence', 'Mentor', 'Resources', 'Retainers', 'Status'];
    backgroundNames.forEach(backgroundName => {
        const textarea = document.getElementById(backgroundName.toLowerCase() + 'Details');
        if (textarea) {
            textarea.addEventListener('input', function() {
                characterData.backgroundDetails[backgroundName] = this.value;
            });
        }
    });
    
    updateBackgroundsSummary();
}

document.addEventListener('DOMContentLoaded', async function() {
    // Initialize any required functionality when page loads
    console.log('LOTN Character Creation form loaded');
    
    // Load discipline data from database
    await loadDisciplineData();
    
    // Initialize discipline section visibility
    initializeDisciplineSections();
    
    // Initialize backgrounds system
    initializeBackgrounds();
    
    // Generate character summary when Final Details tab is shown
    const finalDetailsTab = document.querySelector('[onclick="showTab(7)"]');
    if (finalDetailsTab) {
        finalDetailsTab.addEventListener('click', function() {
            setTimeout(generateCharacterSummary, 100);
        });
    }
});
