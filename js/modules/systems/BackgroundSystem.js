/**
 * BackgroundSystem.js - Handles background selection and management
 * Manages background selection, generation calculation, and display
 */

class BackgroundSystem {
    constructor(stateManager, uiManager, eventManager) {
        this.stateManager = stateManager;
        this.uiManager = uiManager;
        this.eventManager = eventManager;
        
        this.requirements = {
            min: 1,
            max: 5
        };
        
        this.backgroundData = {
            "Allies": { description: "You have friends who can help you.", max: 5 },
            "Contacts": { description: "You know people in various places.", max: 5 },
            "Influence": { description: "You have political or social influence.", max: 5 },
            "Mentor": { description: "You have a wise teacher.", max: 5 },
            "Resources": { description: "You have money and material wealth.", max: 5 },
            "Retainers": { description: "You have loyal servants.", max: 5 },
            "Status": { description: "You have social standing.", max: 5 }
        };
        
        this.init();
    }
    
    /**
     * Initialize the background system
     */
    init() {
        this.resetAllBackgrounds();
        this.setupEventListeners();
        this.updateAllDisplays();
    }
    
    /**
     * Setup event listeners for background selection
     */
    setupEventListeners() {
        const { eventManager } = this;
        
        // Background selection buttons
        const backgroundContainer = this.uiManager.getElement('#backgroundsTab');
        if (backgroundContainer) {
            eventManager.addDelegatedListener(backgroundContainer, '.background-option-btn', 'click', (e) => {
                this.handleBackgroundClick(e);
            });
        }
        
        // Remove background buttons
        eventManager.addDelegatedListener(document, '.remove-background-btn', 'click', (e) => {
            this.handleRemoveBackground(e);
        });
        
        // Generation input
        const generationInput = this.uiManager.getElement('#generation');
        if (generationInput) {
            eventManager.addListener(generationInput, 'change', (e) => {
                this.handleGenerationChange(e);
            });
        }
    }
    
    /**
     * Handle background selection click
     */
    handleBackgroundClick(event) {
        const button = event.target;
        const backgroundName = button.dataset.background;
        const level = parseInt(button.dataset.level);
        
        if (!backgroundName || !level) return;
        
        this.selectBackgroundLevel(backgroundName, level);
    }
    
    /**
     * Handle remove background click
     */
    handleRemoveBackground(event) {
        const button = event.target;
        const backgroundName = button.dataset.background;
        
        if (!backgroundName) return;
        
        this.removeBackground(backgroundName);
    }
    
    /**
     * Handle generation change
     */
    handleGenerationChange(event) {
        const input = event.target;
        const generation = parseInt(input.value) || 13;
        
        this.updateGenerationBackground(generation);
    }
    
    /**
     * Select a specific background level
     */
    selectBackgroundLevel(backgroundName, level) {
        const state = this.stateManager.getState();
        const backgrounds = { ...state.backgrounds };
        
        // Check if level is valid
        const maxLevel = this.backgroundData[backgroundName]?.max || 5;
        
        if (level < 1 || level > maxLevel) {
            console.warn(`BackgroundSystem: ${backgroundName} level must be between 1 and ${maxLevel}.`);
            return;
        }
        
        // Set background level
        backgrounds[backgroundName] = level;
        
        // Update state
        this.stateManager.setState({
            backgrounds: backgrounds
        });
        
        // Update displays
        this.updateBackgroundDisplay();
        this.updateBackgroundButtons();
        this.updateBackgroundsSummary();
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Show feedback
        console.log(`BackgroundSystem: ${backgroundName} set to level ${level}`);
    }
    
    /**
     * Select a background (increment by 1)
     */
    selectBackground(backgroundName) {
        const state = this.stateManager.getState();
        const backgrounds = { ...state.backgrounds };
        
        // Check if background is already at maximum
        const currentLevel = backgrounds[backgroundName] || 0;
        const maxLevel = this.backgroundData[backgroundName]?.max || 5;
        
        if (currentLevel >= maxLevel) {
            console.warn(`BackgroundSystem: ${backgroundName} is already at maximum level (${maxLevel}).`);
            return;
        }
        
        // Increase background level
        backgrounds[backgroundName] = (backgrounds[backgroundName] || 0) + 1;
        
        // Update state
        this.stateManager.setState({
            backgrounds: backgrounds
        });
        
        // Update displays
        this.updateBackgroundDisplay();
        this.updateBackgroundButtons();
        this.updateBackgroundsSummary();
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Show feedback
        console.log(`BackgroundSystem: ${backgroundName} increased to level ${backgrounds[backgroundName]}`);
    }
    
