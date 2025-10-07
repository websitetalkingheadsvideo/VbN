/**
 * AbilitySystem.js - Handles ability selection and management
 * Manages Physical, Social, Mental, and Optional ability selection with validation
 */

class AbilitySystem {
    constructor(stateManager, uiManager, eventManager, notificationManager) {
        this.stateManager = stateManager;
        this.uiManager = uiManager;
        this.eventManager = eventManager;
        this.notificationManager = notificationManager;
        
        this.requirements = {
            Physical: { min: 3, max: 5 },
            Social: { min: 3, max: 5 },
            Mental: { min: 3, max: 5 },
            Optional: { min: 0, max: 5 }
        };
        
        this.init();
    }
    
    /**
     * Initialize the ability system
     */
    init() {
        this.setupEventListeners();
        this.updateAllDisplays();
    }
    
    /**
     * Setup event listeners for ability selection
     */
    setupEventListeners() {
        const { eventManager } = this;
        
        // Ability selection buttons - use document delegation since we have multiple containers
        eventManager.addDelegatedListener(document, '.ability-option-btn', 'click', (e) => {
            this.handleAbilityClick(e);
        });
        
        // Remove ability buttons
        eventManager.addDelegatedListener(document, '.remove-ability-btn', 'click', (e) => {
            this.handleRemoveAbility(e);
        });
    }
    
    /**
     * Handle ability selection click
     */
    handleAbilityClick(event) {
        const button = event.target;
        const category = button.dataset.category;
        const abilityName = button.dataset.ability;
        
        if (!category || !abilityName) return;
        
        this.selectAbility(category, abilityName);
    }
    
    /**
     * Handle remove ability click
     */
    handleRemoveAbility(event) {
        const button = event.target;
        const category = button.dataset.category;
        const abilityName = button.dataset.ability;
        
        if (!category || !abilityName) return;
        
        this.removeAbility(category, abilityName);
    }
    
    /**
     * Select an ability
     */
    selectAbility(category, abilityName) {
        const state = this.stateManager.getState();
        const abilities = [...state.abilities[category]];
        
        // Check if this ability is already at maximum (5 dots)
        const currentCount = abilities.filter(a => a === abilityName).length;
        if (currentCount >= 5) {
            this.notificationManager.warning(`${abilityName} is already at maximum level (5 dots).`);
            return;
        }
        
        // Add ability to the list
        abilities.push(abilityName);
        
        // Update state
        this.stateManager.setState({
            abilities: { [category]: abilities }
        });
        
        // Update displays
        this.updateAbilityDisplay(category);
        this.updateAbilityCount(category);
        this.updateButtonStates(category, abilityName);
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Show feedback
        this.notificationManager.toast(`${abilityName} added to ${category} abilities`);
    }
    
    /**
     * Remove an ability
     */
    removeAbility(category, abilityName) {
        const state = this.stateManager.getState();
        const abilities = [...state.abilities[category]];
        
        // Remove the last instance of the ability
        const index = abilities.lastIndexOf(abilityName);
        if (index > -1) {
            abilities.splice(index, 1);
        }
        
        // Update state
        this.stateManager.setState({
            abilities: { [category]: abilities }
        });
        
        // Update displays
        this.updateAbilityDisplay(category);
        this.updateAbilityCount(category);
        this.updateButtonStates(category, abilityName);
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Show feedback
        this.notificationManager.toast(`${abilityName} removed from ${category} abilities`);
    }
    
    /**
     * Update ability display for a category
     */
    updateAbilityDisplay(category) {
        const state = this.stateManager.getState();
        const abilities = state.abilities[category];
        const listElement = this.uiManager.getElement(`#${category.toLowerCase()}AbilitiesList`);
        
        if (!listElement) return;
        
        // Group abilities by name and count them
        const abilityCounts = {};
        abilities.forEach(ability => {
            abilityCounts[ability] = (abilityCounts[ability] || 0) + 1;
        });
        
        // Create display elements for each unique ability
        const abilityHTML = Object.keys(abilityCounts).map(abilityName => {
            const count = abilityCounts[abilityName];
            const displayName = count > 1 ? `${abilityName} (${count})` : abilityName;
            return `
                <div class="selected-ability">
                    <span class="ability-name">${displayName}</span>
                    <button type="button" class="remove-ability-btn" 
                            data-category="${category}" 
                            data-ability="${abilityName}">×</button>
                </div>
            `;
        }).join('');
        
        this.uiManager.updateContent(listElement, abilityHTML);
    }
    
