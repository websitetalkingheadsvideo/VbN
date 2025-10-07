/**
 * CashSystem.js - Handles starting cash calculation and display
 * Manages cash calculation based on resources background and generation
 */

class CashSystem {
    constructor(stateManager, uiManager, eventManager, notificationManager) {
        this.stateManager = stateManager;
        this.uiManager = uiManager;
        this.eventManager = eventManager;
        this.notificationManager = notificationManager;
        
        this.cashMultipliers = {
            13: 1,    // Generation 13 = 1x
            12: 2,    // Generation 12 = 2x
            11: 3,    // Generation 11 = 3x
            10: 4,    // Generation 10 = 4x
            9: 5,     // Generation 9 = 5x
            8: 6,     // Generation 8 = 6x
            7: 7,     // Generation 7 = 7x
            6: 8,     // Generation 6 = 8x
            5: 9,     // Generation 5 = 9x
            4: 10,    // Generation 4 = 10x
            3: 11,    // Generation 3 = 11x
            2: 12,    // Generation 2 = 12x
            1: 13     // Generation 1 = 13x
        };
        
        this.baseCash = 100; // Base cash amount
        
        this.init();
    }
    
    /**
     * Initialize the cash system
     */
    init() {
        this.setupEventListeners();
        this.updateCashDisplay();
    }
    
    /**
     * Setup event listeners for cash calculation
     */
    setupEventListeners() {
        const { eventManager } = this;
        
        // Listen for changes to resources background
        eventManager.addListener('resourcesChanged', () => {
            this.updateCashDisplay();
        });
        
        // Listen for changes to generation
        eventManager.addListener('generationChanged', () => {
            this.updateCashDisplay();
        });
        
        // Listen for changes to backgrounds
        eventManager.addListener('backgroundsChanged', () => {
            this.updateCashDisplay();
        });
    }
    
    /**
     * Calculate starting cash
     */
    calculateCash() {
        const state = this.stateManager.getState();
        const resources = state.backgrounds.Resources || 0;
        const generation = state.generation || 13;
        
        // Get multiplier for generation
        const multiplier = this.cashMultipliers[generation] || 1;
        
        // Calculate cash: base * (1 + resources) * generation multiplier
        const cash = this.baseCash * (1 + resources) * multiplier;
        
        return Math.round(cash);
    }
    
    /**
     * Update cash display
     */
    updateCashDisplay() {
        const cash = this.calculateCash();
        
        // Update cash display
        const cashElement = this.uiManager.getElement('#startingCash');
        if (cashElement) {
            this.uiManager.updateContent(cashElement, `$${cash.toLocaleString()}`);
        }
        
        // Update cash breakdown
        this.updateCashBreakdown();
    }
    
    /**
     * Update cash breakdown display
     */
    updateCashBreakdown() {
        const state = this.stateManager.getState();
        const resources = state.backgrounds.Resources || 0;
        const generation = state.generation || 13;
        const multiplier = this.cashMultipliers[generation] || 1;
        
        const breakdownElement = this.uiManager.getElement('#cashBreakdown');
        if (!breakdownElement) return;
        
        const baseAmount = this.baseCash;
        const resourcesAmount = this.baseCash * resources;
        const totalBeforeMultiplier = baseAmount + resourcesAmount;
        const finalAmount = totalBeforeMultiplier * multiplier;
        
        const breakdownHTML = `
            <div class="cash-breakdown">
                <div class="breakdown-item">
                    <span class="label">Base Cash:</span>
                    <span class="value">$${baseAmount.toLocaleString()}</span>
                </div>
                <div class="breakdown-item">
                    <span class="label">Resources (${resources}):</span>
                    <span class="value">$${resourcesAmount.toLocaleString()}</span>
                </div>
                <div class="breakdown-item">
                    <span class="label">Generation ${generation} (${multiplier}x):</span>
                    <span class="value">×${multiplier}</span>
                </div>
                <div class="breakdown-item total">
                    <span class="label">Total:</span>
                    <span class="value">$${Math.round(finalAmount).toLocaleString()}</span>
                </div>
            </div>
        `;
        
        this.uiManager.updateContent(breakdownElement, breakdownHTML);
    }
    
    /**
     * Get cash calculation details
     */
    getCashDetails() {
        const state = this.stateManager.getState();
        const resources = state.backgrounds.Resources || 0;
        const generation = state.generation || 13;
        const multiplier = this.cashMultipliers[generation] || 1;
        
        const baseAmount = this.baseCash;
        const resourcesAmount = this.baseCash * resources;
        const totalBeforeMultiplier = baseAmount + resourcesAmount;
        const finalAmount = totalBeforeMultiplier * multiplier;
        
        return {
            baseCash: baseAmount,
            resources: resources,
            resourcesAmount: resourcesAmount,
            generation: generation,
            multiplier: multiplier,
            totalBeforeMultiplier: totalBeforeMultiplier,
            finalAmount: Math.round(finalAmount)
        };
    }
    
    /**
     * Get cash amount
     */
    getCashAmount() {
        return this.calculateCash();
    }
    
    /**
     * Update cash when resources change
     */
    onResourcesChange() {
        this.updateCashDisplay();
    }
    
    /**
     * Update cash when generation changes
     */
    onGenerationChange() {
        this.updateCashDisplay();
    }
    
    /**
     * Update cash when backgrounds change
     */
    onBackgroundsChange() {
        this.updateCashDisplay();
    }
    
    /**
     * Get cash multiplier for generation
     */
    getCashMultiplier(generation) {
        return this.cashMultipliers[generation] || 1;
    }
    
    /**
     * Get base cash amount
     */
    getBaseCash() {
        return this.baseCash;
    }
    
    /**
     * Set base cash amount
     */
    setBaseCash(amount) {
        this.baseCash = amount;
        this.updateCashDisplay();
    }
    
    /**
     * Get cash calculation formula
     */
    getCashFormula() {
        return 'Base Cash × (1 + Resources) × Generation Multiplier';
    }
    
    /**
     * Validate cash calculation
     */
    validateCash() {
        const cash = this.calculateCash();
        const errors = [];
        
        if (cash < 0) {
            errors.push('Cash amount cannot be negative');
        }
        
        if (cash > 1000000) {
            errors.push('Cash amount seems unusually high');
        }
        
        return {
            isValid: errors.length === 0,
            errors,
            cash
        };
    }
    
    /**
     * Reset cash calculation
     */
    reset() {
        this.updateCashDisplay();
    }
}

// Export for use in other modules
window.CashSystem = CashSystem;
