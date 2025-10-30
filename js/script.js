// Laws of the Night Character Creation - JavaScript Functions
// Version 0.2.0

// API Configuration
const API_BASE_URL = 'http://vbn.talkingheads.video/api';
const PHP_BASE_URL = ''; // Relative path for PHP scripts

// Tab functionality
function showTab(tabIndex) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tabs
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Show selected tab content
    const selectedTab = document.getElementById('tab' + tabIndex);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
    
    // Add active class to selected tab button
    const selectedTabButton = document.querySelector(`[onclick="showTab(${tabIndex})"]`);
    if (selectedTabButton) {
        selectedTabButton.classList.add('active');
    }
    
    // Update progress bar
    updateTabProgress(tabIndex);
}

function updateTabProgress(tabIndex) {
    const progressBar = document.getElementById('tabProgressBar');
    if (progressBar) {
        // Calculate progress percentage (tabIndex + 1) / total tabs * 100
        const totalTabs = 8;
        const progress = ((tabIndex + 1) / totalTabs) * 100;
        progressBar.style.width = progress + '%';
    }
}

// Sheet Mode Toggle
function setSheetMode(mode) {
    const previewCard = document.getElementById('previewCard');
    if (previewCard) {
        if (mode === 'compact') {
            previewCard.classList.add('compact');
        } else {
            previewCard.classList.remove('compact');
        }
    }
}

// Live Character Preview System
function updateCharacterPreview() {
    updatePreviewBasicInfo();
    updatePreviewTraits();
    updatePreviewAbilities();
    updatePreviewDisciplines();
}

function updatePreviewBasicInfo() {
    const name = document.getElementById('characterName')?.value || '';
    const clan = document.getElementById('clan')?.value || '';
    
    const previewName = document.getElementById('previewName');
    const previewClan = document.getElementById('previewClan');
    
    if (previewName) {
        previewName.textContent = name || 'Unknown Character';
        previewName.className = name ? 'preview-name' : 'preview-name empty';
    }
    
    if (previewClan) {
        previewClan.textContent = clan || 'No Clan Selected';
        previewClan.className = clan ? 'preview-clan' : 'preview-clan empty';
    }
}

function updatePreviewTraits() {
    updatePreviewTraitCategory('physical', 'previewPhysical');
    updatePreviewTraitCategory('social', 'previewSocial');
    updatePreviewTraitCategory('mental', 'previewMental');
}

function updatePreviewTraitCategory(category, previewId) {
    const previewElement = document.getElementById(previewId);
    if (!previewElement) return;
    
    const selectedTraits = getSelectedTraits(category);
    
    if (selectedTraits.length === 0) {
        previewElement.innerHTML = '<span class="preview-trait empty">None selected</span>';
        return;
    }
    
    previewElement.innerHTML = selectedTraits.map(trait => 
        `<span class="preview-trait">${trait}</span>`
    ).join('');
}

function updatePreviewAbilities() {
    const previewElement = document.getElementById('previewAbilities');
    if (!previewElement) return;
    
    const selectedAbilities = getSelectedAbilities();
    
    if (selectedAbilities.length === 0) {
        previewElement.innerHTML = '<span class="preview-trait empty">None selected</span>';
        return;
    }
    
    previewElement.innerHTML = selectedAbilities.map(ability => 
        `<span class="preview-trait">${ability}</span>`
    ).join('');
}

function updatePreviewDisciplines() {
    const previewElement = document.getElementById('previewDisciplines');
    if (!previewElement) return;
    
    const selectedDisciplines = getSelectedDisciplines();
    
    if (selectedDisciplines.length === 0) {
        previewElement.innerHTML = '<span class="preview-trait empty">None selected</span>';
        return;
    }
    
    previewElement.innerHTML = selectedDisciplines.map(discipline => 
        `<span class="preview-trait">${discipline}</span>`
    ).join('');
}

function getSelectedTraits(category) {
    const traits = [];
    const categoryElement = document.querySelector(`.trait-category[data-category="${category}"]`);
    if (!categoryElement) return traits;
    
    const selectedButtons = categoryElement.querySelectorAll('.trait-btn.selected');
    selectedButtons.forEach(button => {
        const traitName = button.textContent.trim();
        const count = parseInt(button.dataset.count) || 1;
        for (let i = 0; i < count; i++) {
            traits.push(traitName);
        }
    });
    
    return traits;
}

function getSelectedAbilities() {
    const abilities = [];
    const abilityButtons = document.querySelectorAll('.ability-btn.selected');
    abilityButtons.forEach(button => {
        const abilityName = button.textContent.trim();
        const count = parseInt(button.dataset.count) || 1;
        for (let i = 0; i < count; i++) {
            abilities.push(abilityName);
        }
    });
    
    return abilities;
}

