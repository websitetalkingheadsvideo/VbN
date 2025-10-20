/**
 * PreviewManager.js - Handles character preview and real-time updates
 * Manages character sheet preview, live updates, and display formatting
 */

class PreviewManager {
    constructor(stateManager, uiManager, eventManager, notificationManager) {
        this.stateManager = stateManager;
        this.uiManager = uiManager;
        this.eventManager = eventManager;
        this.notificationManager = notificationManager;
        
        this.previewElement = null;
        this.updateQueue = [];
        this.isUpdating = false;
        this.lastUpdate = 0;
        this.updateThrottle = 100; // 100ms throttle
        
        this.init();
    }
    
    /**
     * Initialize the preview manager
     */
    init() {
        this.setupEventListeners();
        this.setupUpdateThrottle();
        this.updateCharacterPreview();
    }
    
    /**
     * Setup event listeners for preview updates
     */
    setupEventListeners() {
        const { eventManager } = this;
        
        // Listen for state changes
        eventManager.onCustomEvent('stateChanged', (event) => {
            this.queuePreviewUpdate();
        });
        
        // Listen for specific state changes
        eventManager.onCustomEvent('characterNameChanged', () => {
            this.updateBasicInfo();
        });
        
        eventManager.onCustomEvent('attributesChanged', () => {
            this.updateAttributes();
        });
        
        eventManager.onCustomEvent('traitsChanged', () => {
            this.updateTraits();
        });
        
        eventManager.onCustomEvent('abilitiesChanged', () => {
            this.updateAbilities();
        });
        
        eventManager.onCustomEvent('disciplinesChanged', () => {
            this.updateDisciplines();
        });
        
        eventManager.onCustomEvent('backgroundsChanged', () => {
            this.updateBackgrounds();
        });
        
        eventManager.onCustomEvent('meritsFlawsChanged', () => {
            this.updateMeritsFlaws();
        });
        
        eventManager.onCustomEvent('moralityChanged', () => {
            this.updateMorality();
        });
    }
    
    /**
     * Setup update throttle
     */
    setupUpdateThrottle() {
        // Process update queue every 50ms
        setInterval(() => {
            if (this.updateQueue.length > 0 && !this.isUpdating) {
                this.processUpdateQueue();
            }
        }, 50);
    }
    
    /**
     * Queue preview update
     */
    queuePreviewUpdate() {
        const now = Date.now();
        
        // Throttle updates
        if (now - this.lastUpdate < this.updateThrottle) {
            return;
        }
        
        this.updateQueue.push({
            timestamp: now,
            type: 'full'
        });
        
        this.lastUpdate = now;
    }
    
    /**
     * Process update queue
     */
    processUpdateQueue() {
        if (this.isUpdating) return;
        
        this.isUpdating = true;
        
        // Process all queued updates
        while (this.updateQueue.length > 0) {
            const update = this.updateQueue.shift();
            this.processUpdate(update);
        }
        
        this.isUpdating = false;
    }
    
    /**
     * Process individual update
     */
    processUpdate(update) {
        try {
            if (update.type === 'full') {
                this.updateCharacterPreview();
            } else {
                this.updateSpecificSection(update.section);
            }
        } catch (error) {
            console.error('Error updating character preview:', error);
        }
    }
    
    /**
     * Update character preview
     */
    updateCharacterPreview() {
        const state = this.stateManager.getState();
        
        // Update basic info
        this.updateBasicInfo();
        
        // Update attributes
        this.updateAttributes();
        
        // Update traits
        this.updateTraits();
        
        // Update abilities
        this.updateAbilities();
        
        // Update disciplines
        this.updateDisciplines();
        
        // Update backgrounds
        this.updateBackgrounds();
        
        // Update merits and flaws
        this.updateMeritsFlaws();
        
        // Update morality
        this.updateMorality();
        
        // Update health and willpower
        this.updateHealthWillpower();
        
        // Update starting cash
        this.updateStartingCash();
    }
    
