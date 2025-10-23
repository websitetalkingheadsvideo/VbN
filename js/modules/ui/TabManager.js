/**
 * TabManager.js - Handles tab navigation and progress tracking
 * Manages tab switching, progress indicators, and navigation state
 */

class TabManager {
    constructor(stateManager, uiManager, eventManager, notificationManager) {
        this.stateManager = stateManager;
        this.uiManager = uiManager;
        this.eventManager = eventManager;
        this.notificationManager = notificationManager;
        
        this.tabs = [
            { id: 'basic', name: 'Basic Info', required: true },
            { id: 'traits', name: 'Traits', required: true },
            { id: 'abilities', name: 'Abilities', required: true },
            { id: 'disciplines', name: 'Disciplines', required: true },
            { id: 'backgrounds', name: 'Backgrounds', required: true },
            { id: 'morality', name: 'Morality', required: true },
            { id: 'merits', name: 'Merits & Flaws', required: false },
            { id: 'review', name: 'Review', required: true }
        ];
        
        this.currentTab = 'basic';
        this.tabProgress = new Map();
        this.tabValidation = new Map();
        
        this.init();
    }
    
    /**
     * Initialize the tab manager
     */
    init() {
        this.setupEventListeners();
        this.updateAllTabs();
        this.showTab(this.currentTab);
    }
    
    /**
     * Setup event listeners for tab navigation
     */
    setupEventListeners() {
        const { eventManager } = this;
        
        // Tab click handlers
        eventManager.addDelegatedListener(document, '.tab-btn', 'click', (e) => {
            this.handleTabClick(e);
        });
        
        // Navigation buttons
        eventManager.addDelegatedListener(document, '.nav-btn', 'click', (e) => {
            this.handleNavigationClick(e);
        });
        
        // Keyboard navigation
        eventManager.addListener(document, 'keydown', (e) => {
            this.handleKeyboardNavigation(e);
        });
        
        // Listen for state changes
        eventManager.onCustomEvent('stateChanged', (event) => {
            this.updateAllTabs();
        });
    }
    
    /**
     * Handle tab click
     */
    handleTabClick(event) {
        const button = event.target;
        const tabId = button.dataset.tab;
        
        
        if (tabId && this.canAccessTab(tabId)) {
            this.showTab(tabId);
        } else if (tabId) {
            this.notificationManager.warning('Please complete the current tab before proceeding');
        }
    }
    
    /**
     * Handle navigation button click
     */
    handleNavigationClick(event) {
        const button = event.target;
        const action = button.dataset.action;
        
        if (action === 'next') {
            this.nextTab();
        } else if (action === 'previous') {
            this.previousTab();
        } else if (action === 'first') {
            this.firstTab();
        } else if (action === 'last') {
            this.lastTab();
        }
    }
    
    /**
     * Handle keyboard navigation
     */
    handleKeyboardNavigation(event) {
        if (event.ctrlKey || event.metaKey) {
            if (event.key === 'ArrowRight') {
                event.preventDefault();
                this.nextTab();
            } else if (event.key === 'ArrowLeft') {
                event.preventDefault();
                this.previousTab();
            }
        }
    }
    
