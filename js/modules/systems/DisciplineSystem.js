/**
 * DisciplineSystem.js - Handles discipline selection and power management
 * Manages discipline selection, power selection, and popover display
 * Test
 */

class DisciplineSystem {
    constructor(stateManager, uiManager, eventManager, dataManager) {
        this.stateManager = stateManager;
        this.uiManager = uiManager;
        this.eventManager = eventManager;
        this.dataManager = dataManager;
        
        this.requirements = {
            min: 1,
            max: 3
        };
        
        this.disciplineData = null;
        this.popoverElement = null;
        this.currentDiscipline = null;
        this.currentPopoverButton = null;
        this.popoverHideTimeout = null;
        this.popoverHoverDelay = 400;
        
        // Clan discipline access mapping
        this.clanDisciplineAccess = {
            'Assamite': ['Animalism', 'Celerity', 'Obfuscate', 'Quietus'],
            'Brujah': ['Celerity', 'Potence', 'Presence'],
            'Caitiff': ['Animalism', 'Auspex', 'Celerity', 'Dominate', 'Fortitude', 'Obfuscate', 'Potence', 'Presence', 'Protean', 'Thaumaturgy', 'Necromancy', 'Koldunic Sorcery', 'Obtenebration', 'Chimerstry', 'Dementation', 'Quietus', 'Vicissitude', 'Serpentis', 'Daimoinon', 'Melpominee', 'Valeren', 'Mortis'],
            'Followers of Set': ['Animalism', 'Obfuscate', 'Presence', 'Serpentis'],
            'Daughter of Cacophony': ['Melpominee', 'Presence', 'Auspex'],
            'Gangrel': ['Animalism', 'Fortitude', 'Protean'],
            'Giovanni': ['Dominate', 'Fortitude', 'Necromancy', 'Mortis'],
            'Lasombra': ['Dominate', 'Obfuscate', 'Obtenebration'],
            'Malkavian': ['Auspex', 'Dementation', 'Obfuscate'],
            'Nosferatu': ['Animalism', 'Fortitude', 'Obfuscate'],
            'Ravnos': ['Animalism', 'Chimerstry', 'Fortitude'],
            'Toreador': ['Auspex', 'Celerity', 'Presence'],
            'Tremere': ['Auspex', 'Dominate', 'Thaumaturgy'],
            'Tzimisce': ['Animalism', 'Auspex', 'Dominate', 'Vicissitude'],
            'Ventrue': ['Dominate', 'Fortitude', 'Presence']
        };
        this.ensureDaughterMapping();
        
        // Discipline category mapping
        this.disciplineCategories = {
            'Clan': ['Animalism', 'Auspex', 'Celerity', 'Dominate', 'Fortitude', 'Obfuscate', 'Potence', 'Presence', 'Protean'],
            'BloodSorcery': ['Thaumaturgy', 'Necromancy', 'Koldunic Sorcery'],
            'Advanced': ['Obtenebration', 'Chimerstry', 'Dementation', 'Quietus', 'Vicissitude', 'Serpentis', 'Daimoinon', 'Melpominee', 'Valeren', 'Mortis']
        };
        
        this.init();
    }
    
    /**
     * Initialize the discipline system
     */
    async init() {
        await this.loadDisciplineData();
        this.setupEventListeners();
        this.setupStateListeners();
        this.updateAllDisplays();
        
        // Initialize discipline availability based on current clan
        const state = this.stateManager.getState();
        if (state.clan) {
            this.updateClanDisciplines(state.clan);
        }
    }
    
    /**
     * Load discipline data from API
     */
    async loadDisciplineData() {
        try {
            // Try to load from API via DataManager
            if (this.dataManager && typeof this.dataManager.fetchDisciplineData === 'function') {
                const response = await this.dataManager.fetchDisciplineData();
                
                if (response && response.success && response.data && response.data.disciplinePowers) {
                    // Transform API response to our expected format
                    this.disciplineData = {};
                    const apiPowers = response.data.disciplinePowers;
                    
                    // Convert array format to object format with powers indexed by level
                    Object.keys(apiPowers).forEach(disciplineName => {
                        const powersArray = apiPowers[disciplineName];
                        if (Array.isArray(powersArray)) {
                            this.disciplineData[disciplineName] = {
                                description: '', // API doesn't provide description in this endpoint
                                powers: {}
                            };
                            
                            // Convert array of {level, name, description} to {level: {name, description, cost}}
                            powersArray.forEach(power => {
                                this.disciplineData[disciplineName].powers[String(power.level)] = {
                                    name: power.name || 'Unknown Power',
                                    description: power.description || '',
                                    cost: '1 Willpower' // Default cost
                                };
                            });
                        }
                    });
                    
                    // Also load clan discipline access if available
                    if (response.data.clanDisciplineAccess) {
                        this.clanDisciplineAccess = { ...this.clanDisciplineAccess, ...response.data.clanDisciplineAccess };
                    }
                    this.ensureDaughterMapping();
                    
                    // Add Thaumaturgy paths to BloodSorcery category dynamically
                    // Thaumaturgy paths are disciplines with parent_discipline='Thaumaturgy' in the database
                    // They'll be named like "Path of Blood", "Path of Geomancy", etc.
                    Object.keys(this.disciplineData).forEach(disciplineName => {
                        // Check if it's a Thaumaturgy path (starts with "Path of" or contains common path indicators)
                        if (disciplineName.startsWith('Path of') || 
                            disciplineName.includes('Path') && !this.disciplineCategories['BloodSorcery'].includes(disciplineName)) {
                            // Add to BloodSorcery category if not already there
                            if (!this.disciplineCategories['BloodSorcery'].includes(disciplineName)) {
                                this.disciplineCategories['BloodSorcery'].push(disciplineName);
                            }
                        }
                    });
                    
                    return;
                }
            }
            
            // Fallback to hardcoded data if API fails
            console.warn('DisciplineSystem: API load failed or unavailable, using fallback data');
            this.loadFallbackData();
        } catch (error) {
            console.error('DisciplineSystem: Error loading discipline data from API:', error);
            console.error('DisciplineSystem: Error details:', error.message, error.stack);
            // Fallback to hardcoded data
            this.loadFallbackData();
        }
    }
    