    /**
     * Update basic info section
     */
    updateBasicInfo() {
        const state = this.stateManager.getState();
        
        // Character name
        this.updatePreviewElement('#previewCharacterName', state.characterName || 'Unnamed Character');
        
        // Player name
        this.updatePreviewElement('#previewPlayerName', state.playerName || 'Unknown Player');
        
        // Chronicle
        this.updatePreviewElement('#previewChronicle', state.chronicle || 'Unknown Chronicle');
        
        // Concept
        this.updatePreviewElement('#previewConcept', state.concept || 'Unknown Concept');
        
        // Clan
        this.updatePreviewElement('#previewClan', state.clan || 'Unknown Clan');
        
        // Generation
        this.updatePreviewElement('#previewGeneration', state.generation || 13);
    }
    
    /**
     * Update attributes section
     */
    updateAttributes() {
        const state = this.stateManager.getState();
        const attributes = state.attributes || {};
        
        // Physical attributes
        this.updateAttributeCategory('Physical', attributes.Physical);
        
        // Social attributes
        this.updateAttributeCategory('Social', attributes.Social);
        
        // Mental attributes
        this.updateAttributeCategory('Mental', attributes.Mental);
    }
    
    /**
     * Update attribute category
     */
    updateAttributeCategory(category, attrs) {
        if (!attrs) return;
        
        const attrNames = category === 'Physical' ? ['Strength', 'Dexterity', 'Stamina'] :
                        category === 'Social' ? ['Charisma', 'Manipulation', 'Appearance'] :
                        ['Perception', 'Intelligence', 'Wits'];
        
        attrNames.forEach(attrName => {
            const value = attrs[attrName] || 1;
            this.updatePreviewElement(`#preview${attrName}`, value);
        });
    }
    
    /**
     * Update traits section
     */
    updateTraits() {
        const state = this.stateManager.getState();
        const traits = state.traits || {};
        
        // Physical traits
        this.updateTraitCategory('Physical', traits.Physical);
        
        // Social traits
        this.updateTraitCategory('Social', traits.Social);
        
        // Mental traits
        this.updateTraitCategory('Mental', traits.Mental);
    }
    
    /**
     * Update trait category
     */
    updateTraitCategory(category, traits) {
        if (!traits) return;
        
        const listElement = this.uiManager.getElement(`#preview${category}Traits`);
        if (listElement) {
            const traitsHTML = traits.map(trait => `<li>${trait}</li>`).join('');
            this.uiManager.updateContent(listElement, traitsHTML);
        }
    }
    
    /**
     * Update abilities section
     */
    updateAbilities() {
        const state = this.stateManager.getState();
        const abilities = state.abilities || {};
        
        // Physical abilities
        this.updateAbilityCategory('Physical', abilities.Physical);
        
        // Social abilities
        this.updateAbilityCategory('Social', abilities.Social);
        
        // Mental abilities
        this.updateAbilityCategory('Mental', abilities.Mental);
        
        // Optional abilities
        this.updateAbilityCategory('Optional', abilities.Optional);
    }
    
    /**
     * Update ability category
     */
    updateAbilityCategory(category, abilities) {
        if (!abilities) return;
        
        const listElement = this.uiManager.getElement(`#preview${category}Abilities`);
        if (listElement) {
            const abilitiesHTML = abilities.map(ability => `<li>${ability}</li>`).join('');
            this.uiManager.updateContent(listElement, abilitiesHTML);
        }
    }
    
