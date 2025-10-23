/**
 * main.js - Main application entry point
 * Initializes all modules and coordinates the character creation system
 */

class CharacterCreationApp {
    constructor() {
        this.modules = {};
        this.isInitialized = false;
        this.init();
    }
    
    /**
     * Initialize the application
     */
    async init() {
        try {
            console.log('Initializing Character Creation App...');
            
            // Initialize core modules
            await this.initializeCoreModules();
            
            // Initialize UI modules
            await this.initializeUIModules();
            
            // Initialize system modules
            await this.initializeSystemModules();
            
            // Setup module communication
            this.setupModuleCommunication();
            
            // Setup global event handlers
            this.setupGlobalEventHandlers();
            
            // Initialize the application
            await this.initializeApplication();
            
            this.isInitialized = true;
            console.log('Character Creation App initialized successfully!');
            
            // Emit initialization complete event
            this.modules.eventManager.emitGlobal('appInitialized', {});
            
        } catch (error) {
            console.error('Failed to initialize Character Creation App:', error);
            this.handleInitializationError(error);
        }
    }
    
    /**
     * Initialize core modules
     */
    async initializeCoreModules() {
        console.log('Initializing core modules...');
        
        // StateManager
        this.modules.stateManager = new StateManager();
        
        // UIManager
        this.modules.uiManager = new UIManager();
        
        // EventManager
        this.modules.eventManager = new EventManager();
        
        // DataManager
        this.modules.dataManager = new DataManager();
        
        // NotificationManager
        this.modules.notificationManager = new NotificationManager(this.modules.uiManager);
        
        // ValidationManager
        this.modules.validationManager = new ValidationManager(this.modules.notificationManager);
        
        console.log('Core modules initialized');
    }
    
    /**
     * Initialize UI modules
     */
    async initializeUIModules() {
        console.log('Initializing UI modules...');
        
        // TabManager
        this.modules.tabManager = new TabManager(
            this.modules.stateManager,
            this.modules.uiManager,
            this.modules.eventManager,
            this.modules.notificationManager
        );
        
        // PreviewManager
        this.modules.previewManager = new PreviewManager(
            this.modules.stateManager,
            this.modules.uiManager,
            this.modules.eventManager,
            this.modules.notificationManager
        );
        
        console.log('UI modules initialized');
    }
    
    /**
     * Initialize system modules
     */
    async initializeSystemModules() {
        console.log('Initializing system modules...');
        
        // TraitSystem
        this.modules.traitSystem = new TraitSystem(
            this.modules.stateManager,
            this.modules.uiManager,
            this.modules.eventManager
        );
        
        // AbilitySystem
        this.modules.abilitySystem = new AbilitySystem(
            this.modules.stateManager,
            this.modules.uiManager,
            this.modules.eventManager
        );
        
        // DisciplineSystem
        this.modules.disciplineSystem = new DisciplineSystem(
            this.modules.stateManager,
            this.modules.uiManager,
            this.modules.eventManager,
            this.modules.dataManager
        );
        
        // MeritsFlawsSystem
        this.modules.meritsFlawsSystem = new MeritsFlawsSystem(
            this.modules.stateManager,
            this.modules.uiManager,
            this.modules.eventManager
        );
        
        // BackgroundSystem
        this.modules.backgroundSystem = new BackgroundSystem(
            this.modules.stateManager,
            this.modules.uiManager,
            this.modules.eventManager
        );
        
        // MoralitySystem
        this.modules.moralitySystem = new MoralitySystem(
            this.modules.stateManager,
            this.modules.uiManager,
            this.modules.eventManager
        );
        
        // CashSystem
        this.modules.cashSystem = new CashSystem(
            this.modules.stateManager,
            this.modules.uiManager,
            this.modules.eventManager
        );
        
        // HealthWillpowerSystem
        this.modules.healthWillpowerSystem = new HealthWillpowerSystem(
            this.modules.stateManager,
            this.modules.uiManager,
            this.modules.eventManager
        );
        
        console.log('System modules initialized');
    }
    