    /**
     * Load fallback discipline data
     */
    loadFallbackData() {
        this.disciplineData = {
            "Animalism": {
                "description": "The Discipline of Animalism allows vampires to communicate with and control animals.",
                "powers": {
                    "1": {
                        "name": "Feral Whispers",
                        "description": "Communicate with animals in their own language.",
                        "cost": "1 Willpower"
                    },
                    "2": {
                        "name": "Beckoning",
                        "description": "Call animals to your location.",
                        "cost": "1 Willpower"
                    },
                    "3": {
                        "name": "Animal Succulence",
                        "description": "Feed from animals without killing them.",
                        "cost": "1 Willpower"
                    }
                }
            },
            "Auspex": {
                "description": "The Discipline of Auspex enhances the vampire's senses and mental abilities.",
                "powers": {
                    "1": {
                        "name": "Heightened Senses",
                        "description": "Enhance your senses to superhuman levels.",
                        "cost": "1 Willpower"
                    },
                    "2": {
                        "name": "Aura Perception",
                        "description": "See the emotional auras of living beings.",
                        "cost": "1 Willpower"
                    },
                    "3": {
                        "name": "Spirit's Touch",
                        "description": "Read the psychic impressions left on objects.",
                        "cost": "1 Willpower"
                    }
                }
            },
            "Celerity": {
                "description": "The Discipline of Celerity allows vampires to move at superhuman speeds.",
                "powers": {
                    "1": {
                        "name": "Quickness",
                        "description": "The vampire can move and react at superhuman speeds, allowing them to perform actions much faster than normal.",
                        "cost": "1 Willpower"
                    },
                    "2": {
                        "name": "Sprint",
                        "description": "The vampire can achieve incredible bursts of speed over short distances.",
                        "cost": "1 Willpower"
                    },
                    "3": {
                        "name": "Enhanced Reflexes",
                        "description": "The vampire's reaction time becomes so fast they can dodge bullets and catch arrows in flight.",
                        "cost": "1 Willpower"
                    },
                    "4": {
                        "name": "Blur",
                        "description": "The vampire moves so fast they become a blur, making them nearly impossible to target.",
                        "cost": "1 Willpower"
                    },
                    "5": {
                        "name": "Accelerated Movement",
                        "description": "The vampire can maintain superhuman speed for extended periods.",
                        "cost": "1 Willpower"
                    }
                }
            },
            "Dominate": {
                "description": "The Discipline of Dominate allows vampires to control the minds of others.",
                "powers": {
                    "1": { "name": "Command", "description": "The vampire can issue simple, direct commands that mortals and weaker vampires must obey.", "cost": "1 Willpower" },
                    "2": { "name": "Mesmerize", "description": "The vampire can place a target in a trance-like state, making them highly suggestible.", "cost": "1 Willpower" },
                    "3": { "name": "Memory Alteration", "description": "The vampire can modify, erase, or implant false memories in a target's mind.", "cost": "1 Willpower" },
                    "4": { "name": "Suggestion", "description": "The vampire can plant subtle suggestions in a target's mind that they will act upon later.", "cost": "1 Willpower" },
                    "5": { "name": "Mental Domination", "description": "The vampire gains complete control over a target's mind, able to command them to perform any action.", "cost": "1 Willpower" }
                }
            },
            "Fortitude": {
                "description": "The Discipline of Fortitude allows vampires to resist physical damage and environmental hazards.",
                "powers": {
                    "1": { "name": "Resistance", "description": "The vampire can resist physical damage and environmental hazards better than normal.", "cost": "1 Willpower" },
                    "2": { "name": "Endurance", "description": "The vampire can maintain physical activity and resist fatigue for extended periods.", "cost": "1 Willpower" },
                    "3": { "name": "Pain Tolerance", "description": "The vampire can ignore pain and continue functioning normally even when severely injured.", "cost": "1 Willpower" },
                    "4": { "name": "Damage Reduction", "description": "The vampire can reduce the damage taken from physical attacks.", "cost": "1 Willpower" },
                    "5": { "name": "Supernatural Stamina", "description": "The vampire gains almost supernatural levels of physical resilience.", "cost": "1 Willpower" }
                }
            },
            "Obfuscate": {
                "description": "The Discipline of Obfuscate allows vampires to hide from sight and become invisible.",
                "powers": {
                    "1": { "name": "Cloak of Shadows", "description": "The vampire can blend into shadows and darkness, becoming difficult to see and track.", "cost": "1 Willpower" },
                    "2": { "name": "Vanish", "description": "The vampire can become completely invisible for short periods.", "cost": "1 Willpower" },
                    "3": { "name": "Mask of a Thousand Faces", "description": "The vampire can change their appearance to look like anyone they have seen.", "cost": "1 Willpower" },
                    "4": { "name": "Silent Movement", "description": "The vampire can move without making any sound, becoming completely silent.", "cost": "1 Willpower" },
                    "5": { "name": "Unseen Presence", "description": "The vampire can make others forget they ever saw them.", "cost": "1 Willpower" }
                }
            },
            "Potence": {
                "description": "The Discipline of Potence allows vampires to possess superhuman physical strength.",
                "powers": {
                    "1": { "name": "Prowess", "description": "The vampire gains superhuman physical strength, allowing them to perform feats far beyond mortal capabilities.", "cost": "1 Willpower" },
                    "2": { "name": "Shove", "description": "The vampire can deliver powerful shoves and pushes that can knock down or throw opponents great distances.", "cost": "1 Willpower" },
                    "3": { "name": "Knockdown", "description": "The vampire can deliver devastating blows that can knock down even the strongest opponents.", "cost": "1 Willpower" },
                    "4": { "name": "Crushing Blow", "description": "The vampire can deliver attacks so powerful they can crush through armor and break weapons.", "cost": "1 Willpower" },
                    "5": { "name": "Leap", "description": "The vampire can jump incredible distances and heights, covering great distances with a single bound.", "cost": "1 Willpower" }
                }
            },
            "Presence": {
                "description": "The Discipline of Presence allows vampires to influence and charm others through sheer presence.",
                "powers": {
                    "1": { "name": "Awe", "description": "The vampire can project an aura of majesty and power that makes others feel small and insignificant.", "cost": "1 Willpower" },
                    "2": { "name": "Dread Gaze", "description": "The vampire can project an aura of fear and intimidation that can cause others to flee or submit.", "cost": "1 Willpower" },
                    "3": { "name": "Entrancement", "description": "The vampire can charm and captivate others, making them highly susceptible to influence.", "cost": "1 Willpower" },
                    "4": { "name": "Majesty", "description": "The vampire can project an aura of divine authority that makes others feel compelled to worship them.", "cost": "1 Willpower" },
                    "5": { "name": "Inspire", "description": "The vampire can use their presence to inspire others to greatness, enhancing their abilities.", "cost": "1 Willpower" }
                }
            },
            "Protean": {
                "description": "The Discipline of Protean allows vampires to change form and shape.",
                "powers": {
                    "1": { "name": "Shape of the Beast", "description": "The vampire can transform into a wolf or bat, gaining the abilities and instincts of the chosen animal form.", "cost": "1 Willpower" },
                    "2": { "name": "Claws", "description": "The vampire can extend razor-sharp claws from their fingers, making their hands into deadly weapons.", "cost": "1 Willpower" },
                    "3": { "name": "Feral Leap", "description": "The vampire can leap incredible distances and heights, covering great distances with a single bound.", "cost": "1 Willpower" },
                    "4": { "name": "Flight (Bat Form)", "description": "The vampire can transform into a bat and gain the ability to fly.", "cost": "1 Willpower" },
                    "5": { "name": "Natural Armor", "description": "The vampire can harden their skin to create natural armor that provides protection against physical attacks.", "cost": "1 Willpower" }
                }
            },
            "Dementation": {
                "description": "The Discipline of Dementation allows vampires to drive others to madness.",
                "powers": {
                    "1": { "name": "Awe of Madness", "description": "The vampire can project an aura of madness that can cause others to become confused and disoriented.", "cost": "1 Willpower" },
                    "2": { "name": "Fear Projection", "description": "The vampire can project intense fear into the minds of others.", "cost": "1 Willpower" },
                    "3": { "name": "Confusion", "description": "The vampire can create mental confusion in others, making them unable to distinguish between reality and illusion.", "cost": "1 Willpower" },
                    "4": { "name": "Irrational Fear", "description": "The vampire can create specific, irrational fears in others.", "cost": "1 Willpower" },
                    "5": { "name": "Frenzy Inducement", "description": "The vampire can cause others to enter a state of frenzy, making them lose control.", "cost": "1 Willpower" }
                }
            },
            "Melpominee": {
                "description": "The Discipline of Melpominee lets vampires weave emotion and compulsion through supernatural song.",
                "powers": {
                    "1": { "name": "Captivating Song", "description": "Mesmerize listeners with haunting melodies that command their attention.", "cost": "1 Willpower" },
                    "2": { "name": "Siren's Lure", "description": "Project your voice to draw a chosen target toward you despite their instincts.", "cost": "1 Willpower" },
                    "3": { "name": "Discordant Chorus", "description": "Twist the mood of a gathered crowd by layering harmonics that manipulate shared emotion.", "cost": "1 Willpower" },
                    "4": { "name": "Hymn to Discord", "description": "Split your voice into counterpoint that disorients foes or bolsters allies mid-conflict.", "cost": "1 Willpower" },
                    "5": { "name": "Voice of the Siren", "description": "Resonate with a target's soul, compelling obedience or despair with a single, perfect note.", "cost": "1 Willpower" }
                }
            },
            "Serpentis": {
                "description": "The Discipline of Serpentis allows Followers of Set to transform and control through serpentine powers.",
                "powers": {
                    "1": { "name": "The Eyes of the Serpent", "description": "The vampire can hypnotize others with their gaze, causing them to become entranced and more susceptible to suggestion.", "cost": "1 Willpower" },
                    "2": { "name": "The Tongue of the Asp", "description": "The vampire can extend their tongue into a venomous serpent's tongue, capable of injecting a paralyzing or deadly venom.", "cost": "1 Willpower" },
                    "3": { "name": "The Skin of the Adder", "description": "The vampire's skin becomes scaly and tough like a snake's, providing protection and allowing them to blend with their environment.", "cost": "1 Willpower" },
                    "4": { "name": "The Form of the Cobra", "description": "The vampire can transform into a large cobra, gaining the abilities and form of a serpent while maintaining their intelligence.", "cost": "1 Willpower" },
                    "5": { "name": "The Heart of Darkness", "description": "The vampire can transform their heart into a black, venomous organ that can be used to create powerful blood magic effects or curse others.", "cost": "1 Willpower" }
                }
            }
        };
        
        this.ensureDaughterMapping();
    }
    
