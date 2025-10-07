/**
 * DisciplineSystem.js - Handles discipline selection and power management
 * Manages discipline selection, power selection, and popover display
 */

class DisciplineSystem {
    constructor(stateManager, uiManager, eventManager, notificationManager, dataManager) {
        this.stateManager = stateManager;
        this.uiManager = uiManager;
        this.eventManager = eventManager;
        this.notificationManager = notificationManager;
        this.dataManager = dataManager;
        
        this.requirements = {
            min: 1,
            max: 3
        };
        
        this.disciplineData = null;
        this.popoverElement = null;
        this.currentDiscipline = null;
        
        // Clan discipline access mapping
        this.clanDisciplineAccess = {
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
        
        this.init();
    }
    
    /**
     * Initialize the discipline system
     */
    async init() {
        console.log('DisciplineSystem: Initializing...');
        await this.loadDisciplineData();
        console.log('DisciplineSystem: Discipline data loaded:', !!this.disciplineData);
        this.setupEventListeners();
        this.setupStateListeners();
        this.updateAllDisplays();
        
        // Initialize discipline availability based on current clan
        const state = this.stateManager.getState();
        console.log('DisciplineSystem: Current clan:', state.clan);
        if (state.clan) {
            this.updateClanDisciplines(state.clan);
        }
        console.log('DisciplineSystem: Initialization complete');
    }
    
    /**
     * Load discipline data from API
     */
    async loadDisciplineData() {
        try {
            this.disciplineData = await this.dataManager.fetchDisciplineData();
            console.log('DisciplineSystem: Discipline data loaded successfully');
        } catch (error) {
            console.error('DisciplineSystem: Failed to load discipline data:', error);
            this.notificationManager.error('Failed to load discipline data. Using fallback data.');
            this.loadFallbackData();
        }
    }
    
    /**
     * Load fallback discipline data
     */
    loadFallbackData() {
        this.disciplineData = {
            "Animalism": {
                "description": "The Discipline of Animalism allows vampires to communicate with and control animals.",
                "powers": {
                    "1": {
                        "name": "Feral Whispers",
                        "description": "Communicate with animals in their own language.",
                        "cost": "1 Willpower"
                    },
                    "2": {
                        "name": "Beckoning",
                        "description": "Call animals to your location.",
                        "cost": "1 Willpower"
                    },
                    "3": {
                        "name": "Animal Succulence",
                        "description": "Feed from animals without killing them.",
                        "cost": "1 Willpower"
                    }
                }
            },
            "Auspex": {
                "description": "The Discipline of Auspex enhances the vampire's senses and mental abilities.",
                "powers": {
                    "1": {
                        "name": "Heightened Senses",
                        "description": "Enhance your senses to superhuman levels.",
                        "cost": "1 Willpower"
                    },
                    "2": {
                        "name": "Aura Perception",
                        "description": "See the emotional auras of living beings.",
                        "cost": "1 Willpower"
                    },
                    "3": {
                        "name": "Spirit's Touch",
                        "description": "Read the psychic impressions left on objects.",
                        "cost": "1 Willpower"
                    }
                }
            },
            "Celerity": {
                "description": "The Discipline of Celerity allows vampires to move at superhuman speeds.",
                "powers": {
                    "1": {
                        "name": "Cat's Grace",
                        "description": "Move with supernatural speed and agility.",
                        "cost": "1 Willpower"
                    },
                    "2": {
                        "name": "Rapid Reflexes",
                        "description": "React to threats with lightning speed.",
                        "cost": "1 Willpower"
                    },
                    "3": {
                        "name": "Lightning Strike",
                        "description": "Attack multiple times in a single turn.",
                        "cost": "1 Willpower"
                    }
                }
            }
        };
    }
    
    /**
     * Setup event listeners for discipline selection
     */
    setupEventListeners() {
        const { eventManager } = this;
        
        // Discipline selection buttons - use document delegation since we have multiple containers
        eventManager.addDelegatedListener(document, '.discipline-option-btn', 'click', (e) => {
            console.log('DisciplineSystem: Discipline button clicked');
            this.handleDisciplineClick(e);
        });
        
        // Remove discipline buttons
        eventManager.addDelegatedListener(document, '.remove-discipline-btn', 'click', (e) => {
            this.handleRemoveDiscipline(e);
        });
        
        // Power selection buttons
        eventManager.addDelegatedListener(document, '.power-option-btn', 'click', (e) => {
            this.handlePowerClick(e);
        });
        
        // Remove power buttons
        eventManager.addDelegatedListener(document, '.remove-power-btn', 'click', (e) => {
            this.handleRemovePower(e);
        });
        
        // Discipline popover events
        console.log('DisciplineSystem: Setting up mouse event listeners');
        eventManager.addDelegatedListener(document, '.discipline-option-btn', 'mouseenter', (e) => {
            console.log('DisciplineSystem: Mouse enter event triggered');
            this.handleDisciplineMouseEnter(e);
        });
        
        eventManager.addDelegatedListener(document, '.discipline-option-btn', 'mouseleave', (e) => {
            console.log('DisciplineSystem: Mouse leave event triggered');
            this.handleDisciplineMouseLeave(e);
        });
        
        // Modal close events
        eventManager.addDelegatedListener(document, '[data-action="close-discipline-guide"]', 'click', (e) => {
            this.closeDisciplineGuide();
        });
        
        // Popover close button
        eventManager.addDelegatedListener(document, '.popover-close-btn', 'click', (e) => {
            this.hidePopover();
        });
        
        // Close popover when clicking outside
        eventManager.addListener(document, 'click', (e) => {
            if (this.popoverElement && !this.popoverElement.contains(e.target)) {
                this.hidePopover();
            }
        });
    }
    
    /**
     * Setup state change listeners
     */
    setupStateListeners() {
        // Listen for clan changes to update discipline availability
        this.stateManager.subscribe('clan', (newClan, oldClan) => {
            if (newClan !== oldClan) {
                this.updateClanDisciplines(newClan);
            }
        });
    }
    
    /**
     * Handle discipline selection click
     */
    handleDisciplineClick(event) {
        const button = event.target;
        const disciplineName = button.dataset.discipline;
        
        if (!disciplineName) return;
        
        // Check if discipline is available to current clan
        const state = this.stateManager.getState();
        const currentClan = state.clan;
        if (currentClan) {
            const allowedDisciplines = this.clanDisciplineAccess[currentClan] || [];
            if (!allowedDisciplines.includes(disciplineName)) {
                this.notificationManager.warning(`${disciplineName} is not available to ${currentClan}`);
                return;
            }
        }
        
        this.selectDiscipline(disciplineName);
    }
    
    /**
     * Handle remove discipline click
     */
    handleRemoveDiscipline(event) {
        const button = event.target;
        const disciplineName = button.dataset.discipline;
        
        if (!disciplineName) return;
        
        this.removeDiscipline(disciplineName);
    }
    
    /**
     * Handle power selection click
     */
    handlePowerClick(event) {
        const button = event.target;
        const disciplineName = button.dataset.discipline;
        const powerLevel = button.dataset.powerLevel;
        
        console.log('DisciplineSystem: Power click - discipline:', disciplineName, 'level:', powerLevel);
        
        if (!disciplineName || !powerLevel) return;
        
        this.selectPower(disciplineName, powerLevel);
    }
    
    /**
     * Handle remove power click
     */
    handleRemovePower(event) {
        const button = event.target;
        const disciplineName = button.dataset.discipline;
        const powerLevel = button.dataset.powerLevel;
        
        if (!disciplineName || !powerLevel) return;
        
        this.removePower(disciplineName, powerLevel);
    }
    
    /**
     * Handle discipline mouse enter (show popover)
     */
    handleDisciplineMouseEnter(event) {
        const button = event.target;
        const disciplineName = button.dataset.discipline;
        
        console.log('DisciplineSystem: Mouse enter on discipline:', disciplineName);
        
        if (!disciplineName) return;
        
        this.showPopover(disciplineName, button);
    }
    
    /**
     * Handle discipline mouse leave (hide popover)
     */
    handleDisciplineMouseLeave(event) {
        this.hidePopover();
    }
    
    /**
     * Close discipline guide modal
     */
    closeDisciplineGuide() {
        const modal = this.uiManager.getElement('#disciplineGuideModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    /**
     * Select a discipline
     */
    selectDiscipline(disciplineName) {
        const state = this.stateManager.getState();
        const disciplines = [...state.disciplines];
        
        // Check if discipline is already selected
        if (disciplines.includes(disciplineName)) {
            this.notificationManager.warning(`${disciplineName} is already selected.`);
            return;
        }
        
        // Check if maximum disciplines reached
        if (disciplines.length >= this.requirements.max) {
            this.notificationManager.warning(`Maximum ${this.requirements.max} disciplines allowed.`);
            return;
        }
        
        // Add discipline to the list
        disciplines.push(disciplineName);
        
        // Update state
        this.stateManager.setState({
            disciplines: disciplines
        });
        
        // Update displays
        this.updateDisciplineDisplay();
        this.updateDisciplineCount();
        this.updateButtonStates(disciplineName);
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Show feedback
        this.notificationManager.toast(`${disciplineName} added to disciplines`);
    }
    
    /**
     * Remove a discipline
     */
    removeDiscipline(disciplineName) {
        const state = this.stateManager.getState();
        const disciplines = [...state.disciplines];
        const disciplinePowers = { ...state.disciplinePowers };
        
        // Remove discipline from the list
        const index = disciplines.indexOf(disciplineName);
        if (index > -1) {
            disciplines.splice(index, 1);
        }
        
        // Remove all powers for this discipline
        delete disciplinePowers[disciplineName];
        
        // Update state
        this.stateManager.setState({
            disciplines: disciplines,
            disciplinePowers: disciplinePowers
        });
        
        // Update displays
        this.updateDisciplineDisplay();
        this.updateDisciplineCount();
        this.updateButtonStates(disciplineName);
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Show feedback
        this.notificationManager.toast(`${disciplineName} removed from disciplines`);
    }
    
    /**
     * Select a power
     */
    selectPower(disciplineName, powerLevel) {
        const state = this.stateManager.getState();
        const disciplinePowers = { ...state.disciplinePowers };
        
        // Initialize discipline powers if not exists
        if (!disciplinePowers[disciplineName]) {
            disciplinePowers[disciplineName] = [];
        }
        
        // Check if power is already selected
        if (disciplinePowers[disciplineName].includes(powerLevel)) {
            this.notificationManager.warning(`Power level ${powerLevel} is already selected for ${disciplineName}.`);
            return;
        }
        
        // Add power to the discipline
        disciplinePowers[disciplineName].push(powerLevel);
        
        // Update state
        this.stateManager.setState({
            disciplinePowers: disciplinePowers
        });
        
        // Update displays
        this.updateDisciplineDisplay();
        this.updateButtonStates(disciplineName);
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Show feedback
        this.notificationManager.toast(`Power level ${powerLevel} added to ${disciplineName}`);
    }
    
    /**
     * Remove a power
     */
    removePower(disciplineName, powerLevel) {
        const state = this.stateManager.getState();
        const disciplinePowers = { ...state.disciplinePowers };
        
        // Remove power from the discipline
        if (disciplinePowers[disciplineName]) {
            const index = disciplinePowers[disciplineName].indexOf(powerLevel);
            if (index > -1) {
                disciplinePowers[disciplineName].splice(index, 1);
            }
        }
        
        // Update state
        this.stateManager.setState({
            disciplinePowers: disciplinePowers
        });
        
        // Update displays
        this.updateDisciplineDisplay();
        this.updateButtonStates(disciplineName);
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Show feedback
        this.notificationManager.toast(`Power level ${powerLevel} removed from ${disciplineName}`);
    }
    
    /**
     * Update discipline display
     */
    updateDisciplineDisplay() {
        const state = this.stateManager.getState();
        const disciplines = state.disciplines;
        const disciplinePowers = state.disciplinePowers;
        
        // Update all discipline list elements
        const listElements = [
            '#clanDisciplinesList',
            '#bloodSorceryList', 
            '#advancedDisciplinesList'
        ];
        
        // Create display elements for each discipline
        const disciplineHTML = disciplines.map(disciplineName => {
            const powers = disciplinePowers[disciplineName] || [];
            const powersHTML = powers.map(level => {
                const power = this.getPowerInfo(disciplineName, level);
                return `
                    <div class="selected-power">
                        <span class="power-name">${power.name} (Level ${level})</span>
                        <button type="button" class="remove-power-btn" 
                                data-discipline="${disciplineName}" 
                                data-power-level="${level}">×</button>
                    </div>
                `;
            }).join('');
            
            return `
                <div class="selected-discipline">
                    <div class="discipline-header">
                        <span class="discipline-name">${disciplineName}</span>
                        <button type="button" class="remove-discipline-btn" 
                                data-discipline="${disciplineName}">×</button>
                    </div>
                    <div class="discipline-powers">
                        ${powersHTML}
                    </div>
                </div>
            `;
        }).join('');
        
        // Update all discipline list elements
        listElements.forEach(selector => {
            const listElement = this.uiManager.getElement(selector);
            if (listElement) {
                this.uiManager.updateContent(listElement, disciplineHTML);
            }
        });
    }
    
    /**
     * Update discipline count and progress bar
     */
    updateDisciplineCount() {
        const state = this.stateManager.getState();
        const count = state.disciplines.length;
        const requirement = this.requirements;
        
        // Update count displays for all discipline sections
        const countDisplays = [
            '#clanDisciplinesCountDisplay',
            '#bloodSorceryCountDisplay', 
            '#advancedDisciplinesCountDisplay'
        ];
        
        countDisplays.forEach(selector => {
            const countDisplay = this.uiManager.getElement(selector);
            if (countDisplay) {
                this.uiManager.updateContent(countDisplay, count.toString());
            }
        });
        
        // Update progress bars for all discipline sections
        const progressFills = [
            '#clanDisciplinesProgressFill',
            '#bloodSorceryProgressFill',
            '#advancedDisciplinesProgressFill'
        ];
        
        progressFills.forEach(selector => {
            const progressFill = this.uiManager.getElement(selector);
            if (progressFill) {
                const percentage = Math.min((count / requirement.min) * 100, 100);
                progressFill.style.width = percentage + '%';
                
                // Update progress bar class
                this.uiManager.updateClasses(progressFill, {
                    'complete': count >= requirement.min,
                    'incomplete': count < requirement.min
                });
            }
        });
        
        // Update XP display
        this.updateXPDisplay();
    }
    
    /**
     * Update button states
     */
    updateButtonStates(disciplineName) {
        const state = this.stateManager.getState();
        const isSelected = state.disciplines.includes(disciplineName);
        
        // Find the discipline button
        const button = this.uiManager.getElement(`.discipline-option-btn[data-discipline="${disciplineName}"]`);
        if (button) {
            this.uiManager.updateClasses(button, {
                'selected': isSelected
            });
        }
    }
    
    /**
     * Show discipline popover
     */
    showPopover(disciplineName, button) {
        console.log('DisciplineSystem: showPopover called for:', disciplineName);
        console.log('DisciplineSystem: disciplineData available:', !!this.disciplineData);
        console.log('DisciplineSystem: discipline data for', disciplineName, ':', this.disciplineData?.[disciplineName]);
        
        if (!this.disciplineData || !this.disciplineData[disciplineName]) {
            this.notificationManager.error(`Discipline data not found for ${disciplineName}`);
            return;
        }
        
        this.currentDiscipline = disciplineName;
        this.createPopover(disciplineName, button);
    }
    
    /**
     * Create discipline popover
     */
    createPopover(disciplineName, button) {
        const discipline = this.disciplineData[disciplineName];
        const state = this.stateManager.getState();
        const selectedPowers = state.disciplinePowers[disciplineName] || [];
        
        // Create popover HTML
        const popoverHTML = `
            <div class="discipline-popover" id="disciplinePopover">
                <div class="popover-header">
                    <h3>${disciplineName}</h3>
                    <button type="button" class="popover-close-btn">×</button>
                </div>
                <div class="popover-content">
                    <p class="discipline-description">${discipline.description}</p>
                    <div class="powers-section">
                        <h4>Powers</h4>
                        <div class="powers-list">
                            ${Object.keys(discipline.powers).map(level => {
                                const power = discipline.powers[level];
                                const isSelected = selectedPowers.includes(level);
                                return `
                                    <div class="power-option ${isSelected ? 'selected' : ''}">
                                        <button type="button" class="power-option-btn" 
                                                data-discipline="${disciplineName}" 
                                                data-power-level="${level}"
                                                ${isSelected ? 'disabled' : ''}>
                                            ${power.name} (Level ${level})
                                        </button>
                                        <p class="power-description">${power.description}</p>
                                        <p class="power-cost">Cost: ${power.cost}</p>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing popover
        this.hidePopover();
        
        // Create new popover
        const popoverElement = this.uiManager.createElement('div');
        popoverElement.innerHTML = popoverHTML;
        this.popoverElement = popoverElement.firstElementChild;
        
        // Position popover
        this.positionPopover(button);
        
        // Add to DOM
        document.body.appendChild(this.popoverElement);
        
        // Add event listeners
        this.setupPopoverEventListeners();
    }
    
    /**
     * Position popover relative to button
     */
    positionPopover(button) {
        const rect = button.getBoundingClientRect();
        const popover = this.popoverElement;
        
        // Position popover below button
        popover.style.position = 'absolute';
        popover.style.top = (rect.bottom + 10) + 'px';
        popover.style.left = rect.left + 'px';
        popover.style.zIndex = '1000';
    }
    
    /**
     * Setup popover event listeners
     */
    setupPopoverEventListeners() {
        if (!this.popoverElement) return;
        
        // Power selection buttons
        const powerButtons = this.popoverElement.querySelectorAll('.power-option-btn');
        powerButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                this.handlePowerClick(e);
            });
        });
        