    /**
     * Setup module communication
     */
    setupModuleCommunication() {
        console.log('Setting up module communication...');
        
        // State change events
        this.modules.stateManager.subscribe('*', (newState, oldState) => {
            this.modules.eventManager.emitGlobal('stateChanged', { newState, oldState });
        });
        
        // Specific state change events
        this.modules.stateManager.subscribe('characterName', (newValue) => {
            this.modules.eventManager.emitGlobal('characterNameChanged', { value: newValue });
        });
        
        this.modules.stateManager.subscribe('attributes', (newValue) => {
            this.modules.eventManager.emitGlobal('attributesChanged', { value: newValue });
        });
        
        this.modules.stateManager.subscribe('traits', (newValue) => {
            this.modules.eventManager.emitGlobal('traitsChanged', { value: newValue });
        });
        
        this.modules.stateManager.subscribe('abilities', (newValue) => {
            this.modules.eventManager.emitGlobal('abilitiesChanged', { value: newValue });
        });
        
        this.modules.stateManager.subscribe('disciplines', (newValue) => {
            this.modules.eventManager.emitGlobal('disciplinesChanged', { value: newValue });
        });
        
        this.modules.stateManager.subscribe('backgrounds', (newValue) => {
            this.modules.eventManager.emitGlobal('backgroundsChanged', { value: newValue });
        });
        
        this.modules.stateManager.subscribe('selectedMeritsFlaws', (newValue) => {
            this.modules.eventManager.emitGlobal('meritsFlawsChanged', { value: newValue });
        });
        
        this.modules.stateManager.subscribe('virtues', (newValue) => {
            this.modules.eventManager.emitGlobal('moralityChanged', { value: newValue });
        });
        
        this.modules.stateManager.subscribe('humanity', (newValue) => {
            this.modules.eventManager.emitGlobal('moralityChanged', { value: newValue });
        });
        
        console.log('Module communication setup complete');
    }
    
    /**
     * Setup global event handlers
     */
    setupGlobalEventHandlers() {
        console.log('Setting up global event handlers...');
        
        // Save character button clicks
        this.modules.eventManager.addDelegatedListener(document, '.save-btn', 'click', async (e) => {
            e.preventDefault();
            await this.saveCharacter();
        });
        
        // Help button clicks
        this.modules.eventManager.addDelegatedListener(document, '.help-btn', 'click', (e) => {
            e.preventDefault();
            const action = e.target.closest('.help-btn').dataset.action;
            this.handleHelpButton(action);
        });
        
        // Finalize character button
        this.modules.eventManager.addDelegatedListener(document, '.finalize-btn', 'click', (e) => {
            e.preventDefault();
            this.handleFinalizeCharacter();
        });
        
        // Sheet mode toggle
        this.modules.eventManager.addDelegatedListener(document, 'input[name="sheetMode"]', 'change', (e) => {
            this.handleSheetModeChange(e.target.value);
        });
        
        // Clan selection
        this.modules.eventManager.addDelegatedListener(document, '#clan', 'change', (e) => {
            this.handleClanChange(e.target.value);
        });
        
        // Merits & Flaws filters
        this.modules.eventManager.addDelegatedListener(document, '[data-action="filter-merits-flaws"]', 'change', (e) => {
            this.handleMeritsFlawsFilter();
        });
        
        // Save character
        this.modules.eventManager.onCustomEvent('saveCharacter', async (event) => {
            await this.saveCharacter();
        });
        
        // Load character
        this.modules.eventManager.onCustomEvent('loadCharacter', async (event) => {
            await this.loadCharacter(event.detail.characterId);
        });
        
        // Reset character
        this.modules.eventManager.onCustomEvent('resetCharacter', async (event) => {
            await this.resetCharacter();
        });
        
        // Export character
        this.modules.eventManager.onCustomEvent('exportCharacter', async (event) => {
            await this.exportCharacter();
        });
        
        // Import character
        this.modules.eventManager.onCustomEvent('importCharacter', async (event) => {
            await this.importCharacter(event.detail.data);
        });
        
        // Validate character
        this.modules.eventManager.onCustomEvent('validateCharacter', async (event) => {
            await this.validateCharacter();
        });
        
        console.log('Global event handlers setup complete');
    }
    
    /**
     * Initialize the application
     */
    async initializeApplication() {
        console.log('Initializing application...');
        
        // Only resume saved state if explicitly requested via ?resume=1
        const urlParams = new URLSearchParams(window.location.search);
        const resume = urlParams.get('resume');
        if (resume === '1') {
            const hasSavedState = this.modules.stateManager.loadState();
            if (hasSavedState) {
                console.log('Resumed saved state');
            }
        } else {
            // Start with a fresh state for new character creation
            this.modules.stateManager.reset();
        }
        
        // Initialize basic info tab
        this.initializeBasicInfoTab();
        
        // Setup form validation
        this.setupFormValidation();
        
        // Setup auto-save
        this.setupAutoSave();
        
        console.log('Application initialization complete');
    }
    