    ensureDaughterMapping() {
        const canonicalKey = 'Daughter of Cacophony';
        const aliasKey = 'Daughter of Cacophany';
        const mapping = ['Melpominee', 'Presence', 'Auspex'];
        
        this.clanDisciplineAccess[canonicalKey] = mapping;
        this.clanDisciplineAccess[aliasKey] = mapping;
    }
    
    resolveClanKey(clanName) {
        if (!clanName) return '';
        const normalized = clanName.trim().toLowerCase();
        
        let matchedKey = Object.keys(this.clanDisciplineAccess).find(key => key.trim().toLowerCase() === normalized);
        
        if (!matchedKey && normalized === 'daughter of cacophany') {
            matchedKey = 'Daughter of Cacophony';
            const mapping = ['Melpominee', 'Presence', 'Auspex'];
            this.clanDisciplineAccess[matchedKey] = mapping;
        }
        
        if (matchedKey && matchedKey !== clanName && !this.clanDisciplineAccess[clanName]) {
            this.clanDisciplineAccess[clanName] = this.clanDisciplineAccess[matchedKey];
        }
        
        return matchedKey || clanName;
    }
    
    getAllowedDisciplinesForClan(clanName) {
        const resolvedKey = this.resolveClanKey(clanName);
        const allowed = this.clanDisciplineAccess[resolvedKey];
        return Array.isArray(allowed) ? allowed : [];
    }
    
