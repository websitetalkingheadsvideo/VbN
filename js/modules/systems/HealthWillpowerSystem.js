/**
 * HealthWillpowerSystem.js - Handles health and willpower calculation and display
 * Manages health and willpower calculation based on attributes and virtues
 */

class HealthWillpowerSystem {
    constructor(stateManager, uiManager, eventManager) {
        this.stateManager = stateManager;
        this.uiManager = uiManager;
        this.eventManager = eventManager;
        
        this.init();
    }
    
    /**
     * Initialize the health/willpower system
     */
    init() {
        this.setupEventListeners();
        this.updateHealthWillpowerDisplay();
    }
    
    /**
     * Setup event listeners for health/willpower calculation
     */
    setupEventListeners() {
        const { eventManager } = this;
        
        // Listen for changes to attributes
        eventManager.addListener('attributesChanged', () => {
            this.updateHealthWillpowerDisplay();
        });
        
        // Listen for changes to virtues
        eventManager.addListener('virtuesChanged', () => {
            this.updateHealthWillpowerDisplay();
        });
        
        // Listen for changes to generation
        eventManager.addListener('generationChanged', () => {
            this.updateHealthWillpowerDisplay();
        });
    }
    
    /**
     * Calculate health points
     */
    calculateHealth() {
        const state = this.stateManager.getState();
        const stamina = state.attributes.Stamina || 1;
        const generation = state.generation || 13;
        
        // Health = Stamina + 3 (base) + generation bonus
        const baseHealth = 3;
        const generationBonus = this.getGenerationHealthBonus(generation);
        const health = baseHealth + stamina + generationBonus;
        
        return health;
    }
    
    /**
     * Calculate willpower points
     */
    calculateWillpower() {
        const state = this.stateManager.getState();
        const conscience = state.virtues.Conscience || 1;
        const selfControl = state.virtues.SelfControl || 1;
        
        // Willpower = Conscience + Self-Control
        const willpower = conscience + selfControl;
        
        return willpower;
    }
    
    /**
     * Get generation health bonus
     */
    getGenerationHealthBonus(generation) {
        // Higher generation = more health
        const bonuses = {
            13: 0,    // Generation 13 = +0
            12: 1,    // Generation 12 = +1
            11: 2,    // Generation 11 = +2
            10: 3,    // Generation 10 = +3
            9: 4,     // Generation 9 = +4
            8: 5,     // Generation 8 = +5
            7: 6,     // Generation 7 = +6
            6: 7,     // Generation 6 = +7
            5: 8,     // Generation 5 = +8
            4: 9,     // Generation 4 = +9
            3: 10,    // Generation 3 = +10
            2: 11,    // Generation 2 = +11
            1: 12     // Generation 1 = +12
        };
        
        return bonuses[generation] || 0;
    }
    
    /**
     * Update health and willpower display
     */
    updateHealthWillpowerDisplay() {
        const health = this.calculateHealth();
        const willpower = this.calculateWillpower();
        
        // Update health display
        const healthElement = this.uiManager.getElement('#healthPoints');
        if (healthElement) {
            this.uiManager.updateContent(healthElement, health.toString());
        }
        
        // Update willpower display
        const willpowerElement = this.uiManager.getElement('#willpowerPoints');
        if (willpowerElement) {
            this.uiManager.updateContent(willpowerElement, willpower.toString());
        }
        
        // Update health breakdown
        this.updateHealthBreakdown();
        
        // Update willpower breakdown
        this.updateWillpowerBreakdown();
    }
    
    /**
     * Update health breakdown display
     */
    updateHealthBreakdown() {
        const state = this.stateManager.getState();
        const stamina = state.attributes.Stamina || 1;
        const generation = state.generation || 13;
        const generationBonus = this.getGenerationHealthBonus(generation);
        const baseHealth = 3;
        const totalHealth = baseHealth + stamina + generationBonus;
        
        const breakdownElement = this.uiManager.getElement('#healthBreakdown');
        if (!breakdownElement) return;
        
        const breakdownHTML = `
            <div class="health-breakdown">
                <div class="breakdown-item">
                    <span class="label">Base Health:</span>
                    <span class="value">${baseHealth}</span>
                </div>
                <div class="breakdown-item">
                    <span class="label">Stamina (${stamina}):</span>
                    <span class="value">+${stamina}</span>
                </div>
                <div class="breakdown-item">
                    <span class="label">Generation ${generation}:</span>
                    <span class="value">+${generationBonus}</span>
                </div>
                <div class="breakdown-item total">
                    <span class="label">Total Health:</span>
                    <span class="value">${totalHealth}</span>
                </div>
            </div>
        `;
        
        this.uiManager.updateContent(breakdownElement, breakdownHTML);
    }
    