function getSelectedDisciplines() {
    const disciplines = [];
    const disciplineButtons = document.querySelectorAll('.discipline-btn.selected');
    disciplineButtons.forEach(button => {
        const disciplineName = button.textContent.trim();
        const count = parseInt(button.dataset.count) || 1;
        for (let i = 0; i < count; i++) {
            disciplines.push(disciplineName);
        }
    });
    
    return disciplines;
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
    fetch('save_character.php', {
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
        merits_flaws: [...selectedMerits, ...selectedFlaws],
        morality: {
            path_name: 'Humanity',
            path_rating: getHumanityValue(),
            conscience: getConscienceValue(),
            self_control: getSelfControlValue(),
            courage: 1, // Not used in Humanity path
            willpower_permanent: characterData.willpower ? characterData.willpower.permanent : 5,
            willpower_current: characterData.willpower ? characterData.willpower.current : 5,
            humanity: getHumanityValue(),
            current_moral_state: getMoralState(getHumanityValue())
        },
        status: {
            sect_status: '',
            clan_status: '',
            city_status: '',
            health_levels: characterData.health ? characterData.health.levels : 7,
            health_current: characterData.health ? characterData.health.current : 7,
            blood_pool_current: 10,
            blood_pool_maximum: 10
        },
        health: characterData.health || {
            levels: 7,
            current: 7,
            damage: 0
        },
        willpower: characterData.willpower || {
            current: 5,
            permanent: 5
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
    
    // Check if we're at the maximum (capped at free points during character creation)
    let freePoints = 7; // Default fallback
    if (window.characterCreationApp && window.characterCreationApp.modules.traitSystem) {
        freePoints = window.characterCreationApp.modules.traitSystem.getFreePoints(category);
    }
    
    if (traitList.length >= freePoints) {
        showNotification(`Maximum ${freePoints} traits allowed in ${category} category during character creation.`, 'error');
        return;
    }
    
    // Add trait to character data (all traits are free during character creation)
    traitList.push(traitName);
    
    // Show feedback
    showNotification(`${traitName} added (FREE)`, 'success');
    
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
    
    // Update character preview
    updateCharacterPreview();
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
    
    // Update character preview
    updateCharacterPreview();
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
    
    // Get free points for this category (default to old system if not set)
    let freePoints = 7; // Default fallback
    if (window.characterCreationApp && window.characterCreationApp.modules.traitSystem) {
        freePoints = window.characterCreationApp.modules.traitSystem.getFreePoints(category);
    }
    
    // Update progress bar based on free points (capped at 100%)
    const percentage = Math.min((count / freePoints) * 100, 100);
    progressFill.style.width = percentage + '%';
    
    // Update progress bar class based on free points
    if (count >= freePoints) {
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
    
    // Calculate XP spent on virtues (first 7 points are free, additional points cost 2 XP each)
    const totalVirtuePoints = conscience + selfControl;
    const virtuesXP = Math.max(0, totalVirtuePoints - 7) * 2;
    
    // Calculate XP spent on merits and flaws
    const meritsCost = selectedMerits.reduce((sum, merit) => sum + (merit.selectedCost || merit.cost), 0);
    const flawsPoints = selectedFlaws.reduce((sum, flaw) => sum + (flaw.selectedCost || flaw.cost), 0);
    const meritsFlawsXP = meritsCost - flawsPoints; // Flaws give XP back
    
    totalXP = traitsXP + abilitiesXP + disciplinesXP + backgroundsXP + virtuesXP + meritsFlawsXP - negativeTraitsXP; // Negative traits reduce XP cost
    
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
    document.getElementById('xpVirtues').textContent = virtuesXP;
    document.getElementById('xpMeritsFlaws').textContent = meritsFlawsXP;
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
    const meritFlawModal = document.getElementById('meritFlawDescriptionModal');
    
    if (event.target === clanModal) {
        clanModal.style.display = 'none';
    }
    if (event.target === disciplineModal) {
        disciplineModal.style.display = 'none';
    }
    if (event.target === meritFlawModal) {
        meritFlawModal.style.display = 'none';
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
    console.log('generateCharacterSummary called');
    
    const summaryDiv = document.getElementById('characterSummary');
    const previewDiv = document.getElementById('finalizePreview');
    
    console.log('summaryDiv:', summaryDiv);
    console.log('previewDiv:', previewDiv);
    
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
                    <h5>Resources</h5>
                    <p><strong>Cash:</strong> $${characterData.cash || 0}</p>
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
    
    console.log('Generated summaryHTML length:', summaryHTML.length);
    console.log('Generated summaryHTML preview:', summaryHTML.substring(0, 200) + '...');
    
    if (summaryDiv) {
        console.log('Setting innerHTML on summaryDiv');
        summaryDiv.innerHTML = summaryHTML;
    }
    if (previewDiv) {
        console.log('Setting innerHTML on previewDiv');
        previewDiv.innerHTML = summaryHTML;
    }
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
    // Resolve portrait -> prefer hidden filename, then current preview src, then app state
    let portraitUrl = '';
    (function resolvePortrait(){
        const imagePathHidden = document.getElementById('imagePath');
        const filename = imagePathHidden && imagePathHidden.value ? imagePathHidden.value : '';
        if (filename) { portraitUrl = '/uploads/characters/' + filename; return; }
        const previewEl = document.getElementById('characterImagePreview');
        if (previewEl && previewEl.src) { portraitUrl = previewEl.src; return; }
        try {
            const state = window.characterCreationApp && window.characterCreationApp.modules && window.characterCreationApp.modules.stateManager ? window.characterCreationApp.modules.stateManager.getState() : null;
            const stateFilename = state && state.imagePath ? state.imagePath : '';
            if (stateFilename) { portraitUrl = '/uploads/characters/' + stateFilename; return; }
        } catch (_) {}
    })();
    
    const sheetHTML = `
        <div class="character-sheet-content">
            <div class="sheet-header">
                <h1>Laws of the Night - Character Sheet</h1>
                <div class="character-info">
                    <h2>${characterName}</h2>
                    <p><strong>Player:</strong> ${playerName} | <strong>Chronicle:</strong> ${chronicle}</p>
                </div>
                ${portraitUrl ? `<div class="character-portrait"><img src="${portraitUrl}" alt="${characterName} portrait" style="max-width:160px;border-radius:6px;border:1px solid #444"/></div>` : ''}
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
                ${portraitUrl ? `<div class="compact-portrait" style="margin-top:10px"><img src="${portraitUrl}" alt="${characterName} portrait" style="max-width:120px;border-radius:6px;border:1px solid #444"/></div>` : ''}
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
    
    // Generate character summary and calculate cash when Final Details tab is shown
    const finalDetailsTab = document.querySelector('[onclick="showTab(7)"]');
    if (finalDetailsTab) {
        finalDetailsTab.addEventListener('click', function() {
            calculateCash(); // Calculate cash when Final Details tab is clicked
            setTimeout(generateCharacterSummary, 100);
        });
    }
    
    // Setup live character preview
    setupPreviewEventListeners();
    
    // Setup mobile responsiveness
    setupMobileResponsiveness();
    
    // Initial preview update
    updateCharacterPreview();
});

// Setup event listeners for live character preview
function setupPreviewEventListeners() {
    // Basic info inputs
    const basicInfoInputs = ['characterName', 'clan', 'nature', 'demeanor', 'concept'];
    basicInfoInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', updateCharacterPreview);
            input.addEventListener('change', updateCharacterPreview);
        }
    });
    
    // Trait buttons
    const traitButtons = document.querySelectorAll('.trait-option-btn');
    traitButtons.forEach(button => {
        button.addEventListener('click', () => {
            setTimeout(updateCharacterPreview, 100); // Small delay to ensure data is updated
        });
    });
    
    // Ability buttons
    const abilityButtons = document.querySelectorAll('.ability-btn');
    abilityButtons.forEach(button => {
        button.addEventListener('click', () => {
            setTimeout(updateCharacterPreview, 100);
        });
    });
    
    // Discipline buttons
    const disciplineButtons = document.querySelectorAll('.discipline-btn');
    disciplineButtons.forEach(button => {
        button.addEventListener('click', () => {
            setTimeout(updateCharacterPreview, 100);
        });
    });
}

// Mobile Responsiveness Setup
function setupMobileResponsiveness() {
    // Add collapsible functionality to trait categories on mobile
    if (window.innerWidth <= 768) {
        setupCollapsibleSections();
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            setupCollapsibleSections();
        } else {
            removeCollapsibleSections();
        }
    });
    
    // Improve touch interactions
    setupTouchImprovements();
}

function setupCollapsibleSections() {
    const categories = document.querySelectorAll('.trait-category, .ability-category, .discipline-category');
    
    categories.forEach(category => {
        const header = category.querySelector('h3');
        if (header && !header.classList.contains('collapsible-header')) {
            header.classList.add('collapsible-header');
            header.addEventListener('click', function() {
                const content = category.querySelector('.trait-options, .ability-options, .discipline-options');
                if (content) {
                    content.classList.toggle('collapsible-content');
                    header.classList.toggle('collapsed');
                }
            });
        }
    });
}

function removeCollapsibleSections() {
    const headers = document.querySelectorAll('.collapsible-header');
    headers.forEach(header => {
        header.classList.remove('collapsible-header', 'collapsed');
        const content = header.parentElement.querySelector('.collapsible-content');
        if (content) {
            content.classList.remove('collapsible-content', 'collapsed');
        }
    });
}

function setupTouchImprovements() {
    // Add touch feedback to buttons
    const buttons = document.querySelectorAll('button, .trait-btn, .ability-btn, .discipline-btn');
    buttons.forEach(button => {
        button.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.95)';
        });
        
        button.addEventListener('touchend', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Prevent double-tap zoom on buttons
    buttons.forEach(button => {
        button.addEventListener('touchend', function(e) {
            e.preventDefault();
        });
    });
    
    // Improve scroll behavior for tabs
    const tabsContainer = document.querySelector('.tabs');
    if (tabsContainer) {
        tabsContainer.addEventListener('touchstart', function(e) {
            this.style.scrollBehavior = 'auto';
        });
        
        tabsContainer.addEventListener('touchend', function(e) {
            this.style.scrollBehavior = 'smooth';
        });
    }
}

// Morality System Functions
const moralStates = {
    // High Humanity (8-10)
    10: 'Compassionate',
    9: 'Empathetic',
    8: 'Principled',
    
    // Medium Humanity (5-7)
    7: 'Conflicted',
    6: 'Troubled',
    5: 'Detached',
    
    // Low Humanity (1-4)
    4: 'Hardened',
    3: 'Callous',
    2: 'Ruthless',
    1: 'Depraved'
};

// Virtue allocation system
let conscience = 1;
let selfControl = 1;

function adjustVirtue(virtue, change) {
    const newValue = virtue === 'conscience' ? conscience + change : selfControl + change;

    // Check bounds (1-5)
    if (newValue < 1 || newValue > 5) return;
    
    // Calculate current and new virtue totals
    const currentTotal = conscience + selfControl;
    const newTotal = virtue === 'conscience' ? newValue + selfControl : conscience + newValue;
    
    // Check if we have enough XP for the change
    const currentVirtueXP = Math.max(0, currentTotal - 7) * 2;
    const newVirtueXP = Math.max(0, newTotal - 7) * 2;
    const xpCost = newVirtueXP - currentVirtueXP;
    
    if (xpCost > 0 && characterData.xpRemaining < xpCost) {
        alert(`Not enough XP! You need ${xpCost} XP but only have ${characterData.xpRemaining} remaining.`);
        return;
    }
    
    // Update values
    if (virtue === 'conscience') {
        conscience = newValue;
    } else {
        selfControl = newValue;
    }

    // Update display
    updateVirtueDisplay();
    updateHumanityDisplay();
    updateVirtueButtons();
    updateXPDisplay(); // Update XP display to reflect virtue costs
}

function updateVirtueDisplay() {
    const conscienceValue = document.getElementById('conscienceValue');
    const selfControlValue = document.getElementById('selfControlValue');
    const conscienceProgress = document.getElementById('conscienceProgress');
    const selfControlProgress = document.getElementById('selfControlProgress');
    const conscienceMarkers = document.getElementById('conscienceMarkers');
    const selfControlMarkers = document.getElementById('selfControlMarkers');
    const pointsRemaining = document.getElementById('virtuePointsRemaining');
    
    if (!conscienceValue || !selfControlValue || !conscienceProgress || !selfControlProgress || !pointsRemaining) return;
    
    // Update values with visual emphasis
    conscienceValue.textContent = conscience;
    selfControlValue.textContent = selfControl;
    
    // Update virtue points remaining (7 free points)
    const totalVirtuePoints = conscience + selfControl;
    const remainingPoints = Math.max(0, 7 - totalVirtuePoints);
    pointsRemaining.textContent = remainingPoints;
    
    // Add visual feedback for current level
    conscienceValue.style.transform = 'scale(1.1)';
    selfControlValue.style.transform = 'scale(1.1)';
    setTimeout(() => {
        conscienceValue.style.transform = 'scale(1)';
        selfControlValue.style.transform = 'scale(1)';
    }, 200);
    
    // Update progress bars
    updateVirtueProgress(conscienceProgress, conscience);
    updateVirtueProgress(selfControlProgress, selfControl);
    
    // Update level markers
    updateVirtueMarkers(conscienceMarkers, conscience);
    updateVirtueMarkers(selfControlMarkers, selfControl);
}

function updateVirtueProgress(progressBar, value) {
    if (!progressBar) return;
    
    // Calculate percentage (value out of 5)
    const percentage = (value / 5) * 100;
    progressBar.style.width = percentage + '%';
}

function updateVirtueMarkers(container, value) {
    if (!container) return;
    
    // Clear existing markers
    container.innerHTML = '';
    
    // Create 5 level markers
    for (let i = 1; i <= 5; i++) {
        const marker = document.createElement('div');
        marker.className = 'virtue-marker';
        
        if (i <= value) {
            marker.classList.add('active');
        }
        
        container.appendChild(marker);
    }
}

function updateVirtueButtons() {
    // Update Conscience buttons
    const conscienceMinus = document.getElementById('conscienceMinus');
    const consciencePlus = document.getElementById('consciencePlus');
    const selfControlMinus = document.getElementById('selfControlMinus');
    const selfControlPlus = document.getElementById('selfControlPlus');
    
    if (conscienceMinus) conscienceMinus.disabled = conscience <= 1;
    if (selfControlMinus) selfControlMinus.disabled = selfControl <= 1;
    
    // Check if we can afford to increase Conscience
    if (consciencePlus) {
        const currentTotal = conscience + selfControl;
        const newTotal = conscience + 1 + selfControl;
        const currentVirtueXP = Math.max(0, currentTotal - 7) * 2;
        const newVirtueXP = Math.max(0, newTotal - 7) * 2;
        const xpCost = newVirtueXP - currentVirtueXP;
        
        consciencePlus.disabled = conscience >= 5 || (xpCost > 0 && characterData.xpRemaining < xpCost);
    }
    
    // Check if we can afford to increase Self-Control
    if (selfControlPlus) {
        const currentTotal = conscience + selfControl;
        const newTotal = conscience + selfControl + 1;
        const currentVirtueXP = Math.max(0, currentTotal - 7) * 2;
        const newVirtueXP = Math.max(0, newTotal - 7) * 2;
        const xpCost = newVirtueXP - currentVirtueXP;
        
        selfControlPlus.disabled = selfControl >= 5 || (xpCost > 0 && characterData.xpRemaining < xpCost);
    }
}

function updateHumanityDisplay() {
    const humanityValue = document.getElementById('humanityValue');
    const humanityFill = document.getElementById('humanityFill');
    const moralStateDisplay = document.getElementById('moralStateDisplay');
    const humanityCalculation = document.getElementById('humanityCalculation');
    
    if (!humanityValue || !humanityFill || !moralStateDisplay || !humanityCalculation) return;
    
    const humanity = conscience + selfControl;
    const percentage = (humanity / 10) * 100;
    
    // Update display values
    humanityValue.textContent = humanity;
    humanityFill.style.width = percentage + '%';
    
    // Update moral state
    const moralState = moralStates[humanity] || 'Conflicted';
    moralStateDisplay.textContent = moralState;
    
    // Update calculation display
    humanityCalculation.textContent = `${conscience} + ${selfControl} = ${humanity}`;
}

function getMoralState(humanity) {
    return moralStates[humanity] || 'Conflicted';
}

function getHumanityValue() {
    return conscience + selfControl;
}

function getConscienceValue() {
    return conscience;
}

function getSelfControlValue() {
    return selfControl;
}

function initializeMorality() {
    // Initialize the morality display when the page loads
    updateVirtueDisplay();
    updateHumanityDisplay();
    updateVirtueButtons();
}

// Initialize morality system when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeMorality();
    initializeMeritsFlaws();
});

// ===== MERITS & FLAWS SYSTEM =====

// Merits and Flaws data from the JSON database
const meritsFlawsData = {
    "merits": {
        "Physical": [
            {
                "name": "Acute Senses",
                "cost": 1,
                "category": "Physical",
                "description": "One of your senses (sight, hearing, smell, touch, or taste) is unusually sharp. You reduce the difficulty of rolls involving that sense by 2.",
                "effects": {"sense_bonus": 2}
            },
            {
                "name": "Ambidextrous",
                "cost": 1,
                "category": "Physical",
                "description": "You can use either hand with equal facility; off-hand penalties vanish.",
                "effects": {"remove_offhand_penalty": true}
            },
            {
                "name": "Catlike Balance",
                "cost": 1,
                "category": "Physical",
                "description": "You have superior balance and agility. Tasks involving movement on narrow surfaces or recovering from stumbles are easier.",
                "effects": {"balance_bonus": true}
            },
            {
                "name": "Bruiser",
                "cost": 1,
                "category": "Physical",
                "description": "Your intimidating physical presence gives you an edge. Intimidation rolls versus those who haven't shown dominance over you get –1 to difficulty (i.e. one extra die).",
                "effects": {"intimidation_bonus_against_unproven": 1}
            },
            {
                "name": "Daredevil",
                "cost": 3,
                "category": "Physical",
                "description": "You excel at high-risk stunts, acrobatics or recklessness. In daring maneuvers, you reduce difficulty or gain bonus dice.",
                "effects": {"stunt_bonus": true}
            },
            {
                "name": "Efficient Digestion",
                "cost": 3,
                "category": "Physical",
                "description": "You derive more nourishment from blood than is typical. When feeding, you gain extra Blood points (without exceeding your pool).",
                "effects": {"extra_blood_per_feed": 1}
            },
            {
                "name": "Eat Food",
                "cost": 1,
                "category": "Physical",
                "description": "Though you do not truly depend on it, you can eat mortal food without suffering the usual penalties or complications.",
                "effects": {"normal_food_tolerance": true}
            }
        ],
        "Mental": [
            {
                "name": "Common Sense",
                "cost": 1,
                "category": "Mental",
                "description": "You tend to avoid obvious mistakes and pitfalls; you get a bonus or reduced difficulty on practical reasoning or everyday judgments.",
                "effects": {"practical_rolls_bonus": true}
            },
            {
                "name": "Concentration",
                "cost": 1,
                "category": "Mental",
                "description": "You can maintain focus even under stress or distraction. You take fewer penalties when concentrating on a task under duress.",
                "effects": {"resist_interference_bonus": true}
            },
            {
                "name": "Eidetic Memory",
                "cost": 2,
                "category": "Mental",
                "description": "You recall details with sharp precision. You can reconstruct conversations, documents, or scenes with high fidelity.",
                "effects": {"recall_difficulty_reduced": true}
            },
            {
                "name": "Iron Will",
                "cost": 3,
                "category": "Mental",
                "description": "Your mental fortitude is exceptional; you resist fear, influence, or coercion more easily.",
                "effects": {"willpower_resistance_bonus": true}
            },
            {
                "name": "Light Sleeper",
                "cost": 2,
                "category": "Mental",
                "description": "You awaken easily to threats or disturbances. Surprises or stealth attacks are less likely to catch you off guard.",
                "effects": {"reduced_surprise_penalty": true}
            },
            {
                "name": "Time Sense",
                "cost": 1,
                "category": "Mental",
                "description": "You have an internal sense of passing time. You can estimate durations accurately, notice when time is being manipulated, or detect anomalies.",
                "effects": {"time_estimation_bonus": true}
            }
        ],
        "Social": [
            {
                "name": "Natural Leader",
                "cost": 1,
                "category": "Social",
                "description": "You inspire others, take command, or rally loyalty. Leadership or persuasion rolls in group settings are easier.",
                "effects": {"group_influence_bonus": true}
            },
            {
                "name": "Reputation",
                "cost": 2,
                "category": "Social",
                "description": "You have standing or renown in your community or among other Kindred. Add 3 dice to social interactions with those who recognize you.",
                "effects": {"bonus_dice_social_recognizers": 3}
            },
            {
                "name": "Prestigious Sire",
                "cost": 1,
                "category": "Social",
                "description": "Your sire's reputation benefits you; in certain social contexts with your sire's allies, you gain advantage or easier access.",
                "effects": {"sire_association_bonus": true}
            }
        ],
        "Supernatural": [
            {
                "name": "Oracular Ability",
                "cost": 3,
                "category": "Supernatural",
                "description": "You sometimes receive prophetic insight, visions, or omens that guide your actions or warn of danger.",
                "effects": {"vision_trigger": true}
            },
            {
                "name": "Medium",
                "cost": 2,
                "category": "Supernatural",
                "description": "You are especially attuned to spirits. You may see or converse with ghosts more easily than others.",
                "effects": {"spirit_sense_bonus": true}
            },
            {
                "name": "Magic Resistance",
                "cost": 2,
                "category": "Supernatural",
                "description": "You resist magical or supernatural effects more strongly. The difficulty or potency of such effects against you is reduced.",
                "effects": {"resist_supernatural_effects": true}
            },
            {
                "name": "Lucky",
                "cost": 3,
                "category": "Supernatural",
                "description": "Fortune occasionally favors you. In critical situations, you may reroll a die or reduce difficulty.",
                "effects": {"bonus_die_once": true}
            },
            {
                "name": "Danger Sense",
                "cost": 2,
                "category": "Supernatural",
                "description": "You have a sense of impending danger more reliable than mere awareness. When threatened, your Storyteller secretly rolls vs. your Perception + Alertness and may warn you in advance.",
                "effects": {"foreshadow_warning": true}
            }
        ]
    },
    "flaws": {
        "Physical": [
            {
                "name": "Bad Sight",
                "cost": 1,
                "category": "Physical",
                "description": "Your vision is impaired. Rolls relying on sight have +2 difficulty (i.e. fewer dice).",
                "effects": {"vision_penalty": 2}
            },
            {
                "name": "Hard of Hearing",
                "cost": 1,
                "category": "Physical",
                "description": "Your hearing is deficient. Rolls based on hearing have +2 difficulty.",
                "effects": {"hearing_penalty": 2}
            },
            {
                "name": "One Eye",
                "cost": 2,
                "category": "Physical",
                "description": "You have lost or never had one eye. You suffer penalties on depth perception, peripheral checks, and ranged tasks when that side is obscured.",
                "effects": {"depth_perception_penalty": 2}
            },
            {
                "name": "Fourteenth Generation",
                "cost": 2,
                "category": "Physical",
                "description": "Your vampiric lineage is weak. You are more vulnerable, perhaps with fewer innate advantages or lower resistance thresholds.",
                "effects": {"lineage_weakness": true}
            },
            {
                "name": "Monstrous",
                "cost": 3,
                "category": "Physical",
                "description": "Your appearance is grotesque or disturbing, reducing your Appearance to 0 and causing penalties in social interactions.",
                "effects": {"appearance_override": 0, "social_penalty": true}
            },
            {
                "name": "Slow Healing",
                "cost": 3,
                "category": "Physical",
                "description": "Your body is slow to recover. Healing takes doubled time or more, and aggravated damage may not heal.",
                "effects": {"healing_rate_multiplier": 2}
            }
        ],
        "Mental": [
            {
                "name": "Nightmares",
                "cost": 1,
                "category": "Mental",
                "description": "You are plagued by disturbing dreams that disturb rest, impose fatigue or stress penalties upon waking.",
                "effects": {"rest_penalty": true}
            },
            {
                "name": "Phobia",
                "cost": "1-3",
                "category": "Mental",
                "description": "You suffer an irrational fear of a specific object, creature or situation. When confronted, you incur significant penalties or may be forced to flee.",
                "effects": {"phobia_trigger_penalty": true},
                "variableCost": true,
                "minCost": 1,
                "maxCost": 3
            },
            {
                "name": "Short Fuse",
                "cost": 2,
                "category": "Mental",
                "description": "You have little patience or impulse control. Under provocation or stress, you may snap, suffer penalties, or lose control.",
                "effects": {"impulse_penalty": true}
            },
            {
                "name": "Soft-Hearted",
                "cost": 1,
                "category": "Mental",
                "description": "You are emotionally susceptible. Manipulation, appeals to empathy or emotional strife are more effective against you.",
                "effects": {"emotional_susceptibility": true}
            },
            {
                "name": "Weak-Willed",
                "cost": 2,
                "category": "Mental",
                "description": "Your willpower is fragile. You suffer extra difficulty resisting mental pressure, charms or coercion.",
                "effects": {"willpower_resist_penalty": 1}
            }
        ],
        "Social": [
            {
                "name": "Dark Secret",
                "cost": 1,
                "category": "Social",
                "description": "You hold a secret that, if revealed, could ruin or shame you. Discovery inflicts social penalties or dramatic consequences.",
                "effects": {"social_penalty_if_revealed": true}
            },
            {
                "name": "Prey Exclusion",
                "cost": 1,
                "category": "Social",
                "description": "You refuse to feed on a specified class (e.g. children, animals, etc.). Violating this leads to negative consequences (frenzy, Humanity loss).",
                "effects": {"feeding_restriction": true}
            },
            {
                "name": "Sire's Resentment",
                "cost": 1,
                "category": "Social",
                "description": "Your sire harbors ill will toward you. You incur penalties in social dealings involving your sire's allies or in family politics.",
                "effects": {"social_penalty_related_to_sire": true}
            },
            {
                "name": "Clan Enmity",
                "cost": 2,
                "category": "Social",
                "description": "One clan holds a grudge against you. You suffer a –2 dice (or +2 difficulty) on social rolls involving that clan.",
                "effects": {"penalty_against_clan": 2}
            }
        ],
        "Supernatural": [
            {
                "name": "Cursed",
                "cost": "1-5",
                "category": "Supernatural",
                "description": "You bear a supernatural curse. Depending on severity, you endure sporadic hindrances, vulnerabilities, or stigma.",
                "effects": {"curse_effect": true},
                "variableCost": true,
                "minCost": 1,
                "maxCost": 5
            },
            {
                "name": "Haunted",
                "cost": 3,
                "category": "Supernatural",
                "description": "Spirits or supernatural forces cling to you. You suffer interference, disturbances, or heightened sensitivity to ghostly presence.",
                "effects": {"spiritual_interference": true}
            },
            {
                "name": "Hunted",
                "cost": 4,
                "category": "Supernatural",
                "description": "You are actively pursued by someone or something (vampire hunters, enemies, spirits). You are at constant risk of ambush, betrayal, or exposure.",
                "effects": {"persistent_threat": true}
            },
            {
                "name": "Grip of the Damned",
                "cost": 4,
                "category": "Supernatural",
                "description": "A supernatural compulsion or curse has hold over you. At times you lose control, are compelled, or the 'grip' imposes hardship or interference.",
                "effects": {"compulsion_interference": true}
            }
        ]
    }
};

// Conflict rules for merits and flaws
const conflictRules = {
    "Ambidextrous": ["One Eye", "One Arm"],
    "Eat Food": ["Efficient Digestion"],
    "Efficient Digestion": ["Eat Food"],
    "One Eye": ["Ambidextrous"],
    "One Arm": ["Ambidextrous"],
    "Monstrous": ["Blush of Health"]
};

// Global variables for merits and flaws
let selectedMerits = [];
let selectedFlaws = [];
let filteredMeritsFlaws = [];

// Initialize the merits and flaws system
function initializeMeritsFlaws() {
    // Load any existing merits/flaws from character data
    if (characterData.merits_flaws) {
        selectedMerits = characterData.merits_flaws.filter(item => item.type === 'merit');
        selectedFlaws = characterData.merits_flaws.filter(item => item.type === 'flaw');
    }
    
    // Populate the available list
    populateAvailableList();
    updateSelectedList();
    updateSummary();
}

// Populate the available merits and flaws list
function populateAvailableList() {
    const availableList = document.getElementById('availableList');
    if (!availableList) return;
    
    // Clear existing content
    availableList.innerHTML = '';
    
    // Get all merits and flaws
    const allItems = [];
    
    // Add merits
    Object.keys(meritsFlawsData.merits).forEach(category => {
        meritsFlawsData.merits[category].forEach(merit => {
            allItems.push({...merit, type: 'merit'});
        });
    });
    
    // Add flaws
    Object.keys(meritsFlawsData.flaws).forEach(category => {
        meritsFlawsData.flaws[category].forEach(flaw => {
            allItems.push({...flaw, type: 'flaw'});
        });
    });
    
    // Sort by cost, then alphabetically
    allItems.sort((a, b) => {
        const costA = typeof a.cost === 'string' ? parseInt(a.cost.split('-')[0]) : a.cost;
        const costB = typeof b.cost === 'string' ? parseInt(b.cost.split('-')[0]) : b.cost;
        if (costA !== costB) return costA - costB;
        return a.name.localeCompare(b.name);
    });
    
    // Store filtered items
    filteredMeritsFlaws = allItems;
    
    // Render items
    allItems.forEach(item => {
        const itemElement = createMeritFlawItem(item, false);
        availableList.appendChild(itemElement);
    });
}

// Create a merit/flaw item element
function createMeritFlawItem(item, isSelected = false) {
    const div = document.createElement('div');
    div.className = `merit-flaw-item ${isSelected ? 'selected' : ''}`;
    div.dataset.name = item.name;
    div.dataset.type = item.type;
    div.dataset.category = item.category;
    
    const cost = typeof item.cost === 'string' ? item.cost : item.cost.toString();
    const isVariableCost = item.variableCost || false;
    
    // Get category icon
    const categoryIcons = {
        'Physical': '💪',
        'Mental': '🧠',
        'Social': '👥',
        'Supernatural': '✨'
    };
    const categoryIcon = categoryIcons[item.category] || '📋';

    // Get cost color class
    const costValue = parseCost(cost);
    let costColorClass = '';
    if (costValue === 1) {
        costColorClass = 'cost-low'; // Green
    } else if (costValue >= 2 && costValue <= 3) {
        costColorClass = 'cost-medium'; // Yellow
    } else if (costValue >= 4) {
        costColorClass = 'cost-high'; // Red
    }

    div.innerHTML = `
        <div class="merit-flaw-info" onclick="showMeritFlawDescription('${item.name}', '${item.type}')" title="Click for details">
            <div class="merit-flaw-name">
                ${item.name}
                <span class="type-badge ${item.type === 'merit' ? 'merit-badge' : 'flaw-badge'}" title="${item.type === 'merit' ? 'Merit' : 'Flaw'}">
                    ${item.type === 'merit' ? '⭐M' : '⚠️F'}
                </span>
            </div>
            <div class="merit-flaw-category">
                <span class="category-icon">${categoryIcon}</span>
                <span class="category-text">${item.category}</span>
            </div>
            ${isSelected ? `<div class="merit-flaw-description">
                <input type="text" placeholder="Enter description..." value="${item.customDescription || ''}" 
                       onchange="updateMeritFlawDescription('${item.name}', '${item.type}', this.value)" 
                       onclick="event.stopPropagation()">
            </div>` : ''}
        </div>
        <div class="merit-flaw-cost ${costColorClass}">${cost}pt${cost !== '1' ? 's' : ''}</div>
        <div class="merit-flaw-actions">
            ${isSelected ? 
                `<button class="merit-flaw-btn remove" onclick="removeMeritFlaw('${item.name}', '${item.type}')">Remove</button>` :
                `<button class="merit-flaw-btn" onclick="addMeritFlaw('${item.name}', '${item.type}')">Add</button>`
            }
        </div>
        ${isVariableCost && isSelected ? `
            <div class="cost-slider">
                <input type="range" min="${item.minCost}" max="${item.maxCost}" 
                       value="${item.selectedCost || item.minCost}" 
                       onchange="updateVariableCost('${item.name}', '${item.type}', this.value)">
                <span class="slider-value">${item.selectedCost || item.minCost}</span>
            </div>
        ` : ''}
    `;
    
    return div;
}

// Add a merit or flaw
function addMeritFlaw(name, type) {
    // Check for conflicts
    const conflict = checkConflicts(name, type);
    if (conflict) {
        showConflictWarning(conflict);
        return;
    }
    
    // Check if already selected
    const existing = type === 'merit' ? 
        selectedMerits.find(m => m.name === name) : 
        selectedFlaws.find(f => f.name === name);
    
    if (existing) return;
    
    // Find the item data
    const itemData = findMeritFlawData(name, type);
    if (!itemData) return;
    
    // Create the selected item
    const selectedItem = {
        ...itemData,
        customDescription: '',
        selectedCost: itemData.variableCost ? itemData.minCost : itemData.cost
    };
    
    // Add to appropriate array
    if (type === 'merit') {
        selectedMerits.push(selectedItem);
    } else {
        selectedFlaws.push(selectedItem);
    }
    
    // Update displays
    updateSelectedList();
    updateSummary();
    updateAvailableList();
    updateXPDisplay();
}

// Remove a merit or flaw
function removeMeritFlaw(name, type) {
    if (type === 'merit') {
        selectedMerits = selectedMerits.filter(m => m.name !== name);
    } else {
        selectedFlaws = selectedFlaws.filter(f => f.name !== name);
    }
    
    // Update displays
    updateSelectedList();
    updateSummary();
    updateAvailableList();
    updateXPDisplay();
}

// Update variable cost for items like Phobia or Cursed
function updateVariableCost(name, type, newCost) {
    const array = type === 'merit' ? selectedMerits : selectedFlaws;
    const item = array.find(i => i.name === name);
    if (item) {
        item.selectedCost = parseInt(newCost);
        updateSummary();
        updateXPDisplay();
    }
}

// Update custom description for a merit/flaw
function updateMeritFlawDescription(name, type, description) {
    const array = type === 'merit' ? selectedMerits : selectedFlaws;
    const item = array.find(i => i.name === name);
    if (item) {
        item.customDescription = description;
    }
}

// Check for conflicts
function checkConflicts(name, type) {
    const conflicts = conflictRules[name] || [];
    const selectedNames = [...selectedMerits, ...selectedFlaws].map(item => item.name);
    
    for (const conflictName of conflicts) {
        if (selectedNames.includes(conflictName)) {
            return `${name} conflicts with ${conflictName}. You cannot have both.`;
        }
    }
    
    // Check clan-specific requirements
    const clan = document.getElementById('clan').value;
    const clanRequirements = checkClanRequirements(name, type, clan);
    if (clanRequirements) {
        return clanRequirements;
    }
    
    return null;
}

// Check clan-specific requirements
function checkClanRequirements(name, type, clan) {
    // Clan-specific restrictions
    const clanRestrictions = {
        'Tremere': {
            'merits': ['Prestigious Sire'], // Tremere-specific merits
            'flaws': ['Clan Enmity'] // Some flaws may not make sense for certain clans
        },
        'Brujah': {
            'merits': ['Natural Leader'],
            'flaws': ['Weak-Willed'] // Brujah are known for strong will
        },
        'Ventrue': {
            'merits': ['Reputation', 'Prestigious Sire'],
            'flaws': ['Monstrous'] // Ventrue value appearance
        }
    };
    
    // Check if this merit/flaw is restricted for this clan
    const restrictions = clanRestrictions[clan];
    if (restrictions && restrictions[type] && restrictions[type].includes(name)) {
        return `${name} is not appropriate for ${clan} characters.`;
    }
    
    return null;
}

// Show conflict warning
function showConflictWarning(message) {
    const warning = document.getElementById('conflictWarning');
    const text = document.getElementById('conflictText');
    
    if (warning && text) {
        text.textContent = message;
        warning.style.display = 'flex';
        
        // Hide after 5 seconds
        setTimeout(() => {
            warning.style.display = 'none';
        }, 5000);
    }
}

// Update the selected list display
function updateSelectedList() {
    const selectedList = document.getElementById('selectedList');
    if (!selectedList) return;
    
    selectedList.innerHTML = '';
    
    // Add selected merits
    selectedMerits.forEach(merit => {
        const itemElement = createMeritFlawItem(merit, true);
        selectedList.appendChild(itemElement);
    });
    
    // Add selected flaws
    selectedFlaws.forEach(flaw => {
        const itemElement = createMeritFlawItem(flaw, true);
        selectedList.appendChild(itemElement);
    });
}

// Update the available list (disable conflicting items)
function updateAvailableList() {
    const availableList = document.getElementById('availableList');
    if (!availableList) return;
    
    const items = availableList.querySelectorAll('.merit-flaw-item');
    const selectedNames = [...selectedMerits, ...selectedFlaws].map(item => item.name);
    
    items.forEach(item => {
        const name = item.dataset.name;
        const type = item.dataset.type;
        
        // Check if already selected
        if (selectedNames.includes(name)) {
            item.style.display = 'none';
            return;
        }
        
        // Check for conflicts
        const conflict = checkConflicts(name, type);
        if (conflict) {
            item.classList.add('disabled');
            item.title = conflict;
        } else {
            item.classList.remove('disabled');
            item.title = '';
        }
    });
}

// Update the summary display
function updateSummary() {
    const meritsCost = selectedMerits.reduce((sum, merit) => sum + (merit.selectedCost || merit.cost), 0);
    const flawsPoints = selectedFlaws.reduce((sum, flaw) => sum + (flaw.selectedCost || flaw.cost), 0);
    const netCost = meritsCost - flawsPoints;
    
    document.getElementById('meritsCost').textContent = meritsCost;
    document.getElementById('flawsPoints').textContent = flawsPoints;
    document.getElementById('netCost').textContent = netCost;
    
    // Update character data
    characterData.merits_flaws = [...selectedMerits, ...selectedFlaws];
}

// Filter merits and flaws based on search and category
function filterMeritsFlaws() {
    const categoryFilter = document.getElementById('categoryFilter').value;
    const typeFilter = document.getElementById('typeFilter').value;
    const searchFilter = document.getElementById('searchFilter').value.toLowerCase();
    const sortFilter = document.getElementById('sortFilter').value;
    
    const availableList = document.getElementById('availableList');
    if (!availableList) return;
    
    const items = Array.from(availableList.querySelectorAll('.merit-flaw-item'));
    
    // Filter items
    const filteredItems = items.filter(item => {
        const category = item.dataset.category;
        const type = item.dataset.type;
        const name = item.querySelector('.merit-flaw-name').textContent.toLowerCase();
        
        let show = true;
        
        // Category filter
        if (categoryFilter !== 'all' && category !== categoryFilter) {
            show = false;
        }
        
        // Type filter
        if (typeFilter === 'merits' && type !== 'merit') {
            show = false;
        }
        if (typeFilter === 'flaws' && type !== 'flaw') {
            show = false;
        }
        
        // Search filter
        if (searchFilter && !name.includes(searchFilter)) {
            show = false;
        }
        
        return show;
    });
    
    // Sort filtered items
    const sortFunction = getSortFunction(sortFilter);
    filteredItems.sort(sortFunction);
    
    // Hide all items first
    items.forEach(item => item.style.display = 'none');
    
    // Show filtered and sorted items
    filteredItems.forEach(item => item.style.display = 'flex');
}

// Get sort function based on sort filter
function getSortFunction(sortFilter) {
    return (a, b) => {
        const aName = a.querySelector('.merit-flaw-name').textContent;
        const bName = b.querySelector('.merit-flaw-name').textContent;
        const aCategory = a.dataset.category;
        const bCategory = b.dataset.category;
        
        // Get cost values
        const aCostText = a.querySelector('.merit-flaw-cost').textContent;
        const bCostText = b.querySelector('.merit-flaw-cost').textContent;
        const aCost = parseCost(aCostText);
        const bCost = parseCost(bCostText);
        
        switch (sortFilter) {
            case 'cost':
                if (aCost !== bCost) return aCost - bCost;
                return aName.localeCompare(bName);
            case 'cost-desc':
                if (aCost !== bCost) return bCost - aCost;
                return aName.localeCompare(bName);
            case 'name':
                return aName.localeCompare(bName);
            case 'name-desc':
                return bName.localeCompare(aName);
            case 'category':
                if (aCategory !== bCategory) return aCategory.localeCompare(bCategory);
                return aName.localeCompare(bName);
            default:
                return 0;
        }
    };
}

// Parse cost from text (handles "1pt", "2pts", "1-3pts", etc.)
function parseCost(costText) {
    const match = costText.match(/(\d+)/);
    return match ? parseInt(match[1]) : 0;
}

// Reset all filters to default values
function resetMeritsFlawsFilters() {
    // Reset all filter controls to default values
    document.getElementById('categoryFilter').value = 'all';
    document.getElementById('typeFilter').value = 'both';
    document.getElementById('sortFilter').value = 'cost';
    document.getElementById('searchFilter').value = '';
    
    // Reapply filters with default values
    filterMeritsFlaws();
}

// Find merit/flaw data by name and type
function findMeritFlawData(name, type) {
    const data = type === 'merit' ? meritsFlawsData.merits : meritsFlawsData.flaws;
    
    for (const category in data) {
        const item = data[category].find(item => item.name === name);
        if (item) return item;
    }
    
    return null;
}

// Show merit/flaw description modal
function showMeritFlawDescription(name, type) {
    const item = findMeritFlawData(name, type);
    if (!item) return;
    
    // Update modal content
    document.getElementById('meritFlawModalTitle').textContent = item.name;
    document.getElementById('meritFlawType').textContent = type.charAt(0).toUpperCase() + type.slice(1);
    document.getElementById('meritFlawCategory').textContent = item.category;
    document.getElementById('meritFlawCost').textContent = typeof item.cost === 'string' ? item.cost : item.cost + ' point' + (item.cost !== 1 ? 's' : '');
    document.getElementById('meritFlawDescription').textContent = item.description;
    
    // Handle effects
    const effectsDiv = document.getElementById('meritFlawEffects');
    const effectsList = document.getElementById('meritFlawEffectsList');
    
    if (item.effects && Object.keys(item.effects).length > 0) {
        effectsDiv.style.display = 'block';
        effectsList.innerHTML = '';
        
        Object.entries(item.effects).forEach(([key, value]) => {
            const li = document.createElement('li');
            li.textContent = `${key}: ${value}`;
            effectsList.appendChild(li);
        });
    } else {
        effectsDiv.style.display = 'none';
    }
    
    // Show modal
    document.getElementById('meritFlawDescriptionModal').style.display = 'block';
}

// Close merit/flaw description modal
function closeMeritFlawDescription() {
    document.getElementById('meritFlawDescriptionModal').style.display = 'none';
}

// Calculate character's starting cash based on various factors
function calculateCash() {
    console.log('💰 calculateCash called');
    let cash = 100; // Base cash - everyone starts with something
    const factors = ['Base: $100'];
    
    // Get character data
    const clan = document.getElementById('clan')?.value || '';
    const concept = document.getElementById('concept')?.value || '';
    const resourcesLevel = characterData.backgrounds.Resources || 0;
    
    console.log(`📊 Current values - Clan: "${clan}", Concept: "${concept}", Resources: ${resourcesLevel}`);
    
    // Resources Background (primary factor)
    const resourcesCash = {
        0: 0,
        1: Math.floor(Math.random() * 300) + 200, // $200-500
        2: Math.floor(Math.random() * 1000) + 1000, // $1,000-2,000
        3: Math.floor(Math.random() * 5000) + 5000, // $5,000-10,000
        4: Math.floor(Math.random() * 30000) + 20000, // $20,000-50,000
        5: Math.floor(Math.random() * 100000) + 100000 // $100,000-200,000
    };
    const resourcesAmount = resourcesCash[resourcesLevel] || 0;
    cash += resourcesAmount;
    if (resourcesAmount > 0) {
        factors.push(`Resources ${resourcesLevel}: +$${resourcesAmount.toLocaleString()}`);
    }
    
    // Concept/Profession modifier
    const conceptModifiers = {
        'Business Executive': Math.floor(Math.random() * 4000) + 1000, // $1,000-5,000
        'Socialite': Math.floor(Math.random() * 8000) + 2000, // $2,000-10,000
        'Criminal': Math.floor(Math.random() * 1500) + 500, // $500-2,000
        'Street Thug': Math.floor(Math.random() * 150) + 50, // $50-200
        'Academic': Math.floor(Math.random() * 400) + 100, // $100-500
        'Homeless': Math.floor(Math.random() * 50) + 0, // $0-50
        'Criminal Mastermind': Math.floor(Math.random() * 15000) + 5000 // $5,000-20,000
    };
    const conceptAmount = conceptModifiers[concept] || 0;
    cash += conceptAmount;
    if (conceptAmount > 0) {
        factors.push(`Concept "${concept}": +$${conceptAmount.toLocaleString()}`);
    }
    
    // Clan modifier
    const clanModifiers = {
        'Ventrue': Math.floor(Math.random() * 300) + 200, // $200-500
        'Toreador': Math.floor(Math.random() * 200) + 100, // $100-300
        'Brujah': Math.floor(Math.random() * 150) + 50, // $50-200
        'Nosferatu': Math.floor(Math.random() * 300) + 100, // $100-400
        'Tremere': Math.floor(Math.random() * 100) + 0, // $0-100
        'Caitiff': -(Math.floor(Math.random() * 100) + 100), // -$100-200
        'Thin-Blood': -(Math.floor(Math.random() * 100) + 200) // -$200-300
    };
    const clanAmount = clanModifiers[clan] || 0;
    cash += clanAmount;
    if (clanAmount !== 0) {
        const sign = clanAmount > 0 ? '+' : '';
        factors.push(`Clan "${clan}": ${sign}$${clanAmount.toLocaleString()}`);
    }
    
    // Check for Poverty flaw (overrides everything)
    const hasPoverty = characterData.meritsFlaws && characterData.meritsFlaws.some(item => 
        item.name.toLowerCase().includes('poverty') && item.type === 'flaw'
    );
    
    if (hasPoverty) {
        const povertyAmount = Math.floor(Math.random() * 200) + 0; // $0-200
        cash = povertyAmount;
        factors.push(`POVERTY FLAW: Override to $${povertyAmount.toLocaleString()}`);
    }
    
    // Ensure cash doesn't go below 0
    cash = Math.max(0, cash);
    
    // Log the calculation breakdown
    console.log('💵 Cash Calculation:');
    factors.forEach(factor => console.log(`  ${factor}`));
    console.log(`  = Total: $${cash.toLocaleString()}`);
    
    // Update character data
    characterData.cash = cash;
    
    // Update display
    updateCashDisplay();
}

// Update cash display in the character preview
function updateCashDisplay() {
    console.log('updateCashDisplay called, cash:', characterData.cash);
    const cashDisplay = document.getElementById('previewCash');
    console.log('cashDisplay element:', cashDisplay);
    if (cashDisplay) {
        cashDisplay.textContent = `$${characterData.cash || 0}`;
        console.log('Updated cash display to:', cashDisplay.textContent);
    }
}

// Health Levels & Willpower Display (Read-Only)
// Initialize health and willpower data
function initializeHealthWillpower() {
    if (!characterData.health) {
        characterData.health = {
            levels: 7, // 13th Generation default
            current: 7, // Start at full health
            damage: 0 // No damage taken
        };
    }
    
    if (!characterData.willpower) {
        characterData.willpower = {
            current: 5, // Default starting willpower
            permanent: 5 // Default permanent willpower
        };
    }
}

// Update health and willpower display (read-only)
function updateHealthWillpowerDisplay() {
    // Update health levels display
    const healthProgress = document.getElementById('healthProgress');
    const healthValue = document.getElementById('healthValue');
    const healthMarkers = document.getElementById('healthMarkers');
    
    if (healthProgress && healthValue) {
        // Calculate health percentage (current/levels * 100)
        const healthPercentage = (characterData.health.current / characterData.health.levels) * 100;
        healthProgress.style.width = healthPercentage + '%';
        healthValue.textContent = characterData.health.current;
        
        // Create level markers for health (7 levels)
        if (healthMarkers) {
            healthMarkers.innerHTML = '';
            for (let i = 1; i <= characterData.health.levels; i++) {
                const marker = document.createElement('div');
                marker.className = 'virtue-level-marker';
                marker.style.left = ((i - 1) / (characterData.health.levels - 1)) * 100 + '%';
                healthMarkers.appendChild(marker);
            }
        }
    }
    
    // Update willpower display
    const willpowerProgress = document.getElementById('willpowerProgress');
    const willpowerValue = document.getElementById('willpowerValue');
    const willpowerMarkers = document.getElementById('willpowerMarkers');
    
    if (willpowerProgress && willpowerValue) {
        // Calculate willpower percentage (current/permanent * 100)
        const willpowerPercentage = (characterData.willpower.current / characterData.willpower.permanent) * 100;
        willpowerProgress.style.width = willpowerPercentage + '%';
        willpowerValue.textContent = characterData.willpower.current;
        
        // Create level markers for willpower (permanent value)
        if (willpowerMarkers) {
            willpowerMarkers.innerHTML = '';
            for (let i = 1; i <= characterData.willpower.permanent; i++) {
                const marker = document.createElement('div');
                marker.className = 'virtue-level-marker';
                marker.style.left = ((i - 1) / (characterData.willpower.permanent - 1)) * 100 + '%';
                willpowerMarkers.appendChild(marker);
            }
        }
    }
}

// Initialize health and willpower when Basic Info tab is shown
function initializeBasicInfoTab() {
    initializeHealthWillpower();
    updateHealthWillpowerDisplay();
}

// Override the showTab function to initialize health/willpower when Basic Info tab is shown
const originalShowTab = showTab;
showTab = function(tabIndex) {
    originalShowTab(tabIndex);
    
    // Initialize health and willpower when Basic Info tab (tab 0) is shown
    if (tabIndex === 0) {
        setTimeout(() => {
            initializeBasicInfoTab();
        }, 100); // Small delay to ensure DOM is ready
    }
};