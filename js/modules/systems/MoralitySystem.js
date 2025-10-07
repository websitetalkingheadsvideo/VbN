/**
 * MoralitySystem.js - Handles morality and virtue management
 * Manages virtue selection, humanity calculation, and moral state tracking
 */

class MoralitySystem {
    constructor(stateManager, uiManager, eventManager, notificationManager) {
        this.stateManager = stateManager;
        this.uiManager = uiManager;
        this.eventManager = eventManager;
        this.notificationManager = notificationManager;
        
        this.virtueRequirements = {
            Conscience: { min: 1, max: 5 },
            SelfControl: { min: 1, max: 5 }
        };
        
        this.moralStates = {
            10: { name: 'Humanity 10', description: 'Perfectly human' },
            9: { name: 'Humanity 9', description: 'Nearly human' },
            8: { name: 'Humanity 8', description: 'Mostly human' },
            7: { name: 'Humanity 7', description: 'Somewhat human' },
            6: { name: 'Humanity 6', description: 'Barely human' },
            5: { name: 'Humanity 5', description: 'Losing humanity' },
            4: { name: 'Humanity 4', description: 'Barely human' },
            3: { name: 'Humanity 3', description: 'Losing humanity' },
            2: { name: 'Humanity 2', description: 'Barely human' },
            1: { name: 'Humanity 1', description: 'Losing humanity' },
            0: { name: 'Humanity 0', description: 'Lost humanity' }
        };
        
        this.init();
    }
    
    /**
     * Initialize the morality system
     */
    init() {
        this.setupEventListeners();
        this.updateAllDisplays();
    }
    
    /**
     * Setup event listeners for virtue selection
     */
    setupEventListeners() {
        const { eventManager } = this;
        
        // Virtue adjustment buttons
        const virtueContainer = this.uiManager.getElement('.virtue-selection');
        if (virtueContainer) {
            eventManager.addDelegatedListener(virtueContainer, '.virtue-btn', 'click', (e) => {
                this.handleVirtueClick(e);
            });
        }
        
        // Humanity display
        const humanityContainer = this.uiManager.getElement('.humanity-display');
        if (humanityContainer) {
            eventManager.addDelegatedListener(humanityContainer, '.humanity-btn', 'click', (e) => {
                this.handleHumanityClick(e);
            });
        }
    }
    
    /**
     * Handle virtue button click
     */
    handleVirtueClick(event) {
        const button = event.target;
        const virtue = button.dataset.virtue;
        const action = button.dataset.action;
        
        if (!virtue || !action) return;
        
        this.adjustVirtue(virtue, action);
    }
    
    /**
     * Handle humanity button click
     */
    handleHumanityClick(event) {
        const button = event.target;
        const action = button.dataset.action;
        
        if (!action) return;
        
        this.adjustHumanity(action);
    }
    
    /**
     * Adjust virtue level
     */
    adjustVirtue(virtue, action) {
        const state = this.stateManager.getState();
        const virtues = { ...state.virtues };
        const currentLevel = virtues[virtue] || 1;
        
        let newLevel = currentLevel;
        
        if (action === 'increase' && currentLevel < 5) {
            newLevel = currentLevel + 1;
        } else if (action === 'decrease' && currentLevel > 1) {
            newLevel = currentLevel - 1;
        }
        
        if (newLevel !== currentLevel) {
            virtues[virtue] = newLevel;
            
            // Update state
            this.stateManager.setState({
                virtues: virtues
            });
            
            // Update displays
            this.updateVirtueDisplay(virtue);
            this.updateVirtueProgress(virtue);
            this.updateVirtueMarkers(virtue);
            this.updateVirtueButtons(virtue);
            this.updateHumanityDisplay();
            
            // Update character preview
            this.updateCharacterPreview();
            
            // Show feedback
            this.notificationManager.toast(`${virtue} ${action === 'increase' ? 'increased' : 'decreased'} to ${newLevel}`);
        }
    }
    
    /**
     * Adjust humanity level
     */
    adjustHumanity(action) {
        const state = this.stateManager.getState();
        const humanity = state.humanity || 7;
        
        let newHumanity = humanity;
        
        if (action === 'increase' && humanity < 10) {
            newHumanity = humanity + 1;
        } else if (action === 'decrease' && humanity > 0) {
            newHumanity = humanity - 1;
        }
        
        if (newHumanity !== humanity) {
            // Update state
            this.stateManager.setState({
                humanity: newHumanity
            });
            
            // Update displays
            this.updateHumanityDisplay();
            
            // Update character preview
            this.updateCharacterPreview();
            
            // Show feedback
            this.notificationManager.toast(`Humanity ${action === 'increase' ? 'increased' : 'decreased'} to ${newHumanity}`);
        }
    }
    