    /**
     * Remove a background
     */
    removeBackground(backgroundName) {
        const state = this.stateManager.getState();
        const backgrounds = { ...state.backgrounds };
        
        // Decrease background level
        if (backgrounds[backgroundName] && backgrounds[backgroundName] > 0) {
            backgrounds[backgroundName] -= 1;
            
            // Remove if at 0
            if (backgrounds[backgroundName] === 0) {
                delete backgrounds[backgroundName];
            }
        }
        
        // Update state
        this.stateManager.setState({
            backgrounds: backgrounds
        });
        
        // Update displays
        this.updateBackgroundDisplay();
        this.updateBackgroundButtons();
        this.updateBackgroundsSummary();
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Show feedback
        console.log(`BackgroundSystem: ${backgroundName} decreased to level ${backgrounds[backgroundName] || 0}`);
    }
    
    /**
     * Update generation background
     */
    updateGenerationBackground(generation) {
        const state = this.stateManager.getState();
        const backgrounds = { ...state.backgrounds };
        
        // Calculate generation background points
        const generationBackground = this.calculateGenerationBackground(generation);
        
        // Update generation background
        backgrounds['Generation'] = generationBackground;
        
        // Update state
        this.stateManager.setState({
            backgrounds: backgrounds,
            generation: generation
        });
        
        // Update displays
        this.updateBackgroundDisplay();
        this.updateBackgroundsSummary();
        
        // Update character preview
        this.updateCharacterPreview();
    }
    
    /**
     * Calculate generation background points
     */
    calculateGenerationBackground(generation) {
        // Generation 13 = 5 points, Generation 12 = 4 points, etc.
        return Math.max(0, 18 - generation);
    }
    