    /**
     * Setup event listeners for discipline selection
     */
    setupEventListeners() {
        const { eventManager } = this;
        
        // Discipline selection buttons - use document delegation since we have multiple containers
        eventManager.addDelegatedListener(document, '.discipline-option-btn', 'click', (e) => {
            this.handleDisciplineClick(e);
        });
        
        // Remove discipline buttons
        eventManager.addDelegatedListener(document, '.remove-discipline-btn', 'click', (e) => {
            this.handleRemoveDiscipline(e);
        });
        
        // Power selection buttons
        eventManager.addDelegatedListener(document, '.power-option-btn', 'click', (e) => {
            this.handlePowerClick(e);
        });
        
        // Remove power buttons
        eventManager.addDelegatedListener(document, '.remove-power-btn', 'click', (e) => {
            this.handleRemovePower(e);
        });
        
        // Discipline popover events
        eventManager.addDelegatedListener(document, '.discipline-option-btn', 'mouseover', (e) => {
            this.handleDisciplineMouseEnter(e);
        });
        
        eventManager.addDelegatedListener(document, '.discipline-option-btn', 'mouseout', (e) => {
            this.handleDisciplineMouseLeave(e);
        });
        
        // Modal close events
        eventManager.addDelegatedListener(document, '[data-action="close-discipline-guide"]', 'click', (e) => {
            this.closeDisciplineGuide();
        });
        
        // Popover close button
        eventManager.addDelegatedListener(document, '.popover-close-btn', 'click', (e) => {
            this.hidePopover();
        });
        
        // Close popover when clicking outside
        eventManager.addListener(document, 'click', (e) => {
            if (this.popoverElement && !this.popoverElement.contains(e.target)) {
                if (e.target.closest('.discipline-option-btn')) {
                    return;
                }
                this.hidePopover();
            }
        });
    }
    
    /**
     * Setup state change listeners
     */
    setupStateListeners() {
        // Listen for clan changes to update discipline availability
        this.stateManager.subscribe('clan', (newClan, oldClan) => {
            if (newClan !== oldClan) {
                this.updateClanDisciplines(newClan);
            }
        });
    }
    
    /**
     * Handle discipline selection click
     */
    handleDisciplineClick(event) {
        const button = event.target.closest('.discipline-option-btn');
        if (!button) return;
        
        const disciplineName = button.dataset.discipline;
        if (!disciplineName) return;
        
        // Block clicks on disallowed disciplines
        const state = this.stateManager.getState();
        const currentClan = this.resolveClanKey(state.clan);
        if (currentClan) {
            const allowedDisciplines = this.getAllowedDisciplinesForClan(currentClan);
            if (!allowedDisciplines.includes(disciplineName)) {
                console.warn(`DisciplineSystem: ${disciplineName} is not available to ${currentClan}`);
                return;
            }
        }
        
        event.preventDefault();
        event.stopPropagation();
        this.showPopover(disciplineName, button);
    }
    
    /**
     * Handle remove discipline click
     */
    handleRemoveDiscipline(event) {
        const button = event.target;
        const disciplineName = button.dataset.discipline;
        
        if (!disciplineName) return;
        
        this.removeDiscipline(disciplineName);
    }
    
    /**
     * Handle power selection click
     */
    handlePowerClick(event) {
        const button = event.target;
        const disciplineName = button.dataset.discipline;
        const powerLevel = button.dataset.powerLevel;
        
        if (!disciplineName || !powerLevel) return;
        
        this.selectPower(disciplineName, powerLevel);
    }
    
