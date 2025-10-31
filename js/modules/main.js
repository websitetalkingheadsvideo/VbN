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
            this.modules.eventManager,
            this.modules.notificationManager
        );
        
        // AbilitySystem
        this.modules.abilitySystem = new AbilitySystem(
            this.modules.stateManager,
            this.modules.uiManager,
            this.modules.eventManager,
            this.modules.notificationManager
        );
        
        // DisciplineSystem - Using simple script version instead
        // this.modules.disciplineSystem = new DisciplineSystem(
        //     this.modules.stateManager,
        //     this.modules.uiManager,
        //     this.modules.eventManager,
        //     this.modules.notificationManager,
        //     this.modules.dataManager
        // );
        
        // MeritsFlawsSystem
        this.modules.meritsFlawsSystem = new MeritsFlawsSystem(
            this.modules.stateManager,
            this.modules.uiManager,
            this.modules.eventManager,
            this.modules.notificationManager
        );
        
        // BackgroundSystem
        this.modules.backgroundSystem = new BackgroundSystem(
            this.modules.stateManager,
            this.modules.uiManager,
            this.modules.eventManager,
            this.modules.notificationManager
        );
        
        // MoralitySystem
        this.modules.moralitySystem = new MoralitySystem(
            this.modules.stateManager,
            this.modules.uiManager,
            this.modules.eventManager,
            this.modules.notificationManager
        );
        
        // CashSystem
        this.modules.cashSystem = new CashSystem(
            this.modules.stateManager,
            this.modules.uiManager,
            this.modules.eventManager,
            this.modules.notificationManager
        );
        
        // HealthWillpowerSystem
        this.modules.healthWillpowerSystem = new HealthWillpowerSystem(
            this.modules.stateManager,
            this.modules.uiManager,
            this.modules.eventManager,
            this.modules.notificationManager
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

		// Minimal UI logging (no noisy state-change spam)
		document.addEventListener('click', (event) => {
			// Buttons
			const btn = event.target.closest('button');
			if (btn) {
				// Only log for Next and explicit upload buttons by id/class/text
				const id = btn.id || '';
				const label = (btn.textContent || '').trim().toLowerCase();
				const cls = String(btn.className || '').toLowerCase();
				if (btn.dataset.action === 'next' || /upload/.test(id) || /upload/.test(label) || /upload/.test(cls)) {
					console.log('[ui] Button clicked:', { id: id || '(no-id)', label });
				}
			}
			// File chooser (Choose Image) - only log, don't interfere
			const fileClickEl = event.target.closest('input[type="file"], label[for]');
			if (fileClickEl) {
				const el = fileClickEl;
				const kind = el.tagName.toLowerCase();
				const forAttr = el.getAttribute('for') || '';
				console.log('[ui] File chooser clicked:', { kind, for: forAttr });
				// Don't preventDefault or stopPropagation - let native behavior work
			}
			// Generic Upload Image-ish controls (covers anchors/divs used as buttons)
			const clickable = event.target.closest('[id], [class], a, div, span');
			if (clickable) {
				const text = (clickable.textContent || '').toLowerCase();
				const id2 = clickable.id || '';
				const cls = clickable.className || '';
				if (/upload/.test(text) || /upload/.test(id2) || /upload/.test(cls)) {
					console.log('[ui] Upload-ish element clicked:', { id: id2, class: cls, text: text.slice(0, 80) });
				}
			}
		}, { capture: true });

		// Log selected files when file input changes
		document.addEventListener('change', (event) => {
			const input = event.target;
			if (input && input.type === 'file') {
				console.log('[main.js] File input change detected on:', input.id || 'unnamed input');
				if (input.files && input.files.length > 0) {
					const names = Array.from(input.files).map(f => f.name);
					console.log('[main.js] File selected:', names);
				} else {
					console.log('[main.js] File input change but no files - user may have canceled');
				}
			}
		}, { capture: true });
        
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
        
        // Check for character ID in URL for editing
        const urlParams = new URLSearchParams(window.location.search);
        const characterId = urlParams.get('id');
        
        if (characterId) {
            console.log('Loading character for editing:', characterId);
            try {
                await this.loadCharacter(characterId);
            } catch (error) {
                console.error('Failed to load character:', error);
            }
        } else {
            // Only resume saved state if explicitly requested via ?resume=1
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
        } else {
            console.warn('Form validation failed:', validation.errors);
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
                this.modules.stateManager.setStateProperty('isDirty', false);
                this.modules.stateManager.setStateProperty('lastSaved', Date.now());
            } else {
                throw new Error(response.error || 'Failed to save character');
            }
        } catch (error) {
            console.error('Error saving character:', error);
        }
    }
    
    /**
     * Load character
     */
    async loadCharacter(characterId) {
        try {
            const characterData = await this.modules.dataManager.loadCharacter(characterId);
            
            if (characterData && characterData.success) {
                console.log('Character data loaded successfully:', characterData);
                console.log('Disciplines in loaded data:', characterData.disciplines);
                
				// Set state with the loaded data
				this.modules.stateManager.setState(characterData);
				// Ensure ID and image are explicitly tracked for updates
				if (characterData.character && characterData.character.id) {
					this.modules.stateManager.setStateProperty('id', characterData.character.id);
				}
				if (characterData.character && characterData.character.character_image) {
					this.modules.stateManager.setStateProperty('imagePath', characterData.character.character_image);
				}
                
                // Populate form fields with loaded data (with a small delay to ensure DOM is ready)
                setTimeout(() => {
                    this.populateFormFromCharacterData(characterData);
                }, 500); // Increased delay to ensure all modules are initialized
                
                // no popup
            } else {
                throw new Error(characterData?.message || 'Character not found');
            }
        } catch (error) {
            console.error('Error loading character:', error);
        }
    }
    
    /**
     * Populate form fields with character data
     */
    populateFormFromCharacterData(data) {
        const character = data.character;
        
        console.log('Populating form with character data:', character);
        
        // Populate basic info fields
        this.setFormValue('#characterName', character.character_name);
        this.setFormValue('#playerName', character.player_name);
        this.setFormValue('#clan', character.clan);
        this.setFormValue('#nature', character.nature);
        this.setFormValue('#demeanor', character.demeanor);
        this.setFormValue('#concept', character.concept);
        this.setFormValue('#chronicle', character.chronicle);
        this.setFormValue('#generation', character.generation);
        this.setFormValue('#sire', character.sire);
        
        // Set PC checkbox based on is_pc field or player_name
        const isPC = character.is_pc !== undefined ? character.is_pc : (character.player_name !== 'NPC');
        const pcCheckbox = document.querySelector('#pc');
        if (pcCheckbox) {
            pcCheckbox.checked = isPC;
            pcCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
        }
        
        // Trigger form validation after populating data
        setTimeout(() => {
            // Trigger validation by dispatching events on required fields
            const requiredFields = ['#characterName', '#playerName', '#nature', '#demeanor', '#concept', '#clan', '#generation'];
            requiredFields.forEach(selector => {
                const field = document.querySelector(selector);
                if (field && field.value) {
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }, 100);
        
        // Populate traits
        if (data.traits) {
            this.populateTraitsFromData(data.traits);
        }
        
        // Populate negative traits
        if (data.negative_traits) {
            this.populateNegativeTraitsFromData(data.negative_traits);
        }
        
        // Populate abilities
        if (data.abilities) {
            this.populateAbilitiesFromData(data.abilities);
        }
        
        // Populate disciplines
        if (data.disciplines) {
            this.populateDisciplinesFromData(data.disciplines);
        }
        
        // Populate backgrounds
        if (data.backgrounds) {
            this.populateBackgroundsFromData(data.backgrounds);
        }
        
        // Populate morality
        if (data.morality) {
            this.populateMoralityFromData(data.morality);
        }
        
        // Populate merits/flaws
        if (data.merits_flaws) {
            this.populateMeritsFlawsFromData(data.merits_flaws);
        }
    }
    
    /**
     * Set form value helper
     */
    setFormValue(selector, value) {
        const element = document.querySelector(selector);
        if (element && value !== null && value !== undefined) {
            element.value = value;
            // Trigger change event to update any dependent fields
            element.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
    
    /**
     * Populate traits from loaded data
     */
    populateTraitsFromData(traits) {
        // Clear existing selections
        document.querySelectorAll('.trait-select').forEach(select => {
            select.value = '';
        });
        
        // Set selected traits
        Object.entries(traits).forEach(([category, traitNames]) => {
            traitNames.forEach(traitName => {
                const select = document.querySelector(`select[data-category="${category}"] option[value="${traitName}"]`);
                if (select) {
                    select.selected = true;
                    select.parentElement.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
    }
    
    /**
     * Populate negative traits from loaded data
     */
    populateNegativeTraitsFromData(negativeTraits) {
        // Similar to populateTraitsFromData but for negative traits
        Object.entries(negativeTraits).forEach(([category, traitNames]) => {
            traitNames.forEach(traitName => {
                const checkbox = document.querySelector(`input[type="checkbox"][data-category="${category}"][value="${traitName}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
    }
    
    /**
     * Populate abilities from loaded data
     */
    populateAbilitiesFromData(abilities) {
        console.log('Populating abilities from data:', abilities);
        
        // Handle both array and object formats
        if (Array.isArray(abilities)) {
            // Old format - array of ability objects
            abilities.forEach(ability => {
                const input = document.querySelector(`input[name="ability_${ability.ability_name}"]`);
                if (input) {
                    input.value = ability.level;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        } else if (typeof abilities === 'object' && abilities !== null) {
            // New format - object with categories as keys
            Object.entries(abilities).forEach(([category, abilityNames]) => {
                abilityNames.forEach(abilityName => {
                    const input = document.querySelector(`input[name="ability_${abilityName}"]`);
                    if (input) {
                        input.value = 1; // Default level for loaded abilities
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            });
        }
    }
    
    /**
     * Populate disciplines from loaded data
     */
    populateDisciplinesFromData(disciplines) {
        console.log('Populating disciplines from data:', disciplines);
        
        Object.entries(disciplines).forEach(([disciplineName, levels]) => {
            console.log(`Processing discipline: ${disciplineName}, levels:`, levels);
            
            if (Array.isArray(levels)) {
                levels.forEach(levelData => {
                    const level = typeof levelData === 'object' ? levelData.level : levelData;
                    console.log(`Looking for button: discipline="${disciplineName}", level="${level}"`);
                    
                    const button = document.querySelector(`button[data-discipline="${disciplineName}"][data-level="${level}"]`);
                    if (button) {
                        console.log('Found button, selecting:', button);
                        button.classList.add('selected');
                        button.dispatchEvent(new Event('click', { bubbles: true }));
                    } else {
                        console.log('Button not found for discipline:', disciplineName, 'level:', level);
                    }
                });
            }
        });
    }
    
    /**
     * Populate backgrounds from loaded data
     */
    populateBackgroundsFromData(backgrounds) {
        backgrounds.forEach(background => {
            const input = document.querySelector(`input[name="background_${background.background_name}"]`);
            if (input) {
                input.value = background.level;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }
    
    /**
     * Populate morality from loaded data
     */
    populateMoralityFromData(morality) {
        this.setFormValue('#humanity', morality.humanity);
        this.setFormValue('#willpower_current', morality.willpower_current);
        this.setFormValue('#willpower_permanent', morality.willpower_permanent);
        this.setFormValue('#conscience', morality.conscience);
        this.setFormValue('#self_control', morality.self_control);
        this.setFormValue('#courage', morality.courage);
    }
    
    /**
     * Populate merits/flaws from loaded data
     */
    populateMeritsFlawsFromData(meritsFlaws) {
        meritsFlaws.forEach(item => {
            const checkbox = document.querySelector(`input[type="checkbox"][data-type="${item.type}"][value="${item.name}"]`);
            if (checkbox) {
                checkbox.checked = true;
                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }
    
    /**
     * Reset character
     */
    async resetCharacter() {
        const confirmed = await this.modules.notificationManager.showConfirmation(
            'Reset Character',
            'Are you sure you want to reset the character? All unsaved changes will be lost.',
            { showCancel: true }
        );
        
        if (confirmed) {
            this.modules.stateManager.reset();
            this.modules.notificationManager.success('Character reset successfully');
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
            
            this.modules.notificationManager.success('Character exported successfully');
        } catch (error) {
            console.error('Error exporting character:', error);
            this.modules.notificationManager.error('Failed to export character: ' + error.message);
        }
    }
    
    /**
     * Import character
     */
    async importCharacter(data) {
        try {
            const success = this.modules.stateManager.importState(data);
            
            if (success) {
                this.modules.notificationManager.success('Character imported successfully');
            } else {
                throw new Error('Invalid character data');
            }
        } catch (error) {
            console.error('Error importing character:', error);
            this.modules.notificationManager.error('Failed to import character: ' + error.message);
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
                this.modules.notificationManager.success('Character is valid');
            } else {
                this.modules.notificationManager.error(`Character validation failed: ${validation.errors.join(', ')}`);
            }
        } catch (error) {
            console.error('Error validating character:', error);
            this.modules.notificationManager.error('Failed to validate character: ' + error.message);
        }
    }
    
    /**
     * Handle initialization error
     */
    handleInitializationError(error) {
        console.error('Initialization error:', error);
        
        // Show error notification
        if (this.modules.notificationManager) {
            this.modules.notificationManager.error('Failed to initialize application: ' + error.message);
        } else {
            alert('Failed to initialize application: ' + error.message);
        }
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