    /**
     * Update background display
     */
    updateBackgroundDisplay() {
        const state = this.stateManager.getState();
        const backgrounds = state.backgrounds;
        const listElement = this.uiManager.getElement('#backgroundsList');
        
        if (!listElement) return;
        
        // Create display elements for each background (only show if level > 0)
        const backgroundHTML = Object.keys(backgrounds)
            .filter(backgroundName => backgrounds[backgroundName] > 0)
            .map(backgroundName => {
                const level = backgrounds[backgroundName];
                const maxLevel = this.backgroundData[backgroundName]?.max || 5;
                const description = this.backgroundData[backgroundName]?.description || '';
                
                return `
                    <div class="selected-background">
                        <div class="background-header">
                            <span class="background-name">${backgroundName}</span>
                            <span class="background-level">${level}/${maxLevel}</span>
                            <button type="button" class="remove-background-btn" 
                                    data-background="${backgroundName}">×</button>
                        </div>
                        <div class="background-description">${description}</div>
                        <div class="background-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: ${(level / maxLevel) * 100}%"></div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        
        this.uiManager.updateContent(listElement, backgroundHTML);
    }
    
    /**
     * Update background buttons
     */
    updateBackgroundButtons() {
        const state = this.stateManager.getState();
        const backgrounds = state.backgrounds;
        
        Object.keys(this.backgroundData).forEach(backgroundName => {
            const currentLevel = backgrounds[backgroundName] || 0;
            const maxLevel = this.backgroundData[backgroundName].max;
            
            // Update all level buttons for this background
            for (let level = 1; level <= maxLevel; level++) {
                const button = this.uiManager.getElement(`.background-option-btn[data-background="${backgroundName}"][data-level="${level}"]`);
                if (!button) continue;
                
                // Update button appearance
                this.uiManager.updateClasses(button, {
                    'selected': currentLevel >= level,
                    'at-max': currentLevel >= maxLevel
                });
                
                // Update button text (keep the level number)
                this.uiManager.updateContent(button, level.toString());
                
                // Update button title
                this.uiManager.updateAttributes(button, {
                    'title': currentLevel >= level ? 
                        `${backgroundName} level ${level} (selected)` : 
                        `Select ${backgroundName} level ${level}`
                });
            }
        });
    }
    
    /**
     * Update backgrounds summary
     */
    updateBackgroundsSummary() {
        const state = this.stateManager.getState();
        const backgrounds = state.backgrounds;
        
        // Calculate total background points
        const totalPoints = Object.values(backgrounds).reduce((total, level) => total + level, 0);
        
        // Update total points display
        const totalElement = this.uiManager.getElement('#backgroundsTotal');
        if (totalElement) {
            this.uiManager.updateContent(totalElement, totalPoints.toString());
        }
        
        // Update count display
        const countElement = this.uiManager.getElement('#backgroundsCount');
        if (countElement) {
            const count = Object.keys(backgrounds).length;
            this.uiManager.updateContent(countElement, count.toString());
        }
        
        // Update progress bar
        const progressFill = this.uiManager.getElement('#backgroundsProgressFill');
        if (progressFill) {
            const percentage = Math.min((totalPoints / this.requirements.min) * 100, 100);
            progressFill.style.width = percentage + '%';
            
            // Update progress bar class
            this.uiManager.updateClasses(progressFill, {
                'complete': totalPoints >= this.requirements.min,
                'incomplete': totalPoints < this.requirements.min
            });
        }
    }
    
    /**
     * Update character preview
     */
    updateCharacterPreview() {
        console.log('BackgroundSystem: Character preview updated');
    }
    
    /**
     * Update all displays
     */
    updateAllDisplays() {
        this.updateBackgroundDisplay();
        this.updateBackgroundButtons();
        this.updateBackgroundsSummary();
    }
    
    /**
     * Validate background selection
     */
    validateBackgrounds() {
        const state = this.stateManager.getState();
        const backgrounds = state.backgrounds;
        
        const totalPoints = Object.values(backgrounds).reduce((total, level) => total + level, 0);
        const errors = [];
        
        if (totalPoints < this.requirements.min) {
            errors.push(`Backgrounds: ${totalPoints}/${this.requirements.min} points required`);
        } else if (totalPoints > this.requirements.max) {
            errors.push(`Backgrounds: ${totalPoints}/${this.requirements.max} points maximum exceeded`);
        }
        
        return {
            isValid: errors.length === 0,
            errors,
            totalPoints
        };
    }
    
    /**
     * Get background statistics
     */
    getBackgroundStats() {
        const state = this.stateManager.getState();
        const backgrounds = state.backgrounds;
        
        const totalPoints = Object.values(backgrounds).reduce((total, level) => total + level, 0);
        const count = Object.keys(backgrounds).length;
        
        return {
            count,
            totalPoints,
            requirement: this.requirements,
            isComplete: totalPoints >= this.requirements.min,
            isOverLimit: totalPoints > this.requirements.max
        };
    }
    
    /**
     * Get background level
     */
    getBackgroundLevel(backgroundName) {
        const state = this.stateManager.getState();
        return state.backgrounds[backgroundName] || 0;
    }
    
    /**
     * Set background level
     */
    setBackgroundLevel(backgroundName, level) {
        const state = this.stateManager.getState();
        const backgrounds = { ...state.backgrounds };
        
        if (level <= 0) {
            delete backgrounds[backgroundName];
        } else {
            backgrounds[backgroundName] = level;
        }
        
        this.stateManager.setState({
            backgrounds: backgrounds
        });
        
        this.updateAllDisplays();
    }
    
    /**
     * Reset all backgrounds to 0
     */
    resetAllBackgrounds() {
        const state = this.stateManager.getState();
        const backgrounds = {};
        
        // Initialize all background types to 0
        Object.keys(this.backgroundData).forEach(backgroundName => {
            backgrounds[backgroundName] = 0;
        });
        
        this.stateManager.setState({
            backgrounds: backgrounds
        });
    }
    
    /**
     * Reset all backgrounds
     */
    resetAll() {
        this.stateManager.setState({
            backgrounds: {}
        });
        
        this.updateAllDisplays();
    }
    
    /**
     * Get all backgrounds
     */
    getBackgrounds() {
        const state = this.stateManager.getState();
        return { ...state.backgrounds };
    }
}

// Export for use in other modules
window.BackgroundSystem = BackgroundSystem;