    /**
     * Update virtue display
     */
    updateVirtueDisplay(virtue) {
        const state = this.stateManager.getState();
        const level = state.virtues[virtue] || 1;
        
        // Update level display
        const levelElement = this.uiManager.getElement(`#${virtue}Level`);
        if (levelElement) {
            this.uiManager.updateContent(levelElement, level.toString());
        }
        
        // Update sidebar display
        const sidebarElement = this.uiManager.getElement(`#${virtue}Sidebar`);
        if (sidebarElement) {
            this.uiManager.updateContent(sidebarElement, level.toString());
        }
    }
    
    /**
     * Update virtue progress bar
     */
    updateVirtueProgress(virtue) {
        const state = this.stateManager.getState();
        const level = state.virtues[virtue] || 1;
        const requirement = this.virtueRequirements[virtue];
        
        // Update progress bar
        const progressFill = this.uiManager.getElement(`#${virtue}ProgressFill`);
        if (progressFill) {
            const percentage = (level / requirement.max) * 100;
            progressFill.style.width = percentage + '%';
            
            // Update progress bar class
            this.uiManager.updateClasses(progressFill, {
                'complete': level >= requirement.min,
                'incomplete': level < requirement.min
            });
        }
    }
    
    /**
     * Update virtue markers
     */
    updateVirtueMarkers(virtue) {
        const state = this.stateManager.getState();
        const level = state.virtues[virtue] || 1;
        
        // Update virtue markers
        for (let i = 1; i <= 5; i++) {
            const marker = this.uiManager.getElement(`#${virtue}Marker${i}`);
            if (marker) {
                this.uiManager.updateClasses(marker, {
                    'active': i <= level,
                    'inactive': i > level
                });
            }
        }
    }
    
    /**
     * Update virtue buttons
     */
    updateVirtueButtons(virtue) {
        const state = this.stateManager.getState();
        const level = state.virtues[virtue] || 1;
        
        // Update increase button
        const increaseBtn = this.uiManager.getElement(`#${virtue}Increase`);
        if (increaseBtn) {
            increaseBtn.disabled = level >= 5;
            this.uiManager.updateClasses(increaseBtn, {
                'disabled': level >= 5
            });
        }
        
        // Update decrease button
        const decreaseBtn = this.uiManager.getElement(`#${virtue}Decrease`);
        if (decreaseBtn) {
            decreaseBtn.disabled = level <= 1;
            this.uiManager.updateClasses(decreaseBtn, {
                'disabled': level <= 1
            });
        }
    }
    
    /**
     * Update humanity display
     */
    updateHumanityDisplay() {
        const state = this.stateManager.getState();
        const humanity = state.humanity || 7;
        const conscience = state.virtues.Conscience || 1;
        const selfControl = state.virtues.SelfControl || 1;
        
        // Calculate moral state
        const moralState = this.getMoralState(humanity, conscience, selfControl);
        
        // Update humanity level display
        const humanityElement = this.uiManager.getElement('#humanityLevel');
        if (humanityElement) {
            this.uiManager.updateContent(humanityElement, humanity.toString());
        }
        
        // Update moral state display
        const moralStateElement = this.uiManager.getElement('#moralState');
        if (moralStateElement) {
            this.uiManager.updateContent(moralStateElement, moralState.name);
        }
        
        // Update moral state description
        const moralStateDescElement = this.uiManager.getElement('#moralStateDescription');
        if (moralStateDescElement) {
            this.uiManager.updateContent(moralStateDescElement, moralState.description);
        }
        
        // Update humanity markers
        for (let i = 0; i <= 10; i++) {
            const marker = this.uiManager.getElement(`#humanityMarker${i}`);
            if (marker) {
                this.uiManager.updateClasses(marker, {
                    'active': i === humanity,
                    'inactive': i !== humanity
                });
            }
        }
        
        // Update humanity buttons
        const increaseBtn = this.uiManager.getElement('#humanityIncrease');
        if (increaseBtn) {
            increaseBtn.disabled = humanity >= 10;
            this.uiManager.updateClasses(increaseBtn, {
                'disabled': humanity >= 10
            });
        }
        
        const decreaseBtn = this.uiManager.getElement('#humanityDecrease');
        if (decreaseBtn) {
            decreaseBtn.disabled = humanity <= 0;
            this.uiManager.updateClasses(decreaseBtn, {
                'disabled': humanity <= 0
            });
        }
    }
    