    /**
     * Show specific tab
     */
    showTab(tabId) {
        
        if (!this.tabs.find(tab => tab.id === tabId)) {
            console.error(`Tab ${tabId} not found`);
            return;
        }
        
        // Hide all tab content
        this.hideAllTabs();
        
        // Show selected tab content
        const tabContent = this.uiManager.getElement(`#${tabId}Tab`);
        if (tabContent) {
            // Add active class for CSS visibility
            tabContent.classList.add('active');
            this.uiManager.show(tabContent, 'fadeIn');
        } else {
            console.error('Tab content not found:', `#${tabId}Tab`);
        }
        
        // Update tab buttons
        this.updateTabButtons(tabId);
        
        // Update current tab
        this.currentTab = tabId;
        
        // Update state
        this.stateManager.setStateProperty('currentTab', tabId);
        
        // Emit tab change event
        this.eventManager.emitGlobal('tabChanged', { tabId, previousTab: this.currentTab });
        
        // Update navigation buttons
        this.updateNavigationButtons();

        // Update overall progress bar based on current tab position
        this.updateOverallProgressBar();
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    /**
     * Hide all tabs
     */
    hideAllTabs() {
        this.tabs.forEach(tab => {
            const tabContent = this.uiManager.getElement(`#${tab.id}Tab`);
            if (tabContent) {
                // Remove active class for CSS visibility
                tabContent.classList.remove('active');
                this.uiManager.hide(tabContent);
            }
        });
    }
    
    /**
     * Update tab buttons
     */
    updateTabButtons(activeTabId) {
        this.tabs.forEach(tab => {
            const button = this.uiManager.getElement(`[data-tab="${tab.id}"]`);
            if (button) {
                this.uiManager.updateClasses(button, {
                    'active': tab.id === activeTabId,
                    'completed': this.isTabCompleted(tab.id),
                    'incomplete': !this.isTabCompleted(tab.id) && tab.required
                });
            }
        });
    }
    
    /**
     * Update navigation buttons
     */
    updateNavigationButtons() {
        const currentIndex = this.tabs.findIndex(tab => tab.id === this.currentTab);
        
        // Previous button
        const prevButton = this.uiManager.getElement('[data-action="previous"]');
        if (prevButton) {
            prevButton.disabled = currentIndex === 0;
            this.uiManager.updateClasses(prevButton, {
                'disabled': currentIndex === 0
            });
        }
        
        // Next button
        const nextButton = this.uiManager.getElement('[data-action="next"]');
        if (nextButton) {
            const canProceed = this.canProceedToNext();
            nextButton.disabled = !canProceed;
            this.uiManager.updateClasses(nextButton, {
                'disabled': !canProceed
            });
        }
        
        // First button
        const firstButton = this.uiManager.getElement('[data-action="first"]');
        if (firstButton) {
            firstButton.disabled = currentIndex === 0;
            this.uiManager.updateClasses(firstButton, {
                'disabled': currentIndex === 0
            });
        }
        
        // Last button
        const lastButton = this.uiManager.getElement('[data-action="last"]');
        if (lastButton) {
            lastButton.disabled = currentIndex === this.tabs.length - 1;
            this.uiManager.updateClasses(lastButton, {
                'disabled': currentIndex === this.tabs.length - 1
            });
        }
    }
    
    /**
     * Next tab
     */
    nextTab() {
        const currentIndex = this.tabs.findIndex(tab => tab.id === this.currentTab);
        if (currentIndex < this.tabs.length - 1) {
            const nextTab = this.tabs[currentIndex + 1];
            if (this.canAccessTab(nextTab.id)) {
                this.showTab(nextTab.id);
            } else {
                this.notificationManager.warning('Please complete the current tab before proceeding');
            }
        }
    }
    
    /**
     * Previous tab
     */
    previousTab() {
        const currentIndex = this.tabs.findIndex(tab => tab.id === this.currentTab);
        if (currentIndex > 0) {
            const prevTab = this.tabs[currentIndex - 1];
            this.showTab(prevTab.id);
        }
    }
    
    /**
     * First tab
     */
    firstTab() {
        this.showTab(this.tabs[0].id);
    }
    
    /**
     * Last tab
     */
    lastTab() {
        const lastTab = this.tabs[this.tabs.length - 1];
        if (this.canAccessTab(lastTab.id)) {
            this.showTab(lastTab.id);
        } else {
            this.notificationManager.warning('Please complete all required tabs before proceeding');
        }
    }
    
    /**
     * Check if tab can be accessed
     */
    canAccessTab(tabId) {
        // Temporarily allow all tab access for testing
        return true;
        
        // Original logic (commented out for testing):
        // const tabIndex = this.tabs.findIndex(tab => tab.id === tabId);
        // const currentIndex = this.tabs.findIndex(tab => tab.id === this.currentTab);
        // 
        // // Can always go back
        // if (tabIndex < currentIndex) {
        //     return true;
        // }
        // 
        // // Can only go forward if current tab is completed
        // if (tabIndex > currentIndex) {
        //     return this.isTabCompleted(this.currentTab);
        // }
        // 
        // return true;
    }
    
    /**
     * Check if can proceed to next tab
     */
    canProceedToNext() {
        return this.isTabCompleted(this.currentTab);
    }
    
    /**
     * Check if tab is completed
     */
    isTabCompleted(tabId) {
        const tab = this.tabs.find(t => t.id === tabId);
        if (!tab || !tab.required) return true;
        
        const state = this.stateManager.getState();
        
        switch (tabId) {
            case 'basic':
                return !!(state.characterName && state.playerName && state.clan);
            
            
            case 'traits':
                return this.validateTraits(state.traits);
            
            case 'abilities':
                return this.validateAbilities(state.abilities);
            
            case 'disciplines':
                return state.disciplines && state.disciplines.length > 0;
            
            case 'backgrounds':
                return this.validateBackgrounds(state.backgrounds);
            
            case 'merits':
                return true; // Optional tab
            
            case 'morality':
                return this.validateMorality(state.virtues, state.humanity);
            
            case 'review':
                return this.validateAllTabs();
            
            default:
                return false;
        }
    }
    
    
    /**
     * Validate traits
     */
    validateTraits(traits) {
        if (!traits) return false;
        
        const requirements = {
            Physical: 7,
            Social: 5,
            Mental: 3
        };
        
        return Object.keys(requirements).every(category => {
            const count = traits[category] ? traits[category].length : 0;
            return count >= requirements[category];
        });
    }
    
    /**
     * Validate abilities
     */
    validateAbilities(abilities) {
        if (!abilities) return false;
        
        const requirements = {
            Physical: 3,
            Social: 3,
            Mental: 3
        };
        
        return Object.keys(requirements).every(category => {
            const count = abilities[category] ? abilities[category].length : 0;
            return count >= requirements[category];
        });
    }
    
    /**
     * Validate backgrounds
     */
    validateBackgrounds(backgrounds) {
        if (!backgrounds) return false;
        
        const totalPoints = Object.values(backgrounds).reduce((total, level) => total + level, 0);
        return totalPoints >= 1;
    }
    
    /**
     * Validate morality
     */
    validateMorality(virtues, humanity) {
        if (!virtues || !humanity) return false;
        
        return virtues.Conscience >= 1 && virtues.Conscience <= 5 &&
               virtues.SelfControl >= 1 && virtues.SelfControl <= 5 &&
               humanity >= 0 && humanity <= 10;
    }
    
    /**
     * Validate all tabs
     */
    validateAllTabs() {
        const state = this.stateManager.getState();
        
        return this.isTabCompleted('basic') &&
               this.isTabCompleted('attributes') &&
               this.isTabCompleted('traits') &&
               this.isTabCompleted('abilities') &&
               this.isTabCompleted('disciplines') &&
               this.isTabCompleted('backgrounds') &&
               this.isTabCompleted('morality');
    }
    
    /**
     * Update all tabs
     */
    updateAllTabs() {
        this.tabs.forEach(tab => {
            this.updateTabProgress(tab.id);
        });
        
        this.updateTabButtons(this.currentTab);
        this.updateNavigationButtons();
        this.updateOverallProgressBar();
    }
    
    /**
     * Update tab progress
     */
    updateTabProgress(tabId) {
        const isCompleted = this.isTabCompleted(tabId);
        this.tabProgress.set(tabId, isCompleted);
        
        // Update progress indicator
        const progressElement = this.uiManager.getElement(`#${tabId}Progress`);
        if (progressElement) {
            this.uiManager.updateClasses(progressElement, {
                'completed': isCompleted,
                'incomplete': !isCompleted
            });
        }
    }
    
    /**
     * Get current tab
     */
    getCurrentTab() {
        return this.currentTab;
    }
    
    /**
     * Get tab by ID
     */
    getTab(tabId) {
        return this.tabs.find(tab => tab.id === tabId);
    }
    
    /**
     * Get all tabs
     */
    getAllTabs() {
        return [...this.tabs];
    }
    
    /**
     * Get tab progress
     */
    getTabProgress() {
        return new Map(this.tabProgress);
    }
    
    /**
     * Get completion percentage
     */
    getCompletionPercentage() {
        const completedTabs = Array.from(this.tabProgress.values()).filter(Boolean).length;
        const totalTabs = this.tabs.length;
        return Math.round((completedTabs / totalTabs) * 100);
    }
    
    /**
     * Get next incomplete tab
     */
    getNextIncompleteTab() {
        return this.tabs.find(tab => !this.isTabCompleted(tab.id));
    }
    
    /**
     * Get tab statistics
     */
    getTabStats() {
        const completed = Array.from(this.tabProgress.values()).filter(Boolean).length;
        const total = this.tabs.length;
        const percentage = Math.round((completed / total) * 100);
        
        return {
            currentTab: this.currentTab,
            completedTabs: completed,
            totalTabs: total,
            completionPercentage: percentage,
            nextIncompleteTab: this.getNextIncompleteTab()?.id
        };
    }

    /**
     * Update the overall top progress bar (by index position)
     */
    updateOverallProgressBar() {
        const progressBar = this.uiManager.getElement('#tabProgressBar');
        if (!progressBar) return;
        const currentIndex = this.tabs.findIndex(tab => tab.id === this.currentTab);
        const totalTabs = this.tabs.length;
        if (currentIndex < 0 || totalTabs <= 0) return;
        const pct = ((currentIndex + 1) / totalTabs) * 100;
        progressBar.style.width = pct.toFixed(2) + '%';
    }
}

// Export for use in other modules
window.TabManager = TabManager;