    /**
     * Initialize basic info tab
     */
    initializeBasicInfoTab() {
        const state = this.modules.stateManager.getState();
        
        // Set up form fields
        const characterNameInput = this.modules.uiManager.getElement('#characterName');
        if (characterNameInput) {
            characterNameInput.value = state.characterName || '';
            characterNameInput.addEventListener('input', (e) => {
                this.modules.stateManager.setStateProperty('characterName', e.target.value);
            });
        }
        
        const playerNameInput = this.modules.uiManager.getElement('#playerName');
        if (playerNameInput) {
            playerNameInput.value = state.playerName || '';
            playerNameInput.addEventListener('input', (e) => {
                this.modules.stateManager.setStateProperty('playerName', e.target.value);
            });
        }
        
        const chronicleInput = this.modules.uiManager.getElement('#chronicle');
        if (chronicleInput) {
            chronicleInput.value = state.chronicle || '';
            chronicleInput.addEventListener('input', (e) => {
                this.modules.stateManager.setStateProperty('chronicle', e.target.value);
            });
        }
        
        const conceptInput = this.modules.uiManager.getElement('#concept');
        if (conceptInput) {
            conceptInput.value = state.concept || '';
            conceptInput.addEventListener('input', (e) => {
                this.modules.stateManager.setStateProperty('concept', e.target.value);
            });
        }
        
        const clanSelect = this.modules.uiManager.getElement('#clan');
        if (clanSelect) {
            clanSelect.value = state.clan || '';
            clanSelect.addEventListener('change', (e) => {
                this.modules.stateManager.setStateProperty('clan', e.target.value);
            });
        }
        
        const generationInput = this.modules.uiManager.getElement('#generation');
        if (generationInput) {
            generationInput.value = state.generation || 13;
            generationInput.addEventListener('change', (e) => {
                const generation = parseInt(e.target.value) || 13;
                this.modules.stateManager.setStateProperty('generation', generation);
            });
        }
    }
    