    /**
     * Update willpower breakdown display
     */
    updateWillpowerBreakdown() {
        const state = this.stateManager.getState();
        const conscience = state.virtues.Conscience || 1;
        const selfControl = state.virtues.SelfControl || 1;
        const totalWillpower = conscience + selfControl;
        
        const breakdownElement = this.uiManager.getElement('#willpowerBreakdown');
        if (!breakdownElement) return;
        
        const breakdownHTML = `
            <div class="willpower-breakdown">
                <div class="breakdown-item">
                    <span class="label">Conscience (${conscience}):</span>
                    <span class="value">+${conscience}</span>
                </div>
                <div class="breakdown-item">
                    <span class="label">Self-Control (${selfControl}):</span>
                    <span class="value">+${selfControl}</span>
                </div>
                <div class="breakdown-item total">
                    <span class="label">Total Willpower:</span>
                    <span class="value">${totalWillpower}</span>
                </div>
            </div>
        `;
        
        this.uiManager.updateContent(breakdownElement, breakdownHTML);
    }
    
    /**
     * Get health calculation details
     */
    getHealthDetails() {
        const state = this.stateManager.getState();
        const stamina = state.attributes.Stamina || 1;
        const generation = state.generation || 13;
        const generationBonus = this.getGenerationHealthBonus(generation);
        const baseHealth = 3;
        const totalHealth = baseHealth + stamina + generationBonus;
        
        return {
            baseHealth: baseHealth,
            stamina: stamina,
            generation: generation,
            generationBonus: generationBonus,
            totalHealth: totalHealth
        };
    }
    
    /**
     * Get willpower calculation details
     */
    getWillpowerDetails() {
        const state = this.stateManager.getState();
        const conscience = state.virtues.Conscience || 1;
        const selfControl = state.virtues.SelfControl || 1;
        const totalWillpower = conscience + selfControl;
        
        return {
            conscience: conscience,
            selfControl: selfControl,
            totalWillpower: totalWillpower
        };
    }
    
    /**
     * Get health amount
     */
    getHealthAmount() {
        return this.calculateHealth();
    }
    
    /**
     * Get willpower amount
     */
    getWillpowerAmount() {
        return this.calculateWillpower();
    }
    
    /**
     * Update health/willpower when attributes change
     */
    onAttributesChange() {
        this.updateHealthWillpowerDisplay();
    }
    
    /**
     * Update health/willpower when virtues change
     */
    onVirtuesChange() {
        this.updateHealthWillpowerDisplay();
    }
    
    /**
     * Update health/willpower when generation changes
     */
    onGenerationChange() {
        this.updateHealthWillpowerDisplay();
    }
    
    /**
     * Get health calculation formula
     */
    getHealthFormula() {
        return 'Base Health (3) + Stamina + Generation Bonus';
    }
    
    /**
     * Get willpower calculation formula
     */
    getWillpowerFormula() {
        return 'Conscience + Self-Control';
    }
    
    /**
     * Validate health/willpower calculation
     */
    validateHealthWillpower() {
        const health = this.calculateHealth();
        const willpower = this.calculateWillpower();
        const errors = [];
        
        if (health < 1) {
            errors.push('Health cannot be less than 1');
        }
        
        if (health > 20) {
            errors.push('Health seems unusually high');
        }
        
        if (willpower < 2) {
            errors.push('Willpower cannot be less than 2');
        }
        
        if (willpower > 10) {
            errors.push('Willpower cannot be more than 10');
        }
        
        return {
            isValid: errors.length === 0,
            errors,
            health,
            willpower
        };
    }
    
    /**
     * Get generation health bonus for specific generation
     */
    getGenerationHealthBonusForGeneration(generation) {
        return this.getGenerationHealthBonus(generation);
    }
    
    /**
     * Reset health/willpower calculation
     */
    reset() {
        this.updateHealthWillpowerDisplay();
    }
    
    /**
     * Get all health/willpower statistics
     */
    getStats() {
        return {
            health: this.getHealthDetails(),
            willpower: this.getWillpowerDetails(),
            formulas: {
                health: this.getHealthFormula(),
                willpower: this.getWillpowerFormula()
            }
        };
    }
}

// Export for use in other modules
window.HealthWillpowerSystem = HealthWillpowerSystem;