    /**
     * Update disciplines section
     */
    updateDisciplines() {
        try {
            const state = this.stateManager.getState();
            let disciplines = state.disciplines || [];
            const disciplinePowers = state.disciplinePowers || {};
            
            console.log('PreviewManager updateDisciplines - raw disciplines:', disciplines);
            
            // Ensure disciplines is always an array
            if (!Array.isArray(disciplines)) {
                console.log('Converting disciplines from object to array format');
                if (typeof disciplines === 'object' && disciplines !== null) {
                    // Convert object format to array format
                    disciplines = Object.keys(disciplines).map(disciplineName => {
                        const levels = disciplines[disciplineName];
                        return {
                            name: disciplineName,
                            levels: Array.isArray(levels) ? levels.map(l => l.level || l) : []
                        };
                    });
                } else {
                    disciplines = [];
                }
            }
            
            console.log('PreviewManager updateDisciplines - processed disciplines:', disciplines);
            
            const listElement = this.uiManager.getElement('#previewDisciplines');
            if (listElement) {
                const disciplinesHTML = disciplines.map(discipline => {
                    // Handle both string and object formats
                    const disciplineName = typeof discipline === 'string' ? discipline : discipline.name;
                    const powers = typeof discipline === 'object' && discipline.levels ? 
                        discipline.levels : 
                        (disciplinePowers[disciplineName] || []);
                    
                    const powersHTML = Array.isArray(powers) ? 
                        powers.map(level => `<span class="power-level">${level}</span>`).join('') : '';
                    
                    return `
                        <li class="discipline-item">
                            <span class="discipline-name">${disciplineName}</span>
                            <div class="discipline-powers">${powersHTML}</div>
                        </li>
                    `;
                }).join('');
                
                this.uiManager.updateContent(listElement, disciplinesHTML);
            }
        } catch (error) {
            console.error('Error in PreviewManager.updateDisciplines:', error);
            const state = this.stateManager.getState();
            console.error('Disciplines data:', state?.disciplines);
        }
    }
    
    /**
     * Update backgrounds section
     */
    updateBackgrounds() {
        const state = this.stateManager.getState();
        const backgrounds = state.backgrounds || {};
        
        const listElement = this.uiManager.getElement('#previewBackgrounds');
        if (listElement) {
            const backgroundsHTML = Object.keys(backgrounds).map(background => {
                const level = backgrounds[background];
                return `<li class="background-item">
                    <span class="background-name">${background}</span>
                    <span class="background-level">${level}</span>
                </li>`;
            }).join('');
            
            this.uiManager.updateContent(listElement, backgroundsHTML);
        }
    }
    
    /**
     * Update merits and flaws section
     */
    updateMeritsFlaws() {
        const state = this.stateManager.getState();
        const meritsFlaws = state.selectedMeritsFlaws || [];
        
        const listElement = this.uiManager.getElement('#previewMeritsFlaws');
        if (listElement) {
            const meritsFlawsHTML = meritsFlaws.map(item => {
                const cost = item.variableCost !== null ? item.variableCost : item.cost;
                const costDisplay = cost > 0 ? `+${cost}` : cost.toString();
                
                return `<li class="merit-flaw-item ${item.type.toLowerCase()}">
                    <span class="merit-flaw-name">${item.name}</span>
                    <span class="merit-flaw-cost">${costDisplay}</span>
                </li>`;
            }).join('');
            
            this.uiManager.updateContent(listElement, meritsFlawsHTML);
        }
    }
    
    /**
     * Update morality section
     */
    updateMorality() {
        const state = this.stateManager.getState();
        const virtues = state.virtues || {};
        const humanity = state.humanity || 7;
        
        // Conscience
        this.updatePreviewElement('#previewConscience', virtues.Conscience || 1);
        
        // Self-Control
        this.updatePreviewElement('#previewSelfControl', virtues.SelfControl || 1);
        
        // Humanity
        this.updatePreviewElement('#previewHumanity', humanity);
        
        // Moral state
        const moralState = this.getMoralState(humanity, virtues.Conscience || 1, virtues.SelfControl || 1);
        this.updatePreviewElement('#previewMoralState', moralState.name);
    }
    
    /**
     * Update health and willpower
     */
    updateHealthWillpower() {
        const state = this.stateManager.getState();
        const stamina = state.attributes?.Physical?.Stamina || 1;
        const conscience = state.virtues?.Conscience || 1;
        const selfControl = state.virtues?.SelfControl || 1;
        const generation = state.generation || 13;
        
        // Calculate health
        const health = 3 + stamina + this.getGenerationHealthBonus(generation);
        this.updatePreviewElement('#previewHealth', health);
        
        // Calculate willpower
        const willpower = conscience + selfControl;
        this.updatePreviewElement('#previewWillpower', willpower);
    }
    
