/**
 * MeritsFlawsSystem.js - Handles merits and flaws selection and management
 * Manages merit/flaw selection, conflict checking, and cost calculation
 */

class MeritsFlawsSystem {
    constructor(stateManager, uiManager, eventManager, notificationManager) {
        this.stateManager = stateManager;
        this.uiManager = uiManager;
        this.eventManager = eventManager;
        this.notificationManager = notificationManager;
        
        this.meritsFlawsData = null;
        this.currentFilters = {
            category: 'all',
            type: 'all',
            cost: 'all',
            search: ''
        };
        
        this.init();
    }
    
    /**
     * Initialize the merits/flaws system
     */
    init() {
        this.loadMeritsFlawsData();
        this.setupEventListeners();
        this.updateAllDisplays();
    }
    
    /**
     * Load merits/flaws data
     */
    loadMeritsFlawsData() {
        // This would typically load from an API or data file
        // For now, we'll use a simplified version
        this.meritsFlawsData = {
            "Merits": {
                "Physical": [
                    { name: "Ambidextrous", cost: 1, description: "You can use both hands equally well." },
                    { name: "Bruiser", cost: 2, description: "You are naturally strong and intimidating." },
                    { name: "Eagle's Sight", cost: 1, description: "Your vision is exceptionally sharp." }
                ],
                "Social": [
                    { name: "Allies", cost: "Variable", description: "You have contacts who can help you." },
                    { name: "Contacts", cost: "Variable", description: "You know people in various places." },
                    { name: "Mentor", cost: "Variable", description: "You have a wise teacher." }
                ],
                "Mental": [
                    { name: "Eidetic Memory", cost: 2, description: "You never forget anything." },
                    { name: "Iron Will", cost: 3, description: "You are resistant to mental influence." },
                    { name: "Linguist", cost: 2, description: "You learn languages quickly." }
                ]
            },
            "Flaws": {
                "Physical": [
                    { name: "Blind", cost: -3, description: "You cannot see." },
                    { name: "Deaf", cost: -2, description: "You cannot hear." },
                    { name: "Mute", cost: -2, description: "You cannot speak." }
                ],
                "Social": [
                    { name: "Enemy", cost: "Variable", description: "You have a powerful enemy." },
                    { name: "Infamy", cost: "Variable", description: "You are known for bad deeds." },
                    { name: "Notoriety", cost: "Variable", description: "You are infamous." }
                ],
                "Mental": [
                    { name: "Amnesia", cost: -2, description: "You have lost your memory." },
                    { name: "Nightmares", cost: -1, description: "You have terrible dreams." },
                    { name: "Phobia", cost: "Variable", description: "You have an irrational fear." }
                ]
            }
        };
    }
    
    /**
     * Setup event listeners for merits/flaws selection
     */
    setupEventListeners() {
        const { eventManager } = this;
        
        // Add merit/flaw buttons
        eventManager.addDelegatedListener(document, '.add-merit-flaw-btn', 'click', (e) => {
            this.handleAddMeritFlaw(e);
        });
        
        // Remove merit/flaw buttons
        eventManager.addDelegatedListener(document, '.remove-merit-flaw-btn', 'click', (e) => {
            this.handleRemoveMeritFlaw(e);
        });
        
        // Filter controls
        const filterContainer = this.uiManager.getElement('.merits-flaws-filters');
        if (filterContainer) {
            eventManager.addDelegatedListener(filterContainer, 'select', 'change', (e) => {
                this.handleFilterChange(e);
            });
            
            eventManager.addDelegatedListener(filterContainer, 'input', 'input', (e) => {
                this.handleSearchInput(e);
            });
        }
        
        // Variable cost inputs
        eventManager.addDelegatedListener(document, '.variable-cost-input', 'change', (e) => {
            this.handleVariableCostChange(e);
        });
        
        // Description buttons
        eventManager.addDelegatedListener(document, '.merit-flaw-description-btn', 'click', (e) => {
            this.handleDescriptionClick(e);
        });
    }
    
    /**
     * Handle add merit/flaw click
     */
    handleAddMeritFlaw(event) {
        const button = event.target;
        const name = button.dataset.name;
        const type = button.dataset.type;
        const category = button.dataset.category;
        const cost = button.dataset.cost;
        
        if (!name || !type || !category) return;
        
        this.addMeritFlaw(name, type, category, cost);
    }
    
