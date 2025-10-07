/**
 * TraitSystem.js - Handles trait selection and management
 * Manages Physical, Social, and Mental trait selection with validation
 */

class TraitSystem {
    constructor(stateManager, uiManager, eventManager, notificationManager) {
        this.stateManager = stateManager;
        this.uiManager = uiManager;
        this.eventManager = eventManager;
        this.notificationManager = notificationManager;
        
        // Dynamic point distribution - user chooses which category gets 7, 5, or 3
        this.pointDistribution = {
            Physical: 7,
            Social: 5,
            Mental: 3
        };
        
        this.requirements = {
            Physical: { min: 0, max: 10 },
            Social: { min: 0, max: 10 },
            Mental: { min: 0, max: 10 }
        };
        
        this.physicalTraitCategories = {
            agility: ['Agile', 'Lithe', 'Nimble', 'Quick', 'Spry', 'Graceful', 'Slender'],
            strength: ['Strong', 'Hardy', 'Tough', 'Resilient', 'Sturdy', 'Vigorous', 'Burly'],
            dexterity: ['Coordinated', 'Precise', 'Steady-handed', 'Sleek', 'Flexible', 'Balanced'],
            reflexes: ['Alert', 'Sharp-eyed', 'Quick-reflexed', 'Perceptive', 'Reactive', 'Observant'],
            appearance: ['Athletic', 'Well-built'],
            legacy: ['Fast', 'Muscular']
        };
        
        this.init();
    }
    
    /**
     * Initialize the trait system
     */
    init() {
        this.setupEventListeners();
        this.updatePointDistribution();
        this.updateAllDisplays();
    }
    
    /**
     * Setup event listeners for trait selection
     */
    setupEventListeners() {
        const { eventManager } = this;
        
        // Trait selection buttons
        const traitContainer = this.uiManager.getElement('.trait-selection');
        if (traitContainer) {
            eventManager.addDelegatedListener(traitContainer, '.trait-option-btn', 'click', (e) => {
                this.handleTraitClick(e);
            });
        }
        
        // Negative trait selection buttons
        const negativeTraitContainer = this.uiManager.getElement('.negative-trait-selection');
        if (negativeTraitContainer) {
            eventManager.addDelegatedListener(negativeTraitContainer, '.trait-option-btn.negative', 'click', (e) => {
                this.handleNegativeTraitClick(e);
            });
        }
        
        // Remove trait buttons
        eventManager.addDelegatedListener(document, '.remove-trait-btn', 'click', (e) => {
            this.handleRemoveTrait(e);
        });
        
        eventManager.addDelegatedListener(document, '.remove-negative-trait-btn', 'click', (e) => {
            this.handleRemoveNegativeTrait(e);
        });
        
        // Point distribution interface
        eventManager.addDelegatedListener(document, '.dist-btn', 'click', (e) => {
            this.handleQuickDistribution(e);
        });
        
        eventManager.addDelegatedListener(document, '.point-select', 'change', (e) => {
            this.handleManualDistribution(e);
        });
    }
    
    /**
     * Handle trait selection click
     */
    handleTraitClick(event) {
        const button = event.target;
        const category = button.dataset.category;
        const traitName = button.dataset.trait;
        
        if (!category || !traitName) return;
        
        this.selectTrait(category, traitName);
    }
    
    /**
     * Handle negative trait selection click
     */
    handleNegativeTraitClick(event) {
        const button = event.target;
        const category = button.dataset.category;
        const traitName = button.dataset.trait;
        
        if (!category || !traitName) return;
        
        this.selectNegativeTrait(category, traitName);
    }
    
    /**
     * Handle remove trait click
     */
    handleRemoveTrait(event) {
        const button = event.target;
        const category = button.dataset.category;
        const traitName = button.dataset.trait;
        
        if (!category || !traitName) return;
        
        this.removeTrait(category, traitName);
    }
    
    /**
     * Handle remove negative trait click
     */
    handleRemoveNegativeTrait(event) {
        const button = event.target;
        const category = button.dataset.category;
        const traitName = button.dataset.trait;
        
        if (!category || !traitName) return;
        
        this.removeNegativeTrait(category, traitName);
    }
    