    /**
     * Get moral state based on humanity and virtues
     */
    getMoralState(humanity, conscience, selfControl) {
        // Calculate moral state based on humanity level
        const moralState = this.moralStates[humanity] || this.moralStates[7];
        
        // Adjust based on virtue levels
        if (conscience >= 4 && selfControl >= 4) {
            return {
                ...moralState,
                name: moralState.name + ' (High Virtues)',
                description: moralState.description + ' You have strong moral virtues.'
            };
        } else if (conscience <= 2 && selfControl <= 2) {
            return {
                ...moralState,
                name: moralState.name + ' (Low Virtues)',
                description: moralState.description + ' You have weak moral virtues.'
            };
        }
        
        return moralState;
    }
    
    /**
     * Update character preview
     */
    updateCharacterPreview() {
        console.log('MoralitySystem: Character preview updated');
    }
    
    /**
     * Update all displays
     */
    updateAllDisplays() {
        ['Conscience', 'SelfControl'].forEach(virtue => {
            this.updateVirtueDisplay(virtue);
            this.updateVirtueProgress(virtue);
            this.updateVirtueMarkers(virtue);
            this.updateVirtueButtons(virtue);
        });
        
        this.updateHumanityDisplay();
    }
    
    /**
     * Validate virtue selection
     */
    validateVirtues() {
        const state = this.stateManager.getState();
        const virtues = state.virtues;
        const errors = [];
        
        Object.keys(this.virtueRequirements).forEach(virtue => {
            const level = virtues[virtue] || 1;
            const requirement = this.virtueRequirements[virtue];
            
            if (level < requirement.min) {
                errors.push(`${virtue}: ${level}/${requirement.min} minimum required`);
            } else if (level > requirement.max) {
                errors.push(`${virtue}: ${level}/${requirement.max} maximum exceeded`);
            }
        });
        
        return {
            isValid: errors.length === 0,
            errors
        };
    }
    
    /**
     * Get virtue statistics
     */
    getVirtueStats() {
        const state = this.stateManager.getState();
        const virtues = state.virtues;
        const humanity = state.humanity || 7;
        
        const stats = {};
        
        Object.keys(this.virtueRequirements).forEach(virtue => {
            const level = virtues[virtue] || 1;
            const requirement = this.virtueRequirements[virtue];
            
            stats[virtue] = {
                level,
                requirement,
                isComplete: level >= requirement.min,
                isOverLimit: level > requirement.max
            };
        });
        
        stats.humanity = {
            level: humanity,
            moralState: this.getMoralState(humanity, virtues.Conscience || 1, virtues.SelfControl || 1)
        };
        
        return stats;
    }
    
    /**
     * Get humanity value
     */
    getHumanityValue() {
        const state = this.stateManager.getState();
        return state.humanity || 7;
    }
    
    /**
     * Get conscience value
     */
    getConscienceValue() {
        const state = this.stateManager.getState();
        return state.virtues.Conscience || 1;
    }
    
    /**
     * Get self control value
     */
    getSelfControlValue() {
        const state = this.stateManager.getState();
        return state.virtues.SelfControl || 1;
    }
    
    /**
     * Set virtue level
     */
    setVirtueLevel(virtue, level) {
        const state = this.stateManager.getState();
        const virtues = { ...state.virtues };
        
        virtues[virtue] = Math.max(1, Math.min(5, level));
        
        this.stateManager.setState({
            virtues: virtues
        });
        
        this.updateVirtueDisplay(virtue);
        this.updateVirtueProgress(virtue);
        this.updateVirtueMarkers(virtue);
        this.updateVirtueButtons(virtue);
        this.updateHumanityDisplay();
    }
    
    /**
     * Set humanity level
     */
    setHumanityLevel(level) {
        const newHumanity = Math.max(0, Math.min(10, level));
        
        this.stateManager.setState({
            humanity: newHumanity
        });
        
        this.updateHumanityDisplay();
    }
    
    /**
     * Reset all virtues and humanity
     */
    resetAll() {
        this.stateManager.setState({
            virtues: { Conscience: 1, SelfControl: 1 },
            humanity: 7
        });
        
        this.updateAllDisplays();
    }
}

// Export for use in other modules
window.MoralitySystem = MoralitySystem;