    /**
     * Update starting cash
     */
    updateStartingCash() {
        const state = this.stateManager.getState();
        const resources = state.backgrounds?.Resources || 0;
        const generation = state.generation || 13;
        
        // Calculate cash
        const multiplier = this.getGenerationCashMultiplier(generation);
        const cash = 100 * (1 + resources) * multiplier;
        
        this.updatePreviewElement('#previewStartingCash', `$${Math.round(cash).toLocaleString()}`);
    }
    
    /**
     * Update specific section
     */
    updateSpecificSection(section) {
        switch (section) {
            case 'basic':
                this.updateBasicInfo();
                break;
            case 'attributes':
                this.updateAttributes();
                break;
            case 'traits':
                this.updateTraits();
                break;
            case 'abilities':
                this.updateAbilities();
                break;
            case 'disciplines':
                this.updateDisciplines();
                break;
            case 'backgrounds':
                this.updateBackgrounds();
                break;
            case 'meritsFlaws':
                this.updateMeritsFlaws();
                break;
            case 'morality':
                this.updateMorality();
                break;
            case 'healthWillpower':
                this.updateHealthWillpower();
                break;
            case 'startingCash':
                this.updateStartingCash();
                break;
        }
    }
    
    /**
     * Update preview element
     */
    updatePreviewElement(selector, value) {
        const element = this.uiManager.getElement(selector);
        if (element) {
            this.uiManager.updateContent(element, value.toString());
        }
    }
    
    /**
     * Get moral state
     */
    getMoralState(humanity, conscience, selfControl) {
        const moralStates = {
            10: { name: 'Humanity 10', description: 'Perfectly human' },
            9: { name: 'Humanity 9', description: 'Nearly human' },
            8: { name: 'Humanity 8', description: 'Mostly human' },
            7: { name: 'Humanity 7', description: 'Somewhat human' },
            6: { name: 'Humanity 6', description: 'Barely human' },
            5: { name: 'Humanity 5', description: 'Losing humanity' },
            4: { name: 'Humanity 4', description: 'Barely human' },
            3: { name: 'Humanity 3', description: 'Losing humanity' },
            2: { name: 'Humanity 2', description: 'Barely human' },
            1: { name: 'Humanity 1', description: 'Losing humanity' },
            0: { name: 'Humanity 0', description: 'Lost humanity' }
        };
        
        const moralState = moralStates[humanity] || moralStates[7];
        
        // Adjust based on virtue levels
        if (conscience >= 4 && selfControl >= 4) {
            return {
                ...moralState,
                name: moralState.name + ' (High Virtues)'
            };
        } else if (conscience <= 2 && selfControl <= 2) {
            return {
                ...moralState,
                name: moralState.name + ' (Low Virtues)'
            };
        }
        
        return moralState;
    }
    
    /**
     * Get generation health bonus
     */
    getGenerationHealthBonus(generation) {
        const bonuses = {
            13: 0, 12: 1, 11: 2, 10: 3, 9: 4, 8: 5, 7: 6, 6: 7, 5: 8, 4: 9, 3: 10, 2: 11, 1: 12
        };
        return bonuses[generation] || 0;
    }
    
    /**
     * Get generation cash multiplier
     */
    getGenerationCashMultiplier(generation) {
        const multipliers = {
            13: 1, 12: 2, 11: 3, 10: 4, 9: 5, 8: 6, 7: 7, 6: 8, 5: 9, 4: 10, 3: 11, 2: 12, 1: 13
        };
        return multipliers[generation] || 1;
    }
    
    /**
     * Get preview statistics
     */
    getPreviewStats() {
        return {
            isUpdating: this.isUpdating,
            queuedUpdates: this.updateQueue.length,
            lastUpdate: this.lastUpdate,
            updateThrottle: this.updateThrottle
        };
    }
    
    /**
     * Force preview update
     */
    forceUpdate() {
        this.updateQueue = [];
        this.updateCharacterPreview();
    }
    
    /**
     * Set update throttle
     */
    setUpdateThrottle(throttle) {
        this.updateThrottle = throttle;
    }
}

// Export for use in other modules
window.PreviewManager = PreviewManager;