    /**
     * Update ability count and progress bar
     */
    updateAbilityCount(category) {
        const state = this.stateManager.getState();
        const count = state.abilities[category].length;
        const requirement = this.requirements[category];
        
        // Update count displays
        const countDisplay = this.uiManager.getElement(`#${category.toLowerCase()}AbilitiesCountDisplay`);
        if (countDisplay) {
            this.uiManager.updateContent(countDisplay, count.toString());
        }
        
        // Update progress bar
        const progressFill = this.uiManager.getElement(`#${category.toLowerCase()}AbilitiesProgressFill`);
        if (progressFill) {
            const percentage = Math.min((count / requirement.min) * 100, 100);
            progressFill.style.width = percentage + '%';
            
            // Update progress bar class
            this.uiManager.updateClasses(progressFill, {
                'complete': count >= requirement.min,
                'incomplete': count < requirement.min
            });
        }
        
        // Update XP display
        this.updateXPDisplay();
    }
    
    /**
     * Update button states
     */
    updateButtonStates(category, abilityName) {
        const state = this.stateManager.getState();
        const abilities = state.abilities[category];
        const count = abilities.filter(a => a === abilityName).length;
        
        // Find the button
        const button = this.uiManager.getElement(`.ability-option-btn[data-category="${category}"][data-ability="${abilityName}"]`);
        if (!button) return;
        
        // Update button appearance
        this.uiManager.updateClasses(button, {
            'selected': count > 0
        });
        
        // Update button text to show count
        if (count > 1) {
            this.uiManager.updateContent(button, `${abilityName} (${count})`);
        } else if (count === 1) {
            this.uiManager.updateContent(button, abilityName);
        }
        
        // Disable button if at maximum
        const isAtMax = count >= 5;
        button.disabled = isAtMax;
        this.uiManager.updateAttributes(button, {
            'style': isAtMax ? 'opacity: 0.6;' : 'opacity: 1;',
            'title': isAtMax ? `${abilityName} is at maximum level (5 dots)` : ''
        });
    }
    
    /**
     * Update character preview
     */
    updateCharacterPreview() {
        // This would be handled by the main application
        console.log('AbilitySystem: Character preview updated');
    }
    
    /**
     * Update XP display
     */
    updateXPDisplay() {
        // This would be handled by the main application
        console.log('AbilitySystem: XP display updated');
    }
    
    /**
     * Update all displays
     */
    updateAllDisplays() {
        ['Physical', 'Social', 'Mental', 'Optional'].forEach(category => {
            this.updateAbilityDisplay(category);
            this.updateAbilityCount(category);
        });
    }
    
    /**
     * Validate ability selection
     */
    validateAbilities() {
        const state = this.stateManager.getState();
        const errors = [];
        
        Object.keys(this.requirements).forEach(category => {
            const count = state.abilities[category].length;
            const requirement = this.requirements[category];
            
            if (count < requirement.min) {
                errors.push(`${category} abilities: ${count}/${requirement.min} required`);
            } else if (count > requirement.max) {
                errors.push(`${category} abilities: ${count}/${requirement.max} maximum exceeded`);
            }
        });
        
        return {
            isValid: errors.length === 0,
            errors
        };
    }
    
    /**
     * Get ability statistics
     */
    getAbilityStats() {
        const state = this.stateManager.getState();
        const stats = {};
        
        Object.keys(this.requirements).forEach(category => {
            const count = state.abilities[category].length;
            const requirement = this.requirements[category];
            
            stats[category] = {
                count,
                requirement,
                isComplete: count >= requirement.min,
                isOverLimit: count > requirement.max
            };
        });
        
        return stats;
    }
    
    /**
     * Get total ability count
     */
    getTotalAbilityCount() {
        const state = this.stateManager.getState();
        return Object.values(state.abilities).reduce((total, abilities) => total + abilities.length, 0);
    }
    
    /**
     * Get ability count for category
     */
    getAbilityCount(category) {
        const state = this.stateManager.getState();
        return state.abilities[category].length;
    }
    
    /**
     * Check if ability is at maximum
     */
    isAbilityAtMax(category, abilityName) {
        const state = this.stateManager.getState();
        const abilities = state.abilities[category];
        const count = abilities.filter(a => a === abilityName).length;
        return count >= 5;
    }
    
    /**
     * Reset abilities for a category
     */
    resetCategory(category) {
        this.stateManager.setState({
            abilities: { [category]: [] }
        });
        
        this.updateAbilityDisplay(category);
        this.updateAbilityCount(category);
    }
    
    /**
     * Reset all abilities
     */
    resetAll() {
        this.stateManager.setState({
            abilities: { Physical: [], Social: [], Mental: [], Optional: [] }
        });
        
        this.updateAllDisplays();
    }
    
    /**
     * Get ability list for category
     */
    getAbilities(category) {
        const state = this.stateManager.getState();
        return [...state.abilities[category]];
    }
    
    /**
     * Set abilities for category (useful for importing)
     */
    setAbilities(category, abilities) {
        this.stateManager.setState({
            abilities: { [category]: [...abilities] }
        });
        
        this.updateAbilityDisplay(category);
        this.updateAbilityCount(category);
    }
}

// Export for use in other modules
window.AbilitySystem = AbilitySystem;