    /**
     * Select a trait
     */
    selectTrait(category, traitName) {
        const state = this.stateManager.getState();
        const traits = [...state.traits[category]];
        
        // Check if we're at the maximum (capped at free points during character creation)
        const freePoints = this.getFreePoints(category);
        if (traits.length >= freePoints) {
            this.notificationManager.showError(`Maximum ${freePoints} traits allowed in ${category} category during character creation.`);
            return;
        }
        
        // Add trait to the list (all traits are free during character creation)
        traits.push(traitName);
        
        // Update state
        this.stateManager.setState({
            traits: { [category]: traits }
        });
        
        // Show feedback
        this.notificationManager.showSuccess(`${traitName} added (FREE)`);
        
        // Update displays
        this.updateTraitDisplay(category);
        this.updateTraitCount(category);
        this.updateButtonStates(category, traitName);
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Update XP display
        this.updateXPDisplay();
    }
    
    /**
     * Select a negative trait
     */
    selectNegativeTrait(category, traitName) {
        const state = this.stateManager.getState();
        const negativeTraits = [...state.negativeTraits[category]];
        
        // Add negative trait to the list
        negativeTraits.push(traitName);
        
        // Update state
        this.stateManager.setState({
            negativeTraits: { [category]: negativeTraits }
        });
        
        // Update displays
        this.updateNegativeTraitDisplay(category);
        this.updateTraitCount(category);
        this.updateButtonStates(category, traitName, true);
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Update XP display
        this.updateXPDisplay();
        
        // Show feedback
        this.notificationManager.toast(`${traitName} added to ${category} negative traits`);
    }
    
    /**
     * Remove a trait
     */
    removeTrait(category, traitName) {
        const state = this.stateManager.getState();
        const traits = [...state.traits[category]];
        
        // Remove the last instance of the trait
        const index = traits.lastIndexOf(traitName);
        if (index > -1) {
            traits.splice(index, 1);
        }
        
        // Update state
        this.stateManager.setState({
            traits: { [category]: traits }
        });
        
        // Update displays
        this.updateTraitDisplay(category);
        this.updateTraitCount(category);
        this.updateButtonStates(category, traitName);
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Update XP display
        this.updateXPDisplay();
        
        // Show feedback
        this.notificationManager.toast(`${traitName} removed from ${category} traits`);
    }
    
    /**
     * Remove a negative trait
     */
    removeNegativeTrait(category, traitName) {
        const state = this.stateManager.getState();
        const negativeTraits = [...state.negativeTraits[category]];
        
        // Remove the last instance of the negative trait
        const index = negativeTraits.lastIndexOf(traitName);
        if (index > -1) {
            negativeTraits.splice(index, 1);
        }
        
        // Update state
        this.stateManager.setState({
            negativeTraits: { [category]: negativeTraits }
        });
        
        // Update displays
        this.updateNegativeTraitDisplay(category);
        this.updateTraitCount(category);
        this.updateButtonStates(category, traitName, true);
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Update XP display
        this.updateXPDisplay();
        
        // Show feedback
        this.notificationManager.toast(`${traitName} removed from ${category} negative traits`);
    }
    
    /**
     * Update trait display for a category
     */
    updateTraitDisplay(category) {
        const state = this.stateManager.getState();
        const traits = state.traits[category];
        const listElement = this.uiManager.getElement(`#${category.toLowerCase()}TraitList`);
        
        if (!listElement) return;
        
        // Group traits by name and count them
        const traitCounts = {};
        traits.forEach(trait => {
            traitCounts[trait] = (traitCounts[trait] || 0) + 1;
        });
        
        // Create display elements for each unique trait
        const traitHTML = Object.keys(traitCounts).map(traitName => {
            const count = traitCounts[traitName];
            const displayName = count > 1 ? `${traitName} (${count})` : traitName;
            return `
                <div class="selected-trait">
                    <span class="trait-name">${displayName}</span>
                    <button type="button" class="remove-trait-btn" 
                            data-category="${category}" 
                            data-trait="${traitName}">×</button>
                </div>
            `;
        }).join('');
        
        this.uiManager.updateContent(listElement, traitHTML);
    }
    