    /**
     * Handle remove power click
     */
    handleRemovePower(event) {
        const button = event.target;
        const disciplineName = button.dataset.discipline;
        const powerLevel = button.dataset.powerLevel;
        
        if (!disciplineName || !powerLevel) return;
        
        this.removePower(disciplineName, powerLevel);
    }
    
    /**
     * Handle discipline mouse enter (show popover)
     */
    handleDisciplineMouseEnter(event) {
        const button = event.target.closest('.discipline-option-btn');
        if (!button) return;
        
        const disciplineName = button.dataset.discipline;
        if (!disciplineName) return;
        
        const state = this.stateManager.getState();
        const currentClan = this.resolveClanKey(state.clan);
        if (currentClan) {
            const allowedDisciplines = this.getAllowedDisciplinesForClan(currentClan);
            if (!allowedDisciplines.includes(disciplineName)) {
                return;
            }
        }
        
        this.clearPopoverHideTimer();
        this.showPopover(disciplineName, button);
    }
    
    /**
     * Handle discipline mouse leave (hide popover)
     */
    handleDisciplineMouseLeave(event) {
        const button = event.target.closest('.discipline-option-btn');
        if (!button) return;
        
        const related = event.relatedTarget;
        if (related) {
            if (related === this.popoverElement) return;
            if (this.popoverElement && this.popoverElement.contains(related)) return;
            if (related.closest && related.closest('.discipline-option-btn') === button) return;
        }
        
        this.startPopoverHideTimer();
    }
    
