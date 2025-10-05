// Laws of the Night Character Creation - JavaScript Functions
// Version 0.2.0

// API Configuration
const API_BASE_URL = 'http://localhost:5000/api';

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

// Character saving function
function saveCharacter() {
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
        btn.innerHTML = '💾 Saving...';
    });
    
    // Send data to server via Python API
    fetch(`${API_BASE_URL}/characters`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Character saved successfully!');
            // Reset form or redirect
            console.log('Character ID:', data.character_id);
        } else {
            alert('Error saving character: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving character. Please try again.');
    })
    .finally(() => {
        // Reset button state
        saveButtons.forEach(btn => {
            btn.disabled = false;
            btn.innerHTML = '💾 Save Character';
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
        disciplines: [], // Will be populated when disciplines tab is implemented
        backgrounds: [], // Will be populated when backgrounds tab is implemented
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

// Validate form data
function validateFormData(data) {
    const required = ['character_name', 'player_name', 'nature', 'demeanor', 'concept', 'clan', 'generation'];
    
    for (let field of required) {
        if (!data[field] || data[field].toString().trim() === '') {
            alert(`Please fill in the ${field.replace('_', ' ')} field.`);
            return false;
        }
    }
    
    // Check trait requirements (7 per category)
    const traitCounts = {
        Physical: characterData.traits.Physical.length,
        Social: characterData.traits.Social.length,
        Mental: characterData.traits.Mental.length
    };
    
    for (let category in traitCounts) {
        if (traitCounts[category] < 7) {
            alert(`Please select at least 7 ${category} traits.`);
            return false;
        }
    }
    
    // Check ability requirements (3 per category, Optional not required)
    const abilityCounts = {
        Physical: characterData.abilities.Physical.length,
        Social: characterData.abilities.Social.length,
        Mental: characterData.abilities.Mental.length
    };
    
    for (let category in abilityCounts) {
        if (abilityCounts[category] < 3) {
            alert(`Please select at least 3 ${category} abilities.`);
            return false;
        }
    }
    
    return true;
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
    physicalTraitCategories: {
        agility: ['Agile', 'Lithe', 'Nimble', 'Quick', 'Spry', 'Graceful', 'Slender'],
        strength: ['Strong', 'Hardy', 'Tough', 'Resilient', 'Sturdy', 'Vigorous', 'Burly'],
        dexterity: ['Coordinated', 'Precise', 'Steady-handed', 'Sleek', 'Flexible', 'Balanced'],
        reflexes: ['Alert', 'Sharp-eyed', 'Quick-reflexed', 'Perceptive', 'Reactive', 'Observant'],
        appearance: ['Athletic', 'Well-built'],
        legacy: ['Fast', 'Muscular']
    },
    xpSpent: 0,
    xpRemaining: 30
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

// XP tracking and validation functions
function updateXPDisplay() {
    let totalXP = 0;
    let traitsXP = 0;
    let abilitiesXP = 0;
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
    
    // Calculate XP gained from negative traits (+4 XP each)
    ['Physical', 'Social', 'Mental'].forEach(category => {
        const negativeCount = characterData.negativeTraits[category].length;
        negativeTraitsXP += negativeCount * 4;
    });
    
    totalXP = traitsXP + abilitiesXP - negativeTraitsXP; // Negative traits reduce XP cost
    
    // Update character data
    characterData.xpSpent = totalXP;
    characterData.xpRemaining = 30 - totalXP;
    
    // Update displays
    document.getElementById('xpSpent').textContent = totalXP;
    document.getElementById('xpRemaining').textContent = characterData.xpRemaining;
    document.getElementById('xpDisplay').textContent = characterData.xpRemaining;
    document.getElementById('xpTraits').textContent = traitsXP;
    document.getElementById('xpAbilities').textContent = abilitiesXP;
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

// Discipline Guide Modal Functions
function showDisciplineGuide() {
    document.getElementById('disciplineGuideModal').style.display = 'block';
}

function closeDisciplineGuide() {
    document.getElementById('disciplineGuideModal').style.display = 'none';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('disciplineGuideModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

// Initialize the character creation form
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any required functionality when page loads
    console.log('LOTN Character Creation form loaded');
});