        // Close button
        const closeButton = this.popoverElement.querySelector('.popover-close-btn');
        if (closeButton) {
            closeButton.addEventListener('click', (e) => {
                e.stopPropagation();
                this.hidePopover();
            });
        }
    }
    
    /**
     * Hide discipline popover
     */
    hidePopover() {
        if (this.popoverElement) {
            this.popoverElement.remove();
            this.popoverElement = null;
            this.currentDiscipline = null;
        }
    }
    
    /**
     * Get power information
     */
    getPowerInfo(disciplineName, level) {
        if (!this.disciplineData || !this.disciplineData[disciplineName]) {
            return { name: 'Unknown Power', description: 'Power not found', cost: 'Unknown' };
        }
        
        const discipline = this.disciplineData[disciplineName];
        return discipline.powers[level] || { name: 'Unknown Power', description: 'Power not found', cost: 'Unknown' };
    }
    
    /**
     * Update character preview
     */
    updateCharacterPreview() {
        console.log('DisciplineSystem: Character preview updated');
    }
    
    /**
     * Update XP display
     */
    updateXPDisplay() {
        console.log('DisciplineSystem: XP display updated');
    }
    
    /**
     * Update all displays
     */
    updateAllDisplays() {
        this.updateDisciplineDisplay();
        this.updateDisciplineCount();
    }
    
    /**
     * Validate discipline selection
     */
    validateDisciplines() {
        const state = this.stateManager.getState();
        const count = state.disciplines.length;
        const errors = [];
        
        if (count < this.requirements.min) {
            errors.push(`Disciplines: ${count}/${this.requirements.min} required`);
        } else if (count > this.requirements.max) {
            errors.push(`Disciplines: ${count}/${this.requirements.max} maximum exceeded`);
        }
        
        return {
            isValid: errors.length === 0,
            errors
        };
    }
    
    /**
     * Get discipline statistics
     */
    getDisciplineStats() {
        const state = this.stateManager.getState();
        const count = state.disciplines.length;
        
        return {
            count,
            requirement: this.requirements,
            isComplete: count >= this.requirements.min,
            isOverLimit: count > this.requirements.max
        };
    }
    
    /**
     * Reset all disciplines
     */
    resetAll() {
        this.stateManager.setState({
            disciplines: [],
            disciplinePowers: {}
        });
        
        this.updateAllDisplays();
    }
    
    /**
     * Update discipline availability based on selected clan
     */
    updateClanDisciplines(selectedClan) {
        if (!selectedClan) return;
        
        // Get allowed disciplines for the selected clan
        const allowedDisciplines = this.clanDisciplineAccess[selectedClan] || [];
        
        // Get all discipline option buttons
        const allDisciplineButtons = document.querySelectorAll('.discipline-option-btn');
        
        // Update each discipline button
        allDisciplineButtons.forEach(button => {
            const disciplineName = button.dataset.discipline;
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
        
        // Clear any selected disciplines that the clan can't access
        this.clearInvalidDisciplines(selectedClan, allowedDisciplines);
    }
    
    /**
     * Clear disciplines that are not available to the selected clan
     */
    clearInvalidDisciplines(selectedClan, allowedDisciplines) {
        const state = this.stateManager.getState();
        const disciplines = [...state.disciplines];
        const disciplinePowers = { ...state.disciplinePowers };
        
        let removedDisciplines = [];
        
        // Remove disciplines not available to the clan
        const validDisciplines = disciplines.filter(discipline => {
            if (allowedDisciplines.includes(discipline)) {
                return true;
            } else {
                removedDisciplines.push(discipline);
                // Also remove any powers for this discipline
                delete disciplinePowers[discipline];
                return false;
            }
        });
        
        // Update state if disciplines were removed
        if (removedDisciplines.length > 0) {
            this.stateManager.setState({
                disciplines: validDisciplines,
                disciplinePowers: disciplinePowers
            });
            
            // Show notification about removed disciplines
            this.notificationManager.warning(
                `Removed ${removedDisciplines.length} discipline(s) not available to ${selectedClan}: ${removedDisciplines.join(', ')}`
            );
            
            this.updateAllDisplays();
        }
    }
}

// Export for use in other modules
window.DisciplineSystem = DisciplineSystem;