    /**
     * Update negative trait display for a category
     */
    updateNegativeTraitDisplay(category) {
        const state = this.stateManager.getState();
        const negativeTraits = state.negativeTraits[category];
        const listElement = this.uiManager.getElement(`#${category.toLowerCase()}NegativeTraitList`);
        
        if (!listElement) return;
        
        // Group negative traits by name and count them
        const traitCounts = {};
        negativeTraits.forEach(trait => {
            traitCounts[trait] = (traitCounts[trait] || 0) + 1;
        });
        
        // Create display elements for each unique negative trait
        const traitHTML = Object.keys(traitCounts).map(traitName => {
            const count = traitCounts[traitName];
            const displayName = count > 1 ? `${traitName} (${count})` : traitName;
            return `
                <div class="selected-trait negative">
                    <span class="trait-name">${displayName}</span>
                    <button type="button" class="remove-negative-trait-btn" 
                            data-category="${category}" 
                            data-trait="${traitName}">×</button>
                </div>
            `;
        }).join('');
        
        this.uiManager.updateContent(listElement, traitHTML);
    }
    
    /**
     * Update trait count and progress bar
     */
    updateTraitCount(category) {
        const state = this.stateManager.getState();
        const count = state.traits[category].length;
        const negativeCount = state.negativeTraits[category].length;
        
        // Update count displays
        const countDisplay = this.uiManager.getElement(`#${category.toLowerCase()}CountDisplay`);
        if (countDisplay) {
            this.uiManager.updateContent(countDisplay, count.toString());
        }
        
        const sidebarCount = this.uiManager.getElement(`#${category.toLowerCase()}Count`);
        if (sidebarCount) {
            this.uiManager.updateContent(sidebarCount, count.toString());
        }
        
        // Update progress bar
        this.updateTraitProgressBar(category);
    }
    
    /**
     * Update trait progress bar
     */
    updateTraitProgressBar(category) {
        const state = this.stateManager.getState();
        const count = state.traits[category].length;
        const freePoints = this.getFreePoints(category);
        const progressFill = this.uiManager.getElement(`#${category.toLowerCase()}ProgressFill`);
        
        if (progressFill) {
            // Calculate percentage based on free points (capped at 100%)
            const percentage = (count / freePoints) * 100;
            progressFill.style.width = Math.min(percentage, 100) + '%';
            
            // Update progress bar class
            if (count >= freePoints) {
                progressFill.classList.remove('incomplete');
                progressFill.classList.add('complete');
            } else {
                progressFill.classList.remove('complete');
                progressFill.classList.add('incomplete');
            }
        }
    }
    
    /**
     * Update button states for trait selection
     */
    updateButtonStates(category, traitName, isNegative = false) {
        const state = this.stateManager.getState();
        const traits = isNegative ? state.negativeTraits[category] : state.traits[category];
        const count = traits.filter(t => t === traitName).length;
        
        // Update button text to show count
        const buttons = document.querySelectorAll(`[data-category="${category}"][data-trait="${traitName}"]`);
        buttons.forEach(button => {
            if (isNegative && button.classList.contains('negative')) {
                button.textContent = `${traitName} (${count})`;
            } else if (!isNegative && !button.classList.contains('negative')) {
                button.textContent = `${traitName} (${count})`;
            }
        });
    }
    
    /**
     * Update character preview
     */
    updateCharacterPreview() {
        const state = this.stateManager.getState();
        
        // Update trait previews
        ['Physical', 'Social', 'Mental'].forEach(category => {
            const previewElement = this.uiManager.getElement(`#preview${category}`);
            if (previewElement) {
                const traits = state.traits[category];
                if (traits.length === 0) {
                    this.uiManager.updateContent(previewElement, '<span class="preview-trait">None selected</span>');
                } else {
                    const traitHTML = traits.map(trait => `<span class="preview-trait">${trait}</span>`).join('');
                    this.uiManager.updateContent(previewElement, traitHTML);
                }
            }
        });
    }
    
    /**
     * Update XP display
     */
    updateXPDisplay() {
        // This would integrate with the XP system
        // For now, just emit an event for other systems to handle
        this.eventManager.emitGlobal('traitsUpdated', {
            traits: this.stateManager.getState().traits,
            negativeTraits: this.stateManager.getState().negativeTraits
        });
    }
    