    /**
     * Handle remove merit/flaw click
     */
    handleRemoveMeritFlaw(event) {
        const button = event.target;
        const name = button.dataset.name;
        const type = button.dataset.type;
        
        if (!name || !type) return;
        
        this.removeMeritFlaw(name, type);
    }
    
    /**
     * Handle filter change
     */
    handleFilterChange(event) {
        const select = event.target;
        const filterType = select.dataset.filter;
        const value = select.value;
        
        this.currentFilters[filterType] = value;
        this.updateAvailableList();
    }
    
    /**
     * Handle search input
     */
    handleSearchInput(event) {
        const input = event.target;
        const value = input.value;
        
        this.currentFilters.search = value;
        this.updateAvailableList();
    }
    
    /**
     * Handle variable cost change
     */
    handleVariableCostChange(event) {
        const input = event.target;
        const name = input.dataset.name;
        const type = input.dataset.type;
        const cost = parseInt(input.value) || 0;
        
        this.updateVariableCost(name, type, cost);
    }
    
    /**
     * Handle description click
     */
    handleDescriptionClick(event) {
        const button = event.target;
        const name = button.dataset.name;
        const type = button.dataset.type;
        
        this.showDescription(name, type);
    }
    
    /**
     * Add a merit or flaw
     */
    addMeritFlaw(name, type, category, cost) {
        const state = this.stateManager.getState();
        const selectedMeritsFlaws = [...state.selectedMeritsFlaws];
        
        // Check for conflicts
        const conflicts = this.checkConflicts(name, type, selectedMeritsFlaws);
        if (conflicts.length > 0) {
            this.showConflictWarning(conflicts);
            return;
        }
        
        // Add merit/flaw to the list
        selectedMeritsFlaws.push({
            name,
            type,
            category,
            cost: this.parseCost(cost),
            variableCost: this.parseCost(cost) === 0 ? 0 : null
        });
        
        // Update state
        this.stateManager.setState({
            selectedMeritsFlaws: selectedMeritsFlaws
        });
        
        // Update displays
        this.updateSelectedList();
        this.updateAvailableList();
        this.updateSummary();
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Show feedback
        this.notificationManager.toast(`${name} added to ${type}`);
    }
    
    /**
     * Remove a merit or flaw
     */
    removeMeritFlaw(name, type) {
        const state = this.stateManager.getState();
        const selectedMeritsFlaws = [...state.selectedMeritsFlaws];
        
        // Remove merit/flaw from the list
        const index = selectedMeritsFlaws.findIndex(item => 
            item.name === name && item.type === type
        );
        
        if (index > -1) {
            selectedMeritsFlaws.splice(index, 1);
        }
        
        // Update state
        this.stateManager.setState({
            selectedMeritsFlaws: selectedMeritsFlaws
        });
        
        // Update displays
        this.updateSelectedList();
        this.updateAvailableList();
        this.updateSummary();
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Show feedback
        this.notificationManager.toast(`${name} removed from ${type}`);
    }
    
    /**
     * Update variable cost
     */
    updateVariableCost(name, type, cost) {
        const state = this.stateManager.getState();
        const selectedMeritsFlaws = [...state.selectedMeritsFlaws];
        
        // Find and update the merit/flaw
        const item = selectedMeritsFlaws.find(item => 
            item.name === name && item.type === type
        );
        
        if (item) {
            item.variableCost = cost;
        }
        
        // Update state
        this.stateManager.setState({
            selectedMeritsFlaws: selectedMeritsFlaws
        });
        
        // Update displays
        this.updateSelectedList();
        this.updateSummary();
        
        // Update character preview
        this.updateCharacterPreview();
    }
    
    /**
     * Show description
     */
    showDescription(name, type) {
        const item = this.findMeritFlawData(name, type);
        if (item) {
            this.notificationManager.info(`${name}: ${item.description}`);
        }
    }
    