    /**
     * Setup form validation
     */
    setupFormValidation() {
        // Add form validation listeners
        const forms = this.modules.uiManager.getElements('form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.validateAndSubmitForm(form);
            });
        });
    }
    
    /**
     * Setup auto-save
     */
    setupAutoSave() {
        // Auto-save every 30 seconds
        setInterval(() => {
            if (this.modules.stateManager.getState().isDirty) {
                this.saveCharacter();
            }
        }, 30000);
        
        // Auto-save on page unload
        window.addEventListener('beforeunload', () => {
            if (this.modules.stateManager.getState().isDirty) {
                this.saveCharacter();
            }
        });
    }
    
    /**
     * Validate and submit form
     */
    async validateAndSubmitForm(form) {
        const formData = new FormData(form);
        const data = {};
        
        for (const [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        const validation = this.modules.validationManager.validateFields(data);
        
        if (validation.isValid) {
            // Update state with form data
            this.modules.stateManager.setState(data);
            console.log('CharacterCreationApp: Form submitted successfully');
        } else {
            console.error(`CharacterCreationApp: Form validation failed: ${validation.errors.join(', ')}`);
        }
    }
    
    /**
     * Save character
     */
    async saveCharacter() {
        try {
            const state = this.modules.stateManager.getState();
            const response = await this.modules.dataManager.saveCharacter(state);
            
            if (response.success) {
                console.log('CharacterCreationApp: Character saved successfully');
                this.modules.stateManager.setStateProperty('isDirty', false);
                this.modules.stateManager.setStateProperty('lastSaved', Date.now());
            } else {
                throw new Error(response.error || 'Failed to save character');
            }
        } catch (error) {
            console.error('Error saving character:', error);
            console.error('CharacterCreationApp: Failed to save character: ' + error.message);
        }
    }
    
    /**
     * Load character
     */
    async loadCharacter(characterId) {
        try {
            const characterData = await this.modules.dataManager.loadCharacter(characterId);
            
            if (characterData) {
                this.modules.stateManager.setState(characterData);
                console.log('CharacterCreationApp: Character loaded successfully');
            } else {
                throw new Error('Character not found');
            }
        } catch (error) {
            console.error('Error loading character:', error);
            console.error('CharacterCreationApp: Failed to load character: ' + error.message);
        }
    }
    
    /**
     * Reset character
     */
    async resetCharacter() {
        const confirmed = confirm('Are you sure you want to reset the character? All unsaved changes will be lost.');
        
        if (confirmed) {
            this.modules.stateManager.reset();
            console.log('CharacterCreationApp: Character reset successfully');
        }
    }
    
    /**
     * Export character
     */
    async exportCharacter() {
        try {
            const state = this.modules.stateManager.getState();
            const exportData = this.modules.stateManager.exportState();
            
            // Create download link
            const blob = new Blob([exportData], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${state.characterName || 'character'}.json`;
            a.click();
            
            URL.revokeObjectURL(url);
            
            console.log('CharacterCreationApp: Character exported successfully');
        } catch (error) {
            console.error('Error exporting character:', error);
            console.error('CharacterCreationApp: Failed to export character: ' + error.message);
        }
    }
    
    /**
     * Import character
     */
    async importCharacter(data) {
        try {
            const success = this.modules.stateManager.importState(data);
            
            if (success) {
                console.log('CharacterCreationApp: Character imported successfully');
            } else {
                throw new Error('Invalid character data');
            }
        } catch (error) {
            console.error('Error importing character:', error);
            console.error('CharacterCreationApp: Failed to import character: ' + error.message);
        }
    }
    
    /**
     * Validate character
     */
    async validateCharacter() {
        try {
            const state = this.modules.stateManager.getState();
            const validation = this.modules.validationManager.validateCharacter(state);
            
            if (validation.isValid) {
                console.log('CharacterCreationApp: Character is valid');
            } else {
                console.error(`CharacterCreationApp: Character validation failed: ${validation.errors.join(', ')}`);
            }
        } catch (error) {
            console.error('Error validating character:', error);
            console.error('CharacterCreationApp: Failed to validate character: ' + error.message);
        }
    }
    
    /**
     * Handle initialization error
     */
    handleInitializationError(error) {
        console.error('Initialization error:', error);
        
        // Show error notification
        console.error('CharacterCreationApp: Failed to initialize application: ' + error.message);
        alert('Failed to initialize application: ' + error.message);
    }
    
    /**
     * Handle help button clicks
     */
    handleHelpButton(action) {
        switch (action) {
            case 'show-clan-guide':
                this.showClanGuide();
                break;
            case 'show-discipline-guide':
                this.showDisciplineGuide();
                break;
            default:
                console.warn('Unknown help action:', action);
        }
    }
    
    /**
     * Handle finalize character button
     */
    handleFinalizeCharacter() {
        console.info('CharacterCreationApp: Finalize character functionality coming soon!');
    }
    
    /**
     * Handle sheet mode change
     */
    handleSheetModeChange(mode) {
        this.modules.stateManager.setStateProperty('sheetMode', mode);
        this.modules.previewManager?.updatePreviewMode(mode);
    }
    
    /**
     * Handle clan selection change
     */
    handleClanChange(clan) {
        this.modules.stateManager.setStateProperty('clan', clan);
        
        // Update disciplines based on clan selection
        if (this.modules.disciplineSystem) {
            this.modules.disciplineSystem.updateClanDisciplines(clan);
        }
        
        // Update character preview
        this.modules.previewManager?.updateCharacterPreview();
    }
    
    /**
     * Handle merits & flaws filter changes
     */
    handleMeritsFlawsFilter() {
        if (this.modules.meritsFlawsSystem) {
            this.modules.meritsFlawsSystem.filterMeritsFlaws();
        }
    }
    
    /**
     * Show clan guide
     */
    showClanGuide() {
        console.info('CharacterCreationApp: Clan guide functionality coming soon!');
    }
    
    /**
     * Show discipline guide
     */
    showDisciplineGuide() {
        console.info('CharacterCreationApp: Discipline guide functionality coming soon!');
    }
    
    /**
     * Get application statistics
     */
    getAppStats() {
        return {
            isInitialized: this.isInitialized,
            modules: Object.keys(this.modules),
            stateStats: this.modules.stateManager?.getStateStats(),
            eventStats: this.modules.eventManager?.getEventStats(),
            uiStats: this.modules.uiManager?.getUIStats(),
            dataStats: this.modules.dataManager?.getStats(),
            notificationStats: this.modules.notificationManager?.getStats(),
            validationStats: this.modules.validationManager?.getStats(),
            tabStats: this.modules.tabManager?.getTabStats(),
            previewStats: this.modules.previewManager?.getPreviewStats()
        };
    }
    
    /**
     * Get module by name
     */
    getModule(name) {
        return this.modules[name];
    }
    
    /**
     * Get all modules
     */
    getAllModules() {
        return { ...this.modules };
    }
}

// Initialize the application when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.characterCreationApp = new CharacterCreationApp();
});

// Export for use in other modules
window.CharacterCreationApp = CharacterCreationApp;