    /**
     * Update all displays
     */
    updateAllDisplays() {
        ['Physical', 'Social', 'Mental'].forEach(category => {
            this.updateTraitDisplay(category);
            this.updateNegativeTraitDisplay(category);
            this.updateTraitCount(category);
        });
    }
    
    /**
     * Validate trait selection
     */
    validateTraits() {
        const state = this.stateManager.getState();
        const errors = [];
        
        Object.keys(this.requirements).forEach(category => {
            const count = state.traits[category].length;
            const requirement = this.requirements[category];
            
            if (count < requirement.min) {
                errors.push(`${category} traits: Need at least ${requirement.min} traits`);
            }
            
            if (count > requirement.max) {
                errors.push(`${category} traits: Maximum ${requirement.max} traits allowed`);
            }
        });
        
        return {
            isValid: errors.length === 0,
            errors
        };
    }
    
    /**
     * Get trait statistics
     */
    getTraitStats() {
        const state = this.stateManager.getState();
        const stats = {};
        
        Object.keys(this.requirements).forEach(category => {
            const count = state.traits[category].length;
            const negativeCount = state.negativeTraits[category].length;
            const requirement = this.requirements[category];
            
            stats[category] = {
                count,
                negativeCount,
                requirement,
                isComplete: count >= requirement.min,
                isOverLimit: count > requirement.max
            };
        });
        
        return stats;
    }
    
    /**
     * Reset traits for a category
     */
    resetCategory(category) {
        this.stateManager.setState({
            traits: { [category]: [] },
            negativeTraits: { [category]: [] }
        });
        
        this.updateTraitDisplay(category);
        this.updateNegativeTraitDisplay(category);
        this.updateTraitCount(category);
    }
    
    /**
     * Reset all traits
     */
    resetAll() {
        this.stateManager.setState({
            traits: { Physical: [], Social: [], Mental: [] },
            negativeTraits: { Physical: [], Social: [], Mental: [] }
        });
        
        this.updateAllDisplays();
    }
    
    /**
     * Handle quick distribution selection
     */
    handleQuickDistribution(event) {
        const button = event.target;
        const distribution = button.dataset.dist;
        
        // Remove active class from all buttons
        document.querySelectorAll('.dist-btn').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        
        // Set the distribution based on selection
        switch(distribution) {
            case 'physical-primary':
                this.pointDistribution = { Physical: 7, Social: 5, Mental: 3 };
                break;
            case 'social-primary':
                this.pointDistribution = { Physical: 5, Social: 7, Mental: 3 };
                break;
            case 'mental-primary':
                this.pointDistribution = { Physical: 5, Social: 3, Mental: 7 };
                break;
        }
        
        this.updatePointDistribution();
        this.updateAllDisplays();
    }
    
    /**
     * Handle manual distribution changes
     */
    handleManualDistribution(event) {
        const select = event.target;
        const category = select.id.replace('Points', '');
        
        // Get all current values
        const physicalPoints = parseInt(document.getElementById('physicalPoints').value);
        const socialPoints = parseInt(document.getElementById('socialPoints').value);
        const mentalPoints = parseInt(document.getElementById('mentalPoints').value);
        
        // Validate that we have exactly 7, 5, 3
        const values = [physicalPoints, socialPoints, mentalPoints].sort((a, b) => b - a);
        if (values[0] !== 7 || values[1] !== 5 || values[2] !== 3) {
            this.notificationManager.showError('Point distribution must be 7, 5, and 3 points.');
            // Reset to valid distribution
            this.updatePointDistribution();
            return;
        }
        
        // Update distribution
        this.pointDistribution = {
            Physical: physicalPoints,
            Social: socialPoints,
            Mental: mentalPoints
        };
        
        this.updatePointDistribution();
        this.updateAllDisplays();
    }
    