    /**
     * Update selected list display
     */
    updateSelectedList() {
        const state = this.stateManager.getState();
        const selectedMeritsFlaws = state.selectedMeritsFlaws;
        const listElement = this.uiManager.getElement('#selectedMeritsFlawsList');
        
        if (!listElement) return;
        
        // Group by type
        const grouped = {
            Merits: selectedMeritsFlaws.filter(item => item.type === 'Merits'),
            Flaws: selectedMeritsFlaws.filter(item => item.type === 'Flaws')
        };
        
        // Create display elements
        const listHTML = Object.keys(grouped).map(type => {
            const items = grouped[type];
            if (items.length === 0) return '';
            
            const itemsHTML = items.map(item => {
                const cost = item.variableCost !== null ? item.variableCost : item.cost;
                const costDisplay = cost > 0 ? `+${cost}` : cost.toString();
                
                return `
                    <div class="selected-merit-flaw ${type.toLowerCase()}">
                        <span class="merit-flaw-name">${item.name}</span>
                        <span class="merit-flaw-cost">${costDisplay}</span>
                        ${item.variableCost !== null ? `
                            <input type="number" class="variable-cost-input" 
                                   data-name="${item.name}" 
                                   data-type="${item.type}"
                                   value="${item.variableCost}" 
                                   min="1" max="10">
                        ` : ''}
                        <button type="button" class="remove-merit-flaw-btn" 
                                data-name="${item.name}" 
                                data-type="${item.type}">×</button>
                    </div>
                `;
            }).join('');
            
            return `
                <div class="merit-flaw-type-section">
                    <h4>${type}</h4>
                    <div class="merit-flaw-items">
                        ${itemsHTML}
                    </div>
                </div>
            `;
        }).join('');
        
        this.uiManager.updateContent(listElement, listHTML);
    }
    
    /**
     * Update available list display
     */
    updateAvailableList() {
        const listElement = this.uiManager.getElement('#availableMeritsFlawsList');
        if (!listElement) return;
        
        // Filter items based on current filters
        const filteredItems = this.getFilteredItems();
        
        // Group by category
        const grouped = {};
        filteredItems.forEach(item => {
            if (!grouped[item.category]) {
                grouped[item.category] = [];
            }
            grouped[item.category].push(item);
        });
        
        // Create display elements
        const listHTML = Object.keys(grouped).map(category => {
            const items = grouped[category];
            const itemsHTML = items.map(item => {
                const isSelected = this.isMeritFlawSelected(item.name, item.type);
                const costDisplay = item.cost > 0 ? `+${item.cost}` : item.cost.toString();
                
                return `
                    <div class="merit-flaw-option ${isSelected ? 'selected' : ''}">
                        <button type="button" class="add-merit-flaw-btn" 
                                data-name="${item.name}" 
                                data-type="${item.type}" 
                                data-category="${item.category}" 
                                data-cost="${item.cost}"
                                ${isSelected ? 'disabled' : ''}>
                            ${item.name} (${costDisplay})
                        </button>
                        <button type="button" class="merit-flaw-description-btn" 
                                data-name="${item.name}" 
                                data-type="${item.type}">?</button>
                    </div>
                `;
            }).join('');
            
            return `
                <div class="merit-flaw-category-section">
                    <h4>${category}</h4>
                    <div class="merit-flaw-options">
                        ${itemsHTML}
                    </div>
                </div>
            `;
        }).join('');
        
        this.uiManager.updateContent(listElement, listHTML);
    }
    
    /**
     * Update summary display
     */
    updateSummary() {
        const state = this.stateManager.getState();
        const selectedMeritsFlaws = state.selectedMeritsFlaws;
        
        // Calculate total cost
        const totalCost = selectedMeritsFlaws.reduce((total, item) => {
            const cost = item.variableCost !== null ? item.variableCost : item.cost;
            return total + cost;
        }, 0);
        
        // Update total cost display
        const totalElement = this.uiManager.getElement('#meritsFlawsTotal');
        if (totalElement) {
            this.uiManager.updateContent(totalElement, totalCost.toString());
        }
        
        // Update count displays
        const meritsCount = selectedMeritsFlaws.filter(item => item.type === 'Merits').length;
        const flawsCount = selectedMeritsFlaws.filter(item => item.type === 'Flaws').length;
        
        const meritsCountElement = this.uiManager.getElement('#meritsCount');
        if (meritsCountElement) {
            this.uiManager.updateContent(meritsCountElement, meritsCount.toString());
        }
        
        const flawsCountElement = this.uiManager.getElement('#flawsCount');
        if (flawsCountElement) {
            this.uiManager.updateContent(flawsCountElement, flawsCount.toString());
        }
    }
    