    /**
     * Close discipline guide modal
     */
    closeDisciplineGuide() {
        const modal = this.uiManager.getElement('#disciplineGuideModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    /**
     * Select a discipline
     */
    selectDiscipline(disciplineName) {
        const state = this.stateManager.getState();
        const disciplines = [...state.disciplines];
        
        // Check if discipline is already selected
        if (disciplines.includes(disciplineName)) {
            console.warn(`DisciplineSystem: ${disciplineName} is already selected.`);
            return;
        }
        
        // Check if maximum disciplines reached (only during character creation, not editing)
        // Check both characterId and id properties (state might use either)
        const isEditing = (state.characterId && state.characterId > 0) || (state.id && state.id > 0);
        if (!isEditing && disciplines.length >= this.requirements.max) {
            console.warn(`DisciplineSystem: Maximum ${this.requirements.max} disciplines allowed during character creation.`);
            console.warn(`DisciplineSystem: Current state - characterId: ${state.characterId}, id: ${state.id}, isEditing: ${isEditing}`);
            return;
        }
        
        // Add discipline to the list
        disciplines.push(disciplineName);
        
        // Update state
        this.stateManager.setState({
            disciplines: disciplines
        });
        
        // Update displays
        this.updateDisciplineDisplay();
        this.updateDisciplineCount();
        this.updateButtonStates(disciplineName);
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Show feedback
        console.log(`DisciplineSystem: ${disciplineName} added to disciplines`);
    }
    
    /**
     * Remove a discipline
     */
    removeDiscipline(disciplineName) {
        const state = this.stateManager.getState();
        const disciplines = [...state.disciplines];
        const disciplinePowers = { ...state.disciplinePowers };
        
        // Remove discipline from the list
        const index = disciplines.indexOf(disciplineName);
        if (index > -1) {
            disciplines.splice(index, 1);
        }
        
        // Remove all powers for this discipline
        delete disciplinePowers[disciplineName];
        
        // Update state
        this.stateManager.setState({
            disciplines: disciplines,
            disciplinePowers: disciplinePowers
        });
        
        // Update displays
        this.updateDisciplineDisplay();
        this.updateDisciplineCount();
        this.updateButtonStates(disciplineName);
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Show feedback
        console.log(`DisciplineSystem: ${disciplineName} removed from disciplines`);
    }
    
    /**
     * Select a power
     */
    selectPower(disciplineName, powerLevel) {
        const state = this.stateManager.getState();
        const disciplinePowers = { ...state.disciplinePowers };
        let disciplines = [...state.disciplines];
        
        // Initialize discipline powers if not exists
        if (!disciplinePowers[disciplineName]) {
            disciplinePowers[disciplineName] = [];
        }
        
        // Check if power is already selected
        if (disciplinePowers[disciplineName].includes(powerLevel)) {
            console.warn(`DisciplineSystem: Power level ${powerLevel} is already selected for ${disciplineName}.`);
            return;
        }
        
        // Add power to the discipline
        disciplinePowers[disciplineName].push(powerLevel);
        
        // When a power is selected, ensure the discipline is in the disciplines array
        // (it might already be there from clicking the discipline button, which is fine)
        if (!disciplines.includes(disciplineName)) {
            disciplines.push(disciplineName);
        }
        
        // Update state
        this.stateManager.setState({
            disciplines: disciplines,
            disciplinePowers: disciplinePowers
        });
        
        // Update displays
        this.updateDisciplineDisplay();
        this.updateButtonStates(disciplineName);
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Show feedback
        console.log(`DisciplineSystem: Power level ${powerLevel} added to ${disciplineName}`);
    }
    
    /**
     * Remove a power
     */
    removePower(disciplineName, powerLevel) {
        const state = this.stateManager.getState();
        const disciplinePowers = { ...state.disciplinePowers };
        
        // Remove power from the discipline
        if (disciplinePowers[disciplineName]) {
            const index = disciplinePowers[disciplineName].indexOf(powerLevel);
            if (index > -1) {
                disciplinePowers[disciplineName].splice(index, 1);
            }
        }
        
        // Update state
        this.stateManager.setState({
            disciplinePowers: disciplinePowers
        });
        
        // Update displays
        this.updateDisciplineDisplay();
        this.updateButtonStates(disciplineName);
        
        // Update character preview
        this.updateCharacterPreview();
        
        // Show feedback
        console.log(`DisciplineSystem: Power level ${powerLevel} removed from ${disciplineName}`);
    }
    
    /**
     * Update discipline display
     */
    updateDisciplineDisplay() {
        const state = this.stateManager.getState();
        const disciplines = state.disciplines || [];
        const disciplinePowers = state.disciplinePowers;
        
        // Define category mappings
        const categoryMappings = {
            '#clanDisciplinesList': 'Clan',
            '#bloodSorceryList': 'BloodSorcery',
            '#advancedDisciplinesList': 'Advanced'
        };
        
        // Helper function to create discipline HTML
        // Show all disciplines that are in the state, even if they have no powers yet
        const createDisciplineHTML = (disciplinesArray) => {
            if (disciplinesArray.length === 0) return '';
            
            // Show all disciplines that are in the state (even with 0 powers)
            // This is important for editing existing characters where disciplines might exist without powers
            const disciplinesToShow = disciplinesArray.filter(disciplineName => 
                disciplines.includes(disciplineName)
            );
            
            if (disciplinesToShow.length === 0) return '';
            
            return disciplinesToShow.map(disciplineName => {
                const powers = disciplinePowers[disciplineName] || [];
                
                // If no powers, show a placeholder indicating the discipline is selected but has no powers
                let powersHTML = '';
                if (powers.length > 0) {
                    powersHTML = powers.map(level => {
                        const power = this.getPowerInfo(disciplineName, level);
                        return `
                            <div class="selected-power">
                                <span class="power-name">${disciplineName}: ${power.name}</span>
                                <button type="button" class="remove-power-btn" 
                                        data-discipline="${disciplineName}" 
                                        data-power-level="${level}">×</button>
                            </div>
                        `;
                    }).join('');
                } else {
                    // Show discipline name even without powers (for editing mode)
                    powersHTML = `
                        <div class="selected-power">
                            <span class="power-name">${disciplineName} (no powers selected)</span>
                        </div>
                    `;
                }
                
                return `
                    <div class="selected-discipline">
                        <div class="discipline-powers">
                            ${powersHTML}
                        </div>
                    </div>
                `;
            }).join('');
        };
        
        // Update each list with only its category's disciplines
        Object.entries(categoryMappings).forEach(([selector, category]) => {
            const categoryDisciplines = this.disciplineCategories[category] || [];
            // Filter disciplines by category, but also include Thaumaturgy paths if in BloodSorcery
            let filteredDisciplines = disciplines.filter(disc => categoryDisciplines.includes(disc));
            
            // Special handling for BloodSorcery: also include any discipline that starts with "Path of"
            // (Thaumaturgy paths might not be in the category list yet if API hasn't loaded)
            if (category === 'BloodSorcery') {
                const thaumaturgyPaths = disciplines.filter(disc => 
                    disc.startsWith('Path of') && !filteredDisciplines.includes(disc)
                );
                filteredDisciplines = [...filteredDisciplines, ...thaumaturgyPaths];
            }
            
            const disciplineHTML = createDisciplineHTML(filteredDisciplines);
            
            const listElement = this.uiManager.getElement(selector);
            if (listElement) {
                this.uiManager.updateContent(listElement, disciplineHTML);
            }
        });
    }
    
    /**
     * Update discipline count and progress bar
     */
    updateDisciplineCount() {
        const state = this.stateManager.getState();
        const count = state.disciplines.length;
        const requirement = this.requirements;
        
        // Update count displays for all discipline sections
        const countDisplays = [
            '#clanDisciplinesCountDisplay',
            '#bloodSorceryCountDisplay', 
            '#advancedDisciplinesCountDisplay'
        ];
        
        countDisplays.forEach(selector => {
            const countDisplay = this.uiManager.getElement(selector);
            if (countDisplay) {
                this.uiManager.updateContent(countDisplay, count.toString());
            }
        });
        
        // Update progress bars for all discipline sections
        const progressFills = [
            '#clanDisciplinesProgressFill',
            '#bloodSorceryProgressFill',
            '#advancedDisciplinesProgressFill'
        ];
        
        progressFills.forEach(selector => {
            const progressFill = this.uiManager.getElement(selector);
            if (progressFill) {
                const percentage = Math.min((count / requirement.min) * 100, 100);
                progressFill.style.width = percentage + '%';
                
                // Update progress bar class
                this.uiManager.updateClasses(progressFill, {
                    'complete': count >= requirement.min,
                    'incomplete': count < requirement.min
                });
            }
        });
        
        // Update XP display
        this.updateXPDisplay();
    }
    
    /**
     * Update button states
     */
    updateButtonStates(disciplineName) {
        const state = this.stateManager.getState();
        const isSelected = state.disciplines.includes(disciplineName);
        
        // Find the discipline button
        const button = this.uiManager.getElement(`.discipline-option-btn[data-discipline="${disciplineName}"]`);
        if (button) {
            this.uiManager.updateClasses(button, {
                'selected': isSelected
            });
        }
    }
    
    /**
     * Show discipline popover
     */
    showPopover(disciplineName, button) {
        if (!this.disciplineData || !this.disciplineData[disciplineName]) {
            console.error(`DisciplineSystem: Discipline data not found for ${disciplineName}`);
            return;
        }
        
        this.currentDiscipline = disciplineName;
        this.clearPopoverHideTimer();
        this.createPopover(disciplineName, button);
        this.currentPopoverButton = button;
        if (this.currentPopoverButton) {
            this.currentPopoverButton.classList.add('popover-target');
        }
    }
    
    /**
     * Create discipline popover
     */
    createPopover(disciplineName, button) {
        const discipline = this.disciplineData[disciplineName];
        const state = this.stateManager.getState();
        const selectedPowers = state.disciplinePowers[disciplineName] || [];
        
        // Create popover HTML
        const popoverHTML = `
            <div class="discipline-popover" id="disciplinePopover">
                <div class="popover-header">
                    <h3>${disciplineName}</h3>
                    <button type="button" class="popover-close-btn">×</button>
                </div>
                <div class="popover-content">
                    <p class="discipline-description">${discipline.description}</p>
                    <div class="powers-section">
                        <h4>Powers</h4>
                        <div class="powers-list">
                            ${Object.keys(discipline.powers).map(level => {
                                const power = discipline.powers[level];
                                const isSelected = selectedPowers.includes(level);
                                return `
                                    <div class="power-option ${isSelected ? 'selected' : ''}">
                                        <button type="button" class="power-option-btn" 
                                                data-discipline="${disciplineName}" 
                                                data-power-level="${level}"
                                                ${isSelected ? 'disabled' : ''}>
                                            ${power.name} (Level ${level})
                                        </button>
                                        <p class="power-description">${power.description}</p>
                                        <p class="power-cost">Cost: ${power.cost}</p>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing popover
        this.hidePopover();
        
        // Create new popover
        const popoverElement = this.uiManager.createElement('div');
        popoverElement.innerHTML = popoverHTML;
        this.popoverElement = popoverElement.firstElementChild;
        
        // Position popover
        this.positionPopover(button);
        
        // Add to DOM
        document.body.appendChild(this.popoverElement);
        
        // Add event listeners
        this.setupPopoverEventListeners();
    }
    
    /**
     * Position popover relative to button
     */
    positionPopover(button) {
        const rect = button.getBoundingClientRect();
        const popover = this.popoverElement;
        
        if (!popover) return;
        
        const popoverRect = popover.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        const scrollX = window.pageXOffset || document.documentElement.scrollLeft;
        const scrollY = window.pageYOffset || document.documentElement.scrollTop;
        
        const margin = 4;
        let left = rect.right + scrollX + margin;
        let top = rect.top + scrollY;
        
        if (left + popoverRect.width > viewportWidth + scrollX) {
            left = rect.left + scrollX - popoverRect.width - margin;
        }
        
        if (left < scrollX) {
            left = scrollX + margin;
        }
        
        if (top + popoverRect.height > viewportHeight + scrollY) {
            top = viewportHeight + scrollY - popoverRect.height - margin;
        }
        
        if (top < scrollY) {
            top = scrollY + margin;
        }
        
        popover.style.position = 'absolute';
        popover.style.left = `${left}px`;
        popover.style.top = `${top}px`;
        popover.style.zIndex = '1000';
    }
    
    /**
     * Setup popover event listeners
     */
    setupPopoverEventListeners() {
        if (!this.popoverElement) return;
        
        // Power selection buttons
        const powerButtons = this.popoverElement.querySelectorAll('.power-option-btn');
        powerButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                this.handlePowerClick(e);
            });
        });
        
        // Close button
        const closeButton = this.popoverElement.querySelector('.popover-close-btn');
        if (closeButton) {
            closeButton.addEventListener('click', (e) => {
                e.stopPropagation();
                this.hidePopover();
            });
        }
        
        this.popoverElement.addEventListener('mouseenter', () => {
            this.clearPopoverHideTimer();
        });

        this.popoverElement.addEventListener('mouseleave', (e) => {
            const related = e.relatedTarget;
            if (related && this.currentPopoverButton && related.closest && related.closest('.discipline-option-btn') === this.currentPopoverButton) {
                return;
            }
            this.startPopoverHideTimer();
        });
    }

    clearPopoverHideTimer() {
        if (this.popoverHideTimeout) {
            clearTimeout(this.popoverHideTimeout);
            this.popoverHideTimeout = null;
        }
    }

    startPopoverHideTimer() {
        this.clearPopoverHideTimer();
        this.popoverHideTimeout = setTimeout(() => {
            if (!this.isHoveringPopoverOrButton()) {
                this.hidePopover();
            }
        }, this.popoverHoverDelay);
    }

    isHoveringPopoverOrButton() {
        const hovered = document.querySelectorAll(':hover');
        if (!hovered || hovered.length === 0) return false;
        const hoveredArray = Array.from(hovered);
        const popover = this.popoverElement;
        const button = this.currentPopoverButton;
        return hoveredArray.some((el) => {
            if (!el) return false;
            if (popover && (el === popover || popover.contains(el))) return true;
            if (button && (el === button || button.contains(el))) return true;
            return false;
        });
    }
    
    /**
     * Hide discipline popover
     */
    hidePopover() {
        this.clearPopoverHideTimer();
        if (this.currentPopoverButton) {
            this.currentPopoverButton.classList.remove('popover-target');
        }
        if (this.popoverElement) {
            this.popoverElement.remove();
            this.popoverElement = null;
        }
        this.currentPopoverButton = null;
        this.currentDiscipline = null;
    }
    
    /**
     * Get power information
     */
    getPowerInfo(disciplineName, level) {
        if (!this.disciplineData || !this.disciplineData[disciplineName]) {
            // Discipline data not loaded yet - return fallback (will update when data loads)
            return { name: 'Unknown Power', description: 'Power not found', cost: 'Unknown' };
        }
        
        const discipline = this.disciplineData[disciplineName];
        // Convert level to string since we store powers with string keys
        const levelKey = String(level);
        const power = discipline.powers[levelKey];
        
        if (!power) {
            // Power level not found - return fallback
            return { name: 'Unknown Power', description: 'Power not found', cost: 'Unknown' };
        }
        
        return power;
    }
    
    /**
     * Update character preview
     */
    updateCharacterPreview() {
    }
    
    /**
     * Update XP display
     */
    updateXPDisplay() {
    }
    
    /**
     * Update all displays
     */
    updateAllDisplays() {
        this.updateDisciplineDisplay();
        this.updateDisciplineCount();
    }
    
    /**
     * Validate discipline selection
     */
    validateDisciplines() {
        const state = this.stateManager.getState();
        const count = state.disciplines.length;
        const errors = [];
        
        if (count < this.requirements.min) {
            errors.push(`Disciplines: ${count}/${this.requirements.min} required`);
        } else if (count > this.requirements.max) {
            errors.push(`Disciplines: ${count}/${this.requirements.max} maximum exceeded`);
        }
        
        return {
            isValid: errors.length === 0,
            errors
        };
    }
    
    /**
     * Get discipline statistics
     */
    getDisciplineStats() {
        const state = this.stateManager.getState();
        const count = state.disciplines.length;
        
        return {
            count,
            requirement: this.requirements,
            isComplete: count >= this.requirements.min,
            isOverLimit: count > this.requirements.max
        };
    }
    
    /**
     * Reset all disciplines
     */
    resetAll() {
        this.stateManager.setState({
            disciplines: [],
            disciplinePowers: {}
        });
        
        this.updateAllDisplays();
    }
    
    /**
     * Update discipline availability based on selected clan
     */
    updateClanDisciplines(selectedClan) {
        if (!selectedClan) return;
        
        // Check if this is an NPC (NPCs have unrestricted access)
        const state = this.stateManager.getState();
        const isNPC = state.playerName === 'NPC';
        
        // Don't apply clan restrictions for NPCs
        if (isNPC) {
            // Enable all discipline buttons
            const allDisciplineButtons = document.querySelectorAll('.discipline-option-btn');
            allDisciplineButtons.forEach(button => {
                button.disabled = false;
                button.classList.remove('discipline-disabled');
                button.style.opacity = '1';
                button.style.cursor = 'pointer';
                button.title = '';
            });
            return;
        }
        
        // Original clan restriction logic for new PC characters
        const resolvedClan = this.resolveClanKey(selectedClan);
        const allowedDisciplines = this.getAllowedDisciplinesForClan(resolvedClan);
        
        // Get all discipline option buttons
        const allDisciplineButtons = document.querySelectorAll('.discipline-option-btn');
        
        // Update each discipline button
        allDisciplineButtons.forEach(button => {
            const disciplineName = button.dataset.discipline;
            const isAllowed = allowedDisciplines.includes(disciplineName);
            
            if (isAllowed) {
                // Enable the button
                button.disabled = false;
                button.classList.remove('discipline-disabled');
                button.style.opacity = '1';
                button.style.cursor = 'pointer';
                button.title = ''; // Clear any tooltip
            } else {
                // Disable the button
                button.disabled = true;
                button.classList.add('discipline-disabled');
                button.style.opacity = '0.4';
                button.style.cursor = 'not-allowed';
                button.title = `${disciplineName} is not available to ${resolvedClan}`;
            }
        });
        
        // Clear any selected disciplines that the clan can't access
        this.clearInvalidDisciplines(resolvedClan, allowedDisciplines);
    }
    
    /**
     * Clear disciplines that are not available to the selected clan
     */
    clearInvalidDisciplines(selectedClan, allowedDisciplines) {
        const state = this.stateManager.getState();
        const isNPC = state.playerName === 'NPC';
        
        // Don't clear disciplines for NPCs
        if (isNPC) {
            return;
        }
        
        const resolvedClan = this.resolveClanKey(selectedClan);
        const allowedList = Array.isArray(allowedDisciplines) && allowedDisciplines.length > 0
            ? allowedDisciplines
            : this.getAllowedDisciplinesForClan(resolvedClan);
        
        const disciplines = [...state.disciplines];
        const disciplinePowers = { ...state.disciplinePowers };
        
        let removedDisciplines = [];
        
        // Remove disciplines not available to the clan
        const validDisciplines = disciplines.filter(discipline => {
            if (allowedList.includes(discipline)) {
                return true;
            } else {
                removedDisciplines.push(discipline);
                // Also remove any powers for this discipline
                delete disciplinePowers[discipline];
                return false;
            }
        });
        
        // Update state if disciplines were removed
        if (removedDisciplines.length > 0) {
            this.stateManager.setState({
                disciplines: validDisciplines,
                disciplinePowers: disciplinePowers
            });
            
            // Show notification about removed disciplines
            console.warn(`DisciplineSystem: Removed ${removedDisciplines.length} discipline(s) not available to ${resolvedClan}: ${removedDisciplines.join(', ')}`);
            
            this.updateAllDisplays();
        }
    }
}

// Export for use in other modules
window.DisciplineSystem = DisciplineSystem;