    /**
     * Update point distribution display
     */
    updatePointDistribution() {
        // Update the status display
        const status = document.getElementById('distributionStatus');
        if (status) {
            status.textContent = `Current: Physical(${this.pointDistribution.Physical}), Social(${this.pointDistribution.Social}), Mental(${this.pointDistribution.Mental})`;
        }
        
        // Update the free point displays
        document.getElementById('physicalFreeDisplay').textContent = this.pointDistribution.Physical;
        document.getElementById('socialFreeDisplay').textContent = this.pointDistribution.Social;
        document.getElementById('mentalFreeDisplay').textContent = this.pointDistribution.Mental;
        
        // Update the select boxes
        document.getElementById('physicalPoints').value = this.pointDistribution.Physical;
        document.getElementById('socialPoints').value = this.pointDistribution.Social;
        document.getElementById('mentalPoints').value = this.pointDistribution.Mental;
    }
    
    /**
     * Get free points for a category
     */
    getFreePoints(category) {
        return this.pointDistribution[category] || 0;
    }
    
    /**
     * Check if a trait selection is free
     */
    isTraitFree(category) {
        const state = this.stateManager.getState();
        const currentCount = state.traits[category].length;
        const freePoints = this.getFreePoints(category);
        
        return currentCount < freePoints;
    }
    
    /**
     * Calculate XP cost for a trait
     */
    calculateTraitXP(category) {
        const state = this.stateManager.getState();
        const currentCount = state.traits[category].length;
        const freePoints = this.getFreePoints(category);
        
        if (currentCount < freePoints) {
            return 0; // Free
        } else {
            return 4; // Costs 4 XP
        }
    }
    
    /**
     * Update character preview
     */
    updateCharacterPreview() {
        // This would be handled by the main application
        // For now, we'll just log the update
    }
    
    /**
     * Update XP display
     */
    updateXPDisplay() {
        const state = this.stateManager.getState();
        let traitsXP = 0;
        
        // Calculate XP spent on traits (first 7 are free, 8-10 cost 4 XP each)
        ['Physical', 'Social', 'Mental'].forEach(category => {
            const count = state.traits[category] ? state.traits[category].length : 0;
            if (count > 7) {
                const paidTraits = count - 7;
                traitsXP += paidTraits * 4;
            }
        });
        
        // Calculate XP gained from negative traits (+4 XP each)
        let negativeTraitsXP = 0;
        ['Physical', 'Social', 'Mental'].forEach(category => {
            const negativeCount = state.negativeTraits[category] ? state.negativeTraits[category].length : 0;
            negativeTraitsXP += negativeCount * 4;
        });
        
        // Update trait-specific XP displays
        const xpTraitsElement = document.getElementById('xpTraits');
        if (xpTraitsElement) {
            xpTraitsElement.textContent = traitsXP;
        }
        
        // Update negative traits XP (this reduces total XP cost)
        const xpNegativeElement = document.getElementById('xpNegative');
        if (xpNegativeElement) {
            xpNegativeElement.textContent = negativeTraitsXP;
        }
        
        // Trigger global XP update
        this.eventManager.emitGlobal('xpUpdated', { 
            traitsXP, 
            negativeTraitsXP,
            source: 'TraitSystem'
        });
    }
    
    /**
     * Update all displays
     */
    updateAllDisplays() {
        ['Physical', 'Social', 'Mental'].forEach(category => {
            this.updateTraitDisplay(category);
            this.updateNegativeTraitDisplay(category);
            this.updateTraitCount(category);
        });
    }
    
    /**
     * Validate trait selection
     */
    validateTraits() {
        const state = this.stateManager.getState();
        const errors = [];
        
        Object.keys(this.requirements).forEach(category => {
            const count = state.traits[category].length;
            const requirement = this.requirements[category];
            
            if (count < requirement.min) {
                errors.push(`${category} traits: ${count}/${requirement.min} required`);
            } else if (count > requirement.max) {
                errors.push(`${category} traits: ${count}/${requirement.max} maximum exceeded`);
            }
        });
        
        return {
            isValid: errors.length === 0,
            errors
        };
    }
    
    /**
     * Get trait statistics
     */
    getTraitStats() {
        const state = this.stateManager.getState();
        const stats = {};
        
        Object.keys(this.requirements).forEach(category => {
            const count = state.traits[category].length;
            const negativeCount = state.negativeTraits[category].length;
            const requirement = this.requirements[category];
            
            stats[category] = {
                count,
                negativeCount,
                requirement,
                isComplete: count >= requirement.min,
                isOverLimit: count > requirement.max
            };
        });
        
        return stats;
    }
    