    /**
     * Get filtered items
     */
    getFilteredItems() {
        if (!this.meritsFlawsData) return [];
        
        let items = [];
        
        // Flatten all items
        Object.keys(this.meritsFlawsData).forEach(type => {
            Object.keys(this.meritsFlawsData[type]).forEach(category => {
                this.meritsFlawsData[type][category].forEach(item => {
                    items.push({
                        ...item,
                        type,
                        category
                    });
                });
            });
        });
        
        // Apply filters
        if (this.currentFilters.category !== 'all') {
            items = items.filter(item => item.category === this.currentFilters.category);
        }
        
        if (this.currentFilters.type !== 'all') {
            items = items.filter(item => item.type === this.currentFilters.type);
        }
        
        if (this.currentFilters.cost !== 'all') {
            const costFilter = parseInt(this.currentFilters.cost);
            items = items.filter(item => item.cost === costFilter);
        }
        
        if (this.currentFilters.search) {
            const searchTerm = this.currentFilters.search.toLowerCase();
            items = items.filter(item => 
                item.name.toLowerCase().includes(searchTerm) ||
                item.description.toLowerCase().includes(searchTerm)
            );
        }
        
        return items;
    }
    
    /**
     * Check for conflicts
     */
    checkConflicts(name, type, selectedMeritsFlaws) {
        const conflicts = [];
        
        // Check for duplicate
        const isDuplicate = selectedMeritsFlaws.some(item => 
            item.name === name && item.type === type
        );
        
        if (isDuplicate) {
            conflicts.push(`${name} is already selected`);
        }
        
        // Add more conflict checking logic here
        // For example, checking for mutually exclusive items
        
        return conflicts;
    }
    
    /**
     * Show conflict warning
     */
    showConflictWarning(conflicts) {
        const message = conflicts.join(', ');
        this.notificationManager.warning(`Cannot add: ${message}`);
    }
    
    /**
     * Parse cost string to number
     */
    parseCost(cost) {
        if (typeof cost === 'number') return cost;
        if (typeof cost === 'string') {
            if (cost === 'Variable') return 0;
            return parseInt(cost) || 0;
        }
        return 0;
    }
    
    /**
     * Find merit/flaw data
     */
    findMeritFlawData(name, type) {
        if (!this.meritsFlawsData || !this.meritsFlawsData[type]) return null;
        
        for (const category of Object.keys(this.meritsFlawsData[type])) {
            const item = this.meritsFlawsData[type][category].find(item => item.name === name);
            if (item) return item;
        }
        
        return null;
    }
    
    /**
     * Check if merit/flaw is selected
     */
    isMeritFlawSelected(name, type) {
        const state = this.stateManager.getState();
        return state.selectedMeritsFlaws.some(item => 
            item.name === name && item.type === type
        );
    }
    
    /**
     * Update character preview
     */
    updateCharacterPreview() {
        console.log('MeritsFlawsSystem: Character preview updated');
    }
    
    /**
     * Update all displays
     */
    updateAllDisplays() {
        this.updateSelectedList();
        this.updateAvailableList();
        this.updateSummary();
    }
    
    /**
     * Validate merits/flaws selection
     */
    validateMeritsFlaws() {
        const state = this.stateManager.getState();
        const selectedMeritsFlaws = state.selectedMeritsFlaws;
        
        // Calculate total cost
        const totalCost = selectedMeritsFlaws.reduce((total, item) => {
            const cost = item.variableCost !== null ? item.variableCost : item.cost;
            return total + cost;
        }, 0);
        
        const errors = [];
        
        // Check if total cost is reasonable (not too high or too low)
        if (totalCost > 10) {
            errors.push(`Total merits/flaws cost (${totalCost}) is too high`);
        }
        
        if (totalCost < -10) {
            errors.push(`Total merits/flaws cost (${totalCost}) is too low`);
        }
        
        return {
            isValid: errors.length === 0,
            errors,
            totalCost
        };
    }
    
    /**
     * Get merits/flaws statistics
     */
    getMeritsFlawsStats() {
        const state = this.stateManager.getState();
        const selectedMeritsFlaws = state.selectedMeritsFlaws;
        
        const merits = selectedMeritsFlaws.filter(item => item.type === 'Merits');
        const flaws = selectedMeritsFlaws.filter(item => item.type === 'Flaws');
        
        const totalCost = selectedMeritsFlaws.reduce((total, item) => {
            const cost = item.variableCost !== null ? item.variableCost : item.cost;
            return total + cost;
        }, 0);
        
        return {
            merits: merits.length,
            flaws: flaws.length,
            total: selectedMeritsFlaws.length,
            totalCost
        };
    }
    
    /**
     * Reset all merits/flaws
     */
    resetAll() {
        this.stateManager.setState({
            selectedMeritsFlaws: []
        });
        
        this.updateAllDisplays();
    }
}

// Export for use in other modules
window.MeritsFlawsSystem = MeritsFlawsSystem;
