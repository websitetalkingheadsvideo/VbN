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
			// File chooser (Choose Image) - don't interfere with native behavior
			const fileClickEl = event.target.closest('input[type="file"], label[for]');
			if (fileClickEl) {
				// Don't preventDefault or stopPropagation - let native behavior work
			}
		}, { capture: true });

        // File input change handling is done by character_image.js
        // No need for additional logging here
        
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
                if (this.modules.abilitySystem && typeof this.modules.abilitySystem.resetAll === 'function') {
                    this.modules.abilitySystem.resetAll();
                }
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
            
            // Collect Custom Data, Coterie, and Relationships from Final Details tab
            const customDataField = document.getElementById('customData');
            if (customDataField) {
                state.custom_data = customDataField.value.trim();
            }
            
            // Collect coteries and relationships using global functions
            if (typeof window.collectCoteries === 'function') {
                state.coteries = window.collectCoteries();
            }
            if (typeof window.collectRelationships === 'function') {
                state.relationships = window.collectRelationships();
            }
            
            const response = await this.modules.dataManager.saveCharacter(state);
            
            if (response.success) {
                const returnedId = response.character_id || response.id;
                if (returnedId) {
                    const idValue = parseInt(returnedId, 10);
                    if (!Number.isNaN(idValue) && idValue > 0) {
                        this.modules.stateManager.setStateProperty('characterId', idValue);
                        this.modules.stateManager.setStateProperty('id', idValue);
                        const hidden = document.getElementById('characterId');
                        if (hidden) {
                            hidden.value = String(idValue);
                        }
                    }
                }
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
                
				// Prepare characterData with explicit characterId and playerName
				const characterId = characterData.character?.id || null;
				const characterDataWithIds = {
					...characterData,
					characterId: characterId,
					id: characterId, // Set both characterId and id for compatibility
					playerName: characterData.character?.player_name || ''
				};
				
				console.log('Setting character state with characterId:', characterId);
				
				// Set state with the loaded data
				this.modules.stateManager.setState(characterDataWithIds);
				// Ensure ID and image are explicitly tracked for updates
				if (characterData.character && characterData.character.id) {
					this.modules.stateManager.setStateProperty('id', characterData.character.id);
					this.modules.stateManager.setStateProperty('characterId', characterData.character.id);
				}
				if (characterData.character && characterData.character.character_image) {
					this.modules.stateManager.setStateProperty('imagePath', characterData.character.character_image);
				}
				
				// Store full ability data for character sheet display
				if (characterData.abilities_full && Array.isArray(characterData.abilities_full)) {
					this.modules.stateManager.setStateProperty('abilities_full', characterData.abilities_full);
				}
				
				// Update discipline displays to reflect loaded state
				if (this.modules.disciplineSystem && characterData.disciplines) {
					this.modules.disciplineSystem.updateAllDisplays();
				}
                
                // Update trait displays immediately since state is already set
                if (characterData.traits && this.modules.traitSystem) {
                    setTimeout(() => {
                        this.modules.traitSystem.updateAllDisplays();
                    }, 100);
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
        this.setFormValue('#currentState', character.status || character.current_state || 'active');
        this.setFormValue('#camarillaStatus', character.camarilla_status || 'Unknown');
        
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
        
        // Populate traits - ensure state is set and displays are updated
        if (data.traits) {
            this.populateTraitsFromData(data.traits);
            // Also ensure traits are in state (they should already be from loadCharacter, but double-check)
            const currentState = this.modules.stateManager.getState();
            if (!currentState.traits || Object.keys(currentState.traits).length === 0) {
                this.modules.stateManager.setState({ traits: {
                    Physical: Array.isArray(data.traits.Physical) ? data.traits.Physical : [],
                    Social: Array.isArray(data.traits.Social) ? data.traits.Social : [],
                    Mental: Array.isArray(data.traits.Mental) ? data.traits.Mental : []
                }});
            }
        }
        
        // Populate negative traits - ensure state is set and displays are updated
        if (data.negative_traits) {
            this.populateNegativeTraitsFromData(data.negative_traits);
            // Also ensure negative traits are in state
            const currentState = this.modules.stateManager.getState();
            if (!currentState.negativeTraits || Object.keys(currentState.negativeTraits).length === 0) {
                this.modules.stateManager.setState({ negativeTraits: {
                    Physical: Array.isArray(data.negative_traits.Physical) ? data.negative_traits.Physical : [],
                    Social: Array.isArray(data.negative_traits.Social) ? data.negative_traits.Social : [],
                    Mental: Array.isArray(data.negative_traits.Mental) ? data.negative_traits.Mental : []
                }});
            }
        }
        
        // Populate abilities - prefer abilities_full if available (has levels), otherwise use abilities (category-based)
        console.log('populateFormFromCharacterData - data.abilities_full:', data.abilities_full);
        console.log('populateFormFromCharacterData - data.abilities:', data.abilities);
        
        if (data.abilities_full && Array.isArray(data.abilities_full) && data.abilities_full.length > 0) {
            console.log('Using abilities_full, count:', data.abilities_full.length);
            // Store in state if not already there
            if (this.modules.stateManager) {
                this.modules.stateManager.setStateProperty('abilities_full', data.abilities_full);
            }
            // populateAbilitiesFromData will use abilities_full from state
            this.populateAbilitiesFromData(data.abilities_full);
        } else if (data.abilities) {
            console.log('Using abilities (category-based), checking for content...');
            // Check if abilities object has any actual content
            const hasAbilities = Object.values(data.abilities).some(arr => Array.isArray(arr) && arr.length > 0);
            if (hasAbilities) {
                this.populateAbilitiesFromData(data.abilities);
            } else {
                console.warn('Both abilities_full and abilities are empty or have no content');
            }
        } else {
            console.warn('No abilities data found in response');
        }
        
        // Populate disciplines - use disciplinePowers for the UI mapping
        if (data.disciplinePowers) {
            this.populateDisciplinesFromData(data.disciplinePowers);
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
        
        // Populate Custom Data, Coterie, and Relationships
        if (character.custom_data) {
            const customDataField = document.getElementById('customData');
            if (customDataField) {
                customDataField.value = character.custom_data;
            }
        }
        
        // Populate coteries
        if (data.coteries && Array.isArray(data.coteries) && data.coteries.length > 0) {
            if (typeof window.addCoterieEntry === 'function') {
                // Reset counter and clear existing entries
                if (typeof window.coterieCounter !== 'undefined') {
                    window.coterieCounter = 0;
                }
                const container = document.getElementById('coterieContainer');
                if (container) {
                    container.innerHTML = '';
                    const emptyState = document.getElementById('coterieEmptyState');
                    if (emptyState) emptyState.style.display = 'none';
                }
                // Add each coterie entry
                data.coteries.forEach(coterie => {
                    window.addCoterieEntry(coterie);
                });
            }
        }
        
        // Populate relationships
        if (data.relationships && Array.isArray(data.relationships) && data.relationships.length > 0) {
            if (typeof window.addRelationshipEntry === 'function') {
                // Reset counter and clear existing entries
                if (typeof window.relationshipCounter !== 'undefined') {
                    window.relationshipCounter = 0;
                }
                const container = document.getElementById('relationshipsContainer');
                if (container) {
                    container.innerHTML = '';
                    const emptyState = document.getElementById('relationshipsEmptyState');
                    if (emptyState) emptyState.style.display = 'none';
                }
                // Add each relationship entry
                data.relationships.forEach(relationship => {
                    window.addRelationshipEntry(relationship);
                });
            }
        }
        
        // Final update of trait displays to ensure everything is shown
        setTimeout(() => {
            if (this.modules.traitSystem) {
                this.modules.traitSystem.updateAllDisplays();
            }
        }, 600);
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
        console.log('populateTraitsFromData called with:', traits);
        
        if (!traits || typeof traits !== 'object') {
            console.warn('populateTraitsFromData: Invalid traits data', traits);
            return;
        }
        
        // Set traits in state (format: { Physical: [trait1, trait2], Social: [...], Mental: [...] })
        const traitsState = {
            Physical: Array.isArray(traits.Physical) ? traits.Physical : [],
            Social: Array.isArray(traits.Social) ? traits.Social : [],
            Mental: Array.isArray(traits.Mental) ? traits.Mental : []
        };
        
        console.log('Setting traits state:', traitsState);
        this.modules.stateManager.setState({ traits: traitsState });
        
        // Update trait system displays
        if (this.modules.traitSystem) {
            this.modules.traitSystem.updateAllDisplays();
        }
    }
    
    /**
     * Populate negative traits from loaded data
     */
    populateNegativeTraitsFromData(negativeTraits) {
        console.log('populateNegativeTraitsFromData called with:', negativeTraits);
        
        if (!negativeTraits || typeof negativeTraits !== 'object') {
            console.warn('populateNegativeTraitsFromData: Invalid negative traits data', negativeTraits);
            return;
        }
        
        // Set negative traits in state (format: { Physical: [trait1, trait2], Social: [...], Mental: [...] })
        const negativeTraitsState = {
            Physical: Array.isArray(negativeTraits.Physical) ? negativeTraits.Physical : [],
            Social: Array.isArray(negativeTraits.Social) ? negativeTraits.Social : [],
            Mental: Array.isArray(negativeTraits.Mental) ? negativeTraits.Mental : []
        };
        
        console.log('Setting negative traits state:', negativeTraitsState);
        this.modules.stateManager.setState({ negativeTraits: negativeTraitsState });
        
        // Update trait system displays
        if (this.modules.traitSystem) {
            this.modules.traitSystem.updateAllDisplays();
        }
    }
    
    /**
     * Populate abilities from loaded data
     */
    populateAbilitiesFromData(abilities) {
        console.log('Populating abilities from data:', abilities);
        console.log('abilities type:', typeof abilities, 'isArray:', Array.isArray(abilities));

        const emptyAbilityBuckets = () => ({
            Physical: [],
            Social: [],
            Mental: [],
            Optional: []
        });

        const normalizeCategoryKey = (category) => {
            if (!category) {
                return 'Optional';
            }

            const lowered = category.toString().trim().toLowerCase();
            if (lowered.startsWith('phys')) return 'Physical';
            if (lowered.startsWith('soc')) return 'Social';
            if (lowered.startsWith('ment')) return 'Mental';
            return 'Optional';
        };

        const normalizedState = emptyAbilityBuckets();

        const pushAbilityDots = (categoryKey, abilityName, level = 1) => {
            const normalizedName = (abilityName || '').trim();
            if (!normalizedName || !normalizedState.hasOwnProperty(categoryKey)) {
                return;
            }

            const numericLevel = Number(level);
            const safeLevel = Number.isFinite(numericLevel)
                ? Math.max(1, Math.min(5, Math.floor(numericLevel)))
                : 1;

            for (let dot = 0; dot < safeLevel; dot += 1) {
                normalizedState[categoryKey].push(normalizedName);
            }
        };
        
        // Also update global characterData for character sheet
        if (typeof window.characterData === 'undefined') {
            window.characterData = {
                abilities: { Physical: [], Social: [], Mental: [], Optional: [] }
            };
        }
        window.characterData.abilities = emptyAbilityBuckets();
        
        // Check if we have full ability data with levels (from load_character.php)
        if (this.modules.stateManager) {
            const state = this.modules.stateManager.getState();
            if (state && state.abilities_full && Array.isArray(state.abilities_full) && state.abilities_full.length > 0) {
                // Use full ability data with levels and specializations
                state.abilities_full.forEach(ability => {
                    // Populate form inputs
                    const input = document.querySelector(`input[name="ability_${ability.ability_name}"]`);
                    if (input) {
                        input.value = ability.level || 1;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    
                    // Also populate characterData.abilities for character sheet
                    const normalizedCategory = normalizeCategoryKey(ability.ability_category);
                    if (window.characterData.abilities.hasOwnProperty(normalizedCategory)) {
                        let displayName = ability.ability_name;
                        if (ability.level && ability.level > 0) {
                            displayName += ' x' + ability.level;
                        }
                        if (ability.specialization && ability.specialization.trim()) {
                            displayName += ' (' + ability.specialization.trim() + ')';
                        }
                        window.characterData.abilities[normalizedCategory].push(displayName);
                    }

                    pushAbilityDots(normalizedCategory, ability.ability_name, ability.level);
                });

                this.modules.stateManager.setState({ abilities: normalizedState });
                if (this.modules.abilitySystem) {
                    this.modules.abilitySystem.updateAllDisplays();
                }
                return;
            }
        }
        
        // Handle both array and object formats
        if (Array.isArray(abilities)) {
            // Old format - array of ability objects with level info
            abilities.forEach(ability => {
                const input = document.querySelector(`input[name="ability_${ability.ability_name}"]`);
                if (input) {
                    input.value = ability.level || 1;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
                
                // Populate characterData.abilities
                const normalizedCategory = normalizeCategoryKey(ability.ability_category);
                if (window.characterData.abilities.hasOwnProperty(normalizedCategory)) {
                    let displayName = ability.ability_name;
                    if (ability.level && ability.level > 0) {
                        displayName += ' x' + ability.level;
                    }
                    if (ability.specialization && ability.specialization.trim()) {
                        displayName += ' (' + ability.specialization.trim() + ')';
                    }
                    window.characterData.abilities[normalizedCategory].push(displayName);
                }

                pushAbilityDots(normalizedCategory, ability.ability_name, ability.level);
            });
        } else if (typeof abilities === 'object' && abilities !== null) {
            // New format - object with categories as keys (array of ability names only)
            Object.entries(abilities).forEach(([category, abilityNames]) => {
                const normalizedCategory = normalizeCategoryKey(category);
                if (window.characterData.abilities.hasOwnProperty(normalizedCategory)) {
                    abilityNames.forEach(abilityName => {
                        const input = document.querySelector(`input[name="ability_${abilityName}"]`);
                        if (input) {
                            input.value = 1; // Default level for loaded abilities when only names are provided
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        // Add to characterData for sheet display
                        window.characterData.abilities[normalizedCategory].push(abilityName);
                        pushAbilityDots(normalizedCategory, abilityName, 1);
                    });
                }
            });
        }

        this.modules.stateManager.setState({ abilities: normalizedState });
        if (this.modules.abilitySystem) {
            this.modules.abilitySystem.updateAllDisplays();
        }
    }
    
    /**
     * Populate disciplines from loaded data
     */
    populateDisciplinesFromData(disciplines) {
        console.log('Populating disciplines from data:', disciplines);
        
        // Disciplines are already in state via setState()
        // Just need to trigger display update
        if (this.modules.disciplineSystem) {
            this.modules.disciplineSystem.updateAllDisplays();
        }
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