    /**
     * Reset traits for a category
     */
    resetCategory(category) {
        this.stateManager.setState({
            traits: { [category]: [] },
            negativeTraits: { [category]: [] }
        });
        
        this.updateTraitDisplay(category);
        this.updateNegativeTraitDisplay(category);
        this.updateTraitCount(category);
    }
    
    /**
     * Reset all traits
     */
    resetAll() {
        this.stateManager.setState({
            traits: { Physical: [], Social: [], Mental: [] },
            negativeTraits: { Physical: [], Social: [], Mental: [] }
        });
        
        this.updateAllDisplays();
    }
    
    /**
     * Handle quick distribution selection
     */
    handleQuickDistribution(event) {
        const button = event.target;
        const distribution = button.dataset.dist;
        
        // Remove active class from all buttons
        document.querySelectorAll('.dist-btn').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        
        // Set the distribution based on selection
        switch(distribution) {
            case 'physical-primary':
                this.pointDistribution = { Physical: 7, Social: 5, Mental: 3 };
                break;
            case 'social-primary':
                this.pointDistribution = { Physical: 5, Social: 7, Mental: 3 };
                break;
            case 'mental-primary':
                this.pointDistribution = { Physical: 5, Social: 3, Mental: 7 };
                break;
        }
        
        this.updatePointDistribution();
        this.updateAllDisplays();
    }
    
    /**
     * Handle manual distribution changes
     */
    handleManualDistribution(event) {
        const select = event.target;
        const category = select.id.replace('Points', '');
        
        // Get all current values
        const physicalPoints = parseInt(document.getElementById('physicalPoints').value);
        const socialPoints = parseInt(document.getElementById('socialPoints').value);
        const mentalPoints = parseInt(document.getElementById('mentalPoints').value);
        
        // Validate that we have exactly 7, 5, 3
        const values = [physicalPoints, socialPoints, mentalPoints].sort((a, b) => b - a);
        if (values[0] !== 7 || values[1] !== 5 || values[2] !== 3) {
            this.notificationManager.showError('Point distribution must be 7, 5, and 3 points.');
            // Reset to valid distribution
            this.updatePointDistribution();
            return;
        }
        
        // Update distribution
        this.pointDistribution = {
            Physical: physicalPoints,
            Social: socialPoints,
            Mental: mentalPoints
        };
        
        this.updatePointDistribution();
        this.updateAllDisplays();
    }
    
    /**
     * Update point distribution display
     */
    updatePointDistribution() {
        // Update the status display
        const status = document.getElementById('distributionStatus');
        if (status) {
            status.textContent = `Current: Physical(${this.pointDistribution.Physical}), Social(${this.pointDistribution.Social}), Mental(${this.pointDistribution.Mental})`;
        }
        
        // Update the free point displays
        document.getElementById('physicalFreeDisplay').textContent = this.pointDistribution.Physical;
        document.getElementById('socialFreeDisplay').textContent = this.pointDistribution.Social;
        document.getElementById('mentalFreeDisplay').textContent = this.pointDistribution.Mental;
        
        // Update the select boxes
        document.getElementById('physicalPoints').value = this.pointDistribution.Physical;
        document.getElementById('socialPoints').value = this.pointDistribution.Social;
        document.getElementById('mentalPoints').value = this.pointDistribution.Mental;
    }
    
    /**
     * Get free points for a category
     */
    getFreePoints(category) {
        return this.pointDistribution[category] || 0;
    }
    
    /**
     * Check if a trait selection is free
     */
    isTraitFree(category) {
        const state = this.stateManager.getState();
        const currentCount = state.traits[category].length;
        const freePoints = this.getFreePoints(category);
        
        return currentCount < freePoints;
    }
    
    /**
     * Calculate XP cost for a trait
     */
    calculateTraitXP(category) {
        const state = this.stateManager.getState();
        const currentCount = state.traits[category].length;
        const freePoints = this.getFreePoints(category);
        
        if (currentCount < freePoints) {
            return 0; // Free
        } else {
            return 4; // Costs 4 XP
        }
    }
}

// Export for use in other modules
window.TraitSystem = TraitSystem;